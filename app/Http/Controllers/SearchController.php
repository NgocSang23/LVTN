<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\ClassRoom;
use App\Models\FlashcardSet;
use App\Models\Test;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Hiển thị kết quả tìm kiếm.
     */
    public function instant(Request $request)
    {
        try {
            $user = auth()->user();
            $myClassrooms = [];

            if ($user && $user->roles === 'teacher') {
                $myClassrooms = ClassRoom::where('teacher_id', $user->id)->get();
            }

            $keyword = mb_strtolower(trim($request->input('search')));

            if (empty($keyword)) {
                $data = $this->getDashboardData($myClassrooms);
                return response()->json(['html' => view('user.partials.search_result', $data)->render()]);
            }

            $topicMatches = \App\Models\Topic::where('title', 'LIKE', "%$keyword%")->pluck('id')->toArray();

            $subjectMatches = \App\Models\Subject::where('name', 'LIKE', "%$keyword%")->get();

            $subjectTopicIds = $subjectMatches->flatMap(fn($s) => $s->topics->pluck('id'))->toArray();

            $relatedTopicIds = array_unique(array_merge($topicMatches, $subjectTopicIds));

            if (empty($relatedTopicIds)) {
                return response()->json(['html' => '<p class="text-muted">Không tìm thấy kết quả phù hợp.</p>']);
            }

            $card_defines = $this->getCardsByType($relatedTopicIds);
            $tests = Test::with(['questionNumbers.topic', 'user'])
                ->whereHas('questionNumbers.topic', fn($q) => $q->whereIn('id', $relatedTopicIds))
                ->get();

            // Tính my_flashcards cho user hiện tại, tương tự getDashboardData
            $userId = $user->id ?? null;
            $all_cards = \App\Models\Card::with(['question.topic.subject', 'user', 'flashcardSet'])
                ->whereHas('question.topic', fn($q) => $q->whereIn('id', $relatedTopicIds))
                ->get()
                ->filter(fn($card) => $card->question && $card->question->topic);

            $my_flashcards = collect([]);
            if ($userId) {
                $my_flashcards = $all_cards->filter(fn($card) => $card->user_id === $userId)
                    ->groupBy(fn($card) => $card->question->topic->id)
                    ->map(fn($group) => [
                        'first_card' => $group->first(),
                        'card_ids' => $group->pluck('id')->implode(','),
                        'encoded_ids' => base64_encode($group->pluck('id')->implode(',')),
                    ])
                    ->values();
            }

            // Tạo community_flashcards (flashcard công khai)
            $public_card_ids = FlashcardSet::where('is_public', 1)
                ->pluck('question_ids')
                ->flatMap(fn($ids) => explode(',', $ids))
                ->map(fn($id) => (int) trim($id))
                ->unique()
                ->toArray();

            $community_flashcards = $all_cards->filter(fn($card) => in_array($card->id, $public_card_ids))
                ->groupBy(fn($card) => $card->question->topic->id)
                ->map(fn($group) => [
                    'first_card' => $group->first(),
                    'card_ids' => $group->pluck('id')->implode(','),
                    'encoded_ids' => base64_encode($group->pluck('id')->implode(',')),
                ])
                ->values();

            $data = compact('card_defines', 'tests', 'myClassrooms', 'my_flashcards', 'community_flashcards');

            return response()->json(['html' => view('user.partials.search_result', $data)->render()]);
        } catch (\Throwable $e) {
            Log::error('❌ Lỗi tìm kiếm: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['html' => '<p class="text-danger">Đã xảy ra lỗi trong quá trình tìm kiếm.</p>']);
        }
    }

    protected function getCardsByType(array $topicIds)
    {
        $cards = \App\Models\Card::with(['question.topic', 'user', 'flashcardSet'])
            ->whereHas('question.topic', fn($q) => $q->whereIn('id', $topicIds))
            ->get();

        return $cards->groupBy(fn($card) => optional($card->question->topic)->id)
            ->map(function ($group) {
                $ids = $group->pluck('id')->toArray();
                return [
                    'card_ids' => $ids,
                    'encoded_ids' => base64_encode(implode(',', $ids)), // 🔧 Thêm dòng này
                    'first_card' => $group->first()
                ];
            })
            ->values();
    }

    private function getDashboardData($myClassrooms)
    {
        $public_card_ids = FlashcardSet::where('is_public', 1)
            ->pluck('question_ids')
            ->flatMap(fn($ids) => explode(',', $ids))
            ->map(fn($id) => (int) trim($id))
            ->unique()
            ->toArray();

        $card_defines = Card::with(['question.topic.subject', 'user'])
            ->latest()
            ->get()
            ->filter(fn($card) => $card->question && $card->question->topic)
            ->filter(fn($card) => in_array($card->id, $public_card_ids))
            ->groupBy(fn($card) => $card->question->topic->id)
            ->map(fn($group) => [
                'first_card' => $group->first(),
                'card_ids' => $group->pluck('id')->implode(','),
                'encoded_ids' => base64_encode($group->pluck('id')->implode(',')),
            ])
            ->take(6);

        $tests = Test::with(['questionnumbers.topic', 'user'])->latest()->take(6)->get();

        return compact('card_defines', 'tests', 'myClassrooms');
    }
}
