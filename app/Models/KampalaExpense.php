<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KampalaExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'user_id',
        'category',
        'description',
        'amount',
        'expense_date',
        'receipt_number',
        'notes',
        'receipt_file'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    // Expense categories specific to Kampala shops
    public static function expenseCategories()
    {
        return [
            'rent' => 'Shop Rent',
            'utilities' => 'Utilities (Water, Electricity)',
            'transport' => 'Transport',
            'supplies' => 'Shop Supplies',
            'maintenance' => 'Maintenance',
            'staff' => 'Staff Expenses',
            'marketing' => 'Marketing',
            'other' => 'Other Expenses',
        ];
    }

    public function shop()
    {
        return $this->belongsTo(KampalaShop::class, 'shop_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}