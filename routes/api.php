<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('card_define_essay/{id}', [ApiController::class, 'card_define_essay'])->name('api.card_define');
Route::get('card_multiple_choice/{id}', [ApiController::class, 'card_multiple_choice'])->name('api.card_multiple_choice');
