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
        Schema::table('users', function (Blueprint $table) {
            // 1. Data Administratif (Bio)
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable(); // 'L' atau 'P'
            $table->text('address')->nullable();
            $table->string('phone_number')->nullable();

            // 2. Data Personal (Preferences/Sahabat)
            $table->text('hobbies')->nullable();        // "Sepeda, Membaca"
            $table->text('favorite_foods')->nullable(); // "Nasi Goreng, Sayur Asem"
            $table->text('allergies')->nullable();      // "Udang, Debu"
            $table->text('health_goals')->nullable();   // "Ingin kurus, Ingin tidur nyenyak"
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'birth_date',
                'gender',
                'address',
                'phone_number',
                'hobbies',
                'favorite_foods',
                'allergies',
                'health_goals'
            ]);
        });
    }
};
