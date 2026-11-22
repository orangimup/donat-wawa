<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /** Dummy reviews every product should have (product-only: no order, no order line). */
    private const REVIEWS_PER_PRODUCT = 15;

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

    /**
     * Gives every product a handful of dummy reviews. They are attached to the product only
     * (order_id / order_item_id stay null); real reviews come from completed orders.
     * Safe to run again: it only tops up products that have fewer dummy reviews than needed.
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->where('status', 'active')->get();
        $menu = MenuItem::all();

        if ($users->isEmpty() || $menu->isEmpty()) {
            $this->command?->warn('Need at least one customer user and one menu item. Run UserSeeder and MenuItemSeeder first.');

            return;
        }

        $existing = Review::whereNull('order_item_id')
            ->selectRaw('menu_item_id, COUNT(*) as total')
            ->groupBy('menu_item_id')
            ->pluck('total', 'menu_item_id');

        $created = 0;

        foreach ($menu as $item) {
            $missing = self::REVIEWS_PER_PRODUCT - (int) ($existing[$item->id] ?? 0);

            if ($missing <= 0) {
                continue;
            }

            // Different reviewers per product where possible.
            $reviewers = $users->shuffle()->values();

            for ($n = 0; $n < $missing; $n++) {
                $rating = self::RATINGS[array_rand(self::RATINGS)];
                $pool = self::COMMENTS[$rating];
                $comment = $pool[array_rand($pool)];
                $reviewedAt = now(Order::TIMEZONE)
                    ->subDays(random_int(1, 60))
                    ->setTime(random_int(8, 20), random_int(0, 59));

                $review = Review::create([
                    'order_item_id' => null,
                    'order_id' => null,
                    'user_id' => $reviewers[$n % $reviewers->count()]->id,
                    'menu_item_id' => $item->id,
                    'rating' => $rating,
                    'comment' => $comment === null ? null : str_replace(':product', $item->name, $comment),
                ]);
                $review->forceFill(['created_at' => $reviewedAt, 'updated_at' => $reviewedAt])->saveQuietly();

                $created++;
            }
        }

        $this->command?->info("Created {$created} product reviews.");
    }
}
