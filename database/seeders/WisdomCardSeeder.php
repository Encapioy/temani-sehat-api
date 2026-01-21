<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WisdomCard; // Panggil Modelnya

class WisdomCardSeeder extends Seeder
{
    public function run()
    {
        // Kita masukkan array data motivasi
        $cards = [
            [
                'content' => 'Ingatlah, hanya dengan mengingat Allah hati menjadi tenteram.',
                'source' => 'QS Ar-Ra\'d: 28',
                'category' => 'Cemas'
            ],
            [
                'content' => 'Tidaklah seorang muslim tertimpa rasa letih, penyakit, sedih... melainkan Allah hapuskan dosa-dosanya.',
                'source' => 'HR. Bukhari',
                'category' => 'Sakit'
            ],
            [
                'content' => 'Maka sesungguhnya bersama kesulitan ada kemudahan.',
                'source' => 'QS Al-Insyirah: 5',
                'category' => 'Putus Asa'
            ],
            [
                'content' => 'Dan apabila aku sakit, Dialah yang menyembuhkan aku.',
                'source' => 'QS Asy-Syuara: 80',
                'category' => 'Sakit'
            ],
            [
                'content' => 'Jangan bersedih, sesungguhnya Allah bersama kita.',
                'source' => 'QS At-Taubah: 40',
                'category' => 'Sedih'
            ],
        ];

        // Masukkan semua data ke database
        foreach ($cards as $card) {
            WisdomCard::create($card);
        }
    }
}