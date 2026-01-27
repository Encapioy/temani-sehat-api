<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expert;

class ExpertController extends Controller
{
    // 1. Ambil Semua Data Expert (Public)
    public function index(Request $request)
    {
        // Mulai Query
        $query = Expert::query();

        // A. Logika Filter Kategori (Sesuai Mind Map)
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // B. Logika Rating (Yang kita pindahkan dari controller lama)
        // Menghitung jumlah review dan rata-rata bintang
        $query->withCount([
            'consultations as review_count' => function ($q) {
                $q->whereNotNull('rating');
            }
        ])
            ->withAvg([
                'consultations as average_rating' => function ($q) {
                    $q->whereNotNull('rating');
                }
            ], 'rating');

        // Eksekusi dan kembalikan data
        return response()->json(['data' => $query->get()]);
    }

    // 2. Tambah Expert Baru (Khusus Admin)
    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'title' => 'required|string',   // Contoh: Sp.GK (Spesialis Gizi)
            'category' => 'required|string', // Isi: Dokter, Ahli Gizi, Admin, dll
            'fee' => 'required|integer',
            'wa_number' => 'required|string|starts_with:62', // Format 628xxx
            'photo' => 'nullable|string' // Nanti bisa dikembangin upload foto
        ]);

        $expert = Expert::create($fields);

        return response()->json([
            'message' => 'Data Konsultan berhasil ditambahkan!',
            'data' => $expert
        ]);
    }

    // 3. Update Expert (Misal ganti nomor WA)
    public function update(Request $request, $id)
    {
        $expert = Expert::find($id);
        if (!$expert)
            return response()->json(['message' => 'Not Found'], 404);

        $expert->update($request->all());

        return response()->json(['message' => 'Data berhasil diupdate', 'data' => $expert]);
    }

    // 4. Hapus Expert (Khusus Admin)
    public function destroy($id)
    {
        $expert = Expert::find($id);

        if (!$expert) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        // PERINGATAN: Karena di migrasi kita pakai 'onDelete(cascade)',
        // Menghapus Expert akan otomatis menghapus semua riwayat konsultasi dia.
        // Untuk MVP ini tidak apa-apa, tapi di aplikasi real harus hati-hati.
        $expert->delete();

        return response()->json([
            'message' => 'Data Konsultan berhasil dihapus permanen.'
        ]);
    }
}