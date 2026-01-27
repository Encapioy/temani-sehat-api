<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Content;

class ContentSeeder extends Seeder
{
    public function run()
    {
        $contents = [
            [
                'title' => 'Cara Menjaga Ginjal Tetap Sehat',
                'type' => 'video',
                'url' => 'https://www.youtube.com/watch?v=contoh1',
                'category' => 'Ginjal',
                'duration' => '10 Menit',
                'thumbnail' => 'https://img.youtube.com/vi/contoh1/hqdefault.jpg',
                'description' => 'Tips sederhana dari dr. Tirta tentang air putih.'
            ],
            [
                'title' => 'Makanan Pantangan Penderita Diabetes',
                'type' => 'article',
                'url' => 'https://temanisehat.com/artikel/diabetes',
                'category' => 'Diabetes',
                'duration' => '5 Menit Baca',
                'thumbnail' => 'https://via.placeholder.com/640x480.png/00dd00?text=Diabetes',
                'description' => 'Daftar makanan yang harus dihindari agar gula darah stabil.'
            ],
            [
                'title' => 'Meditasi untuk Mengurangi Cemas',
                'type' => 'podcast',
                'url' => 'https://spotify.com/track/contoh',
                'category' => 'Mental Health',
                'duration' => '15 Menit',
                'thumbnail' => 'https://via.placeholder.com/640x480.png/0000ff?text=Meditasi',
                'description' => 'Dengarkan ini sebelum tidur agar rileks.'
            ],
            [
                'title' => 'Dzikir Pagi Penenang Hati',
                'category' => 'Amalan/Ibadah', // Sesuai Mind Map
                'type' => 'article',
                'url' => '...',
                'description' => 'Baca ini saat hati sedang gelisah.'
            ],
            [
                'title' => 'Menu Sarapan Rendah Gula',
                'category' => 'Pola Makan', // Sesuai Mind Map
                'type' => 'video',
                'url' => '...',
                'description' => 'Cocok untuk menjaga gula darah stabil.'
            ],
            [
                'title' => 'Gerakan Ringan Atasi Nyeri Punggung',
                'category' => 'Olahraga/Terapi', // Sesuai Mind Map
                'type' => 'video',
                'url' => '...',
                'description' => 'Lakukan 5 menit sebelum tidur.'
            ]
        ];

        foreach ($contents as $content) {
            Content::create($content);
        }
    }
}