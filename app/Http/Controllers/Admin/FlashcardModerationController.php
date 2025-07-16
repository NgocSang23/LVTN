<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashcardSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlashcardModerationController extends Controller
{
    // Lấy danh sách các flashcard công khai nhưng chưa được duyệt
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $query = FlashcardSet::with('user') // Nếu có quan hệ 'user'
            ->where('is_public', true);

        if ($status === 'pending') {
            $query->where('is_approved', false);
        } elseif ($status === 'approved') {
            $query->where('is_approved', true);
        }

        $flashcards = $query->latest()->get();

        return $flashcards->map(function ($card) {
            return [
                'id' => $card->id,
                'title' => $card->title,
                'description' => $card->description,
                'author' => optional($card->user)->name ?? 'Không rõ',
            ];
        });
    }

    public function approve($id)
    {
        $flashcard = FlashcardSet::findOrFail($id);

        $flashcard->is_approved = true; // ✅ Gán đúng cột
        $flashcard->save();

        $user = $flashcard->creator ?? $flashcard->user;

        if ($user) {
            $user->notifications()->create([
                'title' => '✅ Bộ flashcard đã được duyệt',
                'message' => 'Bộ "' . $flashcard->title . '" đã được duyệt và công khai.',
            ]);
        }

        return response()->json(['message' => 'Đã duyệt thành công.']);
    }

    public function destroy($id)
    {
        $flashcard = FlashcardSet::findOrFail($id);
        $user = $flashcard->creator;

        // Xoá trước các bản ghi liên kết
        DB::table('classroom_flashcards')
            ->where('flashcard_set_id', $flashcard->id)
            ->delete();

        $flashcard->delete();

        // Gửi thông báo cho người tạo
        $user->notifications()->create([
            'title' => '🗑️ Bộ flashcard đã bị xoá',
            'message' => 'Bộ "' . $flashcard->title . '" đã bị quản trị viên xoá vì không phù hợp.',
        ]);

        return response()->json(['message' => 'Đã xoá flashcard.']);
    }
}
