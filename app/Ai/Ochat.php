<?php

namespace App\AI;

use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use NumberFormatter;

class Ochat
{
    // Gửi prompt đến AI và xử lý phản hồi JSON
    public function send(string $message)
    {
        try {
            // Tạo cache key duy nhất cho mỗi message
            $cacheKey = 'ai_response_' . md5($message);
            Log::info("🔑 Cache key: $cacheKey");

            // Kiểm tra xem kết quả đã được cache chưa
            if ($cachedResponse = Cache::get($cacheKey)) {
                Log::info("📦 Lấy phản hồi từ cache", ['key' => $cacheKey]);
                return $cachedResponse;
            }

            $start = microtime(true); // Thời gian bắt đầu

            // Gửi prompt đến mô hình AI với các tùy chọn
            $response = Ollama::model('llama3.2')
                ->prompt($message)
                ->options([
                    'temperature' => 0.1, // Giảm độ ngẫu nhiên → chính xác hơn
                    'num_predict' => 120, // Dự đoán nhiều token hơn để tránh cắt
                    'keep_alive' => '5m',
                ])
                ->ask();

            $elapsed = round(microtime(true) - $start, 3); // Thời gian phản hồi

            // Ghi log thông tin phản hồi từ AI
            Log::info("🧠 AI Raw Response: " . json_encode($response));
            Log::info("⏱️ Thời gian xử lý AI:", [
                'seconds' => $elapsed,
                'eval' => round(($response['eval_duration'] ?? 0) / 1e9, 3),
                'prompt_eval' => round(($response['prompt_eval_duration'] ?? 0) / 1e9, 3),
                'load' => round(($response['load_duration'] ?? 0) / 1e9, 3),
            ]);

            $aiRawText = $response['response'] ?? '';

            // Tách phần JSON từ phản hồi (nếu AI có thêm giải thích ngoài JSON)
            $jsonText = $this->stripExtraText($aiRawText);

            // Nếu không tìm thấy JSON → trả lỗi
            if (!$jsonText) {
                return ['error' => 'Phản hồi từ AI không đúng định dạng JSON'];
            }

            // Nếu JSON bị thiếu dấu } → thêm vào
            if ($jsonText && substr(trim($jsonText), -1) !== '}') {
                $jsonText .= '}';
            }

            // Kiểm tra JSON có hợp lệ không
            if (!$jsonText || !$this->isJson($jsonText)) {
                Log::warning("⚠️ JSON không hợp lệ sau khi xử lý", ['text' => $jsonText]);
                return ['error' => 'Phản hồi từ AI không đúng định dạng JSON'];
            }

            // Decode phản hồi JSON thành array
            $aiAnswer = json_decode($jsonText, true);

            // Bổ sung giá trị mặc định nếu thiếu
            $aiAnswer['type'] ??= 'text';
            $aiAnswer['feedback'] = trim($aiAnswer['feedback'] ?? '') ?: 'Câu trả lời chưa đúng. Hãy thử lại!';
            $aiAnswer['category'] = in_array($aiAnswer['category'] ?? '', ['Chính xác', 'Một phần', 'Sai']) ? $aiAnswer['category'] : 'Sai';
            $aiAnswer['percent'] = is_numeric($aiAnswer['percent'] ?? null) ? $aiAnswer['percent'] : 0;

            // Lưu vào cache trong 10 phút
            Cache::put($cacheKey, $aiAnswer, 600);
            return $aiAnswer;
        } catch (\Exception $e) {
            Log::error("❌ Lỗi gọi AI: " . $e->getMessage());
            return ['error' => 'Lỗi: AI không phản hồi.'];
        }
    }

