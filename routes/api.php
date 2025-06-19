<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\FlashcardModerationController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::get('card_define_essay/{id}', [ApiController::class, 'card_define_essay'])->name('api.card_define');
Route::get('card_multiple_choice/{id}', [ApiController::class, 'card_multiple_choice'])->name('api.card_multiple_choice');

// ===== ADMIN API ROUTES =====
Route::middleware(['auth:sanctum', 'is_admin'])->prefix('admin')->group(function () {

    // --- 1. Quản lý người dùng ---
    Route::get('/users/pending', [UserManagementController::class, 'getPending']);
    Route::delete('/users/{id}', [UserManagementController::class, 'destroy']);
    Route::get('/users/students', [UserManagementController::class, 'getStudents']);
    Route::post('/users/{id}/assign-teacher', [UserManagementController::class, 'assignTeacher']);

    // --- 2. Kiểm duyệt flashcard ---
    Route::get('/flashcards', [FlashcardModerationController::class, 'index']);            // Lấy danh sách chờ duyệt
    Route::post('/flashcards/{id}/approve', [FlashcardModerationController::class, 'approve']); // Duyệt
    Route::delete('/flashcards/{id}', [FlashcardModerationController::class, 'destroy']);  // Xoá

    // --- 3. Thống kê hệ thống ---
    Route::get('/statistics/overview', [StatisticsController::class, 'overview']);         // Tổng quan
    Route::get('/statistics/flashcards', [StatisticsController::class, 'flashcardStats']); // Thống kê flashcard
    Route::get('/statistics/users', [StatisticsController::class, 'userStats']);           // Thống kê người dùng

    // --- 4. Gửi thông báo hệ thống ---
    Route::get('/notifications', [NotificationController::class, 'index']);               // Danh sách thông báo
    Route::post('/notifications', [NotificationController::class, 'store']);              // Gửi thông báo
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);     // Xoá thông báo
});
