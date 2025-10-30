<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KampalaShop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'manager_id',
        'status'
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function dispatches()
    {
        return $this->hasMany(KampalaDispatch::class, 'shop_id');
    }

    public function sales()
    {
        return $this->hasMany(KampalaSale::class, 'shop_id');
    }

    public function bankings()
    {
        return $this->hasMany(KampalaBanking::class, 'shop_id');
    }

    public function stocks()
    {
        return $this->hasMany(KampalaStock::class, 'shop_id');
    }
}