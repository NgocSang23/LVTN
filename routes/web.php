<?php

use App\AI\Ochat;
use App\Http\Controllers\FlashcardDefineEssayController;
use App\Http\Controllers\FlashcardGameController;
use App\Http\Controllers\FlashcardMultipleChoiceController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HomeworkHistoryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Guest routes: login, register, Google
Route::middleware('guest')->group(function () {
    Route::get('/login', [UserController::class, 'login'])->name('user.login');
    Route::post('/login', [UserController::class, 'post_login'])->name('user.post_login');

    Route::get('/register', [UserController::class, 'register'])->name('user.register');
    Route::post('/register', [UserController::class, 'post_register'])->name('user.post_register');

    Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
});

// Authenticated user routes
Route::middleware('auth')->prefix('user')->group(function () {
    Route::get('/logout', [UserController::class, 'logout'])->name('user.logout');
    Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
    Route::put('/profile', [UserController::class, 'update_profile'])->name('user.update_profile');

    Route::get('/search', [SearchController::class, 'show'])->name('user.search');

    // Flashcard define essay (custom show)
    Route::get('/flashcard_define_essay/{ids}', [FlashcardDefineEssayController::class, 'show'])->name('user.flashcard_define_essay');

    // Flashcard game routes
    Route::prefix('flashcard_define_essay')->group(function () {
        Route::get('game_flashcard/{ids}', [FlashcardGameController::class, 'flashcard'])->name('game.flashcard');
        Route::get('game_match/{ids}', [FlashcardGameController::class, 'match'])->name('game.match');
        Route::get('game_study/{ids}', [FlashcardGameController::class, 'study'])->name('game.study');
        Route::get('game_check/{ids}', [FlashcardGameController::class, 'check'])->name('game.check');
    });

    // Flashcard answer saving
    Route::post('/flashcard/answer/save', [FlashcardDefineEssayController::class, 'storeUserAnswerDefine'])->name('flashcard_define_essay.save');
    Route::post('/ai/check-answer', [FlashcardDefineEssayController::class, 'storeUserAnswer'])->name('user.answer_user');

    // Flashcard resources (exclude `show` to avoid conflict)
    Route::resource('flashcard_define_essay', FlashcardDefineEssayController::class)->except(['show']);
    Route::resource('flashcard_multiple_choice', FlashcardMultipleChoiceController::class);

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
});

// Dashboard (protected)
Route::get('/', [UserController::class, 'dashboard'])->name('user.dashboard');

// Define editing
Route::prefix('define')->name('define.')->group(function () {
    Route::get('/{id}/edit', [FlashcardDefineEssayController::class, 'editAll'])->name('edit');
    Route::delete('/{id}', [FlashcardDefineEssayController::class, 'destroyAll'])->name('destroy');
});

// Admin SPA (Vue)
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
