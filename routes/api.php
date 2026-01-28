<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DailyTrackingController;
use App\Http\Controllers\WisdomCardController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpertController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\OrderController;

// AUTHENTICATION
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // API Profile & Gamification
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/bio', [ProfileController::class, 'updateBio']);
    Route::put('/profile/preferences', [ProfileController::class, 'updatePreferences']);
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);
    Route::get('/stats/me', [StatisticsController::class, 'myStats']);

    // API Tracking
    Route::post('/tracking', [DailyTrackingController::class, 'store']);
    Route::get('/tracking', [DailyTrackingController::class, 'index']);
    Route::get('/tracking/weekly', [DailyTrackingController::class, 'weeklyStatistic']);

    // API Fitur Pendampingan Cerdas
    Route::get('/recommendations/daily', [RecommendationController::class, 'getDailyRecommendation']);

    // API WISDOM CARD
    Route::middleware('throttle:10,1')->group(function () {
        Route::get('/wisdom', [WisdomCardController::class, 'getRandom']);
    });

    // API Edukasi
    Route::get('/contents', [ContentController::class, 'index']);
    Route::get('/contents/{id}', [ContentController::class, 'show']);

    // API Konsultasi
    Route::get('/experts', [ExpertController::class, 'index']);
    Route::post('/consultations', [ConsultationController::class, 'store']);
    Route::get('/consultations', [ConsultationController::class, 'index']);
    Route::put('/consultations/{id}/cancel', [ConsultationController::class, 'cancel']);
    Route::post('/consultations/{id}/review', [ConsultationController::class, 'review']);

    // API Fitur Chat
    Route::get('/consultations/{id}/messages', [ChatController::class, 'getMessages']);
    Route::post('/consultations/{id}/messages', [ChatController::class, 'sendMessage']);

    // API Product (Public List)
    Route::get('/products', [ProductController::class, 'index']);

    // API Fitur Transaksi (User Side)
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/my-orders', [OrderController::class, 'myOrders']);
    Route::post('/orders/{id}/pay', [OrderController::class, 'uploadPaymentProof']);


    // ===== ADMIN DASHBOARD =====
    Route::group(['middleware' => ['admin']], function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/stats/monitoring', [StatisticsController::class, 'riskMonitoring']);

        // API Content Admin
        Route::post('/contents', [ContentController::class, 'store']);
        Route::put('/contents/{id}', [ContentController::class, 'update']);
        Route::delete('/contents/{id}', [ContentController::class, 'destroy']);

        // API Consultations Admin
        Route::put('/consultations/{id}/status', [ConsultationController::class, 'updateStatus']);
        Route::post('/experts', [ExpertController::class, 'store']);
        Route::put('/experts/{id}', [ExpertController::class, 'update']);
        Route::delete('/experts/{id}', [ExpertController::class, 'destroy']);

        // API Product Admin
        Route::post('/products', [ProductController::class, 'store']);
        Route::post('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        // --- API Transaksi Admin (TAMBAHAN BARU) ---
        Route::get('/orders', [OrderController::class, 'getAllOrders']); // Get List Produk yang di-order (Semua User)
        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']); // Update status bayar/kirim
    });
});