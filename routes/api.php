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

// AUTHENTIFICATION
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // API Profile & Gamification
    Route::get('/profile', [ProfileController::class, 'show']);   // Lihat Profil + Streak
    Route::put('/profile', [ProfileController::class, 'update']); // Edit Profil
    Route::put('/profile/bio', [ProfileController::class, 'updateBio']); // Edit administrasi
    Route::put('/profile/preferences', [ProfileController::class, 'updatePreferences']); // Edit personalisasi
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);
    Route::get('/stats/me', [StatisticsController::class, 'myStats']); // Statistik Perkembangan

    // API Tracking
    Route::post('/tracking', [DailyTrackingController::class, 'store']); // Setor Data
    Route::get('/tracking', [DailyTrackingController::class, 'index']);  // Lihat Data

    // API Fitur Pendampingan Cerdas
    Route::get('/recommendations/daily', [RecommendationController::class, 'getDailyRecommendation']);

    // API WISDOM CARD
    Route::middleware('throttle:10,1')->group(function () {
        Route::get('/wisdom', [WisdomCardController::class, 'getRandom']);
    });

    // API Edukasi
    Route::get('/contents', [ContentController::class, 'index']); // Daftar Konten
    Route::get('/contents/{id}', [ContentController::class, 'show']); // Detail Konten

    // API Konsultasi
    Route::get('/experts', [ExpertController::class, 'index']); // List Konsultan
    Route::post('/consultations', [ConsultationController::class, 'store']); // Booking
    Route::get('/consultations', [ConsultationController::class, 'index']); // Riwayat
    Route::put('/consultations/{id}/cancel', [ConsultationController::class, 'cancel']); // Batalkan Booking
    Route::post('/consultations/{id}/review', [ConsultationController::class, 'review']); // Review Konsultasi

    // API Fitur Chat
    Route::get('/consultations/{id}/messages', [ChatController::class, 'getMessages']);
    Route::post('/consultations/{id}/messages', [ChatController::class, 'sendMessage']);


    // API Product
    Route::get('/products', [ProductController::class, 'index']);

    // API Fitur Transaksi (Toko)
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/my-orders', [OrderController::class, 'myOrders']);
    Route::post('/orders/{id}/pay', [OrderController::class, 'uploadPaymentProof']);



    // ===== ADMIN DASHBOARD =====
    Route::group(['middleware' => ['admin']], function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']); // Statistik
        Route::get('/stats/monitoring', [StatisticsController::class, 'riskMonitoring']); // Antisipasi User Kritis

        // API Consultations
        Route::put('/consultations/{id}/status', [ConsultationController::class, 'updateStatus']); // Approve Booking
        Route::post('/experts', [ExpertController::class, 'store']); // Add Expert
        Route::put('/experts/{id}', [ExpertController::class, 'update']); // Update Expert
        Route::delete('/experts/{id}', [ExpertController::class, 'destroy']); // Delete Expert

        // API Product
        Route::post('/products', [ProductController::class, 'store']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        // API Transaksi
        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    });
});

