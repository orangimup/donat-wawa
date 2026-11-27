<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->timestamp('tgl_disetujui')->nullable()->after('tgl_pengajuan');
            $table->timestamp('tgl_diambil')->nullable()->after('tgl_disetujui');
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE refunds MODIFY status ENUM('pending','approved','picked_up','refunded','rejected') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        DB::table('refunds')->whereIn('status', ['approved', 'picked_up'])->update(['status' => 'pending']);

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE refunds MODIFY status ENUM('pending','refunded','rejected') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn(['tgl_disetujui', 'tgl_diambil']);
        });
    }
};
