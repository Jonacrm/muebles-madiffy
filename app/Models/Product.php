<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    PROTECTED $fillable = [
        'sku',
        'name',
        'material',
        'description',
        'unit_price',
        'stock',
        'active',
    ];

    public function quotationItems()
    {
        return $this->hasMany(QuotationItem::class);
    }
}
