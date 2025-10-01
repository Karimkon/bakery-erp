<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = ['name','unit','stock','unit_cost', 'chef_id'];

    public function usages()
    {
        return $this->hasMany(\App\Models\IngredientUsage::class);
    }

    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_id');
    }


}
