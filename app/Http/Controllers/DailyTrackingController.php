<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyTracking;
use Carbon\Carbon; // Pastikan ini tetap ada

class DailyTrackingController extends Controller
{
    // =================================================================
    // 1. Fungsi Setor Laporan (Create)
    // =================================================================
    public function store(Request $request)
    {
        $fields = $request->validate([
            'mood' => 'required|string',
            'physical_symptoms' => 'nullable|string',
            'mood_score' => 'required|integer|min:1|max:5',
            'medication_taken' => 'boolean',
            'prayer_completed' => 'boolean',
            'diet_complied' => 'boolean',
            'exercise_done' => 'boolean',
            'notes' => 'nullable|string'
        ]);

        $tracking = DailyTracking::create([
            'mood' => $fields['mood'],
            'physical_symptoms' => $fields['physical_symptoms'] ?? null,
            'user_id' => $request->user()->id,
            'date' => now(),
            'mood_score' => $fields['mood_score'],
            'medication_taken' => $fields['medication_taken'] ?? false,
            'prayer_completed' => $fields['prayer_completed'] ?? false,
            'diet_complied' => $fields['diet_complied'] ?? false,
            'exercise_done' => $fields['exercise_done'] ?? false,
            'notes' => $fields['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Laporan hari ini berhasil disimpan, semangat sembuh!',
            'data' => $tracking
        ], 201);
    }

    // =================================================================
    // 2. Fungsi Lihat Riwayat (Read)
    // =================================================================
    public function index(Request $request)
    {
        $history = DailyTracking::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $history
        ]);
    }

    // =================================================================
    // 3. Fungsi Statistik Mingguan (UPDATE FITUR FORMAT FRONTEND)
    // =================================================================
    public function weeklyStatistic(Request $request)
    {
        // Atur bahasa tanggal ke Indonesia (biar jadi Sen, Sel, Rab)
        Carbon::setLocale('id');

        // Tentukan rentang waktu minggu ini (Senin - Minggu)
        $startDate = Carbon::now()->startOfWeek();
        $endDate   = Carbon::now()->endOfWeek();

        // Ambil data dan langsung kita 'Map' (Format ulang untuk Frontend)
        $history = DailyTracking::where('user_id', $request->user()->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    // Data tanggal asli (untuk referensi sistem)
                    'date' => $item->date,
                    
                    // 1. Label Hari (Contoh: "Sen", "Sel") untuk sumbu X grafik
                    'day_label' => Carbon::parse($item->date)->isoFormat('ddd'), 
                    
                    // 2. Persentase (Skala 1-5 dikali 20 biar jadi 100%) untuk tinggi bar grafik
                    'percentage' => $item->mood_score * 20, 
                    
                    // Data pendukung lainnya
                    'mood_score' => $item->mood_score,
                    'mood' => $item->mood,
                    'physical_symptoms' => $item->physical_symptoms,
                ];
            });

        // Hitung Summary Mood (Grouping) untuk diagram lingkaran (pie chart) jika perlu
        $moodSummary = $history->groupBy('mood')
            ->map(function ($item) {
                return $item->count();
            });

        return response()->json([
            'message' => 'Statistik mingguan berhasil diambil',
            'data' => [
                'history' => $history,
                'mood_summary' => $moodSummary,
            ]
        ], 200);
    }
}