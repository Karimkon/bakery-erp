<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispatchItem extends Model
{
    protected $fillable = [
        'dispatch_id', 'product',
        'opening_stock', 'dispatched_qty',
        'sold_cash', 'sold_credit',
        'sold_qty', 'remaining_qty',
        'unit_price', 'line_total',
        'commission',
    ];

    public function dispatch()
    {
        return $this->belongsTo(Dispatch::class);
    }
}
