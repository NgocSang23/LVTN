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
        $cardIds = $request->input('card_ids'); // Mảng ID
        $user = auth()->user();

        // ✅ Kiểm tra quyền sở hữu
        foreach ($cardIds as $cardId) {
            $card = \App\Models\Card::find($cardId);
            if (!$card || $card->user_id !== $user->id) {
                return redirect()->back()->with('error', 'Bạn chỉ có thể chia sẻ thẻ do bạn tạo.');
            }
        }

        // ✅ Kiểm tra trùng lặp
        $existingSet = FlashcardSet::where('user_id', $user->id)
            ->where('question_ids', implode(',', $cardIds))
            ->first();

        if ($existingSet) {
            return redirect()->route('flashcard.share', ['slug' => $existingSet->slug]);
        }

        // ✅ Tạo mới (có slug)
        $set = FlashcardSet::create([
            'title' => 'Bộ thẻ chia sẻ',
            'description' => 'Bộ thẻ được chia sẻ công khai.',
            'question_ids' => implode(',', $cardIds),
            'is_public' => true,
            'user_id' => $user->id,
            'slug' => 'bo-the-' . uniqid(), // tạo slug duy nhất
        ]);

        return redirect()->route('flashcard.share', ['slug' => $set->slug]);
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
        $decoded = base64_decode($encodedIds);
        $ids = explode(',', $decoded);
        $questions = Question::whereIn('id', $ids)->get();

        return view('user.flashcard_check', compact('questions'));
    }

    // Dùng để chia sẻ công khai qua slug
    public function publicView($slug)
    {
        $set = FlashcardSet::where('slug', $slug)->where('is_public', 1)->first();

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
