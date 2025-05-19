<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FlashcardGameController extends Controller
{
    public function match($ids)
    {
        // Tách chuỗi các id thành mảng, ví dụ: "1,2,3" => [1, 2, 3]
        $idsArray = explode(',', $ids);

        // Truy vấn cơ sở dữ liệu để lấy các flashcard có id nằm trong $idsArray\
        $cards = Card::whereIn('id', $idsArray)
            ->with(['question', 'question.topic', 'question.answers'])
            ->get();

        Log::debug('Cards: ', $cards->toArray());

        // Tạo mảng chứa các cặp từ (pairs) từ dữ liệu các card
        $pairs = [];

        // Duyệt qua từng card đã lấy để tạo cặp từ
        foreach ($cards as $card) {
            // Lấy nội dung câu hỏi tiếng Anh
            $question = $card->question->content;

            // Lấy danh sách câu trả lời tương ứng (thường chỉ có 1 câu đúng)
            $answers = $card->question->answers;

            // Lấy câu trả lời đầu tiên (giả định là câu đúng, hoặc đơn giản hoá dữ liệu)
            $answer = $answers->first();

            // Nếu có câu trả lời thì lấy nội dung (thường là nghĩa tiếng Việt)
            $answerContent = $answer ? $answer->content : null;

            if ($answerContent) {
                $pairs[] = [
                    'question' => $question,      // Câu hỏi tiếng Anh
                    'vi' => $answerContent,       // Câu trả lời tiếng Việt
                ];
            }
        }

        if (empty($pairs)) {
            return redirect()
                ->route('user.dashboard')
                ->with('message', 'Không có cặp từ nào trong chủ đề này.');
        }

        return view('user.flashcard_game.match', compact('pairs', 'cards'));
    }

    public function check()
    {
        return view('user.flashcard_game.check');
    }

    public function study()
    {
        return view('user.flashcard_game.study');
    }
}
