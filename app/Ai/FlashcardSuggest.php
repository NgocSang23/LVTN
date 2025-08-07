<?php

namespace App\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FlashcardSuggest
{
    public function generate(string $subject, int $count = 3, array $excludedQuestions = []): array
    {
        $count = min($count, 50);

        $excludedText = '';
        if (!empty($excludedQuestions)) {
            $excludedText = 'Danh sách các câu hỏi đã tồn tại, tuyệt đối không được lặp lại hoặc tương tự dưới bất kỳ hình thức nào (ý nghĩa, từ ngữ gần giống):' . PHP_EOL;
            foreach ($excludedQuestions as $question) {
                $excludedText .= '- ' . $question . PHP_EOL;
            }
        }

        $prompt = <<<PROMPT
        Trả lời bằng tiếng Việt. Tuyệt đối không giải thích gì thêm.

        Bạn là trợ lý học tập. Hãy tạo chính xác $count thẻ flashcard hoàn toàn mới cho môn học "$subject".

        Yêu cầu:
        - Không lặp lại hoặc trùng khái niệm với các câu hỏi đã có.
        - Không sử dụng lại từ ngữ, cấu trúc, hoặc ý tưởng tương tự.
        - Mỗi thẻ phải có nội dung riêng biệt hoàn toàn.
        - Các câu hỏi và câu trả lời từ cấp 3 trở lên tới đại học, liên quan tới giáo dục Việt Nam
        - Mỗi thẻ gồm:
          - "question": Câu hỏi ngắn, là một thuật ngữ hoặc khái niệm.
          - "answer": Định nghĩa rõ ràng, ngắn gọn, dễ hiểu.
          - "image_url": Một URL ảnh minh họa phù hợp với nội dung câu hỏi. Ưu tiên các ảnh có chất lượng cao, tự do bản quyền từ Wikimedia Commons, Wikipedia, hoặc các nguồn public domain. Tránh ảnh từ nguồn thương mại. Nếu không thể tìm được ảnh phù hợp, để null.

        ⚠️ Bắt buộc trả về đúng định dạng mảng JSON gồm $count phần tử. Không ít hơn, không nhiều hơn. Không kèm lời giải thích.

        Ví dụ định dạng JSON:

        [
          {
            "question": "Khái niệm 1",
            "answer": "Định nghĩa tương ứng",
            "image_url": "https://example.com/image1.jpg"
          },
          ...
        ]

        $excludedText
        PROMPT;

        Log::info("⚠️ Bỏ qua cache để lấy flashcard mới");
        $estimatedTokens = min($count * 250, 3500);
        Log::info("🧮 Token estimation", ['tokens' => $estimatedTokens]);

        $client = new TogetherClient();
        $raw = $client->chat($prompt, null, $estimatedTokens);

        if (empty($raw)) {
            return ['error' => 'AI không trả về nội dung.'];
        }

        $json = $this->extractJsonArray($raw);
        if (!$json || !is_array($json)) {
            Log::error("❌ Không trích xuất được JSON hợp lệ", ['raw' => $raw]);
            return ['error' => 'Không trích xuất được JSON hợp lệ.'];
        }

        if (count($json) !== $count) {
            Log::warning("⚠️ AI trả về số lượng không đúng", ['expected' => $count, 'actual' => count($json)]);
            return ['error' => "AI không trả về đúng $count thẻ flashcard."];
        }

        // ✅ Gán ảnh từ Wikimedia nếu thiếu
        foreach ($json as &$item) {
            if (!isset($item['image_url']) || empty($item['image_url']) || $item['image_url'] === 'null') {
                $image = $this->getImageFromWikipedia($item['question']);
                $item['image_url'] = $image ?: null;
            }
        }

        return $json;
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

    private function getImageFromWikipedia(string $term): ?string
    {
        $url = 'https://en.wikipedia.org/w/api.php?action=query&titles=' . urlencode($term) . '&prop=pageimages&format=json&pithumbsize=300';

        try {
            $response = file_get_contents($url);
            $data = json_decode($response, true);

            if (isset($data['query']['pages'])) {
                foreach ($data['query']['pages'] as $page) {
                    if (isset($page['thumbnail']['source'])) {
                        return $page['thumbnail']['source'];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning("❗ Không thể lấy ảnh từ Wikipedia", ['term' => $term, 'error' => $e->getMessage()]);
        }

        return null;
    }
}
