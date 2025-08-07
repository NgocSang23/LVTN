<?php

namespace App\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TogetherClient
{
    public function chat(string $prompt, ?string $model = null, int $maxTokens = 5000): ?string
    {
        $model = $model ?? env('TOGETHER_MODEL');
        $apiKey = env('TOGETHER_API_KEY');

        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post(env('TOGETHER_BASE_URL') . '/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens' => $maxTokens,
                ]);

            $result = $response->json();
            Log::info("🔁 Together response", $result);

            return $result['choices'][0]['message']['content'] ?? null;
        } catch (\Throwable $e) {
            Log::error("❌ Together API error: " . $e->getMessage());
            return null;
        }
    }
}
