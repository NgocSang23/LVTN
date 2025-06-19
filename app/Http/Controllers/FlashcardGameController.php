<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
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
            ->get();

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

    public function check(Request $request)
    {
        // Giải mã chuỗi ID được mã hóa base64 gửi từ client (chuỗi ID các flashcard)
        $decodedIds = base64_decode($request->input('ids'));

        // Nếu không giải mã được (có thể do lỗi hoặc chuỗi rỗng), quay lại dashboard với thông báo lỗi
        if (!$decodedIds) {
            return redirect()->route('user.dashboard')->with('message', 'Liên kết không hợp lệ.');
        }

        // Chuyển chuỗi ID thành mảng (danh sách các ID flashcard)
        $idsArray = explode(',', $decodedIds);

        // Lấy các loại câu hỏi được người dùng chọn từ modal (mặc định là 'mcq' - trắc nghiệm)
        $selectedTypes = $request->input('selected_types', ['mcq']);

        // Số lượng câu hỏi muốn lấy (giới hạn), mặc định là 20 nếu người dùng không chọn
        $questionLimit = (int)$request->input('question_limit', 20);

        // Lấy các flashcard theo ID, cùng với thông tin câu hỏi, chủ đề và đáp án liên quan
        $cards = Card::whereIn('id', $idsArray)
            ->with(['question.topic', 'question.answers']) // eager load để tránh truy vấn lặp
            ->whereHas('question', function ($q) use ($selectedTypes) {
                // Chỉ lấy những flashcard có câu hỏi thuộc loại người dùng đã chọn (MCQ, Essay, True/False)
                $q->whereIn('type', $selectedTypes);
            })
            ->inRandomOrder() // Sắp xếp ngẫu nhiên
            ->limit($questionLimit) // Giới hạn số lượng câu hỏi theo yêu cầu
            ->get();

        // Mảng chứa dữ liệu từng câu hỏi sẽ được truyền sang view
        $quizData = [];

        // Duyệt qua từng flashcard để xây dựng dữ liệu kiểm tra
        foreach ($cards as $card) {
            $question = $card->question; // Lấy câu hỏi từ flashcard

            // Bỏ qua nếu không có câu hỏi hoặc không có đáp án nào
            if (!$question || $question->answers->isEmpty()) {
                continue;
            }

            // Đáp án đúng là đáp án đầu tiên (quy ước trong hệ thống)
            $correctAnswer = $question->answers->first();

            $answersToShow = null; // Mảng chứa các đáp án sẽ hiển thị cho người dùng

            // Nếu là câu trắc nghiệm thì tạo danh sách gồm 1 đáp án đúng + 3 sai (ngẫu nhiên)
            if ($question->type === 'mcq') {
                // Lấy 3 đáp án sai từ các câu hỏi khác nhưng cùng chủ đề
                $wrongAnswers = Answer::whereHas('question', function ($q) use ($question) {
                    $q->where('topic_id', $question->topic_id) // cùng chủ đề
                        ->where('id', '!=', $question->id);       // khác câu hiện tại
                })
                    ->inRandomOrder()
                    ->limit(3)
                    ->get();

                // Nếu không đủ 3 đáp án sai → bỏ qua câu này
                if ($wrongAnswers->count() < 3) continue;

                // Gộp đáp án đúng + sai, rồi trộn ngẫu nhiên
                $answersToShow = collect([$correctAnswer])
                    ->merge($wrongAnswers)
                    ->shuffle();
            } else {
                // Nếu là dạng khác (đúng/sai hoặc tự luận), chỉ cần hiển thị danh sách đáp án gốc
                $answersToShow = collect($question->answers);
            }

            // Đưa vào mảng dữ liệu để truyền sang view
            $quizData[] = [
                'question' => $question->content, // nội dung câu hỏi
                'type' => $question->type,        // loại câu hỏi (mcq, true_false, essay)
                'correct_answer_id' => $correctAnswer->id, // ID của đáp án đúng (dùng để kiểm tra)
                'answers' => $answersToShow->map(function ($answer) {
                    return [
                        'id' => $answer->id,
                        'content' => $answer->content, // nội dung của từng đáp án
                    ];
                })->values() // loại bỏ key không cần thiết
            ];
        }

        // Trả về view hiển thị bài kiểm tra, truyền dữ liệu câu hỏi + danh sách ID
        return view('user.flashcard_game.check', [
            'quizData' => $quizData,
            'idsArray' => $idsArray
        ]);
    }

    public function flashcard()
    {
        return view('user.flashcard_game.flashcard');
    }
}
