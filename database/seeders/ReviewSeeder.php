<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReviewSeeder extends Seeder
{
    private const MIN_REVIEWS_PER_PRODUCT = 6;

    private const EXTRA_ORDERS = 10;

    private const MAX_PRODUCTS_PER_ORDER = 3;

    private const REVIEW_CHANCE = 85;

    private const COMMENTS = [
        5 => [
            'Lembut dan segar, glazenya pas. Pasti pesan lagi!',
            'Enak banget, anak-anak habis dalam sekejap.',
            'Donatnya empuk, tidak terlalu manis. Favorit keluarga kami!',
            'Topping melimpah dan rasanya premium. Worth it banget.',
            'Packing rapi, donat masih lembut waktu sampai. Recommended!',
            'Sudah beberapa kali order dan selalu konsisten enak.',
            'Cocok banget buat acara kantor, semua suka.',
            ':product juara! Wajib coba.',
            'Suka banget sama :product, bakal repeat order.',
            'Baru pertama coba :product dan langsung jatuh cinta.',
            'Pengiriman cepat, donat masih fresh. Terima kasih Donat Wawa!',
            'Rasanya mewah tapi harganya bersahabat.',
            'Teksturnya fluffy banget, beda dari donat lain.',
            'Dipesan buat arisan, semua minta tambah.',
        ],
        4 => [
            'Rasanya enak, tapi pengennya topping lebih banyak.',
            'Manis pas, pengiriman cepat.',
            'Teksturnya lembut, ukurannya agak kecil tapi rasanya mantap.',
            'Enak dan wangi, next time mau coba varian lain.',
            'Bagus, cuma sedikit terlalu manis buat saya.',
            ':product enak, tapi sampai agak telat dari jadwal.',
            'Overall puas. Semoga porsi topping konsisten ya.',
            'Donatnya enak, kemasan bisa dibuat lebih kokoh lagi.',
            'Suka sama :product, cuma kurang satu bintang karena agak manis.',
        ],
        3 => [
            'Lumayan, agak kering saat sampai.',
            'Rasa oke tapi biasa saja untuk harganya.',
            null,
            'Glazenya agak meleleh waktu diantar, tapi rasanya masih enak.',
            ':product biasa aja menurut saya, tidak ada yang spesial.',
            'Cukup enak, tapi saya berharap lebih.',
        ],
        2 => [
            'Kurang manis dan agak keras, mungkin karena dikirim siang.',
            ':product kurang sesuai ekspektasi, teksturnya agak padat.',
            'Donat sampai dalam kondisi agak penyok.',
        ],
    ];

    private const RATINGS = [5, 5, 5, 5, 4, 4, 4, 3, 3, 2];

    private const ADDRESSES = [
        'Jl. Pahlawan No. 339, Balearjosari, Blimbing, Kota Malang',
        'Jl. Soekarno Hatta No. 12, Mojolangu, Lowokwaru, Kota Malang',
        'Jl. Kawi No. 7, Klojen, Kota Malang',
    ];

    public function run(): void
    {
        $users = User::where('role', 'user')->where('status', 'active')->get();
        $menu = MenuItem::all();

        if ($users->isEmpty() || $menu->isEmpty()) {
            $this->command?->warn('Need at least one customer user and one menu item. Run UserSeeder and MenuItemSeeder first.');

            return;
        }

        $perProduct = Review::selectRaw('menu_item_id, COUNT(*) as total')->groupBy('menu_item_id')
            ->pluck('total', 'menu_item_id')->map(fn ($n) => (int) $n)->all();
        $pairs = [];
        foreach (Review::get(['user_id', 'menu_item_id']) as $r) {
            $key = $r->user_id . '-' . $r->menu_item_id;
            $pairs[$key] = ($pairs[$key] ?? 0) + 1;
        }

        $orders = 0;
        $reviews = 0;

        $guard = 0;
        while ($guard++ < 500) {
            $needy = $menu->filter(fn (MenuItem $m) => ($perProduct[$m->id] ?? 0) < self::MIN_REVIEWS_PER_PRODUCT);

            if ($needy->isEmpty()) {
                break;
            }

            $products = $needy->shuffle()->take(random_int(1, min(self::MAX_PRODUCTS_PER_ORDER, $needy->count())));
            $reviews += $this->placeOrder($users, $products, 100, $perProduct, $pairs);
            $orders++;
        }

        for ($n = 0; $n < self::EXTRA_ORDERS; $n++) {
            $products = $menu->shuffle()->take(random_int(1, min(self::MAX_PRODUCTS_PER_ORDER, $menu->count())));
            $reviews += $this->placeOrder($users, $products, self::REVIEW_CHANCE, $perProduct, $pairs);
            $orders++;
        }

        $this->command?->info("Created {$orders} done orders and {$reviews} reviews.");
    }

    private function placeOrder($users, $products, int $reviewChance, array &$perProduct, array &$pairs): int
    {
        $user = $users->shuffle()->sortBy(
            fn (User $u) => $products->sum(fn (MenuItem $m) => $pairs[$u->id . '-' . $m->id] ?? 0)
        )->first();

        $placedAt = now(Order::TIMEZONE)
            ->subDays(random_int(1, 40))
            ->setTime(random_int(8, 18), random_int(0, 59));

        $lines = $products->map(fn (MenuItem $m) => [
            'menu_item_id' => $m->id,
            'name' => $m->name,
            'price' => $m->price,
            'quantity' => random_int(1, 4),
        ]);

        $subtotal = $lines->sum(fn (array $l) => $l['price'] * $l['quantity']);
        $shipping = Order::SHIPPING_FEES['same_day'];

        $order = Order::create([
            'id_pesanan' => 'DW-' . $placedAt->format('Ymd') . '-' . Str::upper(Str::random(5)),
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone ?: '081234567890',
            'address' => self::ADDRESSES[array_rand(self::ADDRESSES)],
            'delivery_type' => 'same_day',
            'delivery_date' => $placedAt->toDateString(),
            'delivery_slot' => null,
            'subtotal' => $subtotal,
            'shipping_fee' => $shipping,
            'total' => $subtotal + $shipping,
            'status' => Order::STATUS_DONE,
        ]);
        $order->forceFill(['created_at' => $placedAt, 'updated_at' => $placedAt])->saveQuietly();
        $order->items()->createMany($lines->all());

        $created = 0;

        foreach ($order->items()->get() as $item) {
            if (random_int(1, 100) > $reviewChance) {
                continue; 
            }

            $rating = self::RATINGS[array_rand(self::RATINGS)];
            $pool = self::COMMENTS[$rating];
            $comment = $pool[array_rand($pool)];
            $reviewedAt = $placedAt->copy()->addDay()->addHours(random_int(0, 5))->addMinutes(random_int(0, 59));

            $review = Review::create([
                'order_item_id' => $item->id,
                'order_id' => $order->id,
                'user_id' => $user->id,
                'menu_item_id' => $item->menu_item_id,
                'rating' => $rating,
                'comment' => $comment === null ? null : str_replace(':product', $item->name, $comment),
            ]);
            $review->forceFill(['created_at' => $reviewedAt, 'updated_at' => $reviewedAt])->saveQuietly();

            $perProduct[$item->menu_item_id] = ($perProduct[$item->menu_item_id] ?? 0) + 1;
            $key = $user->id . '-' . $item->menu_item_id;
            $pairs[$key] = ($pairs[$key] ?? 0) + 1;
            $created++;
        }

        return $created;
    }
}