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
        Schema::create('wisdom_cards', function (Blueprint $table) {
            $table->id();
            $table->text('content'); // Isi motivasi/ayat
            $table->string('source')->nullable(); // QS Ar-Rad, dll
            $table->string('category')->nullable(); // Sedih, Marah, dll
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wisdom_cards');
    }
};
