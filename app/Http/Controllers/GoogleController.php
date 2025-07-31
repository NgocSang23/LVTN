<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;


class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Xử lý callback sau khi đăng nhập Google
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Tìm user theo email
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Nếu chưa có user, tạo mới và gán role mặc định là student
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'platform_id' => $googleUser->getId(),
                    'image' => $googleUser->getAvatar(),
                    'roles' => 'student', // Gán mặc định
                ]);
            } else {
                // Nếu đã tồn tại, cập nhật thông tin mới nhất (trừ role)
                $user->update([
                    'name' => $googleUser->getName(),
                    'platform_id' => $googleUser->getId(),
                    'image' => $googleUser->getAvatar(),
                ]);
            }

            // Đăng nhập user
            Auth::guard('web')->login($user);

            return redirect()->intended('/')->with('success', 'Đăng nhập Google thành công!');
        } catch (\Exception $e) {
            Log::error('Google Login Error: ' . $e->getMessage());
            return redirect()->route('user.login')->with('error', 'Đăng nhập Google thất bại!');
        }
    }
}
