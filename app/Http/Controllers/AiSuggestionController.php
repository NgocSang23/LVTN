<?php

namespace App\Http\Controllers;

use App\AI\FlashcardSuggest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiSuggestionController extends Controller
{
    public function suggest(Request $request)
    {
        $request->validate([
            'subject_name' => 'required|string',
            'count' => 'nullable|integer|min:1|max:50',
            'excluded_questions' => 'nullable|array'
        ]);

        $subject = $request->input('subject_name');
        $count = min($request->input('count', 3), 50); // ✅ Giới hạn tối đa 50
        $excluded = $request->input('excluded_questions', []);

        $ai = new FlashcardSuggest();
        $result = $ai->generate($subject, $count, $excluded);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }

        return response()->json(['data' => $result]);
    }

    public function suggestTopics(Request $request)
    {
        $request->validate([
            'subject_name' => 'required|string'
        ]);

        $subject = $request->input('subject_name');
        $ai = new FlashcardSuggest();
        $topics = $ai->suggestTopics($subject);

        if (isset($topics['error'])) {
            return response()->json(['error' => $topics['error']], 500);
        }

        return response()->json(['data' => $topics]);
    }
}
