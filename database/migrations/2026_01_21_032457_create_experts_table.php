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
        Schema::create('experts', function (Blueprint $table) {
            $table->id();
            $table->string('name');      // Nama Ahli
            $table->string('title');     // Gelar (dr. Sp.PD / Ustadz)
            $table->string('category');  // Medis / Spiritual / Psikolog
            $table->string('photo')->nullable();
            $table->boolean('is_online')->default(true); // Status Online/Offline
            $table->integer('fee')->default(0); // Biaya (0 = Gratis)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experts');
    }
};
