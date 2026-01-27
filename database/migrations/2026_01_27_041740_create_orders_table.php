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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->integer('total_price'); // Total belanja (Rp)

            // Status Transaksi
            // pending: Baru dibuat, belum bayar
            // waiting_verification: Sudah upload bukti, nunggu admin cek
            // paid: Sudah lunas
            // shipped: Sedang dikirim
            // completed: Sampai/Selesai
            // cancelled: Dibatalkan
            $table->enum('status', ['pending', 'waiting_verification', 'paid', 'shipped', 'completed', 'cancelled'])->default('pending');

            $table->string('payment_proof_url')->nullable(); // Foto bukti transfer
            $table->text('shipping_address'); // Alamat kirim (snapshot)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
