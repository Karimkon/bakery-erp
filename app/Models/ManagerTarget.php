<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerTarget extends Model
{
    protected $table = 'manager_targets';

    protected $fillable = [
        'manager_id',
        'daily_target',
        'monthly_target',
        'fixed_salary',
        'commission_percentage',
    ];

    // manager (user)
    public function manager(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'manager_id');
    }
}
