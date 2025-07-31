<?php

namespace App\Http\Controllers;

use App\Models\Card;
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
        $keyword = mb_strtolower(trim($request->input('search')));

        if (empty($keyword)) {
            return response()->json(['html' => '<p class="text-muted">Vui lòng nhập từ khóa.</p>']);
        }

        $topicMatches = \App\Models\Topic::where('title', 'LIKE', "%$keyword%")->pluck('id')->toArray();
        $subjectMatches = \App\Models\Subject::where('name', 'LIKE', "%$keyword%")->get();
        $subjectTopicIds = $subjectMatches->flatMap(fn($s) => $s->topics->pluck('id'))->toArray();
        $relatedTopicIds = array_unique(array_merge($topicMatches, $subjectTopicIds));

        if (empty($relatedTopicIds)) {
            return response()->json(['html' => '<p class="text-muted">Không tìm thấy kết quả phù hợp.</p>']);
        }

        $card_defines = $this->getCardsByType('definition', $relatedTopicIds);
        $tests = Test::with(['questionNumbers.topic', 'user'])
            ->whereHas('questionNumbers.topic', fn($q) => $q->whereIn('id', $relatedTopicIds))
            ->get();

        $html = view('user.partials.search_result', compact('card_defines', 'tests'))->render();

        return response()->json(['html' => $html]);
    }
}
