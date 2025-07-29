<?php

use App\Http\Controllers\Admin\FlashcardModerationController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\AI\Ochat;


// ========== PUBLIC API ROUTES ==========
Route::get('card_define_essay/{encodedIds}', [ApiController::class, 'card_define_essay'])->name('api.card_define');
Route::get('card_multiple_choice/{id}', [ApiController::class, 'card_multiple_choice'])->name('api.card_multiple_choice');

// ========== AUTH SANCTUM ROUTES ==========
Route::middleware('auth:sanctum')->get('/notifications/latest', function (Request $request) {
    $user = $request->user();
    $notifications = $user->notifications()->orderByDesc('created_at')->take(5)->get();
    $unread = $notifications->where('is_read', false)->count();
    $html = view('partials.notification_dropdown', compact('notifications'))->render();
    return response()->json(['html' => $html, 'unread' => $unread]);
});

// ========== DUPLICATE (possibly updated) NOTIFICATION ROUTE ==========
Route::middleware('auth:sanctum')->get('/notifications/latest', function () {
    /** @var \App\Models\User $user */
    $user = auth()->user();

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

// ========== ADMIN API ROUTES ==========
Route::middleware(['auth:sanctum', 'is_admin'])->prefix('admin')->group(function () {

    // --- 1. User Management ---
    Route::get('/users/pending', [UserManagementController::class, 'getPending']);
    Route::get('/users/students', [UserManagementController::class, 'getStudents']);
    Route::post('/users/{id}/assign-teacher', [UserManagementController::class, 'assignTeacher']);
    Route::post('/users/{id}/revoke-teacher', [UserManagementController::class, 'revokeTeacher']);

    // --- 2. Flashcard Moderation ---
    Route::get('/flashcards', [FlashcardModerationController::class, 'index']);
    Route::post('/flashcards/{id}/approve', [FlashcardModerationController::class, 'approve']);
    Route::delete('/flashcards/{id}', [FlashcardModerationController::class, 'destroy']);

    // --- 3. System Statistics ---
    Route::get('/statistics/overview', [StatisticsController::class, 'overview']);
    Route::get('/statistics/review-frequency', [StatisticsController::class, 'reviewFrequency']);

    // --- 4. System Notifications ---
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications', [NotificationController::class, 'destroyAll']);
});

Route::post('/compare-answer', function (Request $request) {
    $validated = $request->validate([
        'question' => 'required|string',
        'user_answer' => 'required|string',
        'correct_answer' => 'required|string',
        'last_feedback' => 'nullable|string',
    ]);

    $ai = new Ochat();
    $result = $ai->compareAnswer(
        $validated['question'],
        $validated['user_answer'],
        $validated['correct_answer'],
        $validated['last_feedback'] ?? null
    );

    return response()->json($result);
});
