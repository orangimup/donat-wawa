<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category', 'all');

        $paginated = MenuItem::query()
            ->search($search)
            ->ofMenuFilter($category)
            ->orderByDesc('created_at')
            ->paginate(8)
            ->withQueryString();

        $menuItems = $paginated->through(fn (MenuItem $item) => [
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

        return view('user.menu', ['menuItems' => $menuItems]);
    }
}
