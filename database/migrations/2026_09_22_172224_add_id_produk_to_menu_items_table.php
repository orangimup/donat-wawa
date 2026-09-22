<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('id_produk')->nullable()->after('id');
        });

        DB::table('menu_items')->orderBy('id')->select('id')->each(function ($row, $index) {
            DB::table('menu_items')
                ->where('id', $row->id)
                ->update(['id_produk' => 'DW-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT)]);
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('id_produk')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('id_produk');
        });
    }

};
