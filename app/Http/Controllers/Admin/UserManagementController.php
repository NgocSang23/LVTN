<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    /**
     * Lấy danh sách người dùng đang chờ duyệt (ví dụ: role rỗng hoặc là "pending")
     */
    public function getPending()
    {
        // Lấy tất cả người dùng KHÔNG phải admin
        $users = User::where('roles', '!=', 'admin')->get();

        return response()->json($users);
    }

    /**
     * Lấy danh sách người dùng có role là học sinh (student)
     */
    public function getStudents()
    {
        $users = User::where('roles', 'student')->get();

        return response()->json($users);
    }

    /**
     * Gán quyền giáo viên cho người dùng
     */
    public function assignTeacher($id)
    {
        $user = User::findOrFail($id);
        $user->roles = 'teacher';
        $user->save();

        return response()->json(['message' => 'Gán quyền giáo viên thành công']);
    }

    public function revokeTeacher($id)
    {
        $user = User::findOrFail($id);
        $user->roles = 'student';
        $user->save();

        return response()->json(['message' => 'Huỷ quyền giáo viên thông']);
    }
}
