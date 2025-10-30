<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KampalaSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'user_id',
        'product_type',
        'quantity',
        'unit_price',
        'total_price',
        'payment_method',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function shop()
    {
        return $this->belongsTo(KampalaShop::class, 'shop_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}