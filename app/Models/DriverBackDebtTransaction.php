<?php
// app/Models/DriverBackDebtTransaction.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverBackDebtTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'dispatch_id', 
        'previous_balance',
        'amount_changed',
        'new_balance',
        'transaction_type',
        'description',
        'recorded_by'
    ];

    protected $table = 'driver_back_debt_transactions'; // Explicitly set table name

    public function driver()
    {
        return $this->belongsTo(User::class);
    }

    public function dispatch()
    {
        return $this->belongsTo(Dispatch::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}