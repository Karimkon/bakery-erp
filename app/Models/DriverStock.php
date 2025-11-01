<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'product',
        'quantity'
    ];

    protected $table = 'driver_stocks';

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}