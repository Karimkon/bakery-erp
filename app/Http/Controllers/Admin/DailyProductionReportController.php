<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\Dispatch;
use App\Models\Sale;
use App\Models\BankDeposit;
use App\Models\Banking;
use App\Models\DriverExpense;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DailyProductionReportController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        $reportData = $this->generateReportData($selectedDate);

        return view('admin.reports.daily-production', array_merge($reportData, [
            'selectedDate' => $selectedDate
        ]));
    }

    private function generateReportData(Carbon $date)
    {
        // Get approved productions for the date
        $productions = Production::with('chef')
            ->whereDate('production_date', $date)
            ->where('status', 'approved')
            ->get();

        // Get dispatches for the date
        $dispatches = Dispatch::with(['driver', 'items'])
            ->whereDate('dispatch_date', $date)
            ->get();

        // Get bakery sales for the date
        $sales = Sale::whereDate('created_at', $date)->get();

        // Get bank deposits for the date
        $bankDeposits = BankDeposit::with('depositor')
            ->whereDate('deposit_date', $date)
            ->get();

        // Get driver expenses for the date
        $driverExpenses = DriverExpense::with(['driver', 'dispatch'])
            ->whereDate('created_at', $date)
            ->get();
        
        $chefProductions = $productions->groupBy('user_id')->map(function($chefProds) {
    $totalItems = 0;
    foreach ($chefProds as $production) {
        foreach (array_keys(config('bakery_products')) as $product) {
            $totalItems += $production->$product ?? 0;
        }
    }
    
    return [
        'chef_name' => $chefProds->first()->chef->name ?? 'Unknown Chef',
        'productions' => $chefProds,
        'total_value' => $chefProds->sum('total_value'),
        'total_items' => $totalItems
    ];
});
// Add unified production totals
$unifiedProduction = [];
foreach (array_keys(config('bakery_products')) as $product) {
    $unifiedProduction[$product] = $productions->sum($product);
}

        // Calculate totals
        $totalProduction = $productions->sum('total_value');
        $totalDispatch = $dispatches->sum('total_sales_value');
        $totalSales = $sales->sum('total_price');
        $totalBankDeposits = $bankDeposits->sum('amount');
        $totalExpenses = $driverExpenses->sum('amount');

        // Product-wise breakdown
        $products = config('bakery_products');
        $productReport = [];

        foreach (array_keys($products) as $product) {
            $produced = $productions->sum($product);
            $dispatched = $dispatches->sum(function($dispatch) use ($product) {
                return $dispatch->items->where('product', $product)->sum('dispatched_qty');
            });
            $sold = $dispatches->sum(function($dispatch) use ($product) {
                return $dispatch->items->where('product', $product)->sum('sold_qty');
            });
            $bakerySold = $sales->where('product_type', $product)->sum('quantity');
            $returned = $dispatches->sum(function($dispatch) use ($product) {
                return $dispatch->items->where('product', $product)->sum('returned_qty');
            });
            
            $remaining = $produced - $dispatched - $bakerySold;

            $productReport[$product] = [
                'produced' => $produced,
                'dispatched' => $dispatched,
                'sold' => $sold + $bakerySold,
                'returned' => $returned,
                'remaining' => max(0, $remaining),
                'value' => $products[$product],
                'total_value' => ($sold + $bakerySold) * $products[$product],
            ];
        }

        // Driver stock summary
        $driverStock = [];
        $driverSales = [];
        $driverDeposits = [];

        foreach ($dispatches as $dispatch) {
            $driverName = $dispatch->driver->name;
            
            // Stock
            foreach ($dispatch->items as $item) {
                if (!isset($driverStock[$driverName][$item->product])) {
                    $driverStock[$driverName][$item->product] = 0;
                }
                $driverStock[$driverName][$item->product] += $item->remaining_qty;
            }

            // Sales
            $driverSales[$driverName] = $dispatch->total_sales_value;

            // Deposits
            $driverDeposit = $bankDeposits->where('user_id', $dispatch->driver_id)->first();
            $driverDeposits[$driverName] = $driverDeposit ? $driverDeposit->amount : 0;
        }

        // Financial Summary
        $financialSummary = [
            'total_production_value' => $totalProduction,
            'total_sales_value' => $totalDispatch + $totalSales,
            'total_bank_deposits' => $totalBankDeposits,
            'total_expenses' => $totalExpenses,
            'net_cash' => ($totalDispatch + $totalSales) - $totalExpenses,
            'cash_shortage_excess' => ($totalDispatch + $totalSales) - $totalExpenses - $totalBankDeposits,
        ];

        return compact(
            'productions',
            'dispatches',
            'sales',
            'bankDeposits',
            'driverExpenses',
            'totalProduction',
            'totalDispatch',
            'totalSales',
            'totalBankDeposits',
            'totalExpenses',
            'productReport',
            'driverStock',
            'driverSales',
            'driverDeposits',
            'financialSummary',
            'chefProductions',
            'unifiedProduction'
        );
    }

    public function exportPdf(Request $request)
{
    $date = $request->get('date', Carbon::today()->format('Y-m-d'));
    $selectedDate = Carbon::parse($date);

    $reportData = $this->generateReportData($selectedDate);

    $publicPath = public_path(); // automatically points to your public_html after our AppServiceProvider fix

$pdf = \PDF::loadView('admin.reports.daily-production-pdf', array_merge($reportData, [
    'selectedDate' => $selectedDate
]))->setOptions([
    'chroot' => $publicPath, // ✅ now resolves correctly
    'enable_remote' => true, // optional but safer for asset() images
    'isHtml5ParserEnabled' => true,
    'isPhpEnabled' => true,
]);


    return $pdf->stream("daily-report-{$date}.pdf");
}

