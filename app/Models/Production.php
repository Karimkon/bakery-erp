<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;

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

    protected $casts = [
        'production_date' => 'date',
        'flour_bags' => 'decimal:2',
        'total_value' => 'decimal:2',
        'has_variance' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chef()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ingredientUsages()
    {
        return $this->hasMany(\App\Models\IngredientUsage::class);
    }

    // Helper methods (these should be static or called differently)
    public static function chefDailyPerformance($chefId, $date)
    {
        return self::where('user_id', $chefId)
                    ->whereDate('production_date', $date)
                    ->sum('total_value');
    }

    public static function chefMonthlyPerformance($chefId, $month)
    {
        return self::where('user_id', $chefId)
                    ->whereMonth('production_date', $month->month)
                    ->whereYear('production_date', $month->year)
                    ->sum('total_value');
    }
}