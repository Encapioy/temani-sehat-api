<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\DB; // Wajib buat transaksi database

class OrderController extends Controller
{
    // =========================================================================
    // 1. USER: Checkout (Beli Barang)
    // =========================================================================
    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array', // Harus berupa array barang
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'nullable|string' // Opsional, kalau kosong ambil dari profil
        ]);

        $user = $request->user();

        // Cek Alamat: Kalau di request gak ada, ambil dari profil user
        $address = $request->shipping_address ?? $user->address;

        if (!$address) {
            return response()->json(['message' => 'Mohon lengkapi alamat pengiriman di profil atau isi di form checkout.'], 400);
        }

        // --- MULAI TRANSAKSI DATABASE (SAFETY MODE) ---
        // Gunanya: Kalau ada error di tengah jalan, semua perubahan dibatalkan (Rollback)
        DB::beginTransaction();

        try {
            $totalPrice = 0;
            $orderItemsData = [];

            // A. Looping setiap barang yang dibeli
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                // Cek Stok: Cukup gak?
                if ($product->stock < $item['quantity']) {
                    // Kalau stok kurang, batalkan semua!
                    throw new \Exception("Stok barang '{$product->name}' tidak cukup. Sisa: {$product->stock}");
                }

                // Hitung Subtotal
                $subtotal = $product->price * $item['quantity'];
                $totalPrice += $subtotal;

                // POTONG STOK OTOMATIS
                $product->decrement('stock', $item['quantity']);

                // Siapkan data untuk tabel order_items
                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price, // Simpan harga SAAT INI
                ];
            }

            // B. Buat "Kepala Nota" (Order)
            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'status' => 'pending',
                'shipping_address' => $address,
                'payment_proof_url' => null
            ]);

            // C. Masukkan Isi Keranjang (Order Items)
            $order->items()->createMany($orderItemsData);

            // --- SUKSES! SIMPAN PERUBAHAN ---
            DB::commit();

            return response()->json([
                'message' => 'Pesanan berhasil dibuat. Silakan lakukan pembayaran.',
                'data' => $order->load('items.product'), // Tampilkan detail barangnya
                'payment_instruction' => [
                    'bank' => 'BCA',
                    'account_number' => '1234567890',
                    'account_name' => 'PT Temani Sehat Indonesia',
                    'amount' => $totalPrice
                ]
            ], 201);

        } catch (\Exception $e) {
            // --- ERROR! BATALKAN SEMUA ---
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // =========================================================================
    // 2. USER: Lihat Riwayat Pesanan Saya
    // =========================================================================
    public function myOrders(Request $request)
    {
        $orders = Order::with('items.product') // Ambil data relasi produk
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $orders]);
    }

    // =========================================================================
    // 3. USER: Upload Bukti Bayar 📸
    // =========================================================================
    public function uploadPaymentProof(Request $request, $id)
    {
        $order = Order::find($id);

        // Validasi: Pastikan order ada & milik user yang login
        if (!$order || $order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        // Validasi: Hanya boleh upload kalau status masih pending
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Pesanan ini tidak butuh bukti bayar lagi.'], 400);
        }

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // Proses Upload File
        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $path = $file->store('payment_proofs', 'public'); // Simpan di folder storage
            $url = url('storage/' . $path);

            // Update Database
            $order->update([
                'payment_proof_url' => $url,
                'status' => 'waiting_verification' // Ubah status jadi "Menunggu Cek Admin"
            ]);

            return response()->json([
                'message' => 'Bukti pembayaran berhasil diupload. Tunggu konfirmasi admin ya!',
                'data' => $order
            ]);
        }

        return response()->json(['message' => 'Gagal upload file.'], 500);
    }

    // =========================================================================
    // 4. ADMIN: Lihat Semua Daftar Pesanan Masuk (List Produk yang di-Order)
    // =========================================================================
    public function getAllOrders(Request $request)
    {
        // 1. Cek Hak Akses (Wajib Admin)
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Hanya Admin yang boleh akses.'], 403);
        }

        // 2. Ambil Data Order
        // Kita gunakan 'with' agar data User (siapa yg beli) dan Items (barang apa yg dibeli) ikut terambil
        $orders = Order::with(['user:id,name,email', 'items.product'])
            ->orderBy('created_at', 'desc');

        // 3. Filter (Opsional)
        // Contoh: ?status=pending (hanya tampilkan yg belum bayar)
        if ($request->has('status')) {
            $orders->where('status', $request->status);
        }

        // 4. Gunakan Pagination biar ringan kalau datanya ribuan
        $data = $orders->paginate(10); 

        return response()->json([
            'message' => 'Daftar semua pesanan berhasil diambil',
            'data' => $data
        ]);
    }

    // =========================================================================
    // 5. ADMIN: Update Status (Verifikasi/Kirim Barang) 👮‍♂️
    // =========================================================================
    public function updateStatus(Request $request, $id)
    {
        // Cek apakah yang akses adalah ADMIN
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Hanya Admin yang boleh akses.'], 403);
        }

        $order = Order::find($id);
        if (!$order)
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);

        $request->validate([
            'status' => 'required|in:paid,shipped,completed,cancelled'
        ]);

        $order->update(['status' => $request->status]);

        return response()->json([
            'message' => "Status pesanan berhasil diubah menjadi {$request->status}",
            'data' => $order
        ]);
    }
}