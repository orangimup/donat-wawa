<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_km', 5, 1);
            $table->decimal('max_km', 5, 1);
            $table->unsignedInteger('fee');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'min_km', 'max_km']);
        });

        $now = now();
        DB::table('delivery_zones')->insert(array_map(
            fn (array $z) => ['min_km' => $z[0], 'max_km' => $z[1], 'fee' => $z[2], 'is_active' => $z[3], 'created_at' => $now, 'updated_at' => $now],
            [
                [0, 2, 3000, true],
                [2, 5, 5000, true],
                [5, 8, 8000, true],
                [8, 10, 10000, true],
                [10, 15, 15000, true],
                [15, 20, 20000, false],
                [20, 25, 25000, false],
            ]
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};