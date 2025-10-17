<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;

    protected $casts = [
    'production_date' => 'date',
];

    protected $fillable = [
    'user_id',
    'production_date',
    'flour_bags',
    'buns',
    'small_breads',
    'big_breads',
    'donuts',
    'half_cakes',
    'block_cakes',
    'slab_cakes',
    'birthday_cakes',
    'quarter_breads',
    'birthday_cakes30k',
    'birthday_cakes50k',
    'mandazis',
    'musiba_tayi',
    'scornes',
    'chapatys',
    'toasted_bread',
    'spring_donuts',
    'cream_donuts',
    'cinnamon_rolls',
    'total_value',
    'has_variance',
    'variance_notes',
];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ingredientUsages()
    {
        return $this->hasMany(\App\Models\IngredientUsage::class);
    }

}
