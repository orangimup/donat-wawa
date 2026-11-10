<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    public const MIN_RATING = 1;
    public const MAX_RATING = 5;
    public const COMMENT_MAX_LENGTH = 1000;

    protected $fillable = [
        'order_item_id',
        'order_id',
        'user_id',
        'menu_item_id',
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
    
    protected function reviewerLabel(): Attribute
    {
        return Attribute::make(get: function () {
            $name = trim((string) $this->user?->name);

            return $name !== '' ? $name : __('Customer');
        });
    }

    protected function reviewerInitial(): Attribute
    {
        return Attribute::make(
            get: fn () => mb_strtoupper(mb_substr($this->reviewer_label, 0, 1)),
        );
    }
}