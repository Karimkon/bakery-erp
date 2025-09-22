<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientUsage extends Model
{
    protected $fillable = ['production_id','ingredient_id','quantity','unit','cost'];

    public function ingredient() {
        return $this->belongsTo(Ingredient::class);
    }

    public function production() {
        return $this->belongsTo(Production::class);
    }
}
