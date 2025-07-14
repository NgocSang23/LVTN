<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user(); // hoặc auth()->user()

        if ($user && $user->role === 'admin') {
            return $next($request);
        }

        // Log chi tiết lỗi để debug
        Log::warning('❌ Người dùng không có quyền admin.', [
            'user_id' => optional($user)->id,
            'role' => optional($user)->role,
        ]);

        dd(auth('admin')->user());

        return response()->json([
            'message' => 'Không có quyền truy cập (chỉ dành cho Admin)',
        ], 403);
    }
}
