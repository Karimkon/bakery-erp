<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Damage extends Model
{
    protected $fillable = [
        'manager_id',
        'admin_id',
        'product',
        'quantity',
        'sold_quantity',
        'sold_price', // Add this to track sale price
        'total_sale_amount', // Add this to track total revenue
        'approved_price',
        'notes',
        'photo',
        'status',
        'sold_at',
    ];

    public function manager()
    {
        return $this->belongsTo(\App\Models\User::class, 'manager_id');
    }

    public function admin()
    {
        return $this->belongsTo(\App\Models\User::class, 'admin_id');
    }
}
