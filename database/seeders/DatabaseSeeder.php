<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Temani Sehat',
            'email' => 'admin@temanisehat.com',
            'password' => bcrypt('admin123'), // Password wajib di-encrypt
            'role' => 'admin',
            'phone_number' => '081234567890',
        ]);
    }
}
