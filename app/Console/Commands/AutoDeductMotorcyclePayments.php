<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Purchase;
use App\Models\MotorcyclePayment;
use App\Models\WalletTransaction;
use Carbon\Carbon;

class AutoDeductMotorcyclePayments extends Command
{
    protected $signature = 'motorcycle:autodeduct {--process-defaults : Process all defaulted payments}';
    protected $description = 'Automatically deduct daily motorcycle payments from rider wallets';

    public function handle()
    {
        $processDefaults = $this->option('process-defaults');
        
        if ($processDefaults) {
            $this->processDefaultedPayments();
        } else {
            $this->processTodayPayments();
        }
    }

    private function processTodayPayments()
    {
        $todayCarbon = Carbon::today();

        // 🚫 Skip Sundays
        if ($todayCarbon->isSunday()) {
            $this->info("⏩ Skipping auto deduction for Sunday {$todayCarbon->toDateString()}");
            return;
        }

        $today = $todayCarbon->toDateString();

        $purchases = Purchase::with('user.wallet')
            ->where('status', 'active')
            ->get();

        foreach ($purchases as $purchase) {
            if ($purchase->remaining_balance <= 0) continue;

            // Skip if already paid today
            if ($purchase->payments()->whereDate('payment_date', $today)->exists()) {
                continue;
            }

            // Get the rider wallet
            $wallet = $purchase->user->wallet;

            // Determine daily amount (assuming UGX 12,000 daily)
            $dailyAmount = 12000;

            if ($wallet && $wallet->balance >= $dailyAmount) {
                // Deduct from wallet
                $wallet->decrement('balance', $dailyAmount);

                // Log wallet transaction
                WalletTransaction::create([
                    'user_id' => $wallet->user_id,
                    'amount' => $dailyAmount,
                    'type' => 'debit',
                    'reason' => 'Daily motorcycle payment',
                    'description' => 'Automated daily motorcycle payment',
                    'reference' => 'AUTO-MOTO-' . uniqid(),
                ]);

                // Record motorcycle payment
                MotorcyclePayment::create([
                    'purchase_id' => $purchase->id,
                    'payment_date' => $today,
                    'amount' => $dailyAmount,
                    'type' => 'daily',
                    'note' => 'Automated daily payment from wallet',
                    'status' => 'paid',
                ]);

                $purchase->amount_paid += $dailyAmount;
                $purchase->remaining_balance -= $dailyAmount;

                if ($purchase->remaining_balance <= 0) {
                    $purchase->remaining_balance = 0;
                    $purchase->status = 'cleared';
                }

                $purchase->save();

                $this->info("Payment successful for Rider ID {$purchase->user_id}");
            } else {
                $this->warn("Insufficient funds for Rider ID {$purchase->user_id}");
            }
        }
    }

    private function processDefaultedPayments()
{
    $this->info('🔄 Processing defaulted payments...');

    $purchases = Purchase::with('user.wallet', 'payments', 'discounts', 'motorcycle')
        ->where('status', 'active')
        ->get();

    foreach ($purchases as $purchase) {
        $wallet = $purchase->user->wallet;
        if (!$wallet || $wallet->balance < 12000) {
            $this->warn("🚫 Skipping Rider ID {$purchase->user_id} - No wallet or insufficient funds");
            continue;
        }

        // Use billing logic from model
        $schedule = $purchase->getPaymentScheduleSummary();
        $missedDates = collect($schedule['expected_dates'])
            ->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())
            ->diff(
                $purchase->payments->pluck('payment_date')->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())
            )
            ->values();

        $dailyAmount = 12000;
        $deductedCount = 0;

        foreach ($missedDates->sort() as $missedDate) {
            $missedCarbonDate = Carbon::parse($missedDate)->startOfDay();
             // 🚫 Skip Sundays
    if ($missedCarbonDate->isSunday()) {
        continue;
    }

    if ($wallet->balance < $dailyAmount) {
        break;
    }

    // Deduct balance
    $wallet->decrement('balance', $dailyAmount);

    // Log wallet transaction with created_at set to missed date
    WalletTransaction::create([
        'user_id'    => $wallet->user_id,
        'amount'     => $dailyAmount,
        'type'       => 'debit',
        'reason'     => 'Auto payment for missed date',
        'description'=> "Auto deduction for {$missedDate}",
        'reference'  => 'AUTO-MISS-' . $missedCarbonDate->format('Ymd') . '-' . uniqid(),
        'created_at' => $missedCarbonDate,
        'updated_at' => $missedCarbonDate,
    ]);

    // Save motorcycle payment also with proper date
    MotorcyclePayment::create([
        'purchase_id'   => $purchase->id,
        'user_id'       => $wallet->user_id,
        'payment_date'  => $missedDate,
        'amount'        => $dailyAmount,
        'type'          => 'daily',
        'status'        => 'paid',
        'note'          => 'Auto payment from wallet for missed date',
        'created_at'    => $missedCarbonDate,
        'updated_at'    => $missedCarbonDate,
    ]);

    $purchase->amount_paid += $dailyAmount;
    $purchase->remaining_balance -= $dailyAmount;
    $deductedCount++;
}

        if ($purchase->remaining_balance <= 0) {
            $purchase->remaining_balance = 0;
            $purchase->status = 'cleared';
        }

        $purchase->save();

        $this->info("✅ Rider ID {$purchase->user_id} - Paid {$deductedCount} missed days");
    }
}

}