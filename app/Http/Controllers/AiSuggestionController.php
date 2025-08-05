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
            'count' => 'nullable|integer|min:1|max:10',
        ]);

        $subject = $request->input('subject_name');
        $count = $request->input('count', 3);

        $ai = new FlashcardSuggest();
        $result = $ai->generate($subject, $count);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }

        Log::info('AI result:', $result);

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
