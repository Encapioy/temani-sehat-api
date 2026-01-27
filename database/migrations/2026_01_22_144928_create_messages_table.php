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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            // Terhubung ke tabel konsultasi (Room Chat-nya)
            $table->foreignId('consultation_id')->constrained()->onDelete('cascade');

            $table->text('content'); // Isi pesan

            // Penanda pengirim (biar frontend tau posisi balon chat kiri/kanan)
            $table->boolean('is_from_user')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
