<?php

namespace App\Http\Controllers;

use App\Models\FlashcardSet;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FlashcardSetController extends Controller
{
    public function createFromCards(Request $request)
    {
        $cardIds = $request->input('card_ids');
        $user = auth()->user();

        // Kiểm tra quyền sở hữu
        foreach ($cardIds as $cardId) {
            $card = \App\Models\Card::find($cardId);
            if (!$card || $card->user_id !== $user->id) {
                return redirect()->back()->with('error', 'Bạn chỉ có thể chia sẻ thẻ do bạn tạo.');
            }
        }

        $idsString = implode(',', $cardIds);

        // Tìm xem đã có set này chưa
        $set = FlashcardSet::where('user_id', $user->id)
            ->where('question_ids', $idsString)
            ->first();

        if ($set) {
            // Nếu đã có, thì cập nhật để đảm bảo public và chưa duyệt
            $set->update([
                'is_public' => true,
                'is_approved' => false,
            ]);
        } else {
            // Nếu chưa có, tạo mới
            $set = FlashcardSet::create([
                'title' => 'Bộ thẻ chia sẻ',
                'description' => 'Bộ thẻ được chia sẻ công khai (đang chờ duyệt).',
                'question_ids' => $idsString,
                'is_public' => true,
                'is_approved' => false,
                'user_id' => $user->id,
                'slug' => 'bo-the-' . uniqid(),
            ]);
        }

        // Gửi thông báo cho admin (dù là mới hay cập nhật)
        \App\Models\User::where('roles', 'admin')->each(function ($admin) use ($set, $user) {
            $admin->notifications()->create([
                'title' => '📢 Yêu cầu duyệt flashcard mới',
                'message' => $user->name . ' đã gửi yêu cầu chia sẻ công khai bộ "' . $set->title . '".',
                'link' => url('/admin/flashcards')
            ]);
        });

        return redirect()->back()->with('success', 'Yêu cầu chia sẻ đã được gửi, đang chờ duyệt.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'question_ids' => 'required|string',
        ]);

        FlashcardSet::create([
            'title' => $request->title,
            'description' => $request->description,
            'question_ids' => $request->question_ids,
            'is_public' => $request->boolean('is_public', false),
            'user_id' => auth()->id(),
            'slug' => Str::slug($request->title) . '-' . uniqid(), // tạo slug duy nhất
        ]);

        return redirect()->back()->with('success', 'Đã lưu bộ flashcard');
    }

    // Dùng để mở flashcard qua đường link (base64 encoded)
    public function share($encodedIds)
    {
        // Giải mã danh sách ID từ chuỗi base64
        $decoded = base64_decode($encodedIds);
        if (!$decoded) {
            abort(404, 'Đường dẫn không hợp lệ.');
        }

        $questionIds = explode(',', $decoded);

        // Lấy danh sách câu hỏi kèm liên kết các model liên quan
        $questions = Question::with(['answers', 'images', 'topic'])
            ->whereIn('id', $questionIds)
            ->get();

        // Gán user nếu có đăng nhập
        $user = auth()->check() ? auth()->user() : null;

        // Dùng dummy FlashcardSet để truyền dữ liệu tối thiểu nếu cần
        $set = new \stdClass();
        $set->title = "Bộ flashcard tạm thời";
        $set->description = null;

        return view('user.flashcard_define_essay.share_view', compact('set', 'questions', 'user'));
    }

    // Dùng để chia sẻ công khai qua slug
    public function publicView($slug)
    {
        $set = FlashcardSet::where('slug', $slug)
            ->where('is_public', 1)
            ->where('is_approved', 1) // ✅ chỉ công khai khi đã duyệt
            ->first();

        if (!$set) {
            abort(404, 'Không tìm thấy bộ flashcard hoặc bộ này không được chia sẻ.');
        }

        // KHÔNG dùng auth()->id() hay auth()->user() nếu không kiểm tra login
        $user = auth()->check() ? auth()->user() : null;

        $questionIds = explode(',', $set->question_ids);

        $questions = Question::with(['answers', 'images', 'topic'])
            ->whereIn('id', $questionIds)
            ->get();

        return view('user.flashcard_define_essay.share_view', compact('set', 'questions', 'user'));
    }
}
