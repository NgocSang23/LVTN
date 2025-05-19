<?php

namespace App\AI;

use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use NumberFormatter;

class Ochat
{
    /**
     * Gửi tin nhắn đến AI và nhận phản hồi.
     */
    public function send(string $message)
    {
        try {
            // Kiểm tra nếu đã có phản hồi từ cache
            $cacheKey = 'ai_response_' . md5($message);
            $cachedResponse = Cache::get($cacheKey);

            if ($cachedResponse) {
                return response()->json($cachedResponse);
            }

            // Gọi AI model 'llama3.2' để xử lý yêu cầu
            $response = Ollama::model('llama3.2')
                ->prompt($message)
                ->options(['temperature' => 0.8])
                ->stream(false)
                ->ask();

            // Ghi log phản hồi từ AI
            Log::info("AI Raw Response: " . json_encode($response));

            // Kiểm tra nếu phản hồi không có key 'response'
            if (!isset($response['response'])) {
                return response()->json(['error' => 'AI không phản hồi đúng định dạng']);
            }

            $aiAnswer = $response['response'];

            // Nếu phản hồi từ AI là JSON, chuyển thành mảng PHP
            if ($this->isJson($aiAnswer)) {
                $aiAnswer = json_decode($aiAnswer, true);
            }

            // Lưu phản hồi vào cache
            Cache::put($cacheKey, $aiAnswer, 600); // Cache trong 10 phút

            return response()->json($aiAnswer);
        } catch (\Exception $e) {
            // Xử lý lỗi nếu AI không phản hồi
            Log::error("Lỗi gọi AI: " . $e->getMessage());
            return response()->json(['error' => 'Lỗi: AI không phản hồi.']);
        }
    }

    /**
     * So sánh câu trả lời của người dùng với đáp án đúng.
     */
    public function compareAnswer(string $question, string $userAnswer, string $correctAnswer, string $lastFeedback = null)
    {
        try {
            // Chuẩn hóa câu trả lời của người dùng, đáp án đúng và phản hồi cuối cùng
            $userAnswer = $this->normalizeAnswer($userAnswer);
            $correctAnswer = $this->normalizeAnswer($correctAnswer);
            $lastFeedback = $lastFeedback ? $this->normalizeAnswer($lastFeedback) : null;

            // Kiểm tra nếu câu trả lời trùng lặp với phản hồi trước đó
            if ($lastFeedback && similar_text($userAnswer, $lastFeedback, $similarityToFeedback) && $similarityToFeedback >= 95) {
                return [
                    "type" => "theory",
                    "percent" => 75,
                    "category" => "Trùng lặp gợi ý",
                    "feedback" => "Bạn đã sao chép gần như nguyên văn gợi ý. Hãy thử diễn đạt lại hoặc viết theo cách hiểu của bạn!"
                ];
            }

            // Xác định loại câu hỏi: 'math' nếu chứa từ khóa toán học, ngược lại là 'theory'
            $type = preg_match('/\b(tính|giải|kết quả|bao nhiêu|phép|tổng|hiệu|tích|chia|cộng|trừ|nhân)\b|[+\-*\/=]/iu', $question) ? 'math' : 'theory';

            // Chuẩn hóa đáp án đúng (loại bỏ dấu '=' nếu có)
            $correctAnswer = ltrim($correctAnswer, '=');

            // Chuyển đổi số thành chữ và ngược lại để đảm bảo so sánh chính xác
            $numericUserAnswer = $this->wordsToNumber($userAnswer);
            $numericCorrectAnswer = $this->wordsToNumber($correctAnswer);

            // Nếu cả hai giá trị số học trùng nhau, trả về kết quả 100%
            if ($numericUserAnswer !== null && $numericCorrectAnswer !== null && $numericUserAnswer == $numericCorrectAnswer) {
                return [
                    "type" => "math",
                    "percent" => 100,
                    "category" => "Chính xác",
                    "feedback" => "Câu trả lời hoàn toàn đúng!"
                ];
            }

            // Tính toán mức độ giống nhau giữa câu trả lời và đáp án
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

            // Nếu câu trả lời không khớp hoàn toàn, yêu cầu AI đánh giá
            $prompt = "Hãy đánh giá mức độ chính xác của câu trả lời so với đáp án đúng."
                . "\nCâu hỏi: '$question'"
                . "\nCâu trả lời của người dùng: '$userAnswer'"
                . "\nĐáp án đúng: '$correctAnswer'"
                . "\nHãy trả về JSON hợp lệ, không có văn bản khác, theo định dạng sau:"
                . "\n```json"
                . "\n{ \"type\": \"math/theory\", \"percent\": xx, \"category\": \"Chính xác / Một phần / Sai\", \"feedback\": \"Phản hồi\" }"
                . "\n```";

            // Gọi AI để đánh giá câu trả lời
            $response = Ollama::model('llama3.2')
                ->prompt($prompt)
                ->options(['temperature' => 0.8])
                ->stream(false)
                ->ask();

            // Ghi log phản hồi từ AI
            Log::info("AI Response: " . json_encode($response));

            // Nếu AI trả về JSON hợp lệ, xử lý kết quả
            if (isset($response['response']) && $this->isJson($response['response'])) {
                $result = json_decode($response['response'], true);
                $result['correct_answer'] = $correctAnswer;
                return $result;
            }

            return ['error' => 'Phản hồi từ AI không đúng định dạng JSON'];
        } catch (\Exception $e) {
            Log::error("Lỗi gọi AI: " . $e->getMessage());
            return ['error' => 'Lỗi: AI không phản hồi.'];
        }
    }

