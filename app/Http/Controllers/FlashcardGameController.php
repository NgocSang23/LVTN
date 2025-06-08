<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class FlashcardGameController extends Controller
{
    public function match($ids)
    {
        $idsArray = explode(',', $ids);

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

        // ✅ Phân trang thủ công
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
            'pairs' => $paginator, // phân trang
        ]);
    }

    public function study()
    {
        return view('user.flashcard_game.study');
    }

    public function check()
    {
        return view('user.flashcard_game.check');
    }
}
