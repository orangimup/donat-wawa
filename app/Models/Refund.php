<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    public $timestamps = false;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PICKED_UP = 'picked_up';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_REJECTED = 'rejected';

    public const SOURCE_CUSTOMER = 'pembeli';
    public const SOURCE_SYSTEM = 'sistem';
    public const SOURCE_MIDTRANS = 'midtrans';

    public const SOURCE_LABELS = [
        self::SOURCE_CUSTOMER => 'Customer request',
        self::SOURCE_SYSTEM => 'Automatic (order rejected)',
        self::SOURCE_MIDTRANS => 'Processed in Midtrans',
    ];

    public const SYSTEM_REASONS = [
        'order_rejected' => 'Order rejected',
        'midtrans_direct' => 'Refunded directly via Midtrans',
    ];

    protected $fillable = [
        'id_pesanan',
        'asal_refund',
        'alasan',
        'detail_alasan',
        'foto_bukti',
        'nama_bank',
        'nomor_rekening',
        'nama_pemilik_rekening',
        'bukti_transfer',
        'jumlah_refund',
        'catatan_admin',
        'pesan_error',
        'id_refund_midtrans',
        'jumlah_percobaan',
        'status',
        'tgl_pengajuan',
        'tgl_disetujui',
        'tgl_diambil',
        'tgl_diproses',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_refund' => 'integer',
            'jumlah_percobaan' => 'integer',
            'nomor_rekening' => 'encrypted',
            'tgl_pengajuan' => 'datetime',
            'tgl_disetujui' => 'datetime',
            'tgl_diambil' => 'datetime',
            'tgl_diproses' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_pesanan', 'id_pesanan');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_PICKED_UP], true);
    }

    public function evidencePaths(): array
    {
        $raw = $this->foto_bukti;

        if (blank($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? array_values(array_filter($decoded)) : [$raw];
    }

    public function stage(): string
    {
        $customer = $this->asal_refund === self::SOURCE_CUSTOMER;

        return match (true) {
            !$this->isOpen() => 'final',
            $customer && $this->status === self::STATUS_PENDING => 'review',
            $customer && $this->status === self::STATUS_APPROVED => 'pickup',
            default => 'payout',
        };
    }

    public function hasBankDetails(): bool
    {
        return filled($this->nama_bank) && filled($this->nomor_rekening) && filled($this->nama_pemilik_rekening);
    }
}
