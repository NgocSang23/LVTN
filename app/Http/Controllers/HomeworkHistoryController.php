<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeworkHistoryController extends Controller
{
    /**
     * Phương thức index xử lý request GET để hiển thị trang lịch sử học tập.
     *
     * @param Request $request Đối tượng chứa các thông tin từ request HTTP.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'define');
        // Khởi tạo một mảng rỗng để lưu trữ dữ liệu sẽ được truyền sang view.
        $data = [];

        $stats = $this->getLearningStats();

        // Sử dụng cấu trúc switch để xử lý dữ liệu dựa trên giá trị của biến $tab.
        switch ($tab) {
            case 'define':
                // Nếu tab là 'define' (lịch sử học thẻ khái niệm), gọi phương thức getDefineData
                // để lấy dữ liệu về các thẻ đã học.
                $data['define_data'] = $this->getDefineData();
                break;
            case 'multiple':
                // Nếu tab là 'multiple' (lịch sử làm bài trắc nghiệm), gọi phương thức getMultipleData
                // để lấy dữ liệu về các bài test đã làm.
                $data['multiple_data'] = $this->getMultipleData();
                break;
        }

        return view('user.homework_history.index', array_merge($data, [
            'tab' => $tab,
            'stats' => $stats
        ]));
    }

    /**
     * Lấy dữ liệu các thẻ khái niệm (flashcard) mà người dùng đã học.
     *
     * @return \Illuminate\Support\Collection Trả về một Collection chứa các đối tượng dữ liệu.
     */
    private function getDefineData()
    {
        $userId = Auth::id();
        $sort = request('sort', 'new');

        // Bắt đầu một truy vấn database sử dụng Query Builder của Laravel.
        $query = DB::table('answer_users as au')
            // Nối (join) bảng 'answer_users' với các bảng khác để lấy thông tin liên quan.
            ->join('users as u', 'au.user_id', '=', 'u.id')
            ->join('questions as q', 'au.question_id', '=', 'q.id')
            ->join('topics as t', 'q.topic_id', '=', 't.id')
            ->join('subjects as s', 't.subject_id', '=', 's.id')
            ->join('cards as c', 'q.card_id', '=', 'c.id')
            ->join('users as u_name', 'c.user_id', '=', 'u_name.id')
            // Chọn các cột cần thiết.
            ->select(
                't.id as topic_id',
                't.title as ten_chu_de',
                's.name as ten_mon_hoc',
                'u_name.name as nguoi_tao',
                // Đếm tổng số thẻ khái niệm đã học trong mỗi chủ đề bằng cách đếm các 'question_id' duy nhất.
                DB::raw('COUNT(DISTINCT au.question_id) as tong_so_the_da_hoc'),
                // Lấy ID của thẻ đầu tiên trong chủ đề.
                DB::raw('MIN(c.id) as first_card_id'),
                // Gom tất cả ID thẻ của chủ đề lại thành một chuỗi, sắp xếp tăng dần.
                DB::raw("GROUP_CONCAT(DISTINCT c.id ORDER BY c.id ASC SEPARATOR ',') as card_ids"),
                // Lấy thời gian tạo của thẻ đầu tiên trong chủ đề.
                DB::raw('MIN(c.created_at) as created_at')
            )
            // Lọc dữ liệu chỉ lấy các câu trả lời của người dùng hiện tại.
            ->where('u.id', $userId)
            // Gom nhóm các kết quả theo chủ đề, tiêu đề, tên môn học và người tạo để tổng hợp dữ liệu.
            ->groupBy('t.id', 't.title', 's.name', 'u_name.name');

        // Áp dụng sắp xếp dựa trên giá trị của biến $sort.
        if ($sort === 'new') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort === 'old') {
            $query->orderBy('created_at', 'asc');
        }

        // Thực thi truy vấn và lấy tất cả kết quả.
        $define_data = $query->get();

        // Lặp qua từng kết quả (từng chủ đề) để tính tổng số thẻ trong mỗi chủ đề.
        foreach ($define_data as $item) {
            // Bắt đầu một truy vấn mới để đếm tổng số thẻ.
            $item->tong_so_the = DB::table('cards as c')
                ->join('questions as q', 'c.id', '=', 'q.card_id')
                ->where('q.topic_id', $item->topic_id)
                ->distinct('c.id')
                ->count('c.id');
        }

        // Trả về dữ liệu đã xử lý.
        return $define_data;
    }

    /**
     * Lấy dữ liệu các bài kiểm tra trắc nghiệm (multiple choice) mà người dùng đã làm.
     *
     * @return \Illuminate\Support\Collection Trả về một Collection chứa các đối tượng dữ liệu.
     */
    private function getMultipleData()
    {
        // Lấy giá trị sắp xếp từ request, mặc định là 'new'.
        $sort = request('sort', 'new');

        // Bắt đầu truy vấn từ bảng 'histories' (lịch sử làm bài).
        $query = DB::table('histories as h')
            ->join('tests as t', 'h.test_id', '=', 't.id')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->select(
                't.id as id_de_thi',
                'h.correct_count as so_cau_dung',
                'h.total_questions as tong_so_cau',
                'h.score as diem',
                'h.time_spent as thoi_gian',
                't.content as ten_de_thi',
                'u.name as nguoi_tao',
                'h.created_at'
            )
            // Lọc chỉ lấy các bài làm của người dùng hiện tại.
            ->where('h.user_id', Auth::id());

        // Áp dụng sắp xếp tương tự như phương thức trên.
        if ($sort === 'new') {
            $query->orderBy('h.created_at', 'desc');
        } elseif ($sort === 'old') {
            $query->orderBy('h.created_at', 'asc');
        }

        return $query->get();
    }

    /**
     * Lấy các số liệu thống kê học tập tổng quát của người dùng.
     *
     * @return array Trả về một mảng chứa các số liệu thống kê.
     */
    private function getLearningStats()
    {
        // Lấy ID của người dùng hiện tại.
        $userId = Auth::id();

        // 1. Tính tổng số thẻ học trên toàn hệ thống (do tất cả user tạo).
        $totalCards = DB::table('cards as c')
            ->join('questions as q', 'c.id', '=', 'q.card_id')
            ->distinct('c.id')
            ->count('c.id');

        // 2. Tính số thẻ mà người dùng hiện tại đã học.
        $learnedCards = DB::table('answer_users')
            ->where('user_id', $userId)
            ->distinct('question_id')
            ->count('question_id');

        // 3. Tính tỉ lệ hoàn thành học (%).
        $completionRate = $totalCards > 0 ? round(($learnedCards / $totalCards) * 100, 1) : 0;

        // 4. Tính tổng số bài test mà người dùng đã làm.
        $totalTests = DB::table('histories')
            ->where('user_id', $userId)
            ->count();

        // 5. Tính điểm trung bình của các bài test đã làm.
        $avgScore = DB::table('histories')
            ->where('user_id', $userId)
            ->avg('score');

        // 6. Tính tỉ lệ đúng trung bình (%) của tất cả các bài test.
        $accuracyRate = DB::table('histories')
            ->where('user_id', $userId)
            // Sử dụng selectRaw để viết câu lệnh SQL thô.
            // Tính tổng số câu đúng chia cho tổng số câu hỏi, rồi nhân 100 để lấy tỉ lệ phần trăm.
            // Làm tròn kết quả đến 1 chữ số thập phân.
            ->selectRaw('ROUND(SUM(correct_count) / SUM(total_questions) * 100, 1) as accuracy')
            ->value('accuracy');

        // 7. Tính thời gian trung bình (giây) để hoàn thành một câu hỏi.
        $avgTimePerQuestion = DB::table('histories')
            ->where('user_id', $userId)
            // Sử dụng selectRaw để viết câu lệnh SQL thô.
            // Tính tổng thời gian làm bài (được chuyển đổi sang giây) chia cho tổng số câu hỏi.
            // Làm tròn kết quả đến 2 chữ số thập phân.
            ->selectRaw('ROUND(SUM(TIME_TO_SEC(time_spent)) / SUM(total_questions), 2) as avg_time')
            ->value('avg_time');

        // 8. Đếm số thẻ "khó" mà người dùng chưa ôn lại.
        $difficultCount = DB::table('difficult_cards')
            ->where('user_id', $userId)
            ->where('is_resolved', 0)
            ->count();

        // Trả về một mảng chứa tất cả các số liệu thống kê.
        return [
            'learnedCards'       => $learnedCards,
            'totalCards'         => $totalCards,
            'completionRate'     => $completionRate,
            'totalTests'         => $totalTests,
            'avgScore'           => round($avgScore, 1),
            'accuracyRate'       => $accuracyRate,
            'avgTimePerQuestion' => $avgTimePerQuestion,
            'difficultCount'     => $difficultCount
        ];
    }
}
