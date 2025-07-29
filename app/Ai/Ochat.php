<?php

namespace App\AI;

use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use NumberFormatter; // Import class NumberFormatter để chuyển đổi số thành chữ và ngược lại (dùng cho wordsToNumber).

class Ochat
{
    /**
     * Gửi tin nhắn đến AI và nhận phản hồi.
     * @param string $message Nội dung tin nhắn (prompt) gửi đến AI.
     * @return array Trả về một mảng chứa phản hồi từ AI hoặc thông báo lỗi.
     */
    public function send(string $message)
    {
        try {
            // 🔑 Tạo khóa cache từ nội dung message (băm md5)
            // Sử dụng hàm md5 để tạo một chuỗi băm duy nhất từ nội dung của tin nhắn ($message).
            // Khóa này dùng để kiểm tra xem phản hồi cho tin nhắn này đã được lưu trong cache chưa.
            $cacheKey = 'ai_response_' . md5($message);
            Log::info("🔑 Cache key: $cacheKey"); // Ghi log khóa cache để tiện theo dõi.

            // 📦 Nếu đã có cache thì trả về luôn (tiết kiệm thời gian)
            // Kiểm tra xem có dữ liệu trong cache với khóa $cacheKey không.
            if ($cachedResponse = Cache::get($cacheKey)) {
                Log::info("📦 Lấy phản hồi từ cache", ['key' => $cacheKey]); // Ghi log khi lấy từ cache.
                return $cachedResponse; // Trả về dữ liệu đã cache (là một mảng đã được xử lý JSON từ trước).
            }

            $startTime = microtime(true); // ⏱️ Bắt đầu đếm thời gian thực thi để đo hiệu suất.

            // 🚀 Gọi Ollama với giới hạn token và giảm temperature để tăng tốc
            // Gọi mô hình 'llama3.2' thông qua facade Ollama.
            $response = Ollama::model('llama3.2')
                ->prompt($message) // Gửi prompt ($message) đến mô hình AI.
                ->options([
                    'temperature' => 0.7,    // Ổn định hơn, ít suy diễn. Giá trị thấp hơn (gần 0) làm AI ít "sáng tạo", phản hồi nhất quán hơn.
                    'num_predict' => 80     // ⛳ Giới hạn số lượng token đầu ra tối đa của AI. Giúp giảm thời gian phản hồi.
                ])
                ->ask(); // Gửi yêu cầu và đợi phản hồi hoàn chỉnh (không phải dạng stream).

            Log::info("🧠 AI Raw Response: " . json_encode($response)); // Ghi log phản hồi thô từ AI để debug.

            $aiAnswer = $response['response'] ?? null; // Lấy nội dung phản hồi từ mảng kết quả của Ollama. Nếu không có, gán null.

            // Kiểm tra nếu AI không phản hồi hoặc phản hồi rỗng
            if (!$aiAnswer) {
                return ['error' => 'AI không phản hồi đúng định dạng']; // Trả về lỗi nếu không có phản hồi hợp lệ.
            }

            // ✅ Nếu là JSON hợp lệ thì decode luôn
            // Kiểm tra xem phản hồi AI có phải là chuỗi JSON hợp lệ không.
            if ($this->isJson($aiAnswer)) {
                $aiAnswer = json_decode($aiAnswer, true); // Chuyển đổi chuỗi JSON thành mảng PHP.
            } else {
                // 🕵️‍♂️ Nếu không phải JSON thuần, tìm đoạn JSON trong chuỗi
                // Sử dụng biểu thức chính quy (regex) để tìm kiếm một đoạn JSON (bắt đầu bằng '{' và kết thúc bằng '}')
                // trong trường hợp AI trả về thêm các văn bản khác ngoài JSON.
                if (preg_match('/\{.*\}/s', $aiAnswer, $matches)) {
                    $maybeJson = $matches[0]; // Lấy đoạn văn bản được tìm thấy bằng regex.
                    if ($this->isJson($maybeJson)) { // Kiểm tra lại xem đoạn tìm được có phải JSON hợp lệ không.
                        $aiAnswer = json_decode($maybeJson, true); // Nếu có, decode nó thành mảng.
                    }
                }
            }

            // ❌ Nếu sau các bước trên mà $aiAnswer không phải là một mảng
            if (!is_array($aiAnswer)) {
                return ['error' => 'Phản hồi từ AI không đúng định dạng JSON']; // Trả về lỗi nếu không thể phân tích thành mảng.
            }

            // 💾 Lưu cache để lần sau không phải gọi lại AI
            // Lưu phản hồi đã xử lý vào cache với khóa $cacheKey và thời gian sống 600 giây (10 phút).
            Cache::put($cacheKey, $aiAnswer, 600);

            Log::info("⏱️ Thời gian xử lý AI: ", [
                'seconds' => round(microtime(true) - $startTime, 3) // Ghi log thời gian thực thi của lệnh gọi AI.
            ]);

            return $aiAnswer; // Trả về mảng phản hồi đã được xử lý.
        } catch (\Exception $e) {
            // 🧨 Ghi log lỗi nếu có exception
            // Bắt và ghi lại bất kỳ ngoại lệ nào xảy ra trong quá trình gọi AI.
            Log::error("Lỗi gọi AI: " . $e->getMessage());
            return ['error' => 'Lỗi: AI không phản hồi.']; // Trả về thông báo lỗi cho người dùng.
        }
    }

