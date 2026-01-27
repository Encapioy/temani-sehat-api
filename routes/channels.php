<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Consultation;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('chat.{consultationId}', function ($user, $consultationId) {
    // Cek apakah user ini adalah PEMILIK konsultasi ATAU dia adalah ADMIN
    $consultation = Consultation::find($consultationId);

    if (!$consultation)
        return false;

    // Logic Izin:
    // 1. User ID sama dengan user_id di konsultasi
    // 2. ATAU User punya role 'admin' (Konsultan)
    return (int) $user->id === (int) $consultation->user_id || $user->role === 'admin';
});
