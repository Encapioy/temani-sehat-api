<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyTracking extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi (sesuai migrasi tadi)
    protected $fillable = [
        'user_id',
        'date',
        'mood_score',
        'medication_taken',
        'prayer_completed',
        'diet_complied',
        'exercise_done',
        'notes'
    ];

    // Relasi: Laporan ini milik siapa?
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}