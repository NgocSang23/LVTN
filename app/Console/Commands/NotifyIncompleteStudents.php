<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Models\ClassRoom;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotifyIncompleteStudents extends Command
{
    /**
     * Tên lệnh được đăng ký dùng trong Scheduler hoặc terminal.
     */
    protected $signature = 'app:notify-incomplete-students';

    /**
     * Mô tả lệnh – xuất hiện khi chạy php artisan list
     */
    protected $description = 'Gửi thông báo cho học viên chưa làm bài kiểm tra hoặc chưa nộp bài tập đúng hạn';

    /**
     * Hàm xử lý chính khi chạy lệnh
     */
    public function handle()
    {
        $now = now(); // Thời gian hiện tại

        // ==========================================
        // 1️⃣ Gửi thông báo cho học viên khi bài tập sắp hết hạn (< 24h)
        // ==========================================

        // Lấy tất cả các bài tập có deadline trong khoảng 24h tới
        $upcomingAssignments = Assignment::with('flashcardSet', 'classrooms.members')
            ->where('deadline', '>', $now) // Deadline còn hiệu lực
            ->where('deadline', '<=', $now->copy()->addDay()) // Nhưng dưới 24h nữa
            ->get();

        // Duyệt qua từng bài tập
        foreach ($upcomingAssignments as $assignment) {
            // Mỗi bài tập có thể thuộc nhiều lớp học (quan hệ n-n)
            foreach ($assignment->classrooms as $classroom) {
                foreach ($classroom->members as $student) {
                    // Gửi thông báo cho từng học viên trong lớp
                    Notification::firstOrCreate([
                        'user_id' => $student->id,
                        'title' => '⏰ Sắp hết hạn bài tập',
                        'message' => 'Bài "' . $assignment->flashcardSet->title . '" trong lớp "' . $classroom->name . '" sẽ hết hạn lúc ' . Carbon::parse($assignment->deadline)->format('H:i d/m'),
                    ]);
                }
            }
        }

        // ==========================================
        // 2️⃣ Báo cho giáo viên nếu có học viên chưa nộp bài tập sau deadline
        // ==========================================

        $expiredAssignments = Assignment::with('flashcardSet', 'classrooms.members')
            ->where('deadline', '<', $now) // Các bài tập đã quá hạn
            ->get();

        // Duyệt qua từng bài tập đã hết hạn
        foreach ($expiredAssignments as $assignment) {
            foreach ($assignment->classrooms as $classroom) {
                $teacher = $classroom->teacher; // Lấy giáo viên của lớp

                foreach ($classroom->members as $student) {
                    // Kiểm tra học viên này đã nộp bài chưa (trong bảng assignment_submissions)
                    $hasSubmitted = DB::table('assignment_submissions')
                        ->where('assignment_id', $assignment->id)
                        ->where('user_id', $student->id)
                        ->exists();

                    // Nếu chưa nộp thì gửi thông báo cho giáo viên
                    if (! $hasSubmitted) {
                        Notification::firstOrCreate([
                            'user_id' => $teacher->id,
                            'title' => '📌 Học viên chưa nộp bài',
                            'message' => 'Học viên "' . $student->name . '" chưa nộp bài "' . $assignment->flashcardSet->title . '" đúng hạn trong lớp "' . $classroom->name . '".',
                        ]);
                    }
                }
            }
        }

        // ==========================================
        // 3️⃣ Nhắc học viên nếu chưa làm bài kiểm tra nào trong lớp
        // ==========================================

        $classrooms = ClassRoom::with(['members', 'tests.histories'])->get();

        foreach ($classrooms as $classroom) {
            $students = $classroom->members;

            // Tập hợp các user_id đã từng làm bất kỳ bài kiểm tra nào trong lớp
            $doneUserIds = $classroom->tests
                ->flatMap(fn($test) => $test->histories->pluck('user_id')) // Lấy user_id từ histories của từng test
                ->unique(); // Loại trùng

            // Lọc ra danh sách học viên chưa có trong danh sách làm bài
            $incompleteStudents = $students->whereNotIn('id', $doneUserIds);

            foreach ($incompleteStudents as $student) {
                Notification::firstOrCreate([
                    'user_id' => $student->id,
                    'title' => '📌 Nhắc nhở làm bài kiểm tra',
                    'message' => "Bạn vẫn chưa hoàn thành bài kiểm tra nào trong lớp '{$classroom->name}'",
                ]);
            }
        }

        // Ghi log ra console (nếu chạy bằng terminal)
        $this->info('✅ Đã gửi thông báo cho bài kiểm tra & bài tập sắp/đã hết hạn.');
    }
}
