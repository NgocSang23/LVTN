<?php

namespace App\Http\Controllers;

use App\AI\Ochat;
use App\Models\Answer;
use App\Models\AnswerUser;
use App\Models\Card;
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
    public function index()
    {
        $card_defines = Card::with(['question.topic.subject', 'user'])
            ->where('user_id', Auth::guard('web')->user()->id)
            ->whereHas('question', function ($query) {
                $query->where('type', 'definition');
            })
            ->latest()
            ->get()
            ->filter(fn($card) => $card->question && $card->question->topic)
            ->groupBy(fn($card) => $card->question->topic->id)
            ->map(fn($group) => [
                'first_card' => $group->first(), // Card đầu tiên hiển thị
                'card_ids' => $group->pluck('id')->implode(','), // ID của tất cả cards cùng chủ đề
            ])
            ->take(6);

        $card_essays = Card::with(['question.topic.subject', 'user'])
            ->where('user_id', Auth::guard('web')->user()->id)
            ->whereHas('question', function ($query) {
                $query->where('type', 'essay');
            })
            ->latest()
            ->get()
            ->filter(fn($card) => $card->question && $card->question->topic)
            ->groupBy(fn($card) => $card->question->topic->id)
            ->map(fn($group) => [
                'first_card' => $group->first(),
                'card_ids' => $group->pluck('id')->implode(','),
            ])
            ->take(6);

        return view('user.library.define_essay', compact('card_defines', 'card_essays'));
    }

    public function create()
    {
        $subjects = Subject::all();
        return view('user.flashcard_define_essay.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'topic_title' => 'required|max:255',
            'question_type' => 'required',
            'question_content' => 'required|array',
            'question_content.*' => 'required|string',
            'answer_content' => 'required|array',
            'answer_content.*' => 'required|string',
            'image_name' => 'required|array',
            'image_name.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'subject_id.required' => 'Vui lòng chọn môn học.',
            'subject_id.exists' => 'Môn học không tồn tại.',
            'topic_title.required' => 'Vui lòng nhập tiêu đề.',
            'topic_title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'question_type.required' => 'Vui lòng chọn loại câu hỏi',
            'question_content.required' => 'Vui lòng nhập câu hỏi.',
            'question_content.*.required' => 'Vui lòng nhập nội dung cho tất cả các câu hỏi.',
            'question_content.*.string' => 'Nội dung câu hỏi phải là kiểu chuỗi.',
            'answer_content.required' => 'Vui lòng nhập câu trả lời.',
            'answer_content.*.required' => 'Vui lòng nhập nội dung cho tất cả các câu trả lời.',
            'answer_content.*.string' => 'Nội dung câu trả lời phải là kiểu chuỗi.',
            'image_name.required' => 'Vui lòng tải lên hình ảnh.',
            'image_name.*.image' => 'File tải lên phải là hình ảnh.',
            'image_name.*.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif hoặc webp.',
            'image_name.*.max' => 'Kích thước hình ảnh không được vượt quá 2048KB.',
        ]);

        // Tạo chủ đề mới nếu cần
        $topic = new Topic();
        $topic->title = $data['topic_title'];
        $topic->subject_id = $data['subject_id'];
        $topic->save();

        foreach ($request->question_content as $index => $questionContent) {
            // Tạo thẻ flashcard
            $card = new Card();
            $card->user_id = auth()->user()->id;
            $card->save();

            // Tạo câu hỏi
            $question = new Question();
            $question->content = $questionContent;
            $question->level = 1;
            $question->type = $data['question_type'];
            $question->card_id = $card->id;
            $question->topic_id = $topic->id;
            $question->save();

            // Lưu câu trả lời
            $answer = new Answer();
            $answer->content = $request->answer_content[$index];
            $answer->question_id = $question->id;
            $answer->save();

            // Nếu có hình ảnh, lưu vào thư mục và cơ sở dữ liệu
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
            $response = $chatbot->compareAnswer($question->content, $data['answeruser_content'], $correctAnswer);

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

    public function show(string $id)
    {
        // Lấy topic_id của card đang chọn
        $topicId = DB::table('questions')
            ->where('card_id', $id)
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
            ->where('questions.topic_id', $topicId) // Lọc theo topic_id
            ->get();

        return view('user.flashcard_define_essay.show', compact('cards'));
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Kiểm tra file ảnh
        ], [
            'question.required' => 'Vui lòng nhập nội dung câu hỏi',
            'answer.required' => 'Vui lòng nhập nội dung đáp án',
            'image.image' => 'Vui lòng chọn file ảnh',
        ]);

        // Cập nhật câu hỏi
        $question = Question::find($id);
        if (!$question) {
            return redirect()->back()->with('error', 'Câu hỏi không tồn tại!');
        }
        $question->content = $request->question;
        $question->save();

        // Cập nhật hoặc tạo mới câu trả lời
        $answer = Answer::where('question_id', $id)->first();
        if ($answer) {
            $answer->content = $request->answer;
            $answer->save();
        } else {
            Answer::create([
                'content' => $request->answer,
                'question_id' => $id,
            ]);
        }

        // Cập nhật hoặc thêm hình ảnh
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imageName = time() . '_' . $imageFile->getClientOriginalName();
            $imagePath = $imageFile->storeAs('images', $imageName, 'public');

            $image = Image::where('question_id', $id)->first();
            if ($image) {
                // Xóa ảnh cũ nếu có
                if ($image->path) {
                    Storage::disk('public')->delete($image->path);
                }
                $image->path = $imagePath;
                $image->name = $imageName;
                $image->save();
            } else {
                Image::create([
                    'question_id' => $id,
                    'path' => $imagePath,
                    'name' => $imageName
                ]);
            }
        }

        return redirect()->back()->with('success', 'Cập nhật câu hỏi thành công!');
    }

    public function destroy(string $id)
    {
        $question = Question::find($id);

        if (!$question) {
            return redirect()->back()->with('error', 'Không tìm thấy câu hỏi!');
        }
        $card_id = $question->card_id;

        $question->images()->delete();
        $question->answers()->delete();

        $answerUser = AnswerUser::where('question_id', $question->id)->first();
        if ($answerUser) {
            $answerUser->delete();
        }

        $question->delete();

        // Kiểm tra nếu thẻ không còn câu hỏi nào, thì xóa luôn thẻ
        $remainingQuestions = Question::where('card_id', $card_id)->count();
        if ($remainingQuestions == 0) {
            Card::where('id', $card_id)->delete();
        }

        return redirect()->route('user.dashboard')->with('success', 'Xóa câu hỏi thành công!');
    }

    public function destroyAll(string $card_id)
    {
        // Tìm thẻ theo card_id
        $card = Card::find($card_id);
        if (!$card) {
            return redirect()->back()->with('error', 'Không tìm thấy thẻ!');
        }

        // Lấy tất cả câu hỏi của thẻ này
        $questions = Question::where('card_id', $card_id)->get();

        foreach ($questions as $question) {
            // Xóa các ảnh liên quan
            $question->images()->delete();

            // Xóa các câu trả lời
            $question->answers()->delete();

            // Xóa câu trả lời người dùng nếu có
            $answerUser = AnswerUser::where('question_id', $question->id)->first();
            if ($answerUser) {
                $answerUser->delete();
            }

            // Xóa câu hỏi
            $question->delete();
        }

        // Sau khi xóa hết câu hỏi, xóa thẻ
        $card->delete();

        return redirect()->route('user.dashboard')->with('success', 'Xóa toàn bộ câu hỏi và thẻ thành công!');
    }
}
