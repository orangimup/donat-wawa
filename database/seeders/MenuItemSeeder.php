<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Original Glaze', 'category' => 'classics', 'badge' => 'best_seller', 'price' => 12000, 'stock' => 45, 'status' => 'active', 'description' => 'Donat klasik lembut dengan lapisan glaze manis tipis, favorit sepanjang masa.'],
            ['name' => 'Choco Melt', 'category' => 'classics', 'badge' => 'best_seller', 'price' => 15000, 'stock' => 38, 'status' => 'active', 'description' => 'Cokelat leleh premium yang menyelimuti donat empuk, untuk pecinta cokelat sejati.'],
            ['name' => 'Strawberry Dream', 'category' => 'classics', 'badge' => null, 'price' => 15000, 'stock' => 30, 'status' => 'active', 'description' => 'Glaze stroberi segar dengan taburan remah warna-warni.'],
            ['name' => 'Vanilla Bean', 'category' => 'classics', 'badge' => null, 'price' => 14000, 'stock' => 25, 'status' => 'active', 'description' => 'Donat sourdough dengan glaze vanilla bean asli Madagaskar.'],
            ['name' => 'Boston Cream', 'category' => 'classics', 'badge' => null, 'price' => 16000, 'stock' => 20, 'status' => 'active', 'description' => 'Diisi krim vanila lembut dan disiram cokelat di atasnya.'],
            ['name' => 'Honey Lemon', 'category' => 'classics', 'badge' => 'new', 'price' => 14000, 'stock' => 18, 'status' => 'active', 'description' => 'Perpaduan manis madu dan segarnya perasan lemon.'],
            ['name' => 'Blueberry Burst', 'category' => 'classics', 'badge' => null, 'price' => 16000, 'stock' => 0, 'status' => 'inactive', 'description' => 'Glaze blueberry asli dengan potongan buah di setiap gigitan.'],
            ['name' => 'Cinnamon Sugar', 'category' => 'classics', 'badge' => null, 'price' => 13000, 'stock' => 32, 'status' => 'active', 'description' => 'Taburan gula kayu manis klasik yang hangat dan wangi.'],
            ['name' => 'Zen Matcha', 'category' => 'special', 'badge' => 'best_seller', 'price' => 18000, 'stock' => 27, 'status' => 'active', 'description' => 'Matcha Uji autentik dengan sentuhan white chocolate drizzle.'],
            ['name' => 'Salted Caramel', 'category' => 'special', 'badge' => 'best_seller', 'price' => 18000, 'stock' => 22, 'status' => 'active', 'description' => 'Karamel asin premium dengan tekstur lumer di mulut.'],
            ['name' => 'Hibiscus Bloom', 'category' => 'special', 'badge' => 'new', 'price' => 18000, 'stock' => 15, 'status' => 'active', 'description' => 'Infus bunga hibiscus dengan glaze lemon zest dan bunga edible.'],
            ['name' => 'Midnight Glaze', 'category' => 'special', 'badge' => null, 'price' => 17500, 'stock' => 12, 'status' => 'active', 'description' => 'Ganache dark chocolate 70% dengan taburan garam laut Maldon.'],
            ['name' => 'Caramel Pecan', 'category' => 'special', 'badge' => null, 'price' => 17500, 'stock' => 10, 'status' => 'active', 'description' => 'Glaze karamel asin dengan kacang pecan panggang yang renyah.'],
            ['name' => 'Rose Pistachio', 'category' => 'special', 'badge' => 'new', 'price' => 20000, 'stock' => 8, 'status' => 'active', 'description' => 'Aroma mawar lembut berpadu dengan taburan pistachio cincang.'],
            ['name' => 'Tiramisu Dream', 'category' => 'special', 'badge' => null, 'price' => 20000, 'stock' => 0, 'status' => 'inactive', 'description' => 'Terinspirasi tiramisu klasik dengan lapisan krim mascarpone dan kopi.'],
            ['name' => 'Golden Cheese', 'category' => 'special', 'badge' => null, 'price' => 19000, 'stock' => 16, 'status' => 'active', 'description' => 'Cream cheese lembut dengan taburan keju parut di atasnya.'],
        ];

        foreach ($items as $item) {
            MenuItem::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($item['name'])],
                $item
            );
        }
    }
}
