<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // 1. Lihat Semua Produk (Bisa diakses User & Admin)
    public function index(Request $request)
    {
        $query = Product::query();

        // Fitur Filter Kategori (Misal: User cari 'Obat')
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        return response()->json(['data' => $query->get()]);
    }

    // 2. Tambah Produk (Khusus Admin)
    public function store(Request $request)
    {
        // 1. Validasi (Perhatikan bagian image)
        $request->validate([
            'name' => 'required|string',
            'category' => 'required|string',
            'price' => 'required|integer',
            'stock' => 'required|integer',
            'description' => 'nullable|string',

            // Ubah jadi 'image' dan batasi tipe file (jpg, png) serta ukuran (max 2MB)
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // 2. Siapkan wadah URL default (kalau admin gak upload gambar)
        $finalImageUrl = null;

        // 3. Cek apakah Admin meng-upload file?
        if ($request->hasFile('image')) {
            // A. Ambil filenya
            $file = $request->file('image');

            // B. Simpan ke folder 'storage/app/public/products'
            // Fungsi store() akan mengembalikan path (misal: products/asd1234.jpg)
            $path = $file->store('products', 'public');

            // C. Ubah path jadi URL lengkap biar bisa diakses Frontend
            // Hasil: http://localhost:8000/storage/products/asd1234.jpg
            $finalImageUrl = url('storage/' . $path);
        }

        // 4. Simpan ke Database
        $product = Product::create([
            'name' => $request->name,
            'category' => $request->category,
            'price' => $request->price,
            'stock' => $request->stock,
            'description' => $request->description,
            'image_url' => $finalImageUrl // Masukkan URL yang sudah dibuat tadi
        ]);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan ke Toko!',
            'data' => $product
        ]);
    }

    // 3. Update Produk (Edit Data & Ganti Gambar)
    public function update(Request $request, $id)
    {
        // A. Cari Produknya
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        // B. Validasi (Gunakan 'sometimes' agar user tidak wajib isi semua)
        // Jadi kalau cuma mau ganti harga, nama gak usah dikirim gak apa-apa.
        $request->validate([
            'name' => 'sometimes|string',
            'category' => 'sometimes|string',
            'price' => 'sometimes|integer',
            'stock' => 'sometimes|integer',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048' // Validasi Foto
        ]);

        // C. Ambil semua data inputan KECUALI image
        // Kita urus image secara manual di bawah
        $data = $request->except('image');

        // D. Cek apakah Admin upload gambar baru?
        if ($request->hasFile('image')) {

            // 1. HAPUS GAMBAR LAMA (Jika ada)
            // Biar server kamu gak penuh sampah file tak terpakai
            if ($product->image_url) {
                // Kita harus ubah URL lengkap menjadi path relatif
                // Contoh: "http://localhost:8000/storage/products/abc.jpg"
                // Menjadi: "products/abc.jpg"
                $oldPath = str_replace(url('storage/'), '', $product->image_url);

                // Hapus fisik filenya
                Storage::disk('public')->delete($oldPath);
            }

            // 2. UPLOAD GAMBAR BARU
            $file = $request->file('image');
            $path = $file->store('products', 'public');

            // 3. Masukkan URL baru ke array data
            $data['image_url'] = url('storage/' . $path);
        }

        // E. Update ke Database
        $product->update($data);

        return response()->json([
            'message' => 'Produk berhasil diperbarui!',
            'data' => $product
        ]);
    }

    // 4. Hapus Produk (Khusus Admin)
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product)
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);

        $product->delete();
        return response()->json(['message' => 'Produk dihapus.']);
    }
}