<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoveGuruController;
use App\Http\Controllers\SlayProfileController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\DatingKitController;
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

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/questions', [SlayProfileController::class, 'questions']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [UserProfileController::class, 'show']);
    Route::put('/profile', [UserProfileController::class, 'update']);
    Route::post('/analyze', [SlayProfileController::class, 'analyze']);
    Route::post('/guru/chat', [LoveGuruController::class, 'chat']);
    Route::get('/kits', [DatingKitController::class, 'index']);
    Route::get('/kits/latest', [DatingKitController::class, 'latest']);
});
