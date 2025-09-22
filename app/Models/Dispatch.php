<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    protected $fillable = [
        'driver_id', 'dispatch_date', 'notes',
        'total_items_sold', 'total_sales_value',
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
