<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Card;
use App\Models\Test;

class ApiController extends Controller
{
    public function apiStatus($data, $status_code, $total = 0, $message = null)
    {
        return response()->json([
            'data' => $data,
            'status_code' => $status_code,
            'total' => $total,
            'message' => $message
        ]);
    }

    public function card_define_essay($encodedIds)
    {
        // Giải mã base64
        $decoded = base64_decode($encodedIds);
        $cardIds = explode(',', $decoded);

        if (empty($cardIds)) {
            return $this->apiStatus([], 404, 0, 'Không có ID hợp lệ');
        }

        // Lấy danh sách cards theo các ID đã giải mã
        $cards = Card::with(['question.topic', 'question.answers', 'question.images', 'user'])
            ->whereIn('id', $cardIds)
            ->get();

        return $this->apiStatus($cards, 200, count($cards), 'OK');
    }

    public function card_multiple_choice($id)
    {
        $test = Test::with([
            'user',
            'questionnumbers.topic',
            'multiplequestions.testresults.option',
        ])->findOrFail($id);

        if ($test) {
            return $this->apiStatus($test, 200, 1, 'ok');
        }

        return $this->apiStatus(null, 404, 0, 'Data not found.');
    }
}
