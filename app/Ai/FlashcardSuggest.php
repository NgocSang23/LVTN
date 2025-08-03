<?php

namespace App\AI;

use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FlashcardSuggest
{
    public function generate(string $subject, int $count = 3): array
    {
        $prompt = <<<PROMPT
            Hãy tạo $count thẻ flashcard cho môn "$subject".
            Mỗi thẻ gồm:
            - "question": một thuật ngữ
            - "answer": định nghĩa ngắn
            - "image_url": link ảnh minh hoạ (hoặc null)

            Trả về JSON mảng như:
            [
                {
                    "question": "Ví dụ 1",
                    "answer": "Định nghĩa 1",
                    "image_url": "https://..."
                }
            ]
        PROMPT;

        $cacheKey = 'ai_flashcard_' . md5($prompt);

        if (Cache::has($cacheKey)) {
            Log::info("📦 Gợi ý flashcard từ cache", ['key' => $cacheKey]);
            return Cache::get($cacheKey);
        }

        try {
            $start = microtime(true);

            $response = Ollama::model('llama3.2')
                ->prompt($prompt)
                ->options([
                    'temperature' => 0.1,
                    'num_predict' => 300,
                ])
                ->ask();

            Log::info("🧠 Raw AI Flashcard Suggest:", [$response]);

            $aiRaw = $response['response'] ?? '';
            $json = $this->extractJsonArray($aiRaw);

            if (!$json || !is_array($json)) {
                return ['error' => 'AI không trả về JSON mảng hợp lệ.'];
            }

            Cache::put($cacheKey, $json, 600);
            return $json;
        } catch (\Throwable $e) {
            Log::error("❌ Lỗi AI gợi ý flashcard: " . $e->getMessage());
            return ['error' => 'Lỗi AI: ' . $e->getMessage()];
        }
    }

    // Tách phần JSON array từ đoạn text có thể lẫn mô tả
    private function extractJsonArray(string $text): array|null
    {
        if (preg_match('/\[\s*{.*}\s*]/s', $text, $matches)) {
            $json = $matches[0];
            $parsed = json_decode($json, true);
            return is_array($parsed) ? $parsed : null;
        }

        return null;
    }
}
