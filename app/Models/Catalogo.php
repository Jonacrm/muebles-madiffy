<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catalogo extends Model
{
    protected $table = 'catalogo';

    protected $fillable = [
        'SKU',
        'nombre',
        'descripcion',
        'precio',
    ];

    


}
