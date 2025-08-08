<?php

namespace App\Http\Controllers;

use App\AI\Ochat;
use App\Models\Answer;
use App\Models\AnswerUser;
use App\Models\Card;
use App\Models\ClassRoom;
use App\Models\ClassroomFlashcard;
use App\Models\DifficultCard;
use App\Models\FlashcardSet;
use App\Models\Image;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use OpenAI;

class FlashcardDefineEssayController extends Controller
{
    public function create()
    {
        $subjects = Subject::all();

        // Nếu người dùng là giáo viên thì lấy danh sách lớp học
        $myClassrooms = [];
        if (auth()->user()->roles === 'teacher') {
            $myClassrooms = ClassRoom::where('teacher_id', auth()->id())->get();
        }

        return view('user.flashcard_define_essay.create', compact('subjects', 'myClassrooms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'topic_title' => 'required|max:255',
            'question_content' => 'required|array',
            'question_content.*' => 'required|string',
            'answer_content' => 'required|array',
            'answer_content.*' => 'required|string',
            'image_name' => 'required|array',
            'image_name.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'classroom_id' => 'nullable|exists:class_rooms,id',
        ], [
            'subject_id.required' => 'Vui lòng chọn môn học.',
            'subject_id.exists' => 'Môn học không tồn tại.',
            'topic_title.required' => 'Vui lòng nhập tiêu đề.',
            'topic_title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'question_content.required' => 'Vui lòng nhập câu hỏi.',
            'question_content.*.required' => 'Vui lòng nhập nội dung cho tất cả các câu hỏi.',
            'question_content.*.string' => 'Nội dung câu hỏi phải là kiểu chuỗi.',
            'answer_content.required' => 'Vui lòng nhập câu trả lời.',
            'answer_content.*.required' => 'Vui lòng nhập nội dung cho tất cả các câu trả lời.',
            'answer_content.*.string' => 'Nội dung câu trả lời phải là kiểu chuỗi.',
            'image_name.required' => 'Vui lòng chọn ảnh cho mỗi câu hỏi.',
            'image_name.*.required' => 'Mỗi câu hỏi đều cần có một ảnh.',
            'image_name.*.image' => 'File tải lên phải là hình ảnh.',
            'image_name.*.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif hoặc webp.',
            'image_name.*.max' => 'Kích thước hình ảnh không được vượt quá 2048KB.',
            'classroom_id.exists' => 'Lớp học không tồn tại.',
        ]);

        $topic = new Topic();
        $topic->title = $data['topic_title'];
        $topic->subject_id = $data['subject_id'];
        $topic->save();

        $flashcardSet = new FlashcardSet();
        $flashcardSet->title = $data['topic_title'];
        $flashcardSet->description = 'Tự động tạo từ form giáo viên';
        $flashcardSet->user_id = auth()->id();
        $flashcardSet->save();

        if (!empty($data['classroom_id'])) {
            $exists = ClassroomFlashcard::where('classroom_id', $data['classroom_id'])
                ->where('flashcard_set_id', $flashcardSet->id)
                ->exists();

            if (!$exists) {
                ClassroomFlashcard::create([
                    'classroom_id' => $data['classroom_id'],
                    'flashcard_set_id' => $flashcardSet->id,
                    'user_id' => auth()->id(),
                ]);
            }
        }

        $questionIds = [];

        foreach ($request->question_content as $index => $questionContent) {
            $card = new Card();
            $card->user_id = auth()->id();
            $card->save();

            $question = new Question();
            $question->content = $questionContent;
            $question->level = 1;
            $question->type = "definition";
            $question->card_id = $card->id;
            $question->topic_id = $topic->id;
            $question->save();

            $questionIds[] = $question->id;

            $answer = new Answer();
            $answer->content = $request->answer_content[$index];
            $answer->question_id = $question->id;
            $answer->save();

            if ($request->hasFile("image_name.$index")) {
                $image = $request->file("image_name.$index");
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('images', $imageName, 'public');

                $hinhanh = new Image();
                $hinhanh->name = $imageName;
                $hinhanh->path = $imagePath;
                $hinhanh->question_id = $question->id;
                $hinhanh->save();
            }
        }

        $flashcardSet->question_ids = implode(',', $questionIds);
        $flashcardSet->save();

        return redirect()->route('user.dashboard')->with('success', 'Thêm thẻ thành công!');
    }

