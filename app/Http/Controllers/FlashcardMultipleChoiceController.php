<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\MultipleQuestion;
use App\Models\Notification;
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
use Illuminate\Support\Str;


class FlashcardMultipleChoiceController extends Controller
{
    public function create(Request $request)
    {
        $subjects = Subject::all();
        // Nếu người dùng là giáo viên thì lấy danh sách lớp học
        $myClassrooms = [];
        if (auth()->user()->roles === 'teacher') {
            $myClassrooms = ClassRoom::where('teacher_id', auth()->id())->get();
        }

        return view('user.flashcard_multiple_choice.create', compact('subjects', 'myClassrooms'));
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
            'answer.*' => 'required|min:0|max:3',
            'classroom_ids' => 'nullable|array', // ✅ Thêm dòng này
            'classroom_ids.*' => 'exists:class_rooms,id' // ✅ Kiểm tra từng id
        ]);

        if (!$request->has('multiple_question') && !$request->has('existing_question_ids')) {
            return back()->withErrors(['error' => 'Vui lòng chọn hoặc tạo ít nhất 1 câu hỏi.']);
        }

        $topic = Topic::firstOrCreate(
            [
                'title' => $data['topic_title'],
                'subject_id' => (int) $data['subject_id'], // ép int để tránh cast lỗi
            ],
            [
                'title' => $data['topic_title'],
                'subject_id' => (int) $data['subject_id'],
            ]
        );

        $test = Test::create([
            'title' => '',
            'content' => $data['test_content'],
            'time' => gmdate("H:i:s", $data['test_time'] * 60),
            'user_id' => auth()->id(),
        ]);

        if ($request->has('classroom_ids')) {
            foreach ($request->classroom_ids as $classroomId) {
                DB::table('classroom_tests')->updateOrInsert(
                    [
                        'classroom_id' => $classroomId,
                        'test_id' => $test->id,
                    ],
                    [
                        'deadline' => null, // có thể dùng $request->input('deadline') nếu có gửi từ form
                        'updated_at' => now(),
                        'created_at' => now(), // optional – sẽ không thay đổi nếu đã tồn tại
                    ]
                );
            }
        }

        // ✅ Gắn bài kiểm tra vào các lớp học nếu có
        if (!empty($data['classroom_ids'])) {
            $test->classrooms()->syncWithoutDetaching($data['classroom_ids']);

            foreach ($data['classroom_ids'] as $classroomId) {
                $classroom = ClassRoom::with('members')->find($classroomId);

                foreach ($classroom->members as $student) {
                    if ($student->id !== auth()->id()) {
                        Notification::create([
                            'user_id' => $student->id,
                            'title' => '📝 Bài kiểm tra mới trong lớp ' . $classroom->name,
                            'message' => 'Giáo viên đã chia sẻ bài kiểm tra "' . Str::limit($test->content, 50) . '". Hãy vào lớp để bắt đầu ôn luyện!',
                            'link' => route('classrooms.show', $classroomId),
                        ]);
                    }
                }
            }
        }

        // Câu hỏi có sẵn
        if ($request->has('existing_question_ids')) {
            foreach ($request->existing_question_ids as $questionId) {
                $question = MultipleQuestion::find($questionId);
                if ($question) {
                    Test_MultipleQuestion::create([
                        'test_id' => $test->id,
                        'multiplequestion_id' => $question->id
                    ]);
                }
            }
        }

        $questionNumber = QuestionNumber::create([
            'question_number' => count($data['multiple_question']),
            'test_id' => $test->id,
            'topic_id' => $topic->id
        ]);

        foreach ($data['multiple_question'] as $index => $questionContent) {
            $question = MultipleQuestion::create([
                'content' => $questionContent,
                'topic_id' => $topic->id
            ]);

            Test_MultipleQuestion::create([
                'test_id' => $test->id,
                'multiplequestion_id' => $question->id
            ]);

            foreach ($data['option_content'][$index] as $key => $optionContent) {
                $option = Option::create(['content' => $optionContent]);

                $isCorrect = in_array($key, (array) $data['answer'][$index]) ? 1 : 0;

                TestResult::create([
                    'answer' => $isCorrect,
                    'option_id' => $option->id,
                    'multiplequestion_id' => $question->id
                ]);
            }
        }

        return redirect()->route('user.dashboard')->with('success', 'Thêm bài kiểm tra thành công!');
    }

    public function show(string $id)
    {
        $test = Test::with(['user', 'classrooms'])->findOrFail($id);
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($user->roles !== 'teacher') {
            // Lấy lớp mà user đang học và có test này
            $classroom = $user->classrooms()
                ->whereHas('tests', fn($q) => $q->where('test_id', $id))
                ->first();

            if ($classroom) {
                // ❗ Lấy deadline từ bảng classroom_tests
                $pivot = DB::table('classroom_tests')
                    ->where('classroom_id', $classroom->id)
                    ->where('test_id', $id)
                    ->first();

                if ($pivot && $pivot->deadline && now()->greaterThan($pivot->deadline)) {
                    return redirect()->route('classrooms.show', $classroom->id)
                        ->with('error', 'Bài kiểm tra này đã hết hạn.');
                }
            }
        }

        return view('user.flashcard_multiple_choice.show', compact('test'));
    }

    public function edit(string $id) {}

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
        ]);

        $test = Test::findOrFail($id);
        $test->update([
            'content' => $data['test_content'],
            'time' => gmdate("H:i:s", $data['test_time'] * 60)
        ]);

        foreach ($data['multiple_question'] as $index => $questionContent) {
            $question = MultipleQuestion::find($data['question_id'][$index]);

            if ($question) {
                $question->update(['content' => $questionContent]);

                foreach ($data['option_content'][$index] as $optIndex => $optionContent) {
                    $option = Option::find($data['option_id'][$index][$optIndex]);

                    if ($option) {
                        $option->update(['content' => $optionContent]);
                    }

                    $testResult = TestResult::where('multiplequestion_id', $question->id)
                        ->where('option_id', $option->id)
                        ->first();

                    if ($testResult) {
                        $correct = (int) $data['answer'][$index][0];
                        $testResult->update(['answer' => ($correct === $optIndex) ? 1 : 0]);
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
            $query->select('id')->from('multiple_questions')->where('topic_id', $test->id);
        })->pluck('option_id');

        DB::table('test_results')->whereIn('multiplequestion_id', function ($query) use ($test) {
            $query->select('id')->from('multiple_questions')->where('topic_id', $test->id);
        })->delete();

        DB::table('test__multiple_questions')->whereIn('multiplequestion_id', function ($query) use ($test) {
            $query->select('id')->from('multiple_questions')->where('topic_id', $test->id);
        })->delete();

        DB::table('test__multiple_questions')->where('test_id', $test->id)->delete();
        DB::table('options')->whereIn('id', $optionId)->delete();
        DB::table('multiple_questions')->where('topic_id', $test->id)->delete();
        DB::table('histories')->where('test_id', $test->id)->delete();

        $test->QuestionNumbers()->delete();
        $test->delete();

        return redirect()->route('user.dashboard')->with('success', 'Xóa bài kiểm tra thành công!');
    }
}
