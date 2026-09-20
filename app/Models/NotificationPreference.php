<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notif_order_status',
        'notif_order_confirmation',
        'notif_review_reminder',
        'notif_new_product',
        'notif_daily_reminder',
    ];

    protected function casts(): array
    {
        return [
            'notif_order_status' => 'boolean',
            'notif_order_confirmation' => 'boolean',
            'notif_review_reminder' => 'boolean',
            'notif_new_product' => 'boolean',
            'notif_daily_reminder' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}