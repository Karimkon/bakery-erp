<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    protected $fillable = [
        'driver_id', 'dispatch_date', 'notes', 'dispatch_no',
        'total_items_sold', 'total_sales_value', 'commission_total',
        'cash_received', 'balance_due', 'driver_signature',
        'driver_expenses', 'expected_cash_after_deductions' // ✅ ADD THESE
    ];

    protected $casts = [
        'dispatch_date' => 'date',
        'driver_expenses' => 'decimal:2',
        'expected_cash_after_deductions' => 'decimal:2',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function items()
    {
        return $this->hasMany(DispatchItem::class);
    }

    public function expenses()
    {
        return $this->hasMany(DriverExpense::class);
    }

    // ✅ ADD THESE HELPER METHODS
    public function getTotalExpensesAttribute()
    {
        return $this->expenses->sum('amount');
    }

    public function getCalculatedExpectedCashAttribute()
    {
        return $this->cash_received - $this->commission_total - $this->total_expenses;
    }

    public function getCalculatedBalanceDueAttribute()
    {
        $remainingInventoryValue = $this->items->sum(fn($item) => $item->remaining_qty * $item->unit_price);
        $creditSalesValue = $this->items->sum(fn($item) => ($item->sold_credit ?? 0) * $item->unit_price);
        $driverBackDebt = $this->driver->back_debt ?? 0;
        
        return $remainingInventoryValue + $creditSalesValue + $driverBackDebt;
    }
}