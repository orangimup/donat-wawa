<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    public const TIMEZONE = 'Asia/Jakarta';

    public const STATUS_WAITING = 'waiting';
    public const STATUS_PROCESS = 'process';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DONE = 'done';
    public const STATUS_REJECTED = 'rejected';
    public const FLOW = [self::STATUS_PROCESS, self::STATUS_SHIPPED, self::STATUS_DONE];

    public const SHIPPING_FEES = [
        'same_day' => 5000,
        'pre_order' => 10000,
    ];

    public const DELIVERY_LABELS = [
        'same_day' => 'Same-day',
        'pre_order' => 'Pre-order',
    ];

    public const DELIVERY_SLOTS = [
        '09:00 - 10:00',
        '10:00 - 11:00',
        '13:00 - 14:00',
        '16:00 - 17:00',
    ];

    protected $fillable = [
        'id_pesanan',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'address',
        'delivery_type',
        'delivery_date',
        'delivery_slot',
        'subtotal',
        'shipping_fee',
        'total',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'subtotal' => 'integer',
            'shipping_fee' => 'integer',
            'total' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'id_pesanan', 'id_pesanan');
    }

    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class, 'id_pesanan', 'id_pesanan');
    }

    /** How long an unpaid order holds its stock (also the Midtrans payment window). */
    public static function reservationMinutes(): int
    {
        return (int) config('midtrans.expiry_minutes', 60);
    }

    public static function generateCode(): string
    {
        do {
            $code = 'DW-' . now(self::TIMEZONE)->format('Ymd') . '-' . Str::upper(Str::random(5));
        } while (static::where('id_pesanan', $code)->exists());

        return $code;
    }

    protected function displayCode(): Attribute
    {
        return Attribute::make(get: fn() => '#' . $this->id_pesanan);
    }

    protected function deliveryLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => self::DELIVERY_LABELS[$this->delivery_type] ?? $this->delivery_type,
        );
    }

    protected function placedAt(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->created_at?->clone()->timezone(self::TIMEZONE),
        );
    }

    public function canBeCancelledByCustomer(): bool
    {
        return $this->status === self::STATUS_WAITING;
    }
}