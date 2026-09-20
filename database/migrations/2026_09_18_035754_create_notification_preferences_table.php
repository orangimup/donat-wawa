<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('notif_order_status')->default(true);
            $table->boolean('notif_order_confirmation')->default(true);
            $table->boolean('notif_review_reminder')->default(true);
            $table->boolean('notif_new_product')->default(false);
            $table->boolean('notif_daily_reminder')->default(false);
            $table->timestamps();
        });

        // Carry over whatever was already set on `users` so nobody's
        // preferences silently reset.
        if (Schema::hasColumn('users', 'notif_order_status')) {
            $now = now();

            DB::table('users')->select([
                'id',
                'notif_order_status',
                'notif_order_confirmation',
                'notif_review_reminder',
                'notif_new_product',
                'notif_daily_reminder',
            ])->orderBy('id')->chunk(200, function ($users) use ($now) {
                $rows = $users->map(function ($user) use ($now) {
                    return [
                        'user_id' => $user->id,
                        'notif_order_status' => $user->notif_order_status,
                        'notif_order_confirmation' => $user->notif_order_confirmation,
                        'notif_review_reminder' => $user->notif_review_reminder,
                        'notif_new_product' => $user->notif_new_product,
                        'notif_daily_reminder' => $user->notif_daily_reminder,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->all();

                DB::table('notification_preferences')->insert($rows);
            });

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn([
                    'notif_order_status',
                    'notif_order_confirmation',
                    'notif_review_reminder',
                    'notif_new_product',
                    'notif_daily_reminder',
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notif_order_status')->default(true)->after('status');
            $table->boolean('notif_order_confirmation')->default(true)->after('notif_order_status');
            $table->boolean('notif_review_reminder')->default(true)->after('notif_order_confirmation');
            $table->boolean('notif_new_product')->default(false)->after('notif_review_reminder');
            $table->boolean('notif_daily_reminder')->default(false)->after('notif_new_product');
        });

        DB::table('notification_preferences')->orderBy('id')->chunk(200, function ($prefs) {
            foreach ($prefs as $pref) {
                DB::table('users')->where('id', $pref->user_id)->update([
                    'notif_order_status' => $pref->notif_order_status,
                    'notif_order_confirmation' => $pref->notif_order_confirmation,
                    'notif_review_reminder' => $pref->notif_review_reminder,
                    'notif_new_product' => $pref->notif_new_product,
                    'notif_daily_reminder' => $pref->notif_daily_reminder,
                ]);
            }
        });

        Schema::dropIfExists('notification_preferences');
    }
};