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
        Schema::table('consultations', function (Blueprint $table) {
            $table->integer('rating')->nullable(); // Bintang 1-5
            $table->text('review')->nullable();    // Komentar teks
        });
    }

    public function down()
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['rating', 'review']);
        });
    }
};
