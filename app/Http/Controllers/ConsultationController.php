<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expert;
use App\Models\Consultation;

class ConsultationController extends Controller
{

    // 1. User Booking Jadwal
    public function store(Request $request)
    {
        $request->validate([
            'expert_id' => 'required|exists:experts,id',
            'schedule_date' => 'required|date|after:now', // Jadwal harus masa depan
            'complaint' => 'required|string|max:500'
        ]);

        $expert = Expert::find($request->expert_id);

        $booking = Consultation::create([
            'user_id' => $request->user()->id, // Ambil dari Token
            'expert_id' => $request->expert_id,
            'schedule_date' => $request->schedule_date,
            'complaint' => $request->complaint,
            'status' => 'pending',
            'total_price' => $expert->fee,
            'payment_status' => 'unpaid'
        ]);

        return response()->json([
            'message' => 'Permintaan konsultasi terkirim. Tunggu konfirmasi admin ya!',
            'data' => $booking
        ], 201);
    }

    // 2. User Lihat Riwayat Konsultasi (My Appointments)
    public function index(Request $request)
    {
        // Ambil data konsultasi milik user + data Expert-nya
        $history = Consultation::with('expert') // Eager Loading (biar nama dokternya ikut)
            ->where('user_id', $request->user()->id)
            ->orderBy('schedule_date', 'desc')
            ->get();

        return response()->json(['data' => $history]);
    }

    // 3. Update Status (Untuk Admin/Expert menerima pesanan)
    public function updateStatus(Request $request, $id)
    {
        // Validasi input status
        $request->validate([
            'status' => 'required|in:confirmed,done,cancelled'
        ]);

        // Cari data konsultasi
        $consultation = Consultation::find($id);

        if (!$consultation) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        // Update status
        $consultation->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Status berhasil diubah menjadi ' . $request->status,
            'data' => $consultation
        ]);
    }

    // 4. User Membatalkan Pesanan (Cancel Booking)
    public function cancel(Request $request, $id)
    {
        // Cari konsultasi milik user yang login
        $consultation = Consultation::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$consultation) {
            return response()->json(['message' => 'Data tidak ditemukan atau bukan milikmu'], 404);
        }

        // Cek dulu, kalau sudah 'done' atau 'confirmed' gak boleh batal sembarangan (Optional logic)
        if ($consultation->status === 'done') {
            return response()->json(['message' => 'Konsultasi sudah selesai, tidak bisa dibatalkan'], 400);
        }

        $consultation->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Konsultasi berhasil dibatalkan',
            'data' => $consultation
        ]);
    }

    // 5. User Memberi Review (Hanya jika status 'done')
    public function review(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string'
        ]);

        // Cari konsultasi milik user ini
        $consultation = Consultation::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$consultation) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        // Validasi: Apakah konsultasi sudah selesai?
        if ($consultation->status !== 'done') {
            return response()->json([
                'message' => 'Anda hanya bisa mereview konsultasi yang sudah selesai (done).'
            ], 400);
        }

        // Validasi: Apakah sudah pernah direview?
        if ($consultation->rating) {
            return response()->json(['message' => 'Anda sudah mereview konsultasi ini.'], 400);
        }

        // Simpan Review
        $consultation->update([
            'rating' => $request->rating,
            'review' => $request->review
        ]);

        return response()->json([
            'message' => 'Terima kasih atas ulasan Anda!',
            'data' => $consultation
        ]);
    }
}