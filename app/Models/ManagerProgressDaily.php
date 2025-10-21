<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagerProgressDaily extends Model
{
    use HasFactory;

    protected $table = 'manager_progress_daily';

    protected $fillable = [
        'manager_id',
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

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}