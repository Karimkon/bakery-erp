<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispatch_id',
        'driver_id',
        'expense_type',
        'amount',
        'description',
        'receipt_image',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function dispatch()
    {
        return $this->belongsTo(Dispatch::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Expense types configuration
    public static function expenseTypes()
    {
        return [
            'fuel' => 'Fuel',
            'car_wash' => 'Car Wash',
            'maintenance' => 'Vehicle Maintenance',
            'parking' => 'Parking Fees',
            'toll' => 'Toll Fees',
            'food' => 'Food/Meals',
            'phone_credit' => 'Phone Credit',
            'other' => 'Other Expenses',
        ];
    }
}