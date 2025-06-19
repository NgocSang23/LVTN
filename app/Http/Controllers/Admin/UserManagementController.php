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
        $users = User::whereNull('roles')->orWhere('roles', 'pending')->get();

        return response()->json($users);
    }

    /**
     * Xoá người dùng theo ID
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Xoá người dùng thành công']);
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
}
