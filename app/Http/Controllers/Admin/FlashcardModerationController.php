<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashcardSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlashcardModerationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $query = FlashcardSet::with('user')->where('is_public', true);

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
        $flashcard->update(['is_approved' => true]);

        if ($flashcard->user) {
            $flashcard->user->notifications()->create([
                'title' => '✅ Bộ flashcard đã được duyệt',
                'message' => 'Bộ "' . $flashcard->title . '" đã được duyệt và công khai.',
                'link' => route('flashcard.share', ['slug' => $flashcard->slug])
            ]);
        }

        return response()->json(['message' => 'Đã duyệt thành công.']);
    }

    public function reject($id)
    {
        $flashcard = FlashcardSet::findOrFail($id);
        $flashcard->update(['is_public' => false, 'is_approved' => false]);

        if ($flashcard->user) {
            $flashcard->user->notifications()->create([
                'title' => '❌ Bộ flashcard bị từ chối',
                'message' => 'Bộ "' . $flashcard->title . '" đã bị từ chối chia sẻ công khai.',
                'link' => url('/user/library')
            ]);
        }

        return response()->json(['message' => 'Đã từ chối bộ flashcard.']);
    }

    public function destroy($id)
    {
        $flashcard = FlashcardSet::findOrFail($id);
        $user = $flashcard->creator ?? $flashcard->user;

        DB::table('classroom_flashcards')
            ->where('flashcard_set_id', $flashcard->id)
            ->delete();

        $flashcard->delete();

        if ($user) {
            $user->notifications()->create([
                'title' => '🗑️ Bộ flashcard đã bị xoá',
                'message' => 'Bộ "' . $flashcard->title . '" đã bị quản trị viên xoá vì không phù hợp.',
            ]);
        }

        return response()->json(['message' => 'Đã xoá flashcard.']);
    }

    public function showDetail($id)
    {
        // Tìm một bản ghi trong bảng `flashcard_sets` bằng `$id`.
        // Dùng `with('user')` để eager load (tải trước) thông tin của người dùng (user)
        // liên kết với flashcard này, tránh N+1 query problem.
        // `findOrFail` sẽ tự động trả về lỗi 404 nếu không tìm thấy.
        $flashcard = FlashcardSet::with('user')->findOrFail($id);

        // Kiểm tra xem trường `question_ids` có dữ liệu không.
        // Nếu có, dùng `explode(',', ...)` để chuyển chuỗi các ID (ví dụ: "1,2,3") thành một mảng.
        // Nếu không có, gán một mảng rỗng.
        $cardIds = $flashcard->question_ids ? explode(',', $flashcard->question_ids) : [];

        // Lấy dữ liệu của các thẻ (card) dựa trên mảng `$cardIds`.
        // Dùng `with([...])` để eager load các mối quan hệ liên quan:
        // - `question.answers`: Tải câu hỏi và các câu trả lời của nó.
        // - `question.topic.subject`: Tải chủ đề (topic) và môn học (subject) của câu hỏi.
        // - `question.images`: Tải các hình ảnh liên quan đến câu hỏi.
        // `whereIn('id', $cardIds)` lọc các thẻ có ID nằm trong mảng `$cardIds`.
        // Cuối cùng, `get()` thực thi truy vấn và trả về một Collection.
        $cardsData = \App\Models\Card::with([
            'question.answers',
            'question.topic.subject',
            'question.images' // Tên mối quan hệ là 'images' số nhiều
        ])->whereIn('id', $cardIds)->get();

        // Lấy thẻ đầu tiên từ Collection `$cardsData` để lấy thông tin chung.
        $firstCard = $cardsData->first();

        // Lấy tên chủ đề (topic) từ thẻ đầu tiên.
        // Dùng toán tử `?->` (Nullsafe operator) để truy cập an toàn, tránh lỗi nếu
        // một trong các mối quan hệ `question`, `topic` hoặc `title` không tồn tại.
        // Nếu không có, gán giá trị 'Không có'.
        $topicName = $firstCard?->question?->topic?->title ?? 'Không có';

        // Tương tự, lấy tên môn học (subject) một cách an toàn.
        $subjectName = $firstCard?->question?->topic?->subject?->name ?? 'Không có';

        // Xử lý và định dạng lại dữ liệu của các thẻ.
        // `map` lặp qua từng thẻ trong `$cardsData` và trả về một Collection mới đã được định dạng lại.
        $cards = $cardsData->map(function ($card) {
            // Gán biến `$q` cho `question` của thẻ để code dễ đọc hơn.
            $q = $card->question;
            return [
                // Lấy nội dung câu hỏi, nếu không có thì gán null.
                'question' => $q->content ?? null,
                // Xử lý các câu trả lời.
                // Dùng `?->` để kiểm tra `answers` có tồn tại không.
                // Sau đó, `map` tiếp tục lặp qua từng câu trả lời để lấy `content` và `is_correct`.
                // Nếu không có `answers`, trả về một mảng rỗng `[]`.
                'answers' => $q?->answers->map(fn($ans) => [
                    'content' => $ans->content,
                    'is_correct' => $ans->is_correct ?? null
                ]) ?? [],
                // Xử lý hình ảnh.
                // Kiểm tra xem có hình ảnh nào không (`$q->images->first()`).
                // Nếu có, tạo URL đầy đủ bằng `url('storage/' . ...)`
                // Nếu không, gán giá trị `null`.
                'image' => $q->images->first()
                    ? url('storage/' . $q->images->first()->path)
                    : null
            ];
        });

        // Trả về một phản hồi JSON.
        // Phản hồi này chứa các thông tin chi tiết về bộ flashcard và danh sách các thẻ đã được xử lý.
        return response()->json([
            'id' => $flashcard->id,
            'title' => $flashcard->title,
            'description' => $flashcard->description,
            // Dùng `optional` helper để truy cập thuộc tính `name` của người dùng một cách an toàn.
            // Tránh lỗi nếu flashcard không có người tạo.
            'author' => optional($flashcard->user)->name,
            'subject' => $subjectName,
            'topic' => $topicName,
            'cards' => $cards // Danh sách các thẻ đã được định dạng lại
        ]);
    }
}