    public function storeUserAnswerDefine(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
        ]);

        if (!auth()->check()) {
            return response()->json(['message' => 'Người dùng chưa đăng nhập'], 401);
        }

        $userId = auth()->user()->id;
        $questionId = $request->question_id;

        // Kiểm tra nếu đã tồn tại thì không thêm nữa
        $exist = AnswerUser::where('user_id', $userId)
            ->where('question_id', $questionId)
            ->first();

        if (!$exist) {
            $answerUser = new AnswerUser();
            $answerUser->user_id = $userId;
            $answerUser->question_id = $questionId;
            $answerUser->save();
        }

        return response()->json(['message' => 'Đã lưu thành công']);
    }

    public function storeUserAnswer(Request $request)
    {
        try {
            $data = $request->validate([
                'question_id' => 'exists:questions,id',
                'answeruser_content' => 'required|string',
            ], [
                'question_id.exists' => 'Câu hỏi không tồn tại',
                'answeruser_content.required' => 'Xin nhập câu trả lời'
            ]);

            $answerUser = new AnswerUser();
            $answerUser->content = $data['answeruser_content'];
            $answerUser->question_id = $data['question_id'];
            $answerUser->user_id = Auth::guard('web')->user()->id;
            $answerUser->save();

            $question = Question::with('answers')->find($data['question_id']);

            if (!$question) {
                return response()->json(['error' => 'Câu hỏi không hợp lệ'], 400);
            }

            $correctAnswer = $question->answers->first()->content ?? 'Không có đáp án';

            $chatbot = new Ochat();
            $start = microtime(true); // ⏱️ Bắt đầu đo thời gian

            $response = $chatbot->compareAnswer(
                $question->content,
                $data['answeruser_content'],
                $correctAnswer
            );

            $end = microtime(true); // ⏱️ Kết thúc đo thời gian
            Log::info("⏱️ Thời gian xử lý AI:", ['seconds' => round($end - $start, 3)]);

            Log::info("Gửi tới AI:", [
                'question' => $question->content,
                'user_answer' => $data['answeruser_content'],
                'correct_answer' => $correctAnswer
            ]);

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error("Lỗi xử lý câu trả lời: " . $e->getMessage());
            return response()->json(['error' => 'Lỗi máy chủ', 'message' => $e->getMessage()], 500);
        }
    }

    public function show(string $encodedIds)
    {
        // Giải mã danh sách ID
        $decoded = base64_decode($encodedIds);
        $cardIds = explode(',', $decoded);

        // dd($encodedIds, $decoded, $cardIds);

        if (empty($cardIds) || !is_array($cardIds)) {
            abort(404, 'Danh sách thẻ không hợp lệ.');
        }

        // Lấy topic_id của card đầu tiên
        $firstCardId = $cardIds[0];

        $topicId = DB::table('questions')
            ->where('card_id', $firstCardId)
            ->value('topic_id');

        if (!$topicId) {
            abort(404, 'Card không tồn tại hoặc không có topic.');
        }

        // Lấy tất cả các cards có cùng topic_id
        $cards = DB::table('cards')
            ->join('questions', 'cards.id', '=', 'questions.card_id')
            ->join('users', 'cards.user_id', '=', 'users.id')
            ->join('topics', 'questions.topic_id', '=', 'topics.id')
            ->select('cards.*', 'questions.*')
            ->where('questions.topic_id', $topicId)
            ->get();

        // Câu hỏi đầu tiên để hiển thị
        $question = Question::where('card_id', $firstCardId)->first();
        if (!$question) {
            abort(404, 'Câu hỏi không tồn tại.');
        }

        return view('user.flashcard_define_essay.show', compact('cards', 'question'));
    }

    public function update(Request $request, string $card_id)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'question.required' => 'Vui lòng nhập nội dung câu hỏi',
            'answer.required' => 'Vui lòng nhập nội dung đáp án',
            'image.image' => 'Vui lòng chọn file ảnh hợp lệ',
        ]);

        // Lấy câu hỏi đầu tiên thuộc card_id
        $question = Question::where('card_id', $card_id)->first();

        if (!$question) {
            return redirect()->back()->with('error', 'Không tìm thấy câu hỏi!');
        }

        // Cập nhật nội dung
        $question->content = $request->question;
        $question->save();

        // Cập nhật hoặc tạo mới đáp án
        $answer = Answer::where('question_id', $question->id)->first();
        if ($answer) {
            $answer->content = $request->answer;
            $answer->save();
        } else {
            Answer::create([
                'content' => $request->answer,
                'question_id' => $question->id,
            ]);
        }

        // Cập nhật ảnh
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imageName = time() . '_' . $imageFile->getClientOriginalName();
            $imagePath = $imageFile->storeAs('images', $imageName, 'public');

            $image = Image::where('question_id', $question->id)->first();
            if ($image) {
                if ($image->path && Storage::disk('public')->exists($image->path)) {
                    Storage::disk('public')->delete($image->path);
                }
                $image->update([
                    'path' => $imagePath,
                    'name' => $imageName,
                ]);
            } else {
                Image::create([
                    'question_id' => $question->id,
                    'path' => $imagePath,
                    'name' => $imageName,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Cập nhật câu hỏi thành công!');
    }

    public function destroy(string $card_id)
    {
        $question = Question::where('card_id', $card_id)->first();

        if (!$question) {
            return redirect()->route('user.dashboard')->with('error', 'Không tìm thấy câu hỏi!');
        }

        // Lưu topic_id để redirect sau khi xóa
        $topicId = $question->topic_id;

        // Xoá dữ liệu liên quan
        DifficultCard::where('question_id', $question->id)->delete();
        AnswerUser::where('question_id', $question->id)->delete();

        foreach ($question->images as $image) {
            if ($image->path && Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
            $image->delete();
        }

        $question->answers()->delete();
        $question->delete();

        $remainingQuestions = Question::where('card_id', $card_id)->count();
        if ($remainingQuestions == 0) {
            Card::where('id', $card_id)->delete();
        }

        // Tìm các card còn lại trong topic này
        $remainingCardIds = Question::where('topic_id', $topicId)
            ->pluck('card_id')
            ->unique()
            ->toArray();

        if (count($remainingCardIds) > 0) {
            $encoded = base64_encode(implode(',', $remainingCardIds));
            return redirect()->route('user.flashcard_define_essay', ['ids' => $encoded])
                ->with('success', 'Xóa câu hỏi thành công!');
        } else {
            return redirect()->route('user.dashboard')->with('success', 'Xóa toàn bộ câu hỏi trong chủ đề.');
        }
    }
}
