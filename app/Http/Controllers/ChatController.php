<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consultation;
use App\Models\Message;
use App\Events\MessageSent;

class ChatController extends Controller
{
    // 1. Kirim Pesan
    public function sendMessage(Request $request, $consultationId)
    {
        $user = $request->user();
        $consultation = Consultation::findOrFail($consultationId);

        // Validasi: Pastikan cuma yang berkepentingan yang boleh kirim
        if ($user->role !== 'admin' && $user->id !== $consultation->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate(['content' => 'required|string']);

        // Simpan ke Database
        $message = Message::create([
            'consultation_id' => $consultationId,
            'content' => $request->content,
            // Jika pengirim admin -> is_from_user = false
            // Jika pengirim user biasa -> is_from_user = true
            'is_from_user' => ($user->role !== 'admin')
        ]);

        // 🔥 BROADCAST KE WEBSOCKET 🔥
        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['message' => 'Pesan terkirim', 'data' => $message]);
    }

    // 2. Ambil Riwayat Chat (Load Chat History)
    public function getMessages($consultationId)
    {
        // Ambil semua pesan di room ini
        $messages = Message::where('consultation_id', $consultationId)
            ->orderBy('created_at', 'asc') // Urut dari yang terlama
            ->get();

        return response()->json(['data' => $messages]);
    }
}