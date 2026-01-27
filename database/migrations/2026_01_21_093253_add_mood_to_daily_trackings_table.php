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
            // Kita taruh kolom mood setelah kolom date
            $table->string('mood')->nullable()->after('date');
        });
    }

    public function down()
    {
        Schema::table('daily_trackings', function (Blueprint $table) {
            $table->dropColumn('mood');
        });
    }
};
