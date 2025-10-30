<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KampalaBanking extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'user_id',
        'amount',
        'date',
        'receipt_number',
        'notes',
        'receipt_file'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function shop()
    {
        return $this->belongsTo(KampalaShop::class, 'shop_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}