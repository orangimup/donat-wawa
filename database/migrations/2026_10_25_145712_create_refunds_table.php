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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('id_pesanan')->unique();
            $table->foreign('id_pesanan')->references('id_pesanan')->on('orders')->cascadeOnUpdate()->restrictOnDelete();

            $table->string('alasan');
            $table->text('detail_alasan')->nullable();
            $table->string('foto_bukti')->nullable();
            $table->unsignedInteger('jumlah_refund');
            $table->text('catatan_admin')->nullable();
            $table->string('id_refund_midtrans')->nullable();

            $table->enum('status', ['pending', 'refunded', 'rejected'])->default('pending');
            $table->timestamp('tgl_pengajuan')->useCurrent();
            $table->timestamp('tgl_diproses')->nullable();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
