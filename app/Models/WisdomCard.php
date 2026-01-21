<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WisdomCard extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi
    protected $fillable = [
        'content',  // Isi pesan/ayat
        'source',   // Sumber (QS Ar-Ra'd, Hadits, dll)
        'category'  // Mood: Sedih, Cemas, Putus Asa, Bahagia
    ];
}