<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('rejection_reason');
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->string('asal_refund', 20)->default('pembeli')->after('id_pesanan');

            $table->string('nama_bank', 100)->nullable()->after('foto_bukti');
            $table->text('nomor_rekening')->nullable()->after('nama_bank');
            $table->string('nama_pemilik_rekening', 100)->nullable()->after('nomor_rekening');
            $table->string('bukti_transfer')->nullable()->after('nama_pemilik_rekening');

            $table->text('pesan_error')->nullable()->after('catatan_admin');
            $table->unsignedTinyInteger('jumlah_percobaan')->default(0)->after('id_refund_midtrans');
        });

        DB::table('orders')
            ->where('status', 'done')
            ->whereNull('completed_at')
            ->update(['completed_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn([
                'asal_refund', 'nama_bank', 'nomor_rekening', 'nama_pemilik_rekening',
                'bukti_transfer', 'pesan_error', 'jumlah_percobaan',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};
