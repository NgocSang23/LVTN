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

            // Tìm user theo email hoặc tạo mới
            $user = User::updateOrCreate([
                'email' => $googleUser->getEmail(),
            ], [
                'name' => $googleUser->getName(),
                'platform_id' => $googleUser->getId(),
                'image' => $googleUser->getAvatar(),
            ]);

            // Đăng nhập user
            Auth::guard('web')->login($user);

            // Chuyển hướng về trang trước đó hoặc trang chủ
            return redirect()->intended('/')->with('success', 'Đăng nhập Google thành công!');
        } catch (\Exception $e) {
            return redirect()->route('user.login')->with('error', 'Đăng nhập Google thất bại!');
        }
    }
}
