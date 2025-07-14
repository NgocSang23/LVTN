<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Xử lý đăng nhập cho admin
     */
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Thông tin không hợp lệ'], 401);
        }

        // Chỉ cho phép nếu là admin
        if ($user->roles !== 'admin') {
            return response()->json(['message' => 'Không có quyền admin'], 403);
        }

        // ❌ Không cần đăng nhập bằng session
        // ✅ Tạo token sử dụng Sanctum
        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $token = $user->currentAccessToken();
            if ($token) {
                $token->delete();
            }
        }

        return response()->json(['message' => 'Đăng xuất thành công']);
    }
}
