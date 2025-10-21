<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChefProgressDaily extends Model
{
    use HasFactory;

    protected $table = 'chef_progress_daily';

    protected $fillable = [
        'chef_id',
        'progress_date',
        'target_amount',
        'achieved_amount',
        'progress_percentage'
    ];

    protected $casts = [
        'progress_date' => 'date',
        'target_amount' => 'decimal:2',
        'achieved_amount' => 'decimal:2',
        'progress_percentage' => 'decimal:2'
    ];

    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_id');
    }
}