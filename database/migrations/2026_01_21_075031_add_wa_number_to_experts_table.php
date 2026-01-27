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
        Schema::table('experts', function (Blueprint $table) {
            $table->string('wa_number')->nullable()->after('fee'); // Contoh: 62812345678
        });
    }

    public function down()
    {
        Schema::table('experts', function (Blueprint $table) {
            $table->dropColumn('wa_number');
        });
    }
};
