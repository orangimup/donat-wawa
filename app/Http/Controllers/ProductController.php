<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(MenuItem $menu_item): View
    {
        $related = MenuItem::query()
            ->where('category', $menu_item->category)
            ->where('id', '!=', $menu_item->id)
            ->where('status', 'active')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $relatedItems = $related->map(fn (MenuItem $item) => [
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

        return view('user.detail-product', [
            'product' => $menu_item,
            'related' => $relatedItems,
        ]);
    }

    public function reviews(MenuItem $menu_item): View
    {
        return view('user.product-reviews', [
            'product' => $menu_item,
        ]);
    }
}