public function exportHtml(Request $request)
{
    $date = $request->get('date', Carbon::today()->format('Y-m-d'));
    $selectedDate = Carbon::parse($date);
    $reportData = $this->generateReportData($selectedDate);

    $html = view('admin.reports.daily-production-pdf', array_merge($reportData, [
        'selectedDate' => $selectedDate
    ]))->render();

    $headers = [
        'Content-Type' => 'text/html',
        'Content-Disposition' => "attachment; filename=daily-report-{$date}.html",
    ];

    return response($html, 200, $headers);
}

    public function sendWhatsAppReport(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);
        $phoneNumber = $request->get('phone_number', config('app.boss_whatsapp_number'));

        $reportData = $this->generateReportData($selectedDate);
        $message = $this->generateWhatsAppMessage($reportData, $selectedDate);

        // Send via WhatsApp API (you'll need to integrate with WhatsApp Business API)
        return $this->sendWhatsAppMessage($phoneNumber, $message);
    }

    public function sendEmailReport(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);
        $email = $request->get('email', config('app.boss_email'));

        $reportData = $this->generateReportData($selectedDate);

        Mail::to($email)->send(new \App\Mail\DailyProductionReport($reportData, $selectedDate));

        return back()->with('success', 'Daily report sent to email successfully!');
    }

    public function autoSendDailyReport()
    {
        // This can be called by a scheduled task (cron job)
        $date = Carbon::yesterday(); // Send yesterday's report
        $reportData = $this->generateReportData($date);

        // Send WhatsApp
        $whatsappMessage = $this->generateWhatsAppMessage($reportData, $date);
        $this->sendWhatsAppMessage(config('app.boss_whatsapp_number'), $whatsappMessage);

        // Send Email with PDF
        Mail::to(config('app.boss_email'))->send(new \App\Mail\DailyProductionReport($reportData, $date));

        return response()->json(['message' => 'Daily report sent automatically']);
    }

    private function generateWhatsAppMessage($reportData, $date)
    {
        $message = "📊 *DAILY PRODUCTION REPORT* 📊\n";
        $message .= "Date: " . $date->format('d M Y') . "\n\n";
        
        $message .= "🏭 *PRODUCTION*\n";
        $message .= "Total Value: UGX " . number_format($reportData['totalProduction']) . "\n";
        $message .= "Items Produced: " . $reportData['productions']->count() . "\n\n";
        
        $message .= "💰 *SALES SUMMARY*\n";
        $message .= "Driver Sales: UGX " . number_format($reportData['totalDispatch']) . "\n";
        $message .= "Bakery Sales: UGX " . number_format($reportData['totalSales']) . "\n";
        $message .= "Total Sales: UGX " . number_format($reportData['totalDispatch'] + $reportData['totalSales']) . "\n\n";
        
        $message .= "🏦 *BANKING*\n";
        $message .= "Total Deposits: UGX " . number_format($reportData['totalBankDeposits']) . "\n";
        $message .= "Drivers Deposited: " . $reportData['bankDeposits']->count() . "\n\n";
        
        $message .= "💸 *EXPENSES*\n";
        $message .= "Total Expenses: UGX " . number_format($reportData['totalExpenses']) . "\n\n";
        
        $message .= "📦 *DRIVER STOCK*\n";
        foreach ($reportData['driverStock'] as $driver => $products) {
            $totalStock = array_sum($products);
            if ($totalStock > 0) {
                $message .= "{$driver}: {$totalStock} items\n";
            }
        }

        return $message;
    }

    private function sendWhatsAppMessage($phoneNumber, $message)
    {
        // Implementation depends on your WhatsApp API provider
        // Example using Twilio
        /*
        $twilio = new \Twilio\Rest\Client(config('services.twilio.sid'), config('services.twilio.token'));
        
        $twilio->messages->create(
            "whatsapp:+" . $phoneNumber,
            [
                'from' => 'whatsapp:' . config('services.twilio.whatsapp_from'),
                'body' => $message
            ]
        );
        */

        // For now, just log it
        \Log::info("WhatsApp Message to {$phoneNumber}: {$message}");
        
        return true;
    }
}