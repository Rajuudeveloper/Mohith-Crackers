<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'uom_name',
        'price',
        'opening_stock',
        'description',
        'image',
    ];
}
