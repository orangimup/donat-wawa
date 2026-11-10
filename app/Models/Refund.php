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

    protected $fillable = [
        'id_pesanan',
        'alasan',
        'detail_alasan',
        'foto_bukti',
        'jumlah_refund',
        'catatan_admin',
        'id_refund_midtrans',
        'status',
        'tgl_pengajuan',
        'tgl_diproses',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_refund' => 'integer',
            'tgl_pengajuan' => 'datetime',
            'tgl_diproses' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_pesanan', 'id_pesanan');
    }
}