    // So sánh câu trả lời người dùng và đáp án đúng, có thể dùng AI nếu cần
    public function compareAnswer(string $question, string $userAnswer, string $correctAnswer, ?string $lastFeedback = null)
    {
        try {
            // Chuẩn hoá các câu trả lời để so sánh
            $userAnswer = $this->normalizeAnswer($userAnswer);
            $correctAnswer = $this->normalizeAnswer($correctAnswer);
            $lastFeedback = $lastFeedback ? $this->normalizeAnswer($lastFeedback) : null;

            // Nếu học sinh copy y chang phản hồi trước đó
            if ($lastFeedback && similar_text($userAnswer, $lastFeedback, $similarityToFeedback) && $similarityToFeedback >= 95) {
                return [
                    "percent" => 75,
                    "category" => "Trùng lặp gợi ý",
                    "feedback" => "Bạn đã sao chép gần như nguyên văn gợi ý. Hãy thử diễn đạt lại hoặc viết theo cách hiểu của bạn!",
                    "confidence" => 90,
                    "correct_answer" => $correctAnswer
                ];
            }

            // Bỏ dấu '=' thừa (nếu có)
            $correctAnswer = ltrim($correctAnswer, '=');

            // Chuyển đổi từ chữ số ra số (ex: “hai mươi” -> 20)
            $numericUserAnswer = $this->wordsToNumber($userAnswer);
            $numericCorrectAnswer = $this->wordsToNumber($correctAnswer);

            // Nếu cả 2 đều là số và bằng nhau
            if ($numericUserAnswer !== null && $numericCorrectAnswer !== null && $numericUserAnswer == $numericCorrectAnswer) {
                return [
                    "percent" => 100,
                    "category" => "Chính xác",
                    "feedback" => "Câu trả lời hoàn toàn đúng!",
                    "confidence" => 100,
                    "correct_answer" => $correctAnswer
                ];
            }

            // Tính độ tương đồng bằng `similar_text`
            similar_text($userAnswer, $correctAnswer, $similarity);

            if ($similarity >= 85) {
                return [
                    "percent" => 100,
                    "category" => "Chính xác",
                    "feedback" => "Câu trả lời hoàn toàn chính xác!",
                    "confidence" => 100,
                    "correct_answer" => $correctAnswer
                ];
            } elseif ($similarity >= 60) {
                return [
                    "percent" => 75,
                    "category" => "Một phần",
                    "feedback" => "Câu trả lời gần đúng, bạn nên bổ sung thêm ý cho đầy đủ hơn.",
                    "confidence" => 85,
                    "correct_answer" => $correctAnswer
                ];
            }

            // Tính phần trăm từ khoá trùng
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
                    "correct_answer" => $correctAnswer
                ];
            }

            // Nếu không khớp nhiều → gửi prompt cho AI để chấm điểm
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

            // Nếu phản hồi hợp lệ từ AI
            if (isset($result['percent'])) {
                $result['correct_answer'] = $correctAnswer;

                // Tính độ tin cậy (confidence) dựa trên nhiều yếu tố
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

    // Chuẩn hoá câu trả lời: viết thường, xoá khoảng trắng, chuyển số nếu cần
    private function normalizeAnswer(string $answer)
    {
        $answer = trim(strtolower($answer));
        $answer = preg_replace('/\s+/', ' ', $answer);
        return is_numeric($answer) ? (string)(float)$answer : $answer;
    }

    // Chuyển chữ thành số: ví dụ “hai mươi” → 20
    private function wordsToNumber($words)
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

    // Kiểm tra chuỗi có phải JSON hợp lệ không
    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    // Tách phần JSON từ đoạn phản hồi có thể lẫn text
    private function stripExtraText(string $text): string
    {
        // Tìm đoạn JSON bằng regex
        if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $text, $matches)) {
            return $matches[0];
        }

        // Nếu không có, cố gắng tìm { và thêm } thủ công
        $start = strpos($text, '{');
        if ($start !== false) {
            $substr = substr($text, $start);

            // Đếm số ngoặc
            $open = substr_count($substr, '{');
            $close = substr_count($substr, '}');

            if ($open > $close) {
                $substr .= str_repeat('}', $open - $close);
            }

            // Kiểm tra lại tính hợp lệ
            if ($this->isJson($substr)) {
                return $substr;
            }

            Log::warning("⚠️ JSON không hợp lệ dù đã thêm dấu }", ['raw' => $substr]);
        }

        Log::warning("⚠️ Không tìm thấy JSON trong phản hồi AI", ['raw' => $text]);
        return '';
    }
}
