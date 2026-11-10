<?php

namespace App\Models;

use Database\Factories\MenuItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'description',
    'category',
    'badge',
    'price',
    'stock',
    'image',
    'status',
])]
class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_produk',
        'name',
        'slug',
        'description',
        'category',
        'badge',
        'price',
        'stock',
        'image',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (MenuItem $menuItem) {
            if (blank($menuItem->slug) && filled($menuItem->name)) {
                $menuItem->slug = static::uniqueSlugFor($menuItem->name, $menuItem->id);
            }

            if (blank($menuItem->id_produk)) {
                $menuItem->id_produk = static::nextIdProduk();
            }
        });
    }

    /**
     * Generate the next global sequential product code, e.g. DW-001, DW-002.
     */
    public static function nextIdProduk(): string
    {
        $last = static::query()
            ->where('id_produk', 'like', 'DW-%')
            ->orderByRaw('CAST(SUBSTR(id_produk, 4) AS UNSIGNED) DESC')
            ->value('id_produk');

        $next = $last ? ((int) substr($last, 3)) + 1 : 1;

        return 'DW-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    public static function uniqueSlugFor(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'produk';
        $slug = $base;
        $i = 1;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Route model binding uses the slug instead of the numeric id.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function priceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp' . number_format((float) $this->price, 0, ',', '.'),
        );
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->image
                ? asset('storage/' . $this->image)
                : asset('assets/images/donat.png'),
        );
    }

    /**
     * Stock people can still order = physical stock minus what unpaid orders are holding.
     * An unpaid order only holds stock until its payment window closes, so no cron is needed:
     * once the window passes the order simply stops being counted.
     */
    protected function availableStock(): Attribute
    {
        return Attribute::make(
            get: fn() => max(0, $this->stock - $this->reservedQuantity()),
        );
    }

    protected function isAvailable(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status === 'active' && $this->available_stock > 0,
        );
    }

    /**
     * Order lines that currently hold stock: the order is still waiting, its payment window is
     * open, and it has not been paid yet (paid orders already had their stock deducted).
     */
    private static function reservationQuery(): \Illuminate\Database\Query\Builder
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('transactions', 'transactions.id_pesanan', '=', 'orders.id_pesanan')
            ->where('orders.status', Order::STATUS_WAITING)
            ->where('orders.created_at', '>', now()->subMinutes(Order::reservationMinutes()))
            ->where(function ($q) {
                $q->whereNull('transactions.status')
                    ->orWhere('transactions.status', '!=', Transaction::STATUS_SETTLED);
            });
    }

    /** @return array<int,int> menu_item_id => reserved quantity */
    public static function reservedQuantities(iterable $menuItemIds): array
    {
        return static::reservationQuery()
            ->whereIn('order_items.menu_item_id', collect($menuItemIds)->all())
            ->groupBy('order_items.menu_item_id')
            ->selectRaw('order_items.menu_item_id, SUM(order_items.quantity) as reserved')
            ->pluck('reserved', 'menu_item_id')
            ->map(fn($v) => (int) $v)
            ->all();
    }

    public function reservedQuantity(): int
    {
        // Listings use scopeWithReserved() so this does not run one query per item.
        if (array_key_exists('reserved_qty', $this->attributes)) {
            return (int) $this->attributes['reserved_qty'];
        }

        return (int) static::reservationQuery()
            ->where('order_items.menu_item_id', $this->id)
            ->sum('order_items.quantity');
    }

    /** Load reserved quantities in the same query. Use on every list that shows availability. */
    public function scopeWithReserved(Builder $query): Builder
    {
        if (empty($query->getQuery()->columns)) {
            $query->select($query->getModel()->getTable() . '.*');
        }

        return $query->addSelect([
            'reserved_qty' => static::reservationQuery()
                ->selectRaw('COALESCE(SUM(order_items.quantity), 0)')
                ->whereColumn('order_items.menu_item_id', 'menu_items.id'),
        ]);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when(filled($term), fn($q) => $q->where('name', 'like', '%' . $term . '%'));
    }

    /**
     * Filters by admin "category" tabs (all/classics/special) — used on the
     * admin product management page.
     */
    public function scopeOfCategory(Builder $query, ?string $category): Builder
    {
        return $query->when(
            $category && $category !== 'all',
            fn($q) => $q->where('category', $category)
        );
    }

    /**
     * Filters by the public menu pills, which mix the "category" column
     * (classics/special) with the "badge" column (best_seller/new).
     */
    public function scopeOfMenuFilter(Builder $query, ?string $filter): Builder
    {
        if (!$filter || $filter === 'all') {
            return $query;
        }

        if (in_array($filter, ['classics', 'special'], true)) {
            return $query->where('category', $filter);
        }

        return $query->where('badge', $filter);
    }

    protected static function newFactory()
    {
        return MenuItemFactory::new();
    }
}