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
        Schema::table('daily_trackings', function (Blueprint $table) {
            // Kita taruh setelah kolom mood (biar rapi)
            // Pakai tipe 'string' atau 'text' biar bisa nulis "Pusing", "Nyeri Pinggang"
            $table->string('physical_symptoms')->nullable()->after('mood');
        });
    }

    public function down()
    {
        Schema::table('daily_trackings', function (Blueprint $table) {
            $table->dropColumn('physical_symptoms');
        });
    }
};
