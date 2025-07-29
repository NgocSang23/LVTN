<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class FlashcardGameController extends Controller
{
    public function match($ids)
    {
        // ✅ Giải mã danh sách ID
        $decodedIds = base64_decode($ids);

        // Nếu không đúng định dạng hoặc không giải mã được thì redirect về dashboard
        if (!$decodedIds) {
            return redirect()->route('user.dashboard')->with('message', 'Liên kết không hợp lệ.');
        }

        $idsArray = explode(',', $decodedIds);

        $cards = Card::whereIn('id', $idsArray)
            ->with(['question', 'question.topic', 'question.answers'])
            ->get()
            ->shuffle();

        $pairs = [];

        foreach ($cards as $card) {
            $question = $card->question->content;
            $answer = $card->question->answers->first();

            if ($answer) {
                $pairs[] = [
                    'question' => $question,
                    'vi' => $answer->content,
                ];
            }
        }

        if (empty($pairs)) {
            return redirect()
                ->route('user.dashboard')
                ->with('message', 'Không có cặp từ nào trong chủ đề này.');
        }

        // ✅ Phân trang
        $perPage = 6;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($pairs, ($currentPage - 1) * $perPage, $perPage);

        $paginator = new LengthAwarePaginator(
            $currentItems,
            count($pairs),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('user.flashcard_game.match', [
            'pairs' => $paginator,
            'idsArray' => $idsArray
        ]);
    }

    public function study()
    {
        // Giải mã chuỗi base64 để lấy danh sách ID các flashcard (được mã hoá từ client)
        $decodedIds = base64_decode(request('ids'));

        // Nếu không có dữ liệu sau khi decode, quay về trang chính với thông báo lỗi
        if (!$decodedIds) {
            return redirect()->route('user.dashboard')->with('message', 'Liên kết không hợp lệ.');
        }

        // Tách danh sách ID thành mảng
        $idsArray = explode(',', $decodedIds);

        // Lấy các flashcard có ID nằm trong danh sách, kèm theo câu hỏi, chủ đề và các đáp án
        $cards = Card::whereIn('id', $idsArray)
            ->with(['question.topic', 'question.answers']) // Eager load câu hỏi, chủ đề, đáp án
            ->isRandomOrder() // Sắp xếp ngẫu nhiên (custom scope)
            ->get();

        $quizData = []; // Mảng lưu dữ liệu câu hỏi để truyền sang view

        foreach ($cards as $card) {
            $question = $card->question;

            // Bỏ qua nếu không có câu hỏi hoặc không có đáp án nào
            if (!$question || $question->answers->isEmpty()) {
                continue;
            }

            // Lấy đáp án đúng (được lưu đầu tiên)
            $correctAnswer = $question->answers->first();

            // Lấy ngẫu nhiên 3 đáp án sai từ các câu hỏi khác nhưng cùng chủ đề
            $wrongAnswers = Answer::whereHas('question', function ($q) use ($question) {
                $q->where('topic_id', $question->topic_id) // Cùng chủ đề
                    ->where('id', '!=', $question->id);     // Khác câu hỏi hiện tại
            })
                ->inRandomOrder()
                ->limit(3)
                ->get();

            // Nếu không đủ 3 đáp án sai thì bỏ qua câu hỏi này
            if ($wrongAnswers->count() < 3) {
                continue;
            }

            // Trộn 1 đáp án đúng + 3 sai thành 1 mảng ngẫu nhiên
            $answersToShow = collect([$correctAnswer])->merge($wrongAnswers)->shuffle();

            // Ghi vào dữ liệu để truyền ra view
            $quizData[] = [
                'question' => $question->content,
                'correct_answer_id' => $correctAnswer->id,
                'answers' => $answersToShow->map(function ($answer) {
                    return [
                        'id' => $answer->id,
                        'content' => $answer->content,
                    ];
                })->values()
            ];
        }

        // Truyền dữ liệu ra view để hiển thị flashcard
        return view('user.flashcard_game.study', [
            'quizData' => $quizData,
            'idsArray' => $idsArray
        ]);
    }

    public function fillBlanks($ids, Request $request)
    {
        $decodedIds = base64_decode($ids);
        if (!$decodedIds) {
            return redirect()->route('user.dashboard')->with('message', 'Liên kết không hợp lệ.');
        }

        $idsArray = explode(',', $decodedIds);
        $questionLimit = (int) $request->input('limit', 10);

        $cards = Card::whereIn('id', $idsArray)
            ->with(['question.answers'])
            ->inRandomOrder()
            ->get();

        $quizData = collect();

        foreach ($cards as $card) {
            $question = $card->question;
            $answers = $question?->answers;

            if (!$question || !$answers || $answers->isEmpty()) continue;

            $answerText = trim($answers->first()->content);

            if (stripos($answerText, ' là ') !== false) {
                [$subject, $rest] = explode(' là ', $answerText, 2);
                $questionWithBlank = "___ là " . $rest;
                $displayQuestion = ucfirst($subject) . " là gì?";

                $quizData->push([
                    'question' => $questionWithBlank,
                    'type' => 'fill_blank',
                    'correct_answer_text' => $subject,
                    'display_question' => $displayQuestion,
                ]);
                continue;
            }

            $words = preg_split('/\s+/', $answerText);
            if (count($words) < 3) continue;

            $indexToBlank = rand(0, count($words) - 1);
            $correctAnswer = $words[$indexToBlank];
            $words[$indexToBlank] = '___';
            $questionWithBlank = implode(' ', $words);

            $quizData->push([
                'question' => $questionWithBlank,
                'type' => 'fill_blank',
                'correct_answer_text' => $correctAnswer,
                'display_question' => 'Điền vào chỗ trống:',
            ]);
        }

        $finalQuiz = $quizData->shuffle()->take($questionLimit)->values();

        return view('user.flashcard_game.fill_blank', [
            'quizData' => $finalQuiz,
            'idsArray' => $idsArray,
            'questionCount' => $finalQuiz->count()
        ]);
    }

    public function flashcard(Request $request, $ids = null)
    {
        if (!$ids) {
            return redirect()->route('user.dashboard')->with('message', 'Thiếu dữ liệu flashcard.');
        }

        $idsArray = explode(',', base64_decode($ids));
        $flashcards = Card::whereIn('id', $idsArray)
            ->with(['question', 'question.answers', 'question.images']) // để có content và answer
            ->get()
            ->shuffle();

        return view('user.flashcard_game.flashcard', [
            'flashcards' => $flashcards,
            'idsArray' => $idsArray,
        ]);
    }

    public function essay(Request $request)
    {
        $ids = base64_decode($request->ids); // giải mã danh sách ID flashcard từ query string
        $idsArray = explode(',', $ids);

        // Lấy danh sách flashcard theo ID
        $flashcards = Card::with(['question.answers', 'question.images'])
            ->whereIn('id', $idsArray)
            ->get()
            ->shuffle();

        if ($flashcards->isEmpty()) {
            return redirect()->route('user.dashboard')->with('error', 'Không tìm thấy flashcard.');
        }

        return view('user.flashcard_game.essay', [
            'flashcards' => $flashcards,
            'idsArray' => $idsArray,
        ]);
    }
}
