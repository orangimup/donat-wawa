<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SETTLED = 'settled';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'id_pesanan',
        'id_transaksi_midtrans',
        'metode_pembayaran',
        'snap_token',
        'status',
        'status_midtrans',
        'batas_waktu_bayar',
        'waktu_bayar',
    ];

    protected function casts(): array
    {
        return [
            'batas_waktu_bayar' => 'datetime',
            'waktu_bayar' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_pesanan', 'id_pesanan');
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_SETTLED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function displayCode(): string
    {
        return '#TRX-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }
}