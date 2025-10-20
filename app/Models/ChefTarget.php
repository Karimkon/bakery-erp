<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChefTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'chef_id',
        'daily_target',
        'monthly_target',
        'days_off',
        'commission_percentage',
        'fixed_salary', // ✅ NEW: Salary when target is met
    ];

    protected $casts = [
        'days_off' => 'array',
        'daily_target' => 'decimal:2',
        'monthly_target' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'fixed_salary' => 'decimal:2', // ✅ NEW
    ];

    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_id');
    }
}