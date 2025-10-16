<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingredient_id',
        'chef_id',
        'production_id',
        'quantity_changed',
        'quantity_before',
        'quantity_after',
        'transaction_type',
        'added_by',
        'notes',
    ];

    protected $casts = [
        'quantity_changed' => 'decimal:2',
        'quantity_before' => 'decimal:2',
        'quantity_after' => 'decimal:2',
    ];

    // Relationships
    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    // Helper methods
    public function getTransactionBadgeClass()
    {
        return match($this->transaction_type) {
            'addition' => 'bg-success',
            'usage' => 'bg-danger',
            'adjustment' => 'bg-warning',
            default => 'bg-secondary',
        };
    }

    public function getTransactionIcon()
    {
        return match($this->transaction_type) {
            'addition' => 'bi-plus-circle',
            'usage' => 'bi-dash-circle',
            'adjustment' => 'bi-arrow-left-right',
            default => 'bi-circle',
        };
    }

    public function getFormattedQuantityChange()
    {
        $prefix = $this->quantity_changed > 0 ? '+' : '';
        return $prefix . number_format($this->quantity_changed, 2);
    }
}