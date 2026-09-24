<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'enabled',
        'publishable_key',
        'secret_key',
        'webhook_secret',
        'currency',
    ];

    protected $hidden = [
        'secret_key',
        'webhook_secret',
    ];

    protected $casts = [
        'enabled' => 'boolean',

        'publishable_key' => 'encrypted',
        'secret_key' => 'encrypted',
        'webhook_secret' => 'encrypted',
    ];
}