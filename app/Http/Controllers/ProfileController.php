<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon; // Library pengolah tanggal
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // 1. Lihat Profil + Hitung Streak (Gamification)
    public function show(Request $request)
    {
        $user = $request->user();

        // --- LOGIKA HITUNG STREAK ---
        // Ambil semua tanggal tracking user, urutkan dari yang terbaru
        $dates = $user->trackings()
                      ->orderBy('date', 'desc')
                      ->pluck('date') // Ambil kolom tanggalnya aja
                      ->unique()      // Buang tanggal dobel (kalau sehari lapor 2x)
                      ->values();     // Reset index array

        $streak = 0;
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Cek apakah user sudah lapor hari ini atau kemarin?
        // Kalau terakhir lapor lusa, berarti streak putus (0).
        if ($dates->isNotEmpty()) {
            $lastTrackingDate = Carbon::parse($dates[0]);

            // Validasi: Streak valid hanya jika terakhir lapor Hari Ini atau Kemarin
            if ($lastTrackingDate->isSameDay($today) || $lastTrackingDate->isSameDay($yesterday)) {

                // Mulai hitung mundur
                foreach ($dates as $index => $dateString) {
                    $date = Carbon::parse($dateString);

                    // Harusnya tanggal ke-0 adalah hari ini/kemarin
                    // Tanggal ke-1 harus H-1 dari tanggal ke-0, dst.

                    // Cek selisih hari dengan hari ini/kemarin (relatif terhadap iterasi)
                    // (Logika sederhana: Loop berhenti kalau tanggalnya bolong)
                    if ($index === 0) {
                        $streak++;
                        continue;
                    }

                    $prevDate = Carbon::parse($dates[$index - 1]);

                    // Kalau selisihnya tepat 1 hari, lanjut hitung
                    if ($date->diffInDays($prevDate) === 1) {
                        $streak++;
                    } else {
                        break; // Stop kalau ada celah
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Profil berhasil diambil',
            'user' => $user,
            'gamification' => [
                'current_streak' => $streak,
                'level' => $this->calculateLevel($streak), // Fungsi tambahan di bawah
                'badge' => $this->getBadge($streak)
            ]
        ]);
    }

    // 2. Update Data Diri
    public function update(Request $request)
    {
        $user = $request->user();

        $fields = $request->validate([
            'name' => 'string',
            'email' => 'email|unique:users,email,' . $user->id, // Boleh email sama kalau punya sendiri
            'diagnosis' => 'string|nullable',
            'password' => 'string|min:6|confirmed|nullable' // Password opsional
        ]);

        // Update data standar
        if ($request->has('name')) $user->name = $fields['name'];
        if ($request->has('email')) $user->email = $fields['email'];
        if ($request->has('diagnosis')) $user->diagnosis = $fields['diagnosis'];

        // Update password kalau diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($fields['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui!',
            'user' => $user
        ]);
    }

    // --- Helper Functions (Pemanis) ---

    private function calculateLevel($streak) {
        // Level 1 (0-3 hari), Level 2 (3-7 hari), dst
        return floor($streak / 3) + 1;
    }

    private function getBadge($streak) {
        if ($streak >= 30) return 'Master Konsisten 🏆';
        if ($streak >= 7) return 'Pejuang Tangguh 🔥';
        if ($streak >= 3) return 'Pemula Rajin ⭐';
        return 'Pendatang Baru 🌱';
    }

    // 3. Upload Foto Profil
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Maks 2MB
        ]);

        $user = $request->user();

        // Cek kalau user upload file
        if ($request->file('photo')) {
            // 1. Hapus foto lama kalau ada (biar server gak penuh)
            if ($user->photo_url) {
                // Kita perlu ambil path aslinya dari URL
                // Contoh URL: http://.../storage/profiles/abc.jpg
                // Path: public/profiles/abc.jpg
                $oldPath = str_replace(url('storage/'), 'public/', $user->photo_url);
                Storage::delete($oldPath);
            }

            // 2. Simpan foto baru ke folder 'public/profiles'
            // Laravel otomatis kasih nama unik (hash)
            $path = $request->file('photo')->store('public/profiles');

            // 3. Generate URL Publik
            // Ubah path 'public/...' jadi URL 'http://.../storage/...'
            $url = url(Storage::url($path));

            // 4. Simpan URL ke Database
            $user->update(['photo_url' => $url]);

            return response()->json([
                'message' => 'Foto profil berhasil diupload!',
                'photo_url' => $url
            ]);
        }

        return response()->json(['message' => 'Gagal upload foto'], 400);
    }
}