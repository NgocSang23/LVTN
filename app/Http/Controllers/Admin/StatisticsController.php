<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnswerUser;
use App\Models\Card;
use App\Models\FlashcardSet;
use App\Models\History;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function overview()
    {
        // Đếm tổng số thẻ flashcard
        $totalCards = Card::count();

        // Đếm tổng số bộ flashcard (FlashcardSet)
        $totalSets = FlashcardSet::count();

        // 🔍 Phân tích số thẻ đã/không nằm trong bộ flashcard
        $usedCardIds = FlashcardSet::pluck('question_ids') // Lấy ra danh sách `question_ids` (kiểu chuỗi: "1,2,3")
            ->flatMap(fn($ids) => explode(',', $ids))       // Tách chuỗi thành mảng các ID
            ->unique()                                      // Loại bỏ các ID trùng nhau
            ->filter()                                      // Loại bỏ các phần tử rỗng/null
            ->map(fn($id) => (int) $id);                    // Chuyển về kiểu số nguyên

        // Đếm số thẻ đã nằm trong ít nhất một FlashcardSet
        $usedCardsCount = $usedCardIds->count();

        // Đếm số thẻ chưa được sử dụng trong bất kỳ bộ nào
        $unusedCardsCount = $totalCards - $usedCardsCount;

        // 📊 Đếm lượt ôn tập từ bảng answer_users (click thẻ)
        $reviewFromFlipping = AnswerUser::count();

        // 📊 Đếm lượt làm bài kiểm tra từ bảng histories
        $reviewFromTest = History::count();

        // Tổng số lượt ôn tập
        $totalReviews = $reviewFromFlipping + $reviewFromTest;

        // Trả kết quả dưới dạng JSON cho frontend
        return response()->json([
            'users' => User::count(),                 // Tổng số người dùng
            'cards' => $totalCards,                   // Tổng số thẻ
            'flashcard_sets' => $totalSets,           // Tổng số bộ thẻ
            'cards_in_sets' => $usedCardsCount,       // Số thẻ đã nằm trong bộ
            'cards_not_in_sets' => $unusedCardsCount, // Số thẻ chưa nằm trong bộ
            'totalReviews' => $totalReviews,          // Tổng số lượt ôn tập
        ]);
    }

    public function reviewFrequency()
    {
        // Tạo danh sách 7 ngày gần nhất (kể từ hôm nay lùi về quá khứ)
        $dates = collect(range(0, 6))->map(function ($i) {
            return Carbon::today()->subDays($i)->format('Y-m-d'); // định dạng "2025-07-14"
        });

        // Truy vấn bảng answer_users: đếm số lượt review mỗi ngày
        $answerUser = DB::table('answer_users')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereDate('created_at', '>=', now()->subDays(6)) // chỉ lấy từ 6 ngày trước đến hôm nay
            ->groupBy('date');

        // Truy vấn bảng histories: đếm số lượt kiểm tra mỗi ngày
        $histories = DB::table('histories')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereDate('created_at', '>=', now()->subDays(6))
            ->groupBy('date');

        // Gộp kết quả từ 2 bảng lại (unionAll giữ nguyên tất cả dữ liệu)
        $merged = $answerUser->unionAll($histories);

        // Gộp kết quả theo ngày: cộng tổng số lượt review từ cả 2 bảng
        $results = DB::table(DB::raw("({$merged->toSql()}) as combined"))
            ->mergeBindings($merged) // Gộp biến binding để tránh lỗi SQL
            ->selectRaw('date, SUM(count) as count')
            ->groupBy('date')
            ->get()
            ->keyBy('date'); // Biến kết quả thành mảng có key là ngày

        // Trả về mảng 7 ngày đầy đủ, nếu ngày nào không có thì count = 0
        $final = $dates->map(function ($date) use ($results) {
            return [
                'date' => $date,
                'count' => $results[$date]->count ?? 0, // nếu không có dữ liệu thì mặc định = 0
            ];
        })->reverse()->values(); // đảo lại cho hiển thị theo thứ tự tăng dần thời gian

        // Trả kết quả cho frontend
        return response()->json($final);
    }
}
