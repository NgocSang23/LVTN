<?php

namespace App\AI;

use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use NumberFormatter;

class Ochat
{
    public function send(string $message)
    {
        try {
            $cacheKey = 'ai_response_' . md5($message);
            Log::info("🔑 Cache key: $cacheKey");

            if ($cachedResponse = Cache::get($cacheKey)) {
                Log::info("📦 Lấy phản hồi từ cache", ['key' => $cacheKey]);
                return $cachedResponse;
            }

            $start = microtime(true);

            $response = Ollama::model('llama3.2')
                ->prompt($message)
                ->options([
                    'temperature' => 0.1,
                    'num_predict' => 80,       // Giảm token để phản hồi nhanh hơn
                    'keep_alive' => '5m',      // Giữ model trong bộ nhớ
                ])
                ->ask();

            $elapsed = round(microtime(true) - $start, 3);

            Log::info("🧠 AI Raw Response: " . json_encode($response));
            Log::info("⏱️ Thời gian xử lý AI:", [
                'seconds' => $elapsed,
                'eval' => round(($response['eval_duration'] ?? 0) / 1e9, 3),
                'prompt_eval' => round(($response['prompt_eval_duration'] ?? 0) / 1e9, 3),
                'load' => round(($response['load_duration'] ?? 0) / 1e9, 3),
            ]);

            $aiAnswer = $this->stripExtraText($response['response'] ?? '');

            if (!$aiAnswer) {
                return ['error' => 'Không tìm thấy JSON trong phản hồi AI.'];
            }

            if ($this->isJson($aiAnswer)) {
                $aiAnswer = json_decode($aiAnswer, true);
            } elseif (preg_match('/\{.*\}/s', $aiAnswer, $matches)) {
                $maybeJson = $matches[0];
                if ($this->isJson($maybeJson)) {
                    $aiAnswer = json_decode($maybeJson, true);
                }
            }

            if (!is_array($aiAnswer)) {
                Log::warning("Phản hồi không decode được JSON", [
                    'raw' => $aiAnswer,
                    'json_error' => json_last_error_msg()
                ]);
                return ['error' => 'Phản hồi từ AI không đúng định dạng JSON'];
            }

            // 🧪 Kiểm tra các field bắt buộc
            $requiredFields = ['type', 'percent', 'category', 'feedback'];
            foreach ($requiredFields as $field) {
                if (!array_key_exists($field, $aiAnswer)) {
                    Log::warning("Thiếu trường $field trong phản hồi AI", ['response' => $aiAnswer]);
                    return ['error' => 'Phản hồi từ AI thiếu thông tin cần thiết.'];
                }
            }

            // 🧹 Nếu feedback không hợp lệ (AI nhầm hướng dẫn là phản hồi)
            if (trim($aiAnswer['feedback']) === 'ngắn, dưới 20 từ') {
                $aiAnswer['feedback'] = 'Câu trả lời chưa đúng. Hãy thử lại!';
            }

            if (empty(trim($aiAnswer['feedback'] ?? ''))) {
                $aiAnswer['feedback'] = 'Câu trả lời chưa đúng. Hãy thử lại!';
            }

            $validCategories = ['Chính xác', 'Một phần', 'Sai'];
            if (!in_array($aiAnswer['category'] ?? '', $validCategories)) {
                $aiAnswer['category'] = 'Sai';
            }

            Cache::put($cacheKey, $aiAnswer, 600);

            return $aiAnswer;
        } catch (\Exception $e) {
            Log::error("Lỗi gọi AI: " . $e->getMessage());
            return ['error' => 'Lỗi: AI không phản hồi.'];
        }
    }

