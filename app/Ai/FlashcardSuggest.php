<?php

namespace App\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FlashcardSuggest
{
    public function generate(string $subject, int $count = 3): array
    {
        $prompt = <<<PROMPT
            Trả lời bằng tiếng việt
            gợi ý đang dạng hơn k bị trùng
            Tạo $count thẻ flashcard cho môn "$subject".
            Mỗi thẻ gồm:
            - "question": một thuật ngữ hoặc khái niệm
            - "answer": định nghĩa ngắn
            - "image_url": null

            Trả về kết quả dạng JSON:
            [
            {
                "question": "...",
                "answer": "...",
                "image_url": null
            },
            ...
            ]
        PROMPT;

        $cacheKey = 'together_flashcard_' . md5($prompt);
        if (Cache::has($cacheKey)) {
            Log::info("📦 Flashcard từ cache", ['key' => $cacheKey]);
            return Cache::get($cacheKey);
        }

        $client = new TogetherClient();
        $raw = $client->chat($prompt);

        if (empty($raw)) {
            return ['error' => 'AI không trả về nội dung.'];
        }

        $json = $this->extractJsonArray($raw);
        if (!$json || !is_array($json)) {
            Log::error("❌ Không trích xuất được JSON hợp lệ", ['raw' => $raw]);
            return ['error' => 'Không trích xuất được JSON hợp lệ.'];
        }

        Cache::put($cacheKey, $json, 600);
        return $json;
    }

    public function suggestTopics(string $subject): array
    {
        $prompt = <<<PROMPT
            Trả lời bằng tiếng việt
            gợi ý đang dạng hơn k bị trùng
            Liệt kê chủ đề phổ biến của môn "$subject".
            Trả về mảng JSON:
            ["Chủ đề 1", "Chủ đề 2", "Chủ đề 3", "Chủ đề 4", "Chủ đề 5"]
        PROMPT;

        $cacheKey = 'together_topics_' . md5($prompt);
        if (Cache::has($cacheKey)) {
            Log::info("📦 Chủ đề từ cache", ['key' => $cacheKey]);
            return Cache::get($cacheKey);
        }

        $client = new TogetherClient();
        $raw = $client->chat($prompt);

        if (empty($raw)) {
            return ['error' => 'AI không trả về chủ đề.'];
        }

        $matches = [];
        if (preg_match('/\[[^\]]+\]/', $raw, $matches)) {
            $json = json_decode($matches[0], true);
            if (is_array($json)) {
                Cache::put($cacheKey, $json, 600);
                return $json;
            }
            Log::error("❌ JSON chủ đề không hợp lệ", ['matched' => $matches[0]]);
            return ['error' => 'Không thể phân tích kết quả AI.'];
        }

        Log::error("❌ Không tìm thấy danh sách chủ đề hợp lệ", ['raw' => $raw]);
        return ['error' => 'Không tìm thấy danh sách chủ đề hợp lệ.'];
    }

    private function extractJsonArray(string $text): ?array
    {
        if (preg_match('/\[\s*{.*?}\s*]/s', $text, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        return null;
    }
}
