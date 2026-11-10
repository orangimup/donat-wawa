<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->orderBy('id')->get();
        $menu = MenuItem::where('status', 'active')->orderBy('id')->get();

        if ($users->isEmpty() || $menu->count() < 3 || Order::exists()) {
            return;
        }

        $addresses = [
            'Jl. Pahlawan No. 339, Balearjosari, Blimbing, Kota Malang',
            'Jl. Soekarno Hatta No. 12, Mojolangu, Lowokwaru, Kota Malang',
            'Jl. Kawi No. 7, Klojen, Kota Malang',
        ];

        // status, delivery type, days ago, [[menu index, qty], ...], rejection reason
        $plan = [
            ['waiting', 'same_day', 0, [[0, 4], [3, 2]]],
            ['process', 'same_day', 1, [[2, 2], [4, 2]]],
            ['shipped', 'pre_order', 4, [[0, 6]]],
            ['done', 'same_day', 6, [[1, 4], [3, 2]]],
            ['rejected', 'same_day', 9, [[9, 3]], 'Hibiscus Bloom is sold out for today.'],
        ];

        foreach ($plan as $i => $row) {
            [$status, $delivery, $daysAgo, $lines] = $row;

            $user = $users[$i % $users->count()];
            $placedAt = now(Order::TIMEZONE)->subDays($daysAgo)->setTime(8 + ($i * 2) % 11, ($i * 13) % 60);
            $isPreOrder = $delivery === 'pre_order';

            $items = collect($lines)->map(function (array $line) use ($menu) {
                $menuItem = $menu[$line[0] % $menu->count()];

                return [
                    'menu_item_id' => $menuItem->id,
                    'name' => $menuItem->name,
                    'price' => $menuItem->price,
                    'quantity' => $line[1],
                ];
            });

            $subtotal = $items->sum(fn(array $item) => $item['price'] * $item['quantity']);
            $shippingFee = Order::SHIPPING_FEES[$delivery];

            $order = Order::create([
                'id_pesanan' => 'DW-' . $placedAt->format('Ymd') . '-' . Str::upper(Str::random(5)),
                'user_id' => $user->id,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone ?: '081234567890',
                'address' => $addresses[$i % count($addresses)],
                'delivery_type' => $delivery,
                'delivery_date' => $isPreOrder ? $placedAt->copy()->addDays(2)->toDateString() : $placedAt->toDateString(),
                'delivery_slot' => $isPreOrder ? Order::DELIVERY_SLOTS[$i % count(Order::DELIVERY_SLOTS)] : null,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total' => $subtotal + $shippingFee,
                'status' => $status,
                'rejection_reason' => $row[4] ?? null,
            ]);

            $order->items()->createMany($items->all());

            $order->forceFill(['created_at' => $placedAt, 'updated_at' => $placedAt])->saveQuietly();
        }
    }
}