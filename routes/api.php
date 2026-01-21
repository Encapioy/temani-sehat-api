<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DailyTrackingController;
use App\Http\Controllers\WisdomCardController;

// AUTHENTIFICATION
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // API Tracking
    Route::post('/tracking', [DailyTrackingController::class, 'store']); // Setor Data
    Route::get('/tracking', [DailyTrackingController::class, 'index']);  // Lihat Data

    // API WISDOM CARD
    Route::get('/wisdom', [WisdomCardController::class, 'getRandom']);
});
