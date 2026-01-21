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

// AUTHENTIFICATION
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // API Profile & Gamification
    Route::get('/profile', [ProfileController::class, 'show']);   // Lihat Profil + Streak
    Route::put('/profile', [ProfileController::class, 'update']); // Edit Profil
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);

    // API Tracking
    Route::post('/tracking', [DailyTrackingController::class, 'store']); // Setor Data
    Route::get('/tracking', [DailyTrackingController::class, 'index']);  // Lihat Data

    // API WISDOM CARD
    Route::get('/wisdom', [WisdomCardController::class, 'getRandom']);

    // API Edukasi
    Route::get('/contents', [ContentController::class, 'index']); // Daftar Konten
    Route::get('/contents/{id}', [ContentController::class, 'show']); // Detail Konten

    // API Konsultasi
    Route::get('/experts', [ConsultationController::class, 'getExperts']); // List Dokter
    Route::post('/consultations', [ConsultationController::class, 'store']); // Booking
    Route::get('/consultations', [ConsultationController::class, 'index']); // Riwayat
    Route::put('/consultations/{id}/cancel', [ConsultationController::class, 'cancel']); // Batalkan Booking
    Route::post('/consultations/{id}/review', [ConsultationController::class, 'review']); // Review Konsultasi


    // ===== ADMIN DASHBOARD =====
    Route::group(['middleware' => ['admin']], function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']); // Statistik

        // API Consultations
        Route::put('/consultations/{id}/status', [ConsultationController::class, 'updateStatus']); // Approve Booking


    });
});

