<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Expert;
use App\Models\Consultation;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Hitung Total Pasien (Role 'user')
        $totalUsers = User::where('role', 'user')->count();

        // 2. Hitung Total Dokter
        $totalExperts = Expert::count();

        // 3. Hitung Konsultasi Pending (Perlu Tindakan)
        $pendingConsultations = Consultation::where('status', 'pending')->count();

        // 4. Hitung Konsultasi Selesai
        $doneConsultations = Consultation::where('status', 'done')->count();

        // 5. Hitung Estimasi Omzet (Total Fee dari konsultasi yang done)
        // Kita perlu join ke tabel experts untuk tahu harganya
        $totalRevenue = Consultation::where('status', 'done')
            ->join('experts', 'consultations.expert_id', '=', 'experts.id')
            ->sum('experts.fee');

        return response()->json([
            'message' => 'Data Dashboard berhasil diambil',
            'data' => [
                'total_users' => $totalUsers,
                'total_experts' => $totalExperts,
                'pending_consultations' => $pendingConsultations,
                'done_consultations' => $doneConsultations,
                'total_revenue' => $totalRevenue,
                // Bonus: Ambil 5 konsultasi terbaru buat quick view
                'latest_consultations' => Consultation::with(['user', 'expert'])
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get()
            ]
        ]);
    }
}