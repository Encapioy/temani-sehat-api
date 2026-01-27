<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

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

    // 3. Hapus Produk (Khusus Admin)
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product)
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);

        $product->delete();
        return response()->json(['message' => 'Produk dihapus.']);
    }
}