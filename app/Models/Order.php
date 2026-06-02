<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'quotation_id',
        'client_id',
        'user_id',
        'quotation_folio',
        'client_name',
        'client_email',
        'client_phone',
        'client_rfc',
        'client_address',
        'seller_name',
        'status',
        'subtotal',
        'discount_global',
        'tax',
        'total',
        'expires_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_global' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'expires_at' => 'date',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

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
        return $this->hasMany(OrderItem::class);
    }
}
