<?php

namespace App\Http\Controllers;

use App\AI\FlashcardSuggest;
use Illuminate\Http\Request;

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

        return response()->json(['data' => $result]);
    }
}
