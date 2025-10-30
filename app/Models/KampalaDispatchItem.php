<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KampalaDispatchItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispatch_id',
        'product_type',
        'quantity',
        'unit_price',
        'total_price',
        'received_quantity',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'received_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function dispatch()
    {
        return $this->belongsTo(KampalaDispatch::class, 'dispatch_id');
    }

    // Check if item is fully received
    public function isFullyReceived()
    {
        return $this->received_quantity >= $this->quantity;
    }

    // Get pending quantity
    public function getPendingQuantityAttribute()
    {
        return $this->quantity - $this->received_quantity;
    }
}