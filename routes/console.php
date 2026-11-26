<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:complete-shipped {--days=2 : Days an order may stay "shipped" before it is completed automatically}', function (\App\Services\OrderService $orders) {
    $days = max(1, (int) $this->option('days'));

    $done = \App\Models\Order::query()
        ->where('status', \App\Models\Order::STATUS_SHIPPED)
        ->where('updated_at', '<=', now()->subDays($days))
        ->pluck('id')
        ->filter(fn ($id) => $orders->markDone($id))
        ->count();

    $this->info("{$done} order(s) completed automatically.");
})->purpose('Complete shipped orders that the customer never confirmed');

Schedule::command('orders:complete-shipped')->hourly();

Artisan::command('orders:remind-review {--after=24 : Hours after completion before the reminder is sent} {--within=72 : Stop reminding this many hours after completion}', function (\App\Services\ReviewService $reviews, \App\Services\NotificationService $notifications) {
    $after = max(1, (int) $this->option('after'));
    $within = max($after + 1, (int) $this->option('within'));

    $sent = 0;

    \App\Models\Order::query()
        ->with(['items.review'])
        ->where('status', \App\Models\Order::STATUS_DONE)
        ->whereNotNull('user_id')
        ->whereBetween('completed_at', [now()->subHours($within), now()->subHours($after)])
        ->get()
        ->each(function (\App\Models\Order $order) use ($reviews, $notifications, &$sent) {
            if ($reviews->reviewableItems($order)->isEmpty()) {
                return;
            }

            // One reminder per order, ever. (If the customer's toggle is off nothing is stored, so it is simply skipped.)
            $already = \Illuminate\Notifications\DatabaseNotification::query()
                ->where('notifiable_type', \App\Models\User::class)
                ->where('notifiable_id', $order->user_id)
                ->where('type', \App\Notifications\CustomerNotification::class)
                ->where('data->event', 'review_reminder')
                ->where('data->order_id', $order->id)
                ->exists();

            if (! $already) {
                $notifications->reviewReminder($order);
                $sent++;
            }
        });

    $this->info("{$sent} review reminder(s) processed.");
})->purpose('Remind customers to review completed orders they have not reviewed yet');

Schedule::command('orders:remind-review')->hourly();

Artisan::command('notifications:prune {--days=60 : Delete notifications older than this many days}', function () {
    $deleted = \Illuminate\Notifications\DatabaseNotification::query()
        ->where('created_at', '<', now()->subDays(max(1, (int) $this->option('days'))))
        ->delete();

    $this->info("{$deleted} old notification(s) deleted.");
})->purpose('Delete old stored notifications');

Schedule::command('notifications:prune')->daily();

Artisan::command('dummy:clear-orders {--force : Do not ask for confirmation}', function () {
    // Real orders always get a payment row at checkout. Seeded dummy orders never did, so
    // "no transaction" is what marks an order as dummy. Orders you placed yourself are kept.
    $dummy = \App\Models\Order::query()->whereDoesntHave('transaction')->get(['id', 'id_pesanan', 'status']);

    if ($dummy->isEmpty()) {
        $this->info('No dummy orders found (every order has a payment). Nothing to delete.');
    } else {
        $this->warn("{$dummy->count()} dummy order(s) without a payment will be deleted:");
        $this->line($dummy->pluck('id_pesanan')->implode(', '));
        $this->line('Orders with a payment, products and users are NOT touched.');

        if (! $this->option('force') && ! $this->confirm('Continue?')) {
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($dummy) {
            $ids = $dummy->pluck('id');

            \Illuminate\Support\Facades\DB::table('reviews')->whereIn('order_id', $ids)->delete();
            \Illuminate\Support\Facades\DB::table('refunds')->whereIn('id_pesanan', $dummy->pluck('id_pesanan'))->delete();
            \Illuminate\Support\Facades\DB::table('order_items')->whereIn('order_id', $ids)->delete();
            \Illuminate\Support\Facades\DB::table('orders')->whereIn('id', $ids)->delete();
        });

        $this->info('Dummy orders deleted.');
    }

    // Product-only dummy reviews so product pages are not empty (only tops up what is missing).
    $this->call('db:seed', ['--class' => \Database\Seeders\ReviewSeeder::class, '--force' => true]);
})->purpose('Remove seeded dummy orders (no payment) and top up product-only dummy reviews');
