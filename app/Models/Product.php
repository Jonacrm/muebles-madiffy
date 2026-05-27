<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'material',
        'description',
        'unit_price',
        'stock',
        'active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function quotationItems()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
