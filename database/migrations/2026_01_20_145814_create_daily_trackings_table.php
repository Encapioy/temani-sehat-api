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
        Schema::create('daily_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relasi ke User
            $table->date('date')->default(now());

            // Aspek Holistik
            $table->integer('mood_score'); // 1-5
            $table->boolean('medication_taken')->default(false);
            $table->boolean('prayer_completed')->default(false);
            $table->boolean('diet_complied')->default(false);
            $table->boolean('exercise_done')->default(false);

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_trackings');
    }
};
