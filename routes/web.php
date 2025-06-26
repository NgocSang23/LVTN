<?php

use App\AI\Ochat;
use App\Http\Controllers\AiSuggestionController;
use App\Http\Controllers\DifficultCardController;
use App\Http\Controllers\FlashcardDefineEssayController;
use App\Http\Controllers\FlashcardGameController;
use App\Http\Controllers\FlashcardMultipleChoiceController;
use App\Http\Controllers\FlashcardSetController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HomeworkHistoryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// GUEST ROUTES
Route::middleware('guest')->group(function () {
    Route::get('/login', [UserController::class, 'login'])->name('user.login');
    Route::post('/login', [UserController::class, 'post_login'])->name('user.post_login');

    Route::get('/register', [UserController::class, 'register'])->name('user.register');
    Route::post('/register', [UserController::class, 'post_register'])->name('user.post_register');

    Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
});

// AUTH ROUTES
Route::middleware('auth')->prefix('user')->group(function () {
    Route::get('/logout', [UserController::class, 'logout'])->name('user.logout');
    Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
    Route::put('/profile', [UserController::class, 'update_profile'])->name('user.update_profile');

    Route::get('/search', [SearchController::class, 'show'])->name('user.search');

    // Flashcard Define Essay
    Route::get('/flashcard_define_essay/{ids}', [FlashcardDefineEssayController::class, 'show'])->name('user.flashcard_define_essay');

    // Flashcard Games
    Route::prefix('flashcard_define_essay')->group(function () {
        Route::get('game_flashcard/{ids}', [FlashcardGameController::class, 'flashcard'])->name('game.flashcard');
        Route::get('game_match/{ids}', [FlashcardGameController::class, 'match'])->name('game.match');
        Route::get('game_study/{ids}', [FlashcardGameController::class, 'study'])->name('game.study');
        Route::get('game_check/{ids}', [FlashcardGameController::class, 'check'])->name('game.check');
    });

    // Save user answers
    Route::post('/flashcard/answer/save', [FlashcardDefineEssayController::class, 'storeUserAnswerDefine'])->name('flashcard_define_essay.save');
    Route::post('/ai/check-answer', [FlashcardDefineEssayController::class, 'storeUserAnswer'])->name('user.answer_user');

    // Flashcard Resource Routes
    Route::resource('flashcard_define_essay', FlashcardDefineEssayController::class)->except(['show']);
    Route::resource('flashcard_multiple_choice', FlashcardMultipleChoiceController::class);

    // Flashcard Set (new)
    Route::post('/flashcard/share/create', [FlashcardSetController::class, 'createFromCards'])->name('flashcard.share.create');
    Route::post('/flashcard_set/store', [FlashcardSetController::class, 'store'])->name('flashcard_set.store');
    Route::get('/flashcard/share/{slug}', [FlashcardSetController::class, 'publicView'])->name('flashcard.share');

    // Library
    Route::prefix('library')->group(function () {
        Route::get('/define_essay', [FlashcardDefineEssayController::class, 'index'])->name('user.library_define_essay');
        Route::get('/multiple', [FlashcardMultipleChoiceController::class, 'index'])->name('user.library_multiple');
    });

    // History
    Route::prefix('history')->group(function () {
        Route::get('/define_essay', [HomeworkHistoryController::class, 'defineEssay'])->name('user.history_define_essay');
        Route::get('/multiple_choice', [HomeworkHistoryController::class, 'multipleChoice'])->name('user.history_multiple_choice');
        Route::post('/save', [HomeworkHistoryController::class, 'saveHistory'])->name('user.history_save');
    });

    // Difficult Flashcards
    Route::post('/flashcard/difficult', [DifficultCardController::class, 'mark'])->name('flashcard.mark_difficult');
    Route::post('/flashcard/resolved', [DifficultCardController::class, 'resolve'])->name('flashcard.mark_resolved');
    Route::get('/api/flashcard/check-difficult/{id}', function ($id) {
        $card = \App\Models\DifficultCard::where('user_id', auth()->id())
            ->where('question_id', $id)
            ->first();

        return response()->json([
            'is_difficult' => $card !== null,
            'is_resolved' => $card?->is_resolved ?? false,
        ]);
    });

    // AI Suggestion
    Route::post('/ai/suggest-topic', [AiSuggestionController::class, 'suggest']);
});

// DASHBOARD
Route::get('/', [UserController::class, 'dashboard'])->name('user.dashboard');

// DEFINE EDITING
Route::prefix('define')->name('define.')->group(function () {
    Route::get('/{id}/edit', [FlashcardDefineEssayController::class, 'editAll'])->name('edit');
    Route::delete('/{id}', [FlashcardDefineEssayController::class, 'destroyAll'])->name('destroy');
});

// ADMIN SPA
Route::get('/admin/{any?}', function () {
    return view('admin.welcome');
})->where('any', '.*');

// Route::get('/chatbot', function () {
//     return view('chat_bot');
// });

// Route::post('/chatbot', function (Request $request) {
//     $message = $request->input('message');

//     // Gọi hàm gửi tin nhắn từ Ochat
//     $chatbot = new Ochat();
//     $response = $chatbot->send($message);

//     // Đảm bảo response trả về dưới dạng JSON
//     return response()->json(['response' => $response]);
// });
