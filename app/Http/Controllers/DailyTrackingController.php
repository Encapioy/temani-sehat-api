<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyTracking; // Panggil Modelnya

class DailyTrackingController extends Controller
{
    // 1. Fungsi Setor Laporan (Create)
    public function store(Request $request)
    {
        // Validasi input
        $fields = $request->validate([
            'mood' => 'required|string', // Contoh: "Cemas", "Senang"
            'physical_symptoms' => 'nullable|string', // Contoh: "Pusing", "Nyeri"
            'mood_score' => 'required|integer|min:1|max:5', // Skala 1-5
            'medication_taken' => 'boolean',
            'prayer_completed' => 'boolean',
            'diet_complied' => 'boolean',
            'exercise_done' => 'boolean',
            'notes' => 'nullable|string'
        ]);

        // Simpan ke Database
        // Kuncinya di sini: $request->user()->id
        // Kita ambil ID otomatis dari Token, jadi gak bisa dipalsukan.
        $tracking = DailyTracking::create([
            'mood' => $fields['mood'],
            'physical_symptoms' => $fields['physical_symptoms'] ?? null,
            'user_id' => $request->user()->id,
            'date' => now(), // Otomatis tanggal hari ini
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

    // 2. Fungsi Lihat Riwayat (Read)
    public function index(Request $request)
    {
        // Ambil data tracking HANYA milik user yang sedang login
        $history = DailyTracking::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc') // Yang terbaru di atas
            ->get();

        return response()->json([
            'data' => $history
        ]);
    }
}