    /**
     * Chuẩn hóa câu trả lời: Chuyển về chữ thường, loại bỏ khoảng trắng thừa.
     */
    private function normalizeAnswer(string $answer)
    {
        $answer = trim(strtolower($answer)); // Chuyển về chữ thường và xóa khoảng trắng đầu cuối
        $answer = preg_replace('/\s+/', ' ', $answer); // Chuẩn hóa khoảng trắng
        return is_numeric($answer) ? (string)(float)$answer : $answer; // Nếu là số, chuyển thành dạng số thực
    }

    /**
     * Chuyển đổi số viết bằng chữ thành số (ví dụ: "hai" → 2).
     */
    private function wordsToNumber($words)
    {
        // Loại bỏ các ký tự không cần thiết như "=" hoặc "bằng"
        $words = str_replace(['bằng', '='], '', $words);
        $words = trim($words);

        // Nếu chuỗi chỉ chứa số, trả về số ngay lập tức
        if (is_numeric($words)) {
            return (float) $words;
        }

        // Danh sách formatter cho cả tiếng Việt và tiếng Anh
        $locales = ['vi', 'en'];
        foreach ($locales as $locale) {
            $formatter = new \NumberFormatter($locale, \NumberFormatter::SPELLOUT);
            $number = $formatter->parse($words);
            if ($number !== false) {
                return $number;
            }
        }

        // Nếu không chuyển đổi được, trả về null
        return null;
    }

    /**
     * Kiểm tra xem một chuỗi có phải là JSON hợp lệ hay không.
     */
    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}


// namespace App\AI;

// use Cloudstudio\Ollama\Facades\Ollama;
// use Illuminate\Support\Facades\Log;
// use NumberFormatter;

// class Ochat
// {
//     /**
//      * Gửi tin nhắn đến AI và nhận phản hồi.
//      */
//     public function send(string $message)
//     {
//         try {
//             // Gọi AI model 'llama3.2' để xử lý yêu cầu
//             $response = Ollama::model('llama3.2')
//                 ->prompt($message)
//                 ->options(['temperature' => 0.8]) // Độ sáng tạo của AI (0.8 là trung bình)
//                 ->stream(false) // Không dùng streaming
//                 ->ask();

//             // Ghi log phản hồi từ AI
//             Log::info("AI Raw Response: " . json_encode($response));

//             // Kiểm tra nếu phản hồi không có key 'response'
//             if (!isset($response['response'])) {
//                 return response()->json(['error' => 'AI không phản hồi đúng định dạng']);
//             }

//             $aiAnswer = $response['response'];

//             // Nếu phản hồi từ AI là JSON, chuyển thành mảng PHP
//             if ($this->isJson($aiAnswer)) {
//                 $aiAnswer = json_decode($aiAnswer, true);
//             }

//             return response()->json($aiAnswer);
//         } catch (\Exception $e) {
//             // Xử lý lỗi nếu AI không phản hồi
//             Log::error("Lỗi gọi AI: " . $e->getMessage());
//             return response()->json(['error' => 'Lỗi: AI không phản hồi.']);
//         }
//     }

//     /**
//      * So sánh câu trả lời của người dùng với đáp án đúng.
//      */
//     public function compareAnswer(string $question, string $userAnswer, string $correctAnswer, string $lastFeedback = null)
//     {
//         try {
//             // Chuẩn hóa câu trả lời của người dùng, đáp án đúng và phản hồi cuối cùng
//             $userAnswer = $this->normalizeAnswer($userAnswer);
//             $correctAnswer = $this->normalizeAnswer($correctAnswer);
//             $lastFeedback = $lastFeedback ? $this->normalizeAnswer($lastFeedback) : null;

//             // Kiểm tra nếu câu trả lời trùng lặp với phản hồi trước đó
//             if ($lastFeedback && similar_text($userAnswer, $lastFeedback, $similarityToFeedback) && $similarityToFeedback >= 95) {
//                 return [
//                     "type" => "theory",
//                     "percent" => 75,
//                     "category" => "Trùng lặp gợi ý",
//                     "feedback" => "Bạn đã sao chép gần như nguyên văn gợi ý. Hãy thử diễn đạt lại hoặc viết theo cách hiểu của bạn!"
//                 ];
//             }

//             // Xác định loại câu hỏi: 'math' nếu chứa từ khóa toán học, ngược lại là 'theory'
//             $type = preg_match('/\b(tính|giải|kết quả|bao nhiêu|phép|tổng|hiệu|tích|chia|cộng|trừ|nhân)\b|[+\-*\/=]/iu', $question) ? 'math' : 'theory';

