<?php

namespace App\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FlashcardSuggest
{
    private function isDuplicateQuestion(string $question, array $existingQuestions): bool
    {
        $normalize = function ($text) {
            return mb_strtolower(
                preg_replace('/\s+/u', ' ', trim($text)),
                'UTF-8'
            );
        };

        $normalizedNew = $normalize($question);

        foreach ($existingQuestions as $old) {
            $normalizedOld = $normalize($old);

            // 1. Exact match sau khi normalize
            if ($normalizedNew === $normalizedOld) {
                return true;
            }

            // 2. Fuzzy match: giống nhau > 85%
            // Có thể tăng ngưỡng này lên 90 nếu muốn chống trùng lặp chặt chẽ hơn,
            // nhưng cần cân nhắc tránh loại bỏ các câu hỏi hợp lệ.
            similar_text($normalizedNew, $normalizedOld, $percent);
            if ($percent >= 85) { // Giữ nguyên 85% là một khởi đầu tốt.
                return true;
            }
        }

        return false;
    }

    public function generate(string $subject, int $count = 50, array $excludedQuestions = []): array
    {
        // Giới hạn số lượng flashcard tối đa có thể tạo trong một lần
        $count = min($count, 50);
        $results = [];
        $remaining = $count;
        $maxLoops = 10; // Số vòng lặp tối đa để tránh vòng lặp vô tận
        $loop = 0;
        $requestBatchSize = 10; // Số lượng thẻ yêu cầu AI tạo trong mỗi lần gọi API

        while ($remaining > 0 && $loop < $maxLoops) {
            $loop++;

            // Xác định số lượng thẻ cần yêu cầu trong lượt này, không vượt quá $requestBatchSize
            $currentRequestCount = min($remaining, $requestBatchSize);

            $excludedText = '';
            if (!empty($excludedQuestions)) {
                $excludedText = 'Dưới đây là các khái niệm đã có. **Tuyệt đối không được tạo câu hỏi mới trùng lặp về ý nghĩa, khái niệm hoặc thuật ngữ với bất kỳ câu hỏi nào trong danh sách này, kể cả khi diễn đạt bằng từ ngữ khác hoặc dịch sang ngôn ngữ khác:**' . PHP_EOL;
                foreach ($excludedQuestions as $question) {
                    $excludedText .= '- ' . $question . PHP_EOL;
                }
            }

            $prompt = <<<PROMPT
                Trả lời bằng tiếng Việt. Không giải thích gì thêm.

                Bạn là trợ lý học tập. Hãy tạo **tối đa** $currentRequestCount thẻ flashcard hoàn toàn mới cho môn "$subject".

                Yêu cầu:
                - Câu hỏi và câu trả lời từ cấp 3 trở lên tới đại học, liên quan tới giáo dục Việt Nam.
                - Không lặp lại ý nghĩa, từ ngữ hoặc khái niệm với danh sách đã có (kể cả dịch sang ngôn ngữ khác hoặc diễn đạt khác).
                - Mỗi thẻ gồm:
                    - "question": Câu hỏi ngắn hoặc thuật ngữ.
                    - "answer": Định nghĩa rõ ràng, ngắn gọn, dễ hiểu.
                - Nếu không thể đủ $currentRequestCount, hãy tạo số lượng nhiều nhất có thể.
                - Trả về duy nhất mảng JSON.

                Ví dụ:
                [
                    {"question": "Khái niệm 1", "answer": "Định nghĩa 1"},
                    {"question": "Khái niệm 2", "answer": "Định nghĩa 2"}
                ]

                $excludedText
            PROMPT;

            Log::info("⚠️ Lấy flashcard mới (yêu cầu $currentRequestCount thẻ, vòng $loop)");
            // Ước tính token dựa trên số lượng thẻ yêu cầu
            $estimatedTokens = min($currentRequestCount * 250, 3500);

            $client = new TogetherClient(); // Giả định TogetherClient đã được định nghĩa
            $raw = $client->chat($prompt, null, $estimatedTokens);

            if (empty($raw)) {
                Log::warning("⚠️ AI không trả về nội dung ở vòng $loop.");
                break;
            }

            $json = $this->extractJsonArray($raw);
            if (!$json || !is_array($json)) {
                Log::error("❌ Không trích xuất được JSON hợp lệ", ['raw' => $raw]);
                break;
            }

            $newItems = [];
            foreach ($json as $item) {
                if (!isset($item['question'], $item['answer'])) {
                    continue;
                }
                // Kiểm tra trùng lặp trước khi thêm vào danh sách kết quả và danh sách loại trừ
                if (!$this->isDuplicateQuestion($item['question'], $excludedQuestions)) {
                    $newItems[] = $item;
                    // Thêm câu hỏi mới vào danh sách loại trừ để tránh trùng lặp trong các vòng tiếp theo
                    $excludedQuestions[] = $item['question'];
                } else {
                    Log::warning("🔁 Bỏ câu trùng hoặc tương tự: " . $item['question']);
                }
            }

            $results = array_merge($results, $newItems);
            $remaining = $count - count($results);

            Log::info("✅ Đã thu được " . count($results) . " / $count thẻ flashcard (vòng $loop).");

            // Nếu không có thẻ mới nào được tạo ở vòng này, dừng để tránh lặp vô hạn
            if (count($newItems) === 0 && $remaining > 0) {
                Log::warning("⚠️ Không tạo được câu hỏi mới ở vòng $loop, dừng.");
                break;
            }
        }

        // Trả về số lượng thẻ flashcard theo yêu cầu ban đầu ($count)
        return array_slice($results, 0, $count);
    }

    public function suggestTopics(string $subject): array
    {
        $prompt = <<<PROMPT
        Trả lời bằng tiếng Việt.

        Bạn là trợ lý học tập. Hãy liệt kê từ 8 đến 15 chủ đề học tập khác nhau cho môn "$subject".

        Yêu cầu:
        - Các câu hỏi và câu trả lời từ cấp 3 trở lên tới đại học, liên quan tới giáo dục Việt Nam
        - Bao gồm cả chủ đề cơ bản và nâng cao.
        - Bao gồm các chủ đề liên quan hoặc tích hợp.
        - Không lặp lại.
        - Trả về mảng JSON (không giải thích), ví dụ:
        ["Chủ đề 1", "Chủ đề 2", "Chủ đề 3", ...]
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
