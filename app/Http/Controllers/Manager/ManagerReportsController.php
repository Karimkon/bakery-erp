<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DispatchItem;
use App\Models\Production;
use App\Models\User;
use App\Models\BankDeposit; // ADD THIS
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ManagerDispatchExport;
use App\Exports\ManagerProductionExport;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ManagerReportsController extends Controller
{
    /**
     * Show combined report page (dispatch + production)
     */
    public function index(Request $request)
    {
        // Get grouped data with proper filtering
        $dispatches = $this->getGroupedDispatches($request);
        $productions = $this->getGroupedProductions($request);
        
        // NEW: Get deposit tracking data
        $depositTracking = $this->getDepositTracking($request);

        return view('manager.reports.index', [
            'dispatches'   => $dispatches,
            'productions'  => $productions,
            'depositTracking' => $depositTracking, // ADD THIS
            // Dispatch filters
            'from'         => $request->input('from_date'),
            'to'           => $request->input('to_date'),
            'driver'       => $request->input('driver'),
            'product'      => $request->input('product'),
            // Production filters
            'prod_from'    => $request->input('prod_from'),
            'prod_to'      => $request->input('prod_to'),
            'chef'         => $request->input('chef'),
        ]);
    }

    /**
     * NEW: Get deposit tracking data
     */
    protected function getDepositTracking(Request $request): Collection
    {
        $query = \App\Models\Dispatch::with(['driver', 'expenses'])
            ->select('dispatches.*')
            ->addSelect(DB::raw('(dispatches.cash_received - dispatches.commission_total - COALESCE(expenses.total_expenses, 0)) as expected_to_bank'))
            ->leftJoin(DB::raw('(SELECT dispatch_id, SUM(amount) as total_expenses FROM driver_expenses GROUP BY dispatch_id) as expenses'), 
                'dispatches.id', '=', 'expenses.dispatch_id');

        // Date filter
        if ($request->filled('from_date')) {
            $query->whereDate('dispatch_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('dispatch_date', '<=', $request->to_date);
        }

        // Driver filter
        if ($request->filled('driver')) {
            $query->whereHas('driver', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->driver . '%');
            });
        }

        $dispatches = $query->get();

        // Get bank deposits for the same period and driver
        $depositsQuery = BankDeposit::with('depositor');
        
        if ($request->filled('from_date')) {
            $depositsQuery->whereDate('deposit_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $depositsQuery->whereDate('deposit_date', '<=', $request->to_date);
        }
        if ($request->filled('driver')) {
            $depositsQuery->whereHas('depositor', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->driver . '%');
            });
        }

        $deposits = $depositsQuery->get();

        // Group by driver and calculate totals
        $driverData = [];

        foreach ($dispatches as $dispatch) {
            $driverId = $dispatch->driver_id;
            $driverName = $dispatch->driver->name;

            if (!isset($driverData[$driverId])) {
                $driverData[$driverId] = [
                    'driver_name' => $driverName,
                    'total_cash_collected' => 0,
                    'total_commission' => 0,
                    'total_expenses' => 0,
                    'total_expected_to_bank' => 0,
                    'total_actually_banked' => 0,
                ];
            }

            $expenses = $dispatch->expenses->sum('amount');
            $expectedToBank = $dispatch->cash_received - $dispatch->commission_total - $expenses;

            $driverData[$driverId]['total_cash_collected'] += $dispatch->cash_received;
            $driverData[$driverId]['total_commission'] += $dispatch->commission_total;
            $driverData[$driverId]['total_expenses'] += $expenses;
            $driverData[$driverId]['total_expected_to_bank'] += $expectedToBank;
        }

        // Add actual bank deposits
        foreach ($deposits as $deposit) {
            $driverId = $deposit->user_id;
            if (isset($driverData[$driverId])) {
                $driverData[$driverId]['total_actually_banked'] += $deposit->amount;
            }
        }

        // Convert to collection and calculate shortages
        $result = collect();
        foreach ($driverData as $driverId => $data) {
            $shortage = $data['total_expected_to_bank'] - $data['total_actually_banked'];
            
            $result->push((object)[
                'driver_name' => $data['driver_name'],
                'total_cash_collected' => $data['total_cash_collected'],
                'total_commission' => $data['total_commission'],
                'total_expenses' => $data['total_expenses'],
                'total_expected_to_bank' => $data['total_expected_to_bank'],
                'total_actually_banked' => $data['total_actually_banked'],
                'shortage_excess' => $shortage,
                'status' => $shortage > 0 ? 'SHORTAGE' : ($shortage < 0 ? 'EXCESS' : 'SETTLED')
            ]);
        }

        return $result->sortBy('driver_name')->values();
    }

    /**
     * NEW: Sales vs Deposits Report Page
     */
    public function salesVsDeposits(Request $request)
    {
        $drivers = User::where('role', 'driver')->orderBy('name')->get();
        $reportData = [];
        
        if ($request->filled('driver_id') && $request->filled('date_from') && $request->filled('date_to')) {
            $reportData = $this->generateSalesVsDepositsReport(
                $request->driver_id,
                $request->date_from,
                $request->date_to
            );
        }

        return view('manager.reports.sales-vs-deposits', compact('drivers', 'reportData'));
    }

    /**
     * NEW: Generate detailed sales vs deposits report
     */
    protected function generateSalesVsDepositsReport($driverId, $dateFrom, $dateTo)
    {
        // Get all dispatches for the driver in date range
        $dispatches = \App\Models\Dispatch::with(['expenses', 'items'])
            ->where('driver_id', $driverId)
            ->whereBetween('dispatch_date', [$dateFrom, $dateTo])
            ->get();

        // Get all bank deposits for the driver in date range
        $deposits = BankDeposit::where('user_id', $driverId)
            ->whereBetween('deposit_date', [$dateFrom, $dateTo])
            ->get();

        // Calculate totals
        $totalCashReceived = $dispatches->sum('cash_received');
        $totalCommission = $dispatches->sum('commission_total');
        $totalExpenses = $dispatches->sum(function($dispatch) {
            return $dispatch->expenses->sum('amount');
        });
        $totalExpectedToBank = $totalCashReceived - $totalCommission - $totalExpenses;
        $totalActuallyBanked = $deposits->sum('amount');
        $shortageExcess = $totalExpectedToBank - $totalActuallyBanked;

        // Get total sales value (for reference)
        $totalSalesValue = $dispatches->sum('total_sales_value');

        return [
            'driver' => User::find($driverId),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total_cash_received' => $totalCashReceived,
            'total_commission' => $totalCommission,
            'total_expenses' => $totalExpenses,
            'total_expected_to_bank' => $totalExpectedToBank,
            'total_actually_banked' => $totalActuallyBanked,
            'shortage_excess' => $shortageExcess,
            'total_sales_value' => $totalSalesValue,
            'dispatches' => $dispatches,
            'deposits' => $deposits,
        ];
    }

    // ... keep your existing methods (getGroupedDispatches, getGroupedProductions, etc.) ...
    
    /**
     * Export deposit tracking report
     */
    public function exportDepositTracking(Request $request)
    {
        try {
            $depositTracking = $this->getDepositTracking($request);
            
            return Excel::download(new class($depositTracking) extends \Maatwebsite\Excel\Concerns\FromCollection {
                private $data;
                
                public function __construct($data)
                {
                    $this->data = $data;
                }
                
                public function collection()
                {
                    $headers = collect([[
                        'Driver Name',
                        'Cash Collected',
                        'Commission',
                        'Expenses', 
                        'Expected to Bank',
                        'Actually Banked',
                        'Shortage/Excess',
                        'Status'
                    ]]);
                    
                    $rows = $this->data->map(function($item) {
                        return [
                            $item->driver_name,
                            $item->total_cash_collected,
                            $item->total_commission,
                            $item->total_expenses,
                            $item->total_expected_to_bank,
                            $item->total_actually_banked,
                            $item->shortage_excess,
                            $item->status
                        ];
                    });
                    
                    return $headers->merge($rows);
                }
            }, 'deposit_tracking_' . now()->format('Ymd_His') . '.xlsx');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export: ' . $e->getMessage());
        }
    }
}