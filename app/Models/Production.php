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
        'marble_cakes',
        'total_value',
        'has_variance',
        'variance_notes',

        // 🆕 Approval System Fields
        'status',
        'rejection_reason',
        'approved_at',
        'approved_by',
        'stock_updated',
    ];

    protected $casts = [
        'production_date' => 'date',
        'approved_at' => 'datetime',
        'flour_bags' => 'decimal:2',
        'total_value' => 'decimal:2',
        'has_variance' => 'boolean',
        'stock_updated' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chef()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function ingredientUsages()
    {
        return $this->hasMany(\App\Models\IngredientUsage::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /*
    |--------------------------------------------------------------------------
    | Analytics / Reports
    |--------------------------------------------------------------------------
    */
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
