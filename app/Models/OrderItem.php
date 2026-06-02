<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_sku',
        'product_name',
        'product_material',
        'product_description',
        'quantity',
        'unit_price',
        'line_discount',
        'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
