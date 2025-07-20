<?php

use App\AI\Ochat;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AiSuggestionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\ClassroomTestController;
use App\Http\Controllers\DifficultCardController;
use App\Http\Controllers\FlashcardAssignmentController;
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

// ========== ADMIN ROUTES ==========
Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum'])->get('/api/me', function (Request $request) {
    return $request->user();
});

// ========== GUEST ROUTES ==========
Route::middleware('guest')->group(function () {
    Route::get('/login', [UserController::class, 'login'])->name('user.login');
    Route::post('/login', [UserController::class, 'post_login'])->name('user.post_login');

    Route::get('/register', [UserController::class, 'register'])->name('user.register');
    Route::post('/register', [UserController::class, 'post_register'])->name('user.post_register');

    Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
});

// ========== PUBLIC ROUTES ==========
Route::get('/flashcard/share/{slug}', [FlashcardSetController::class, 'publicView'])->name('flashcard.share');
Route::get('/flashcards/share/base64/{encodedIds}', [FlashcardSetController::class, 'share'])->name('flashcards.share_view');

Route::get('/', [UserController::class, 'dashboard'])->name('user.dashboard');

Route::prefix('define')->name('define.')->group(function () {
    Route::get('/{id}/edit', [FlashcardDefineEssayController::class, 'editAll'])->name('edit');
    Route::delete('/{id}', [FlashcardDefineEssayController::class, 'destroyAll'])->name('destroy');
});

Route::get('/admin/{any?}', function () {
    return view('admin.welcome');
})->where('any', '.*');

// ========== AUTH ROUTES ==========
Route::middleware('auth')->prefix('user')->group(function () {
    // User account
    Route::get('/logout', [UserController::class, 'logout'])->name('user.logout');
    Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
    Route::put('/profile', [UserController::class, 'update_profile'])->name('user.update_profile');

    // Search
    Route::get('/search', [SearchController::class, 'show'])->name('user.search');

    // Flashcard Define Essay
    Route::resource('flashcard_define_essay', FlashcardDefineEssayController::class)->except(['show']);
    Route::get('/flashcard_define_essay/{ids}', [FlashcardDefineEssayController::class, 'show'])->name('user.flashcard_define_essay');

    // Flashcard Multiple Choice
    Route::resource('flashcard_multiple_choice', FlashcardMultipleChoiceController::class);

    // Route load partial HTML block tạo thêm câu hỏi quiz
    Route::get('/quiz/question-partial', function () {
        $index = request()->get('index', 0);
        return view('partials.quiz_create_question_block', compact('index'));
    })->name('quiz.question_partial');

    // Flashcard Set
    Route::post('/flashcard/share/create', [FlashcardSetController::class, 'createFromCards'])->name('flashcard.share.create');
    Route::post('/flashcard_set/store', [FlashcardSetController::class, 'store'])->name('flashcard_set.store');

    // Flashcard Games
    Route::prefix('flashcard_define_essay')->group(function () {
        Route::get('game_flashcard/{ids}', [FlashcardGameController::class, 'flashcard'])->name('game.flashcard');
        Route::get('game_match/{ids}', [FlashcardGameController::class, 'match'])->name('game.match');
        Route::get('game_study/{ids}', [FlashcardGameController::class, 'study'])->name('game.study');
        Route::get('game_fill_blanks/{ids}', [FlashcardGameController::class, 'fillBlanks'])->name('game.fill_blank');
    });

    // Save user answers
    Route::post('/flashcard/answer/save', [FlashcardDefineEssayController::class, 'storeUserAnswerDefine'])->name('flashcard_define_essay.save');
    Route::post('/ai/check-answer', [FlashcardDefineEssayController::class, 'storeUserAnswer'])->name('user.answer_user');

    // Library
    Route::prefix('library')->group(function () {
            Route::get('/', [UserController::class, 'library'])->name('user.library');
    });

    // History
    Route::prefix('history')->group(function () {
        Route::get('/define_essay', [HomeworkHistoryController::class, 'defineEssay'])->name('user.history_define_essay');
        Route::get('/multiple_choice', [HomeworkHistoryController::class, 'multipleChoice'])->name('user.history_multiple_choice');
        Route::post('/save', [HomeworkHistoryController::class, 'saveHistory'])->name('user.history_save');
        Route::get('/multiple/{test_id}', [HomeworkHistoryController::class, 'detailMultipleChoice'])
            ->name('user.history_multiple_choice_detail');
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

    // Classrooms
    Route::get('/classrooms', [ClassroomController::class, 'index'])->name('classrooms.index');
    Route::get('/classrooms/create', [ClassroomController::class, 'create'])->name('classrooms.create');
    Route::get('/classrooms/join', [ClassroomController::class, 'joinForm'])->name('classrooms.joinForm');
    Route::post('/classrooms/join', [ClassroomController::class, 'joinByCode'])->name('classrooms.join');
    Route::get('/classrooms/invite/{code}', [ClassroomController::class, 'inviteLink'])->name('classrooms.inviteLink');
    Route::get('/my-classrooms', [ClassroomController::class, 'myClassrooms'])->name('classrooms.my');

    Route::post('/classrooms', [ClassroomController::class, 'store'])->name('classrooms.store');
    Route::delete('/classrooms/{id}/leave', [ClassroomController::class, 'leave'])->name('classrooms.leave');
    Route::delete('/classrooms/{classroom}/remove-student/{user}', [ClassroomController::class, 'removeStudent'])->name('classrooms.removeStudent');
    Route::get('/classrooms/{id}', [ClassroomController::class, 'show'])->name('classrooms.show');

    // Classroom export
    Route::get('/classrooms/{id}/export-results', [ClassroomController::class, 'exportResults'])->name('classrooms.export');

    // Notifications
    Route::get('/notifications', [UserController::class, 'notifications'])->name('user.notifications');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.delete');
    Route::delete('/notifications', [NotificationController::class, 'deleteAll'])->name('notifications.deleteAll');
    Route::post('/notifications/mark-read', function () {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $user->customNotifications()->where('is_read', false)->update(['is_read' => 1]);
        return response()->json(['status' => 'ok']);
    })->name('notifications.markRead');
    // Route::get('/api/notifications/latest', function () {
    //     /** @var \App\Models\User $user */
    //     $user = auth()->user();
    //     $notifications = $user->customNotifications()->orderByDesc('created_at')->take(5)->get();
    //     $unread = $notifications->where('is_read', false)->count();
    //     $html = view('partials.notification_dropdown', compact('notifications'))->render();
    //     return response()->json(['html' => $html, 'unread' => $unread]);
    // })->name('notifications.ajax');

    Route::post('/classrooms/{id}/notify-incomplete', [ClassroomController::class, 'notifyIncompleteStudents'])
        ->name('classrooms.notifyIncomplete')
        ->middleware('can:teacher');

    Route::post('/classroom-tests/assign', [ClassroomTestController::class, 'assign'])->name('classroom_tests.assign');
    Route::post('/tests/assign', [ClassroomTestController::class, 'assign'])->name('teacher.assignTest');

    // AI Suggestion
    Route::post('/ai/suggest-topic', [AiSuggestionController::class, 'suggest']);
});

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
