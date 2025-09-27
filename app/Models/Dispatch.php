<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    protected $fillable = [
        'driver_id', 'dispatch_date', 'notes', 'dispatch_no',
        'total_items_sold', 'total_sales_value', 'commission_total',
        'cash_received','balance_due',
    ];

    protected $casts = [
        'dispatch_date' => 'date',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function items()
    {
        return $this->hasMany(DispatchItem::class);
    }
}
