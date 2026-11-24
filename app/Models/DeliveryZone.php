<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    protected $fillable = ['min_km', 'max_km', 'fee', 'is_active'];

    protected function casts(): array
    {
        return [
            'min_km' => 'float',
            'max_km' => 'float',
            'fee' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function forDistance(float $km): ?self
    {
        return static::query()
            ->active()
            ->where('min_km', '<=', $km)
            ->where('max_km', '>', $km)
            ->orderBy('min_km')
            ->first();
    }

    public function getLabelAttribute(): string
    {
        $format = fn (float $km) => rtrim(rtrim(number_format($km, 1, '.', ''), '0'), '.');

        return $format($this->min_km) . ' - ' . $format($this->max_km) . ' km';
    }
}