    public function compareAnswer(string $question, string $userAnswer, string $correctAnswer, ?string $lastFeedback = null)
    {
        try {
            $userAnswer = $this->normalizeAnswer($userAnswer);
            $correctAnswer = $this->normalizeAnswer($correctAnswer);
            $lastFeedback = $lastFeedback ? $this->normalizeAnswer($lastFeedback) : null;

            if ($lastFeedback && similar_text($userAnswer, $lastFeedback, $similarityToFeedback) && $similarityToFeedback >= 95) {
                return [
                    "type" => "theory",
                    "percent" => 75,
                    "category" => "Trùng lặp gợi ý",
                    "feedback" => "Bạn đã sao chép gần như nguyên văn gợi ý. Hãy thử diễn đạt lại hoặc viết theo cách hiểu của bạn!"
                ];
            }

            $type = preg_match('/\b(tính|giải|kết quả|bao nhiêu|phép|tổng|hiệu|tích|chia|cộng|trừ|nhân)\b|[+\-*\/=]/iu', $question)
                ? 'math' : 'theory';

            $correctAnswer = ltrim($correctAnswer, '=');

            $numericUserAnswer = $this->wordsToNumber($userAnswer);
            $numericCorrectAnswer = $this->wordsToNumber($correctAnswer);

            if ($numericUserAnswer !== null && $numericCorrectAnswer !== null && $numericUserAnswer == $numericCorrectAnswer) {
                return [
                    "type" => "math",
                    "percent" => 100,
                    "category" => "Chính xác",
                    "feedback" => "Câu trả lời hoàn toàn đúng!"
                ];
            }

            similar_text($userAnswer, $correctAnswer, $similarity);
            if ($similarity >= 85) {
                return [
                    "type" => $type,
                    "percent" => 100,
                    "category" => "Chính xác",
                    "feedback" => "Câu trả lời hoàn toàn chính xác!"
                ];
            } elseif ($similarity >= 60) {
                return [
                    "type" => $type,
                    "percent" => 75,
                    "category" => "Một phần",
                    "feedback" => "Câu trả lời gần đúng, bạn nên bổ sung thêm ý cho đầy đủ hơn."
                ];
            }

            $correctWords = array_unique(preg_split('/\P{L}+/u', $correctAnswer, -1, PREG_SPLIT_NO_EMPTY));
            $userWords = array_unique(preg_split('/\P{L}+/u', $userAnswer, -1, PREG_SPLIT_NO_EMPTY));
            $commonWords = array_intersect($correctWords, $userWords);

            $keywordMatchPercentage = count($correctWords) > 0 ? (count($commonWords) / count($correctWords)) * 100 : 0;

            if ($keywordMatchPercentage >= 40 && $similarity < 60) {
                return [
                    "type" => $type,
                    "percent" => 50,
                    "category" => "Một phần",
                    "feedback" => "Câu trả lời của bạn có một số ý đúng, nhưng chưa đầy đủ."
                ];
            }

            $prompt = <<<EOT
Bạn là hệ thống đánh giá tự động. Hãy chấm điểm câu trả lời của học sinh và chỉ **trả về đúng một object JSON hợp lệ, không có mẫu, không có giải thích**. Kết quả JSON không được thiếu dấu `{}`.

{
  "type": "theory",
  "percent": 0-100,
  "category": "Chính xác" | "Một phần" | "Sai",
  "feedback": "Một câu ngắn, dưới 20 từ"
}

Chỉ cho điểm 100 nếu học sinh trả lời đầy đủ nội dung trong đáp án. Nếu chỉ đúng một phần, cho 50 hoặc 75. Nếu sai hoàn toàn, cho 0.

Câu hỏi: "$question"
Học sinh trả lời: "$userAnswer"
Đáp án đúng: "$correctAnswer"
EOT;

            $jsonResponse = $this->send($prompt);

            $result = $jsonResponse instanceof \Illuminate\Http\JsonResponse
                ? $jsonResponse->getData(true)
                : $jsonResponse;

            if (isset($result['type'])) {
                $result['correct_answer'] = $correctAnswer;
                return $result;
            }

            return ['error' => 'Phản hồi từ AI không đúng định dạng JSON'];
        } catch (\Exception $e) {
            Log::error("Lỗi compareAnswer: " . $e->getMessage());
            return ['error' => 'Lỗi: AI không phản hồi.'];
        }
    }

    private function normalizeAnswer(string $answer)
    {
        $answer = trim(strtolower($answer));
        $answer = preg_replace('/\s+/', ' ', $answer);
        return is_numeric($answer) ? (string)(float)$answer : $answer;
    }

    private function wordsToNumber($words)
    {
        $words = str_replace(['bằng', '='], '', $words);
        $words = trim($words);

        if (is_numeric($words)) {
            return (float) $words;
        }

        $locales = ['vi', 'en'];
        foreach ($locales as $locale) {
            $formatter = new \NumberFormatter($locale, \NumberFormatter::SPELLOUT);
            $number = $formatter->parse($words);
            if ($number !== false) {
                return $number;
            }
        }

        return null;
    }

    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private function stripExtraText(string $text): string
    {
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```|(\{.*)/s', $text, $matches)) {
            $rawJson = $matches[1] ?? $matches[2];

            // ⚠️ Nếu thiếu dấu `}`, tự động đóng lại
            if (substr(trim($rawJson), -1) !== '}') {
                $rawJson .= '}';
            }

            return $rawJson;
        }

        Log::warning("Không tìm thấy JSON trong phản hồi AI", ['raw' => $text]);
        return '';
    }
}
