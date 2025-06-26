<?php

namespace App\Http\Controllers;

use App\Models\DifficultCard;
use Illuminate\Http\Request;

class DifficultCardController extends Controller
{
    public function mark(Request $request)
    {
        // Kiểm tra đầu vào
        $request->validate(['question_id' => 'required|integer']);

        $userId = auth()->id();
        $questionId = $request->question_id;

        // Tìm bản ghi nếu đã tồn tại
        $record = DifficultCard::where('user_id', $userId)
            ->where('question_id', $questionId)
            ->first();

        if ($record) {
            // ✅ Nếu đã có thì cập nhật lại is_resolved = false
            $record->update(['is_resolved' => false]);
        } else {
            // ✅ Nếu chưa có thì tạo mới
            DifficultCard::create([
                'user_id' => $userId,
                'question_id' => $questionId,
                'is_resolved' => false
            ]);
        }

        return response()->json(['status' => 'marked']);
    }

    public function resolve(Request $request)
    {
        // Kiểm tra đầu vào: phải có question_id dạng số nguyên
        $request->validate(['question_id' => 'required|integer']);

        // Cập nhật is_resolved = true cho bản ghi tương ứng
        DifficultCard::where([
            'user_id' => auth()->id(),
            'question_id' => $request->question_id,
        ])->update(['is_resolved' => true]);

        // Trả về phản hồi JSON
        return response()->json(['status' => 'resolved']);
    }
}
