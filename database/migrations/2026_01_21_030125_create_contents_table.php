<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Judul Materi
            $table->string('type');  // video, article, atau podcast
            $table->string('url');   // Link Youtube atau Link Artikel
            $table->string('thumbnail')->nullable(); // Link gambar cover
            $table->string('category'); // Ginjal, Kanker, Diabetes, Umum
            $table->string('duration')->nullable(); // "5 Menit", "10 Halaman"
            $table->text('description')->nullable(); // Ringkasan isi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
