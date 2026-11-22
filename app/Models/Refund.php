<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    public $timestamps = false;

    public const STATUS_PENDING = 'pending';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_REJECTED = 'rejected';

    /** Requested by the buyer through the refund form; an admin approves or rejects it. */
    public const SOURCE_CUSTOMER = 'pembeli';
    /** Created automatically because a paid order was rejected/cancelled. No request needed. */
    public const SOURCE_SYSTEM = 'sistem';
    /** Refunded directly in Midtrans (dashboard, chargeback); we only record it. */
    public const SOURCE_MIDTRANS = 'midtrans';

    public const SOURCE_LABELS = [
        self::SOURCE_CUSTOMER => 'Customer request',
        self::SOURCE_SYSTEM => 'Automatic (order rejected)',
        self::SOURCE_MIDTRANS => 'Processed in Midtrans',
    ];

    /** Reason codes that are not in the customer's refund form. */
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
        'tgl_diproses',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_refund' => 'integer',
            'jumlah_percobaan' => 'integer',
            // The account number is never stored in plain text.
            'nomor_rekening' => 'encrypted',
            'tgl_pengajuan' => 'datetime',
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

    public function hasBankDetails(): bool
    {
        return filled($this->nama_bank) && filled($this->nomor_rekening) && filled($this->nama_pemilik_rekening);
    }
}
