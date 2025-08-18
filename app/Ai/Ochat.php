<?php

namespace App\AI;

use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use NumberFormatter;

class Ochat
{
    protected TogetherClient $client;

    public function __construct()
    {
        $this->client = new TogetherClient();
    }

    /**
     * Gửi prompt đến AI, nhận phản hồi, cache kết quả và xử lý JSON.
     */
    public function send(string $message)
    {
        try {
            $cacheKey = 'ai_response_' . md5($message);
            if ($cachedResponse = Cache::get($cacheKey)) {
                Log::info("📦 Lấy phản hồi từ cache", ['key' => $cacheKey]);
                return $cachedResponse;
            }

            $start = microtime(true);
            $rawResponse = $this->client->chat($message);
            $elapsed = round(microtime(true) - $start, 3);

            Log::info("⏱️ Together API phản hồi trong {$elapsed}s");
            Log::info("🧠 Raw response: " . $rawResponse);

            if (empty($rawResponse)) {
                return ['error' => 'AI không trả về nội dung.'];
            }

            $jsonText = $this->stripExtraText($rawResponse);
            if (!$jsonText) {
                return ['error' => 'Phản hồi từ AI không đúng định dạng JSON'];
            }

            // Đảm bảo kết thúc JSON đúng dấu }
            $jsonText = rtrim($jsonText);
            if (substr($jsonText, -1) !== '}') {
                $jsonText .= '}';
            }

            if (!$this->isJson($jsonText)) {
                Log::warning("⚠️ JSON không hợp lệ sau khi xử lý", ['text' => $jsonText]);
                return ['error' => 'Phản hồi từ AI không đúng định dạng JSON'];
            }

            $aiAnswer = json_decode($jsonText, true);

            // Bổ sung giá trị mặc định nếu thiếu
            $aiAnswer['type'] ??= 'text';
            $aiAnswer['feedback'] = trim($aiAnswer['feedback'] ?? '') ?: 'Câu trả lời chưa đúng. Hãy thử lại!';
            $aiAnswer['category'] = in_array($aiAnswer['category'] ?? '', ['Chính xác', 'Một phần', 'Sai']) ? $aiAnswer['category'] : 'Sai';
            $aiAnswer['percent'] = is_numeric($aiAnswer['percent'] ?? null) ? $aiAnswer['percent'] : 0;

            Cache::put($cacheKey, $aiAnswer, 600); // Cache 10 phút

            return $aiAnswer;
        } catch (\Exception $e) {
            Log::error("❌ Lỗi gọi AI: " . $e->getMessage());
            return ['error' => 'Lỗi: AI không phản hồi.'];
        }
    }

