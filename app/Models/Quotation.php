<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'folio',
        'client_id',
        'user_id',
        'status',
        'subtotal',
        'discount_global',
        'tax',
        'total',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'subtotal' => 'decimal:2',
        'discount_global' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isConvertible(): bool
    {
        return $this->status === 'aceptada' && ! $this->isExpired();
    }
}
