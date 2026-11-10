<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('id_pesanan')->unique();
            $table->foreign('id_pesanan')->references('id_pesanan')->on('orders')->cascadeOnUpdate()->restrictOnDelete();

            $table->string('id_transaksi_midtrans')->nullable();
            $table->string('metode_pembayaran', 30)->nullable();
            $table->text('snap_token')->nullable();
            $table->enum('status', ['pending', 'settled', 'expired', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->string('status_midtrans', 30)->nullable();   
            $table->timestamp('batas_waktu_bayar')->nullable();
            $table->timestamp('waktu_bayar')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};