<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Panggil Model User
use Illuminate\Support\Facades\Hash; // Untuk acak password

class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request)
    {
        // 1. Validasi Input (Cek kelengkapan data)
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email', // Email gak boleh kembar
            'password' => 'required|string|confirmed', // Password harus ada konfirmasi
            'diagnosis' => 'nullable|string' // Boleh kosong, tapi kalau ada harus string
        ]);

        // 2. Buat User Baru di Database
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']), // Password dienkripsi
            'diagnosis' => $fields['diagnosis'] ?? null,
        ]);

        // 3. Kirim Balasan (Response) ke Frontend
        return response()->json([
            'message' => 'Alhamdulillah, user berhasil didaftarkan!',
            'user' => $user
        ], 201);
    }

    // LOGIN
    public function login(Request $request)
    {
        // 1. Validasi input
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        // 2. Cari User berdasarkan Email
        $user = User::where('email', $fields['email'])->first();

        // 3. Cek apakah User ada DAN Password-nya benar
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json([
                'message' => 'Maaf, email atau password salah.'
            ], 401); // 401 = Unauthorized (Gak boleh masuk)
        }

        // 4. Kalau benar, buatkan Token "Kunci Masuk"
        // 'temani_sehat_token' itu cuma nama tokennya, bebas aja sebenarnya
        $token = $user->createToken('temani_sehat_token')->plainTextToken;

        // 5. Kirim Token & Data User ke Frontend
        return response()->json([
            'message' => 'Login berhasil!',
            'user' => $user,
            'token' => $token // <--- INI YANG PENTING
        ], 200);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        // Hapus token yang sedang dipakai (Cabut kunci)
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil, sampai jumpa!'
        ]);
    }
}