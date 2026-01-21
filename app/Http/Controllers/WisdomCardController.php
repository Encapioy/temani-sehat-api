<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WisdomCard;

class WisdomCardController extends Controller
{
    public function getRandom(Request $request)
    {
        // Ambil parameter 'mood' dari URL (kalau ada)
        // Contoh: ?mood=Sedih
        $mood = $request->query('mood');

        // Mulai Query
        $query = WisdomCard::query();

        // Jika user kirim mood, kita filter. Kalau tidak, ambil bebas.
        if ($mood) {
            $query->where('category', 'LIKE', "%{$mood}%");
        }

        // Ambil 1 data secara ACAK (Random)
        $card = $query->inRandomOrder()->first();

        // Kalau mood yang dicari tidak ada, kasih pesan default
        if (!$card) {
            return response()->json([
                'message' => 'Belum ada pesan untuk mood ini, tapi tetap semangat ya!',
                'data' => null
            ], 404);
        }

        return response()->json([
            'message' => 'Ini pesan untuk jiwamu hari ini.',
            'data' => $card
        ]);
    }
}