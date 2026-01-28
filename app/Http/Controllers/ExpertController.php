<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expert;
use Illuminate\Support\Facades\Storage;

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
        // A. Validasi
        $fields = $request->validate([
            'name' => 'required|string',
            'title' => 'required|string',
            'category' => 'required|string',
            'fee' => 'required|integer',
            'wa_number' => 'required|string|starts_with:62',
            // Ubah validasi foto jadi file image
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // B. Logika Upload Foto
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            // Simpan ke folder 'experts' di public
            $path = $file->store('experts', 'public');
            // Masukkan URL lengkap ke array $fields
            $fields['photo'] = url('storage/' . $path);
        }

        // C. Simpan ke Database
        $expert = Expert::create($fields);

        return response()->json([
            'message' => 'Data Konsultan berhasil ditambahkan!',
            'data' => $expert
        ], 201);
    }

    // 4. Update Expert (Ganti Foto & Hapus Foto Lama)
    public function update(Request $request, $id)
    {
        $expert = Expert::find($id);
        if (!$expert)
            return response()->json(['message' => 'Not Found'], 404);

        // A. Validasi (Semua nullable karena update)
        $request->validate([
            'name' => 'string',
            'title' => 'string',
            'category' => 'string',
            'fee' => 'integer',
            'wa_number' => 'string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // B. Ambil data inputan (kecuali foto dulu)
        $data = $request->except('photo');

        // C. Cek apakah ada upload foto baru?
        if ($request->hasFile('photo')) {
            // 1. HAPUS FOTO LAMA (Penting! Biar server gak penuh sampah)
            if ($expert->photo) {
                // Trik mengubah URL lengkap balik jadi path relatif
                // Contoh: "http://localhost/storage/experts/abc.jpg" -> "experts/abc.jpg"
                $oldPath = str_replace(url('storage/'), '', $expert->photo);
                Storage::disk('public')->delete($oldPath);
            }

            // 2. Upload Foto Baru
            $file = $request->file('photo');
            $path = $file->store('experts', 'public');

            // 3. Masukkan ke array data update
            $data['photo'] = url('storage/' . $path);
        }

        // D. Update Database
        $expert->update($data);

        return response()->json([
            'message' => 'Data berhasil diupdate',
            'data' => $expert
        ]);
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