    /**
     * So sánh câu trả lời của người dùng với đáp án đúng.
     * @param string $question Câu hỏi gốc.
     * @param string $userAnswer Câu trả lời của người dùng.
     * @param string $correctAnswer Đáp án đúng.
     * @param string|null $lastFeedback Phản hồi gần nhất từ AI (để tránh trùng lặp).
     * @return array Trả về kết quả so sánh bao gồm loại câu hỏi, phần trăm đúng, danh mục và phản hồi.
     */
    public function compareAnswer(string $question, string $userAnswer, string $correctAnswer, ?string $lastFeedback = null)
    {
        try {
            // Chuẩn hóa câu trả lời của người dùng, đáp án đúng và phản hồi cuối cùng
            // Gọi hàm normalizeAnswer để chuẩn hóa các chuỗi (chuyển về chữ thường, loại bỏ khoảng trắng thừa).
            $userAnswer = $this->normalizeAnswer($userAnswer);
            $correctAnswer = $this->normalizeAnswer($correctAnswer);
            $lastFeedback = $lastFeedback ? $this->normalizeAnswer($lastFeedback) : null;

            // Kiểm tra nếu câu trả lời trùng lặp với phản hồi trước đó
            // Sử dụng similar_text để tính toán độ tương đồng giữa câu trả lời người dùng và phản hồi trước đó.
            // Nếu độ tương đồng cao (>= 95%), coi là trùng lặp và trả về kết quả ngay.
            if ($lastFeedback && similar_text($userAnswer, $lastFeedback, $similarityToFeedback) && $similarityToFeedback >= 95) {
                return [
                    "type" => "theory",
                    "percent" => 75,
                    "category" => "Trùng lặp gợi ý",
                    "feedback" => "Bạn đã sao chép gần như nguyên văn gợi ý. Hãy thử diễn đạt lại hoặc viết theo cách hiểu của bạn!"
                ];
            }

            // Xác định loại câu hỏi (math hoặc theory)
            // Dùng regex để kiểm tra xem câu hỏi có chứa các từ khóa hoặc ký tự liên quan đến toán học không.
            $type = preg_match('/\b(tính|giải|kết quả|bao nhiêu|phép|tổng|hiệu|tích|chia|cộng|trừ|nhân)\b|[+\-*\/=]/iu', $question)
                ? 'math' // Nếu có, đây là câu hỏi toán học.
                : 'theory'; // Ngược lại, là câu hỏi lý thuyết.

            // Chuẩn hóa dấu "=" trong đáp án đúng (loại bỏ dấu bằng ở đầu chuỗi nếu có).
            $correctAnswer = ltrim($correctAnswer, '=');

            // So sánh số học nếu có (ưu tiên xử lý cục bộ nếu là số)
            // Chuyển đổi câu trả lời và đáp án đúng từ chữ sang số (nếu có thể) bằng wordsToNumber.
            $numericUserAnswer = $this->wordsToNumber($userAnswer);
            $numericCorrectAnswer = $this->wordsToNumber($correctAnswer);

            // Nếu cả hai đều là số và bằng nhau, trả về kết quả 100% chính xác ngay lập tức.
            if ($numericUserAnswer !== null && $numericCorrectAnswer !== null && $numericUserAnswer == $numericCorrectAnswer) {
                return [
                    "type" => "math",
                    "percent" => 100,
                    "category" => "Chính xác",
                    "feedback" => "Câu trả lời hoàn toàn đúng!"
                ];
            }

            // So sánh độ tương đồng văn bản (nếu không phải số hoặc số không khớp)
            // Tính toán độ tương đồng phần trăm giữa câu trả lời người dùng và đáp án đúng.
            similar_text($userAnswer, $correctAnswer, $similarity);
            if ($similarity >= 85) {
                // Nếu độ tương đồng rất cao (>= 85%), coi là chính xác hoàn toàn.
                return [
                    "type" => $type,
                    "percent" => 100,
                    "category" => "Chính xác",
                    "feedback" => "Câu trả lời hoàn toàn chính xác!"
                ];
            } elseif ($similarity >= 60) {
                // Nếu độ tương đồng trung bình (>= 60%), coi là đúng một phần.
                return [
                    "type" => $type,
                    "percent" => 75,
                    "category" => "Một phần",
                    "feedback" => "Câu trả lời gần đúng, bạn nên bổ sung thêm ý cho đầy đủ hơn."
                ];
            }

            // Xây dựng prompt để gửi đến AI (chỉ khi các kiểm tra cục bộ không đủ)
            $prompt = <<<EOT
                Bạn là công cụ chấm điểm ngắn gọn.

                So sánh câu trả lời của người dùng với đáp án chính xác, và phản hồi kết quả dưới dạng JSON **duy nhất**. KHÔNG thêm mô tả hay giải thích ngoài JSON.

                - Câu hỏi: "$question"
                - Câu trả lời người dùng: "$userAnswer"
                - Đáp án đúng: "$correctAnswer"

                Trả đúng **một** JSON có dạng:

                {
                "type": "$type",
                "percent": 0,
                "category": "Sai",
                "feedback": "Giải thích ngắn gọn tại sao sai, tối đa 20 từ"
                }
            EOT;

            // 🧠 Gọi AI qua hàm send() đã có cache
            // Gọi lại hàm send() nội bộ để gửi prompt đến AI. Hàm send() đã bao gồm logic caching.
            $jsonResponse = $this->send($prompt);

            // Nếu kết quả trả về là một JsonResponse (từ Laravel, thường không xảy ra trong hàm này)
            // thì trích xuất dữ liệu, nếu không thì dùng trực tiếp kết quả.
            $result = $jsonResponse instanceof \Illuminate\Http\JsonResponse
                ? $jsonResponse->getData(true)
                : $jsonResponse;

            // Thêm correct_answer vào kết quả để frontend có thể hiển thị nếu cần.
            if (isset($result['type'])) {
                $result['correct_answer'] = $correctAnswer;
                return $result; // Trả về kết quả từ AI đã thêm đáp án đúng.
            }

            return ['error' => 'Phản hồi từ AI không đúng định dạng JSON']; // Trả về lỗi nếu phản hồi AI không đúng format.
        } catch (\Exception $e) {
            // Ghi log lỗi và trả về thông báo lỗi nếu có ngoại lệ.
            Log::error("Lỗi gọi AI: " . $e->getMessage());
            return ['error' => 'Lỗi: AI không phản hồi.'];
        }
    }

