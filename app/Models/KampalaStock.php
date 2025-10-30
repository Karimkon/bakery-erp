<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KampalaStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'product_type',
        'opening_stock',
        'dispatched',
        'sold',
        'remaining',
        'unit_price'
    ];

    protected $casts = [
        'opening_stock' => 'integer',
        'dispatched' => 'integer',
        'sold' => 'integer',
        'remaining' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function shop()
    {
        return $this->belongsTo(KampalaShop::class, 'shop_id');
    }

    // Update stock when sale is made
    public function updateOnSale($quantity)
    {
        $this->sold += $quantity;
        $this->remaining = $this->opening_stock + $this->dispatched - $this->sold;
        $this->save();
    }

    // Update stock when dispatch is received
    public function updateOnDispatch($quantity)
    {
        $this->dispatched += $quantity;
        $this->remaining = $this->opening_stock + $this->dispatched - $this->sold;
        $this->save();
    }
}