<?php

namespace App\Console\Commands;

use App\Models\ClassRoom;
use App\Models\Notification;
use Illuminate\Console\Command;

class NotifyIncompleteStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-incomplete-students';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi thông báo cho học viên chưa làm bài kiểm tra trong lớp';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $classrooms = ClassRoom::with(['members', 'tests.histories'])->get();

        foreach ($classrooms as $classroom) {
            $students = $classroom->members;

            $doneUserIds = $classroom->tests
                ->flatMap(fn($test) => $test->histories->pluck('user_id'))
                ->unique();

            $incompleteStudents = $students->whereNotIn('id', $doneUserIds);

            foreach ($incompleteStudents as $student) {
                Notification::firstOrCreate([
                    'user_id' => $student->id,
                    'title' => '📌 Nhắc nhở làm bài kiểm tra',
                    'message' => "Bạn vẫn chưa hoàn thành bài kiểm tra nào trong lớp '{$classroom->name}'",
                ]);
            }
        }

        $this->info('Thông báo đã được gửi cho học viên chưa hoàn thành bài kiểm tra.');
    }
}
