<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Isi keranjang dibaca dari sessionStorage sisi client (lihat basket.js) —
     * halaman ini belum bikin Order ke database, murni tampilan checkout dulu.
     */
    public function index(): View
    {
        return view('user.checkout');
    }

    /**
     * Dipanggil basket.js saat halaman checkout dibuka: menerima daftar slug
     * yang ada di sessionStorage, lalu mengembalikan data TERBARU dari tabel
     * menu_items (nama, harga, gambar, ketersediaan). Ini supaya keranjang
     * tidak pernah "basi" — kalau admin ubah harga/stok/hapus produk setelah
     * item ditambahkan ke keranjang, checkout selalu memakai data DB terkini,
     * bukan snapshot lama di browser.
     */
    public function syncBasket(Request $request): JsonResponse
    {
        $slugs = array_filter((array) $request->query('slugs', []));

        $items = MenuItem::query()
            ->whereIn('slug', $slugs)
            ->get()
            ->map(fn (MenuItem $item) => [
                'slug' => $item->slug,
                'name' => $item->name,
                'price' => $item->price,
                'image' => $item->image_url,
                'available' => $item->is_available,
            ])
            ->keyBy('slug');

        return response()->json(['items' => $items]);
    }
}
