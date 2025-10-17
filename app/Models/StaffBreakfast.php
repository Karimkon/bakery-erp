<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffBreakfast extends Model
{
    protected $fillable = [
        'manager_id',      // who requested
        'admin_id',        // who approved
        'product',
        'quantity',
        'total_value',     // auto-calculated on admin approval
        'status',          // pending, approved, rejected
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
