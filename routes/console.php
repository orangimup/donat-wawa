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
