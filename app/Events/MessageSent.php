<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // <--- PENTING!
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    // Terima data pesan saat event dibuat
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    // Tentukan "Frekuensi Radio" mana yang dipakai
    public function broadcastOn(): array
    {
        // Nama channel unik per konsultasi: chat.1, chat.2, dst.
        return [
            new PrivateChannel('chat.' . $this->message->consultation_id),
        ];
    }
}