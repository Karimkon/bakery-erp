<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShopStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_name',
        'product_type',
        'opening_stock',
        'dispatched',
        'sold',
        'remaining',
    ];
}
