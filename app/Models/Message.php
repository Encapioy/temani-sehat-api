<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['consultation_id', 'content', 'is_from_user'];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
}
