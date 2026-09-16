<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $bundles = [
            [
                'badge' => 'Best Seller',
                'image' => asset('assets/images/donat-bundling.png'),
                'name' => 'Family Box Package (6 Favorite Variants)',
                'description' => '6 favorite artisan donut flavors (Classic Glaze, Choco Melt, Strawberry Dream, Zen Matcha, Salted Caramel, Honey Lemon). Perfect for family gatherings & office friends.',
                'price' => 20000,
                'price_formatted' => $this->formatRupiah(20000),
            ],
            [
                'badge' => 'Best Seller',
                'image' => asset('assets/images/donat-bundling.png'),
                'name' => 'Combo Party Box 12 + 2 Iced Coffee',
                'description' => '12 selected artisan donuts (a combination of Classic Glaze, Choco Melt, Strawberry Dream, Zen Matcha, Salted Caramel, Honey Lemon) + 2 Salted Caramel Latte flavored coffees. Perfect for office gatherings or hangouts.',
                'price' => 68000,
                'price_formatted' => $this->formatRupiah(68000),
            ],
        ];

        $menuItems = MenuItem::query()
            ->where('status', 'active')
            ->orderByRaw("badge = 'best_seller' desc")
            ->orderByRaw("badge = 'new' desc")
            ->latest()
            ->take(8)
            ->get()
            ->map(fn(MenuItem $item) => [
                'slug' => $item->slug,
                'name' => $item->name,
                'description' => $item->description,
                'image' => $item->image_url,
                'price' => $item->price,
                'price_formatted' => $item->price_formatted,
                'badge' => $item->badge,
                'status' => $item->is_available ? 'available' : 'unavailable',
                'category' => $item->category,
            ]);

        return view('user.home', compact('bundles', 'menuItems'));
    }

    private function formatRupiah(int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}