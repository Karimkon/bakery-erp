<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'user_id', 'pay_month', 'base_salary', 'commission', 'total_salary', 'status'
    ];

    protected $casts = [
        'pay_month' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
