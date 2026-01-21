<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'expert_id',
        'schedule_date',
        'complaint',
        'status'
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Expert
    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }
}