//             // Chuẩn hóa đáp án đúng (loại bỏ dấu '=' nếu có)
//             $correctAnswer = ltrim($correctAnswer, '=');

//             // Chuyển đổi số thành chữ và ngược lại để đảm bảo so sánh chính xác
//             $numericUserAnswer = $this->wordsToNumber($userAnswer);
//             $numericCorrectAnswer = $this->wordsToNumber($correctAnswer);

//             // Nếu cả hai giá trị số học trùng nhau, trả về kết quả 100%
//             if ($numericUserAnswer !== null && $numericCorrectAnswer !== null && $numericUserAnswer == $numericCorrectAnswer) {
//                 return [
//                     "type" => "math",
//                     "percent" => 100,
//                     "category" => "Chính xác",
//                     "feedback" => "Câu trả lời hoàn toàn đúng!"
//                 ];
//             }

//             // Tính toán mức độ giống nhau giữa câu trả lời và đáp án
//             similar_text($userAnswer, $correctAnswer, $similarity);

//             if ($similarity >= 85) {
//                 return [
//                     "type" => $type,
//                     "percent" => 100,
//                     "category" => "Chính xác",
//                     "feedback" => "Câu trả lời hoàn toàn chính xác!"
//                 ];
//             } elseif ($similarity >= 60) {
//                 return [
//                     "type" => $type,
//                     "percent" => 75,
//                     "category" => "Một phần",
//                     "feedback" => "Câu trả lời gần đúng, bạn nên bổ sung thêm ý cho đầy đủ hơn."
//                 ];
//             }

//             // Nếu câu trả lời không khớp hoàn toàn, yêu cầu AI đánh giá
//             $prompt = "Hãy đánh giá mức độ chính xác của câu trả lời so với đáp án đúng."
//                 . "\nCâu hỏi: '$question'"
//                 . "\nCâu trả lời của người dùng: '$userAnswer'"
//                 . "\nĐáp án đúng: '$correctAnswer'"
//                 . "\nHãy trả về JSON hợp lệ, không có văn bản khác, theo định dạng sau:"
//                 . "\n```json"
//                 . "\n{ \"type\": \"math/theory\", \"percent\": xx, \"category\": \"Chính xác / Một phần / Sai\", \"feedback\": \"Phản hồi\" }"
//                 . "\n```";

//             // Gọi AI để đánh giá câu trả lời
//             $response = Ollama::model('llama3.2')
//                 ->prompt($prompt)
//                 ->options(['temperature' => 0.8])
//                 ->stream(false)
//                 ->ask();

//             // Ghi log phản hồi từ AI
//             Log::info("AI Response: " . json_encode($response));

//             // Nếu AI trả về JSON hợp lệ, xử lý kết quả
//             if (isset($response['response']) && $this->isJson($response['response'])) {
//                 $result = json_decode($response['response'], true);
//                 $result['correct_answer'] = $correctAnswer;
//                 return $result;
//             }

//             return ['error' => 'Phản hồi từ AI không đúng định dạng JSON'];
//         } catch (\Exception $e) {
//             Log::error("Lỗi gọi AI: " . $e->getMessage());
//             return ['error' => 'Lỗi: AI không phản hồi.'];
//         }
//     }

//     /**
//      * Chuẩn hóa câu trả lời: Chuyển về chữ thường, loại bỏ khoảng trắng thừa.
//      */
//     private function normalizeAnswer(string $answer)
//     {
//         $answer = trim(strtolower($answer)); // Chuyển về chữ thường và xóa khoảng trắng đầu cuối
//         $answer = preg_replace('/\s+/', ' ', $answer); // Chuẩn hóa khoảng trắng
//         return is_numeric($answer) ? (string)(float)$answer : $answer; // Nếu là số, chuyển thành dạng số thực
//     }

//     /**
//      * Chuyển đổi số viết bằng chữ thành số (ví dụ: "hai" → 2).
//      */
//     private function wordsToNumber($words)
//     {
//         // Loại bỏ các ký tự không cần thiết như "=" hoặc "bằng"
//         $words = str_replace(['bằng', '='], '', $words);
//         $words = trim($words);

//         // Nếu chuỗi chỉ chứa số, trả về số ngay lập tức
//         if (is_numeric($words)) {
//             return (float) $words;
//         }

//         // Danh sách formatter cho cả tiếng Việt và tiếng Anh
//         $locales = ['vi', 'en'];
//         foreach ($locales as $locale) {
//             $formatter = new \NumberFormatter($locale, \NumberFormatter::SPELLOUT);
//             $number = $formatter->parse($words);
//             if ($number !== false) {
//                 return $number;
//             }
//         }

//         // Nếu không chuyển đổi được, trả về null
//         return null;
//     }

//     /**
//      * Kiểm tra xem một chuỗi có phải là JSON hợp lệ hay không.
//      */
//     private function isJson($string)
//     {
//         json_decode($string);
//         return json_last_error() === JSON_ERROR_NONE;
//     }
// }
