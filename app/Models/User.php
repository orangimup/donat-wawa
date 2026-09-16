<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'phone',
    'password',
    'avatar',
    'role',
    'notif_order_status',
    'notif_order_confirmation',
    'notif_review_reminder',
    'notif_new_product',
    'notif_daily_reminder'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'role',
        'notif_order_status',
        'notif_order_confirmation',
        'notif_review_reminder',
        'notif_new_product',
        'notif_daily_reminder'
    ];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'notif_order_status' => 'boolean',
            'notif_order_confirmation' => 'boolean',
            'notif_review_reminder' => 'boolean',
            'notif_new_product' => 'boolean',
            'notif_daily_reminder' => 'boolean',
        ];
    }
}