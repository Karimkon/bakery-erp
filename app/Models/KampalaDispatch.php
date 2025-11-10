<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KampalaDispatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'manager_id',
        'dispatch_date',
        'dispatch_no',
        'total_items',
        'total_value',
        'status',
        'received_by',
        'received_at',
        'notes'
    ];

    protected $casts = [
        'dispatch_date' => 'date',
        'received_at' => 'datetime',
        'total_value' => 'decimal:2',
    ];

    public function shop()
    {
        return $this->belongsTo(KampalaShop::class, 'shop_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items()
    {
        return $this->hasMany(KampalaDispatchItem::class, 'dispatch_id');
    }

    // Add this for cascade delete
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($dispatch) {
            // This will automatically delete all related items
            $dispatch->items()->delete();
        });
    }
}