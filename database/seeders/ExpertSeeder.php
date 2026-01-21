<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Expert;

class ExpertSeeder extends Seeder
{
    public function run()
    {
        Expert::create([
            'name' => 'dr. Tirta Mandira',
            'title' => 'Dokter Umum & Edukator',
            'category' => 'Medis',
            'fee' => 150000,
            'is_online' => true
        ]);

        Expert::create([
            'name' => 'Ustadz Adi Hidayat',
            'title' => 'Ahli Tafsir & Spiritual',
            'category' => 'Spiritual',
            'fee' => 0, // Gratis/Infaq
            'is_online' => false
        ]);

        Expert::create([
            'name' => 'Ibu Elly Risman',
            'title' => 'Psikolog Parenting',
            'category' => 'Psikolog',
            'fee' => 200000,
            'is_online' => true
        ]);
    }
}
