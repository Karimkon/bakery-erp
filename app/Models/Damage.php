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
        'approved_price',
        'notes',
        'photo',
        'status',
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
