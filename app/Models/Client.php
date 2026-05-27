<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    PROTECTED $fillable = [
        'name',
        'email',
        'phone',
        'rfc',
        'address',
    ];

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }
}
