<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingredient_id',
        'chef_id',
        'quantity_added',
        'added_by',
    ];

    // Relations
    public function ingredient() {
        return $this->belongsTo(Ingredient::class);
    }

    public function chef() {
        return $this->belongsTo(User::class, 'chef_id');
    }

    public function addedBy() {
        return $this->belongsTo(User::class, 'added_by');
    }
}