    /**
     * So sánh câu trả lời của người dùng với đáp án đúng, có thể gọi AI để chấm điểm.
     */
    public function compareAnswer(string $question, string $userAnswer, string $correctAnswer, ?string $lastFeedback = null)
    {
        try {
            // Chuẩn hoá các câu trả lời
            $userAnswer = $this->normalizeAnswer($userAnswer);
            $correctAnswer = $this->normalizeAnswer($correctAnswer);
            $lastFeedback = $lastFeedback ? $this->normalizeAnswer($lastFeedback) : null;

            // Kiểm tra trùng lặp với phản hồi trước
            if ($lastFeedback && similar_text($userAnswer, $lastFeedback, $similarityToFeedback) && $similarityToFeedback >= 95) {
                return [
                    "percent" => 75,
                    "category" => "Trùng lặp gợi ý",
                    "feedback" => "Bạn đã sao chép gần như nguyên văn gợi ý. Hãy thử diễn đạt lại hoặc viết theo cách hiểu của bạn!",
                    "confidence" => 90,
                    "correct_answer" => $correctAnswer,
                ];
            }

            // Loại bỏ dấu '=' thừa
            $correctAnswer = ltrim($correctAnswer, '=');

            // Chuyển từ chữ số thành số thực
            $numericUserAnswer = $this->wordsToNumber($userAnswer);
            $numericCorrectAnswer = $this->wordsToNumber($correctAnswer);

            // Nếu cả hai là số và bằng nhau
            if ($numericUserAnswer !== null && $numericCorrectAnswer !== null && $numericUserAnswer == $numericCorrectAnswer) {
                return [
                    "percent" => 100,
                    "category" => "Chính xác",
                    "feedback" => "Câu trả lời hoàn toàn đúng!",
                    "confidence" => 100,
                    "correct_answer" => $correctAnswer,
                ];
            }

            // Tính độ tương đồng chuỗi
            similar_text($userAnswer, $correctAnswer, $similarity);

            if ($similarity >= 85) {
                return [
                    "percent" => 100,
                    "category" => "Chính xác",
                    "feedback" => "Câu trả lời hoàn toàn chính xác!",
                    "confidence" => 100,
                    "correct_answer" => $correctAnswer,
                ];
            } elseif ($similarity >= 60) {
                return [
                    "percent" => 75,
                    "category" => "Một phần",
                    "feedback" => "Câu trả lời gần đúng, bạn nên bổ sung thêm ý cho đầy đủ hơn.",
                    "confidence" => 85,
                    "correct_answer" => $correctAnswer,
                ];
            }

            // So sánh từ khoá chung
            $correctWords = array_unique(preg_split('/\P{L}+/u', $correctAnswer, -1, PREG_SPLIT_NO_EMPTY));
            $userWords = array_unique(preg_split('/\P{L}+/u', $userAnswer, -1, PREG_SPLIT_NO_EMPTY));
            $commonWords = array_intersect($correctWords, $userWords);
            $keywordMatchPercentage = count($correctWords) > 0 ? (count($commonWords) / count($correctWords)) * 100 : 0;

            if ($keywordMatchPercentage >= 40 && $similarity < 60) {
                return [
                    "percent" => 50,
                    "category" => "Một phần",
                    "feedback" => "Câu trả lời của bạn có một số ý đúng, nhưng chưa đầy đủ.",
                    "confidence" => 70,
                    "correct_answer" => $correctAnswer,
                ];
            }

            // Gửi prompt cho AI nếu không khớp đủ
            $prompt = <<<EOT
            Bạn là hệ thống đánh giá tự động. Hãy chấm điểm câu trả lời của học sinh và chỉ trả về đúng một object JSON hợp lệ, không có mẫu, không có giải thích.

            JSON phải có đầy đủ các trường sau:
            {
            "percent": 0-100,
            "category": "Chính xác" | "Một phần" | "Sai",
            "feedback": "Một câu ngắn, dưới 20 từ"
            }

            Chỉ được trả về JSON, không có bất kỳ giải thích nào bên ngoài. Không được thiếu dấu { hoặc }.

            Chỉ cho điểm 100 nếu học sinh trả lời đầy đủ nội dung trong đáp án. Nếu đúng một phần, cho 50 hoặc 75. Nếu sai hoàn toàn, cho 0.

            Câu hỏi: "$question"
            Học sinh trả lời: "$userAnswer"
            Đáp án đúng: "$correctAnswer"
            EOT;

            $result = $this->send($prompt);

            if (isset($result['percent'])) {
                $result['correct_answer'] = $correctAnswer;

                // Tính confidence dựa trên các chỉ số
                $result['confidence'] = round(
                    0.5 * ($similarity ?? 0) +
                        0.3 * ($keywordMatchPercentage ?? 0) +
                        0.2 * ($result['percent'] ?? 0)
                );

                return $result;
            }

            return ['error' => 'Phản hồi từ AI không đúng định dạng JSON'];
        } catch (\Exception $e) {
            Log::error("❌ Lỗi compareAnswer: " . $e->getMessage());
            return ['error' => 'Lỗi: AI không phản hồi.'];
        }
    }

    /**
     * Chuẩn hoá câu trả lời: viết thường, xoá khoảng trắng thừa, chuyển số nếu có.
     */
    private function normalizeAnswer(string $answer): string
    {
        $answer = trim(mb_strtolower($answer));
        $answer = preg_replace('/\s+/', ' ', $answer);
        return is_numeric($answer) ? (string)(float)$answer : $answer;
    }

    /**
     * Chuyển chuỗi chữ số thành số thực, hỗ trợ nhiều locale.
     */
    private function wordsToNumber(string $words): ?float
    {
        $words = str_replace(['bằng', '='], '', $words);
        $words = trim($words);

        if (is_numeric($words)) {
            return (float) $words;
        }

        $locales = ['vi', 'en'];
        foreach ($locales as $locale) {
            $formatter = new NumberFormatter($locale, NumberFormatter::SPELLOUT);
            $number = $formatter->parse($words);
            if ($number !== false) {
                return $number;
            }
        }

        return null;
    }

    /**
     * Kiểm tra chuỗi có phải JSON hợp lệ.
     */
    private function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Tách phần JSON ra khỏi đoạn text có thể lẫn text khác.
     */
    private function stripExtraText(string $text): string
    {
        // Regex đệ quy tìm JSON hợp lệ
        if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $text, $matches)) {
            return $matches[0];
        }

        // Nếu không tìm thấy, thử cắt từ dấu { và thêm } nếu thiếu
        $start = strpos($text, '{');
        if ($start !== false) {
            $substr = substr($text, $start);

            $open = substr_count($substr, '{');
            $close = substr_count($substr, '}');

            if ($open > $close) {
                $substr .= str_repeat('}', $open - $close);
            }

            if ($this->isJson($substr)) {
                return $substr;
            }

            Log::warning("⚠️ JSON không hợp lệ dù đã thêm dấu }", ['raw' => $substr]);
        }

        Log::warning("⚠️ Không tìm thấy JSON trong phản hồi AI", ['raw' => $text]);
        return '';
    }
}
