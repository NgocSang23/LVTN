<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\FlashcardModerationController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('card_define_essay/{encodedIds}', [ApiController::class, 'card_define_essay'])->name('api.card_define');
Route::get('card_multiple_choice/{id}', [ApiController::class, 'card_multiple_choice'])->name('api.card_multiple_choice');

Route::middleware('auth:sanctum')->get('/notifications/latest', function (Request $request) {
    $user = $request->user();
    $notifications = $user->notifications()->orderByDesc('created_at')->take(5)->get();
    $unread = $notifications->where('is_read', false)->count();
    $html = view('partials.notification_dropdown', compact('notifications'))->render();
    return response()->json(['html' => $html, 'unread' => $unread]);
});

// ===== ADMIN API ROUTES =====
Route::middleware(['auth:sanctum', 'is_admin'])->prefix('admin')->group(function () {

    // --- 1. Quản lý người dùng ---
    Route::get('/users/pending', [UserManagementController::class, 'getPending']);
    Route::get('/users/students', [UserManagementController::class, 'getStudents']);
    Route::post('/users/{id}/assign-teacher', [UserManagementController::class, 'assignTeacher']);
    Route::post('users/{id}/revoke-teacher', [UserManagementController::class, 'revokeTeacher']);

    // --- 2. Kiểm duyệt flashcard ---
    Route::get('/flashcards', [FlashcardModerationController::class, 'index']);            // Lấy danh sách chờ duyệt
    Route::post('/flashcards/{id}/approve', [FlashcardModerationController::class, 'approve']); // Duyệt
    Route::delete('/flashcards/{id}', [FlashcardModerationController::class, 'destroy']);  // Xoá

    // --- 3. Thống kê hệ thống ---
    Route::get('/statistics/overview', [StatisticsController::class, 'overview']);         // Tổng quan
    Route::get('/statistics/review-frequency', [StatisticsController::class, 'reviewFrequency']); // Thống kê lượt hoạt động mỗi ngày trong 7 ngày gần nhất:

    // --- 4. Gửi thông báo hệ thống ---
    Route::get('/notifications', [NotificationController::class, 'index']);               // Danh sách thông báo
    Route::post('/notifications', [NotificationController::class, 'store']);              // Gửi thông báo
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);     // Xoá thông báo
    Route::delete('/notifications', [NotificationController::class, 'destroyAll']);
});

Route::middleware('auth:sanctum')->get('/notifications/latest', function () {
    /** @var \App\Models\User $user */
    $user = auth()->user();

    // 🔧 Đổi từ notifications() sang customNotifications()
    $notifications = $user->notifications()->latest()->take(5)->get();

    $dropdownHtml = view('partials.notification_dropdown_items', compact('notifications'))->render();
    $fullHtml = view('partials.notification_card_list', compact('notifications'))->render();

    $unread = $notifications->where('is_read', false)->count();

    return response()->json([
        'html' => $dropdownHtml,
        'full_html' => $fullHtml,
        'unread' => $unread,
    ]);
});
