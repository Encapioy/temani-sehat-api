<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Content;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    // 1. Ambil Semua Konten (Bisa Filter Kategori)
    public function index(Request $request)
    {
        $query = Content::query();

        // Fitur Filter: ?category=Ginjal
        if ($request->has('category')) {
            $query->where('category', 'LIKE', '%' . $request->category . '%');
        }

        // Fitur Filter: ?type=video
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        return response()->json([
            'message' => 'Daftar konten berhasil diambil',
            'data' => $query->get()
        ]);
    }

    // 2. Ambil Detail 1 Konten
    public function show($id)
    {
        $content = Content::find($id);

        if (!$content) {
            return response()->json(['message' => 'Konten tidak ditemukan'], 404);
        }

        return response()->json([
            'data' => $content
        ]);
    }

    // 3. Tambah Konten Baru
    public function store(Request $request)
    {
        // A. Cek Role: Harus Admin
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak. Hanya Admin.'], 403);
        }

        // B. Validasi Input
        $request->validate([
            'title' => 'required|string',
            'type' => 'required|in:article,video',
            'category' => 'required|string',
            // Validasi Bersyarat:
            // Kalau tipe-nya article, body WAJIB diisi.
            // Kalau tipe-nya video, url WAJIB diisi.
            'body' => 'required_if:type,article',
            'url' => 'required_if:type,video',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // C. Upload Thumbnail (Jika ada)
        $thumbnailUrl = null;
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $path = $file->store('thumbnails', 'public');
            $thumbnailUrl = url('storage/' . $path);
        }

        // D. Simpan ke Database
        $content = Content::create([
            'title' => $request->title,
            'type' => $request->type,
            'category' => $request->category,
            'body' => $request->body,
            'url' => $request->url,
            'description' => $request->description, // Ringkasan pendek
            'duration' => $request->duration,       // "5 Menit"
            'thumbnail' => $thumbnailUrl
        ]);

        return response()->json([
            'message' => 'Konten berhasil diterbitkan!',
            'data' => $content
        ], 201);
    }

    // 4. Update Konten
    public function update(Request $request, $id)
    {
        // A. Cek Role
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $content = Content::find($id);
        if (!$content)
            return response()->json(['message' => 'Konten tidak ditemukan'], 404);

        // B. Validasi (Semua nullable karena ini edit, user mungkin gak mau ubah semua)
        $request->validate([
            'title' => 'string',
            'type' => 'in:article,video',
            'thumbnail' => 'nullable|image|max:2048'
        ]);

        // C. Cek Upload Thumbnail Baru
        if ($request->hasFile('thumbnail')) {
            // Hapus gambar lama dulu biar server gak penuh (Opsional tapi Recommended)
            if ($content->thumbnail) {
                // Trik ambil path relatif dari URL lengkap
                $oldPath = str_replace(url('storage/'), '', $content->thumbnail);
                Storage::disk('public')->delete($oldPath);
            }

            // Upload yang baru
            $file = $request->file('thumbnail');
            $path = $file->store('thumbnails', 'public');
            $content->thumbnail = url('storage/' . $path);
        }

        // D. Update Data Lainnya
        // Kita pakai $request->only biar aman
        $content->update($request->only([
            'title',
            'type',
            'category',
            'body',
            'url',
            'description',
            'duration'
        ]));

        // Trik: kalau thumbnail diupdate manual di atas, save lagi
        $content->save();

        return response()->json([
            'message' => 'Konten berhasil diperbarui!',
            'data' => $content
        ]);
    }

    // 5. Hapus Konten
    public function destroy(Request $request, $id)
    {
        // A. Cek Role
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $content = Content::find($id);
        if (!$content)
            return response()->json(['message' => 'Konten tidak ditemukan'], 404);

        // B. Hapus File Gambar Fisik (Bersih-bersih)
        if ($content->thumbnail) {
            $oldPath = str_replace(url('storage/'), '', $content->thumbnail);
            Storage::disk('public')->delete($oldPath);
        }

        // C. Hapus Data di Database
        $content->delete();

        return response()->json(['message' => 'Konten berhasil dihapus permanen.']);
    }
}