<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Card;
use App\Models\Test;

class ApiController extends Controller
{
    public function apiStatus($data, $status_code, $total = 0, $message = null) {
        return response()->json([
            'data' => $data,
            'status_code' => $status_code,
            'total' => $total,
            'message' => $message
        ]);
    }

    public function card_define_essay($id) {
        // Tìm card theo ID, nếu không có thì trả về lỗi 404
        $card = Card::with(['question.topic', 'question.answers', 'question.images', 'user'])->findOrFail($id);

        // Lấy tất cả các card có cùng topic_id
        $relatedCards = Card::with(['question.topic', 'question.answers', 'question.images', 'user'])
            ->whereHas('question.topic', function ($query) use ($card) {
                $query->where('id', $card->question->topic->id);
            })
            ->get();

        if ($relatedCards) {
            return $this->apiStatus($relatedCards, 200, 1, 'ok');
        }
        return $this->apiStatus(null, 404, 0, 'Data not found.');
    }

    public function card_multiple_choice($id) {
        $test = Test::with([
            'user',
            'questionnumbers.topic',
            'multiplequestions.testresults.option',
        ])->findOrFail($id);

        if($test) {
            return $this->apiStatus($test, 200, 1, 'ok');
        }

        return $this->apiStatus(null, 404, 0, 'Data not found.');
    }
}
