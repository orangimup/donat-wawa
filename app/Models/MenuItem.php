<?php

namespace App\Models;

use Database\Factories\MenuItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        });
    }

    public static function uniqueSlugFor(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'produk';
        $slug = $base;
        $i = 1;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
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
            get: fn () => 'Rp' . number_format((float) $this->price, 0, ',', '.'),
        );
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image
                ? asset('storage/' . $this->image)
                : asset('assets/images/donat.png'),
        );
    }

    protected function isAvailable(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === 'active' && $this->stock > 0,
        );
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when(filled($term), fn ($q) => $q->where('name', 'like', '%' . $term . '%'));
    }

    /**
     * Filters by admin "category" tabs (all/classics/special) — used on the
     * admin product management page.
     */
    public function scopeOfCategory(Builder $query, ?string $category): Builder
    {
        return $query->when(
            $category && $category !== 'all',
            fn ($q) => $q->where('category', $category)
        );
    }

    /**
     * Filters by the public menu pills, which mix the "category" column
     * (classics/special) with the "badge" column (best_seller/new).
     */
    public function scopeOfMenuFilter(Builder $query, ?string $filter): Builder
    {
        if (! $filter || $filter === 'all') {
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
