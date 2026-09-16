<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function __construct()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    public function index(Request $request): View
    {
        $category = $request->get('category', 'all');
        $search = $request->get('search');
        $sort = $request->get('sort', 'latest');

        $query = MenuItem::query()
            ->search($search)
            ->ofCategory($category);

        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'stock' => $query->orderBy('stock'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        $menuItems = $query->paginate(10)->withQueryString();

        $retryItem = $request->old('_edit_id') ? MenuItem::find($request->old('_edit_id')) : null;

        return view('admin.product-index', [
            'menuItems' => $menuItems,
            'category' => $category,
            'search' => $search,
            'sort' => $sort,
            'retryItem' => $retryItem,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('menu-items', 'public');
        }

        MenuItem::create($validated);

        return redirect()
            ->route('admin.menu-items.index')
            ->with('status', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $validated = $this->validated($request, $menuItem);

        if ($request->hasFile('image')) {
            if ($menuItem->image) {
                Storage::disk('public')->delete($menuItem->image);
            }
            $validated['image'] = $request->file('image')->store('menu-items', 'public');
        } elseif ($request->boolean('remove_image') && $menuItem->image) {
            Storage::disk('public')->delete($menuItem->image);
            $validated['image'] = null;
        }

        $menuItem->update($validated);

        return redirect()
            ->route('admin.menu-items.index')
            ->with('status', 'Produk berhasil diperbarui.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        if ($menuItem->image) {
            Storage::disk('public')->delete($menuItem->image);
        }

        $menuItem->delete();

        return redirect()
            ->route('admin.menu-items.index')
            ->with('status', 'Produk berhasil dihapus.');
    }

    private function validated(Request $request, ?MenuItem $menuItem = null): array
    {
        if ($request->input('badge') === '') {
            $request->merge(['badge' => null]);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', 'in:classics,special'],
            'badge' => ['nullable', 'in:best_seller,new'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'image' => ['nullable', 'image', 'max:2048'],
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'category.required' => 'Kategori wajib dipilih.',
            'price.required' => 'Harga wajib diisi.',
            'stock.required' => 'Stok wajib diisi.',
            'image.image' => 'File harus berupa gambar.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
        ]);
    }
}