    /**
     * Chuẩn hóa câu trả lời: Chuyển về chữ thường, loại bỏ khoảng trắng thừa.
     * @param string $answer Chuỗi cần chuẩn hóa.
     * @return string Chuỗi đã được chuẩn hóa.
     */
    private function normalizeAnswer(string $answer)
    {
        $answer = trim(strtolower($answer)); // Chuyển chuỗi về chữ thường và loại bỏ khoảng trắng ở đầu/cuối.
        $answer = preg_replace('/\s+/', ' ', $answer); // Chuẩn hóa tất cả các khoảng trắng thừa (ví dụ: nhiều dấu cách thành một dấu cách).
        // Nếu chuỗi sau khi chuẩn hóa là một số, chuyển nó thành float rồi lại thành string (để đảm bảo định dạng số nhất quán).
        return is_numeric($answer) ? (string)(float)$answer : $answer;
    }

    /**
     * Chuyển đổi số viết bằng chữ thành số (ví dụ: "hai" → 2).
     * @param string $words Chuỗi chứa số dưới dạng chữ.
     * @return float|null Số thập phân nếu chuyển đổi thành công, ngược lại là null.
     */
    private function wordsToNumber($words)
    {
        // Loại bỏ các ký tự không cần thiết như "=" hoặc "bằng"
        $words = str_replace(['bằng', '='], '', $words);
        $words = trim($words); // Loại bỏ khoảng trắng sau khi loại bỏ ký tự.

        // Nếu chuỗi chỉ chứa số, trả về số ngay lập tức dưới dạng float.
        if (is_numeric($words)) {
            return (float) $words;
        }

        // Danh sách các ngôn ngữ (locales) để thử chuyển đổi (tiếng Việt, tiếng Anh).
        $locales = ['vi', 'en'];
        foreach ($locales as $locale) {
            // Tạo một đối tượng NumberFormatter để chuyển đổi số viết bằng chữ sang số.
            $formatter = new \NumberFormatter($locale, \NumberFormatter::SPELLOUT);
            $number = $formatter->parse($words); // Thử parse chuỗi.
            if ($number !== false) { // Nếu parse thành công (không trả về false).
                return $number; // Trả về giá trị số.
            }
        }

        // Nếu không chuyển đổi được bằng bất kỳ locale nào, trả về null.
        return null;
    }

    /**
     * Kiểm tra xem một chuỗi có phải là JSON hợp lệ hay không.
     * @param string $string Chuỗi cần kiểm tra.
     * @return bool True nếu là JSON hợp lệ, ngược lại là false.
     */
    private function isJson($string)
    {
        json_decode($string); // Cố gắng decode chuỗi JSON.
        return json_last_error() === JSON_ERROR_NONE; // Kiểm tra xem có lỗi JSON nào xảy ra trong quá trình decode không.
        // Nếu không có lỗi, tức là chuỗi đó là JSON hợp lệ.
    }
}
