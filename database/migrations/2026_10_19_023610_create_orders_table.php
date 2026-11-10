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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('id_pesanan')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();

            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 20);
            $table->text('address');

            $table->enum('delivery_type', ['same_day', 'pre_order']);
            $table->date('delivery_date')->nullable();
            $table->string('delivery_slot', 20)->nullable();

            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('shipping_fee');
            $table->unsignedInteger('total');

            $table->enum('status', ['waiting', 'process', 'shipped', 'done', 'rejected'])->default('waiting');
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
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
