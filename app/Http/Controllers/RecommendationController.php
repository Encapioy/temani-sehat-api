<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyTracking;
use App\Models\Content;
use App\Models\Product;

class RecommendationController extends Controller
{
    public function getDailyRecommendation(Request $request)
    {
        $user = $request->user();

        // 1. Cek Tracking Terakhir User (Hari ini atau Kemarin)
        $lastTracking = DailyTracking::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->first();

        // Default Kategori (Kalau belum pernah tracking)
        $recommendedCategory = 'Pola Makan';
        $reason = "Mari mulai hidup sehat dengan menjaga asupan nutrisi.";

        // --- [LOGIC 1: Analisa Mood & Fisik] ---
        if ($lastTracking) {
            // Skenario A: Mood Buruk / Cemas -> Butuh Ibadah/Ketenangan
            if (in_array($lastTracking->mood, ['sedih', 'cemas', 'marah', 'takut'])) {
                $recommendedCategory = 'Amalan/Ibadah';
                $reason = "Sepertinya mood kamu sedang kurang baik. Coba tenangkan hati dengan amalan ini.";
            }
            // Skenario B: Keluhan Fisik -> Butuh Terapi/Olahraga
            elseif (!empty($lastTracking->physical_symptoms) && $lastTracking->physical_symptoms != 'Sehat') {
                $recommendedCategory = 'Olahraga/Terapi';
                $reason = "Untuk meredakan {$lastTracking->physical_symptoms}-mu.";
            }
            // Skenario C: Kalau Mood Bagus tapi jarang minum -> Pola Makan
            else {
                $recommendedCategory = 'Pola Makan';
                $reason = "Kondisimu prima! Pertahankan nutrisinya.";
            }
        }

        // --- [LOGIC 2: Sentuhan Personal (Sahabat)] ---
        // Kita gabungkan alasan dasar dengan Target Sehat user (kalau ada)
        $personalTouch = "";
        if ($user->health_goals) {
            $personalTouch = " Ingat targetmu: '{$user->health_goals}'. Semangat!";
        }

        // Gabungkan pesan
        $finalGuidance = "Halo {$user->name}, {$reason}{$personalTouch}";

        // --- [LOGIC 3: Cari Konten] ---
        $content = Content::where('category', $recommendedCategory)
            ->inRandomOrder()
            ->first();

        // Jika konten kosong, ambil random apa saja
        if (!$content) {
            $content = Content::inRandomOrder()->first();
            $reason = "Rekomendasi harian untuk menemani harimu.";
        }

        // 2. [BARU] Cari PRODUK yang Relevan 🛒
        // Kita mapping dulu Kategori Konten -> Kategori Produk
        $targetProductCategory = 'Makanan Sehat'; // Default

        if ($recommendedCategory == 'Amalan/Ibadah')
            $targetProductCategory = 'Perabotan';
        if ($recommendedCategory == 'Olahraga/Terapi')
            $targetProductCategory = 'Alat Kesehatan';
        if ($recommendedCategory == 'Obat')
            $targetProductCategory = 'Obat & Herbal';

        // Query Produk
        $productQuery = Product::where('category', $targetProductCategory);

        // FITUR PENTING: Jangan tawarkan racun! ☠️
        if ($user->allergies && $targetProductCategory == 'Makanan Sehat') {
            // Contoh sederhana: Exclude produk yang namanya mengandung kata alergi
            // (Nanti bisa dikembangkan pakai tag ingredients)
            $allergies = explode(',', $user->allergies); // Pisahkan koma: "Udang, Kacang"
            foreach ($allergies as $allergy) {
                $productQuery->where('name', 'not like', '%' . trim($allergy) . '%')
                    ->where('description', 'not like', '%' . trim($allergy) . '%');
            }
        }

        $product = $productQuery->inRandomOrder()->first();

        return response()->json([
            'message' => 'Rekomendasi berhasil dibuat',
            'guidance_text' => $finalGuidance,
            'recommendation_type' => $recommendedCategory,
            'suggested_content' => $content,
            'suggested_product' => $product
        ]);
    }
}