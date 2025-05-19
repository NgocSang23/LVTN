<?php

namespace App\Http\Controllers;

use App\Models\MultipleQuestion;
use App\Models\Option;
use App\Models\QuestionNumber;
use App\Models\Subject;
use App\Models\Test;
use App\Models\Topic;
use App\Models\Test_MultipleQuestion;
use App\Models\TestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FlashcardMultipleChoiceController extends Controller
{
    public function index()
    {
        $tests = Test::with(['questionnumbers.topic', 'user'])
                ->where('user_id', Auth::guard('web')->user()->id)
                ->latest()
                ->get()
                ->take(6);
        return view('user.library.multiple', compact('tests'));
    }

    public function create()
    {
        $subjects = Subject::all();
        return view('user.flashcard_multiple_choice.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'test_content' => 'required',
            'test_time' => 'required',
            'subject_id' => 'required|exists:subjects,id',
            'topic_title' => 'required|max:255',
            'multiple_question' => 'required|array',
            'multiple_question.*' => 'required|string',
            'option_content' => 'required|array',
            'option_content.*' => 'required|array',
            'answer' => 'required|array',
            'answer.*' => 'required|min:0|max:3'

        ], [
            'test_content.required' => 'Vui lòng nhập nội dung bài kiểm tra',
            'test_time.required' => 'Vui lòng nhập thời gian kiểm tra',
            'subject_id.required' => 'Vui lòng chọn môn học',
            'subject_id.exists' => 'Môn học không tồn tại',
            'topic_title.required' => 'Vui lòng nhập chủ đề',
            'multiple_question.required' => 'Vui lòng nhập nội dung câu hỏi',
            'multiple_question.*.required' => 'Vui lòng nhập nội dung cho tất cả câu hỏi',
            'option_content.required' => 'Vui lòng nhập các đáp án',
            'option_content.*.required' => 'Vui lòng nhập tất cả các đáp án',
            'answer.required' => 'Vui lòng chọn đáp án đúng',
            'answer.*.required' => 'Vui lòng chọn đáp án đúng cho tất cả câu hỏi'
        ]);

        // Tạo chủ đề
        $topic = new Topic();
        $topic->title = $data['topic_title'];
        $topic->subject_id = $data['subject_id'];
        $topic->save();

        // Tạo bài kiểm tra
        $test = new Test();
        $test->title = "";
        $test->content = $data['test_content'];
        $test->time = gmdate("H:i:s", $data['test_time'] * 60);
        $test->user_id = auth()->user()->id;
        $test->save();

        // Tạo số câu hỏi
        $questionNumber = new QuestionNumber();
        $questionNumber->question_number = count($data['multiple_question']);
        $questionNumber->test_id = $test->id;
        $questionNumber->topic_id = $topic->id;
        $questionNumber->save();

        foreach ($data['multiple_question'] as $index => $questionContent) {
            // Tạo câu hỏi
            $question = new MultipleQuestion();
            $question->content = $questionContent;
            $question->topic_id = $topic->id;
            $question->save();

            $test_multipleQuestion = new Test_MultipleQuestion();
            $test_multipleQuestion->test_id = $test->id;
            $test_multipleQuestion->multiplequestion_id = $question->id;
            $test_multipleQuestion->save();

            // Kiểm tra nếu option_content tồn tại
            if (!isset($data['option_content'][$index]) || !is_array($data['option_content'][$index])) {
                Log::error("option_content bị thiếu hoặc không hợp lệ", ['index' => $index, 'data' => $data]);
                continue;
            }

            foreach ($data['option_content'][$index] as $key => $optionContent) {
                // Tạo option
                $option = new Option();
                $option->content = $optionContent;
                $option->save();

                // Xác định đáp án đúng
                $isCorrect = (isset($data['answer'][$index]) && in_array($key, (array) $data['answer'][$index])) ? 1 : 0;

                // Lưu vào test_results
                $testResult = new TestResult();
                $testResult->answer = $isCorrect;
                $testResult->option_id = $option->id;
                $testResult->multiplequestion_id = $question->id;
                $testResult->save();
            }
        }

        return redirect()->route('user.dashboard')->with('success', 'Thêm bài kiểm tra thành công!');
    }

    public function show(string $id)
    {
        $test = Test::with(['user'])->findOrFail($id);
        return view('user.flashcard_multiple_choice.show', compact('test'));
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'test_content' => 'required',
            'test_time' => 'required|integer|min:1',
            'multiple_question' => 'required|array',
            'multiple_question.*' => 'required|string',
            'question_id' => 'required|array',
            'option_id' => 'required|array',
            'option_content' => 'required|array',
            'option_content.*' => 'required|array|size:4',
            'answer' => 'required|array',
            'answer.*' => 'required|in:0,1,2,3'
        ], [
            'test_content.required' => 'Vui lòng nhập nội dung bài kiểm tra',
            'test_time.required' => 'Vui lòng nhập thời gian kiểm tra',
            'multiple_question.required' => 'Vui lòng nhập nội dung câu hỏi',
            'multiple_question.*.required' => 'Vui lòng nhập nội dung cho tất cả câu hỏi',
            'option_content.required' => 'Vui lòng nhập các đáp án',
            'option_content.*.required' => 'Vui lòng nhập đầy đủ 4 đáp án cho mỗi câu hỏi',
            'answer.required' => 'Vui lòng chọn đáp án đúng',
            'answer.*.required' => 'Vui lòng chọn 1 đáp án đúng cho mỗi câu hỏi',
            'answer.*.in' => 'Đáp án phải nằm trong các phương án A, B, C, D'
        ]);


        // Tìm bài kiểm tra theo ID
        $test = Test::findOrFail($id);

        // Cập nhật nội dung và thời gian cho bài kiểm tra
        $test->content = $data['test_content'];
        $test->time = gmdate("H:i:s", $data['test_time'] * 60); // Chuyển phút sang định dạng H:i:s
        $test->save();

        // Lặp qua từng câu hỏi để cập nhật
        foreach ($data['multiple_question'] as $index => $questionContent) {
            // Tìm câu hỏi theo ID
            $question = MultipleQuestion::find($data['question_id'][$index]);

            if ($question) {
                $question->content = $questionContent;
                $question->save();

                // Lặp qua 4 option của câu hỏi
                foreach ($data['option_content'][$index] as $optIndex => $optionContent) {
                    // Tìm option theo ID
                    $option = Option::find($data['option_id'][$index][$optIndex]);

                    if ($option) {
                        $option->content = $optionContent;
                        $option->save();
                    }

                    // Tìm và cập nhật đáp án đúng/sai cho câu hỏi
                    $testResult = TestResult::where('multiplequestion_id', $question->id)
                        ->where('option_id', $option->id)
                        ->first();

                    if ($testResult) {
                        // answer[0] là array chứa vị trí đáp án đúng (ví dụ [2] nghĩa là option thứ 3 đúng)
                        $correct = (int) $data['answer'][$index][0];
                        $testResult->answer = ($correct === $optIndex) ? 1 : 0;
                        $testResult->save();
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Cập nhật bài kiểm tra thành công!');
    }

    public function destroy(string $id)
    {
        $test = Test::find($id);

        if (!$test) {
            return redirect()->back()->with('error', 'Không tìm thấy bài kiểm tra nào!');
        }

        $optionId = DB::table('test_results')->whereIn('multiplequestion_id', function ($query) use ($test) {
            $query->select('id')
                ->from('multiple_questions')
                ->where('topic_id', $test->id);
        })->pluck('option_id');

        // Xóa câu trả lời trong `test_results` trước
        DB::table('test_results')->whereIn('multiplequestion_id', function ($query) use ($test) {
            $query->select('id')
                ->from('multiple_questions')
                ->where('topic_id', $test->id);
        })->delete();

        // Xóa bảng trung gian `test__multiple_questions` trước khi xóa câu hỏi
        DB::table('test__multiple_questions')->whereIn('multiplequestion_id', function ($query) use ($test) {
            $query->select('id')
                ->from('multiple_questions')
                ->where('topic_id', $test->id);
        })->delete();

        DB::table('test__multiple_questions')->where('test_id', $test->id)->delete();

        // Xóa các phương án (options) liên quan
        DB::table('options')->whereIn('id', $optionId)->delete();

        // Xóa câu hỏi trong `multiple_questions`
        DB::table('multiple_questions')->where('topic_id', $test->id)->delete();

        // Xóa quan hệ số câu hỏi
        $test->QuestionNumbers()->delete();

        // Xóa bài kiểm tra
        $test->delete();

        return redirect()->route('user.dashboard')->with('success', 'Xóa bài kiểm tra thành công!');
    }
}
