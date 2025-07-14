<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassroomTestController extends Controller
{
    public function assign(Request $request)
    {
        $request->validate([
            'classroom_ids' => 'required|array',
            'classroom_ids.*' => 'exists:class_rooms,id',
            'test_id' => 'required|exists:tests,id',
            'deadline' => 'required|date',
        ]);

        foreach ($request->classroom_ids as $classroomId) {
            DB::table('classroom_tests')->updateOrInsert(
                [
                    'classroom_id' => $classroomId,
                    'test_id' => $request->test_id,
                ],
                [
                    'deadline' => $request->deadline,
                    'updated_at' => now(),
                ]
            );

            // Gửi thông báo đến các học viên trong lớp
            $classroom = ClassRoom::with('members')->find($classroomId);
            foreach ($classroom->members as $student) {
                if ($student->id !== auth()->id()) {
                    Notification::create([
                        'user_id' => $student->id,
                        'title' => '📝 Bài kiểm tra mới được giao',
                        'message' => 'Giáo viên đã giao một bài kiểm tra mới trong lớp "' . $classroom->name . '". Hãy vào lớp để làm ngay!',
                        'is_read' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        return back()->with('success', '✅ Đã giao bài kiểm tra cho các lớp học thành công!');
    }
}
