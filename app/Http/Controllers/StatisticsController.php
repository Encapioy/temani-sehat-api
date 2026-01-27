<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyTracking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    // 1. STATISTIK USER (Untuk Grafik di HP User)
    public function myStats(Request $request)
    {
        $userId = $request->user()->id;
        $sevenDaysAgo = Carbon::now()->subDays(7);

        // Ambil data tracking 7 hari terakhir
        $trackings = DailyTracking::where('user_id', $userId)
            ->where('date', '>=', $sevenDaysAgo)
            ->orderBy('date', 'asc')
            ->get(['date', 'mood', 'mood_score', 'physical_symptoms']);

        // Hitung dominasi mood (Contoh: Senang 3x, Sedih 1x)
        $moodSummary = $trackings->groupBy('mood')->map->count();

        return response()->json([
            'message' => 'Statistik mingguan berhasil diambil',
            'data' => [
                'history' => $trackings, // Buat grafik garis
                'mood_summary' => $moodSummary // Buat pie chart
            ]
        ]);
    }

    // 2. MONITORING ADMIN (Early Warning System) 🚨
    public function riskMonitoring()
    {
        // Logika: Cari User yang dalam 7 hari terakhir sering lapor mood negatif
        // Mood negatif: sedih, cemas, takut, putus asa, marah

        $negativeMoods = ['sedih', 'cemas', 'takut', 'putus asa', 'marah'];
        $sevenDaysAgo = Carbon::now()->subDays(7);

        // Ambil user yang punya tracking mood negatif >= 3 kali seminggu
        $atRiskUsers = User::whereHas('trackings', function ($query) use ($negativeMoods, $sevenDaysAgo) {
            $query->whereIn('mood', $negativeMoods)
                ->where('date', '>=', $sevenDaysAgo);
        }, '>=', 3) // Angka 3 adalah ambang batas (threshold) bahaya
            ->with([
                'trackings' => function ($q) use ($sevenDaysAgo) {
                    // Sertakan data tracking terakhirnya biar admin tau kenapa dia bahaya
                    $q->where('date', '>=', $sevenDaysAgo)->orderBy('date', 'desc');
                }
            ])
            ->get();

        return response()->json([
            'message' => 'Daftar User yang butuh perhatian khusus (High Risk)',
            'total_risk_users' => $atRiskUsers->count(),
            'data' => $atRiskUsers
        ]);
    }
}