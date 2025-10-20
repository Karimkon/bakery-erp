<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DispatchItem;
use App\Models\Production;
use App\Models\User; // Assuming chef is a User model
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

        return view('manager.reports.index', [
            'dispatches'   => $dispatches,
            'productions'  => $productions,
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
     * Export reports as PDF
     */
    /**
 * Export reports as PDF
 */
public function exportPdf(Request $request, $reportType)
{
    try {
        if ($reportType === 'dispatch') {
            $items = $this->getGroupedDispatches($request);
            $pdf = PDF::loadView('manager.reports.pdf_dispatch', compact('items'))
                      ->setPaper('A4', 'landscape');
            $filename = 'dispatch_report_' . now()->format('YmdHis') . '.pdf';
        } else {
            $items = $this->getGroupedProductions($request);
            $pdf = PDF::loadView('manager.reports.pdf_production', compact('items'))
                      ->setPaper('A4', 'landscape');
            $filename = 'production_report_' . now()->format('YmdHis') . '.pdf';
        }

        return $pdf->download($filename);
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
    }
}

/**
 * Export reports as Excel
 */
public function exportExcel(Request $request, $reportType)
{
    try {
        if ($reportType === 'dispatch') {
            $items = $this->getGroupedDispatches($request);
            return Excel::download(new ManagerDispatchExport($items),
                'dispatch_report_' . now()->format('Ymd_His') . '.xlsx');
        } else {
            $items = $this->getGroupedProductions($request);
            return Excel::download(new ManagerProductionExport($items),
                'production_report_' . now()->format('Ymd_His') . '.xlsx');
        }
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to generate Excel: ' . e->getMessage());
    }
}

    /**
     * Get filtered and grouped dispatch data
     */
    protected function getGroupedDispatches(Request $request): Collection
{
    $query = DispatchItem::with([
        'dispatch.driver:id,name',
        'dispatch:id,dispatch_date,driver_id'
    ]);

    // Apply filters
    $this->applyDispatchFilters($query, $request);

    $dispatches = $query->get();

    // Group by date and driver - use more flexible date checking
    $grouped = $dispatches->groupBy(function ($item) {
        $date = $item->dispatch->dispatch_date ?? null;
        $driverId = $item->dispatch->driver->id ?? 'unknown';
        
        // Use the date as-is for grouping, we'll handle validation later
        return ($date ?: 'no-date') . '-' . $driverId;
    });

    $flattened = collect();

    foreach ($grouped as $group) {
        $first = $group->first();
        $date = $first->dispatch->dispatch_date ?? null;
        $driverName = $first->dispatch->driver->name ?? 'Unknown Driver';

        // Use the date as provided, we'll handle display in the view
        $flattened->push((object)[
            'date' => $date, // Keep original value (could be null, date string, or 'N/A')
            'driver_name' => $driverName,
            'total_qty' => $group->sum('dispatched_qty'),
            'total_cash' => $group->sum('sold_cash'),
            'total_credit' => $group->sum('sold_credit'),
            'total_remaining' => $group->sum('remaining_qty'),
            'total_value' => $group->sum('line_total'),
        ]);
    }

    return $flattened->sortByDesc(function ($item) {
        // Sort by date, putting null/empty dates at the end
        return $item->date ?: '0000-00-00';
    })->values();
}

    /**
     * Get filtered and grouped production data
     */
    protected function getGroupedProductions(Request $request): Collection
    {
        $query = Production::with(['chef:id,name']);

        // Apply filters
        $this->applyProductionFilters($query, $request);

        $productions = $query->get();

        // Group by date and chef
        $grouped = $productions->groupBy(function ($production) {
            return ($production->production_date ?? 'unknown') . '-' . ($production->chef->id ?? 'unknown');
        });

        $flattened = collect();

        foreach ($grouped as $items) {
            $first = $items->first();
            $date = $first->production_date ?? 'N/A';
            $chefName = $first->chef->name ?? 'N/A';

            $flattened->push((object)[
                'production_date' => $date,
                'chef_name' => $chefName,
                'total_chefs' => $items->unique('chef_id')->count(),
                'total_flour_bags' => $items->sum('flour_bags'),
                'total_value' => $items->sum('total_value'),
                'buns' => $items->sum('buns'),
                'small_breads' => $items->sum('small_breads'),
                'big_breads' => $items->sum('big_breads'),
                'donuts' => $items->sum('donuts'),
                'half_cakes' => $items->sum('half_cakes'),
                'block_cakes' => $items->sum('block_cakes'),
                'slab_cakes' => $items->sum('slab_cakes'),
                'birthday_cakes' => $items->sum('birthday_cakes'),
            ]);
        }

        return $flattened->sortByDesc('production_date')->values();
    }

    /**
     * Apply dispatch filters to query
     */
    protected function applyDispatchFilters($query, Request $request): void
    {
        // Date filters
        if ($request->filled('from_date')) {
            $query->whereHas('dispatch', function ($q) use ($request) {
                $q->whereDate('dispatch_date', '>=', $request->from_date);
            });
        }

        if ($request->filled('to_date')) {
            $query->whereHas('dispatch', function ($q) use ($request) {
                $q->whereDate('dispatch_date', '<=', $request->to_date);
            });
        }

        // Driver filter
        if ($request->filled('driver')) {
            $query->whereHas('dispatch.driver', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->driver . '%');
            });
        }

        // Product filter
        if ($request->filled('product')) {
            $query->where('product', 'like', '%' . $request->product . '%');
        }
    }

    /**
     * Apply production filters to query
     */
    protected function applyProductionFilters($query, Request $request): void
    {
        // Date filters
        if ($request->filled('prod_from')) {
            $query->whereDate('production_date', '>=', $request->prod_from);
        }

        if ($request->filled('prod_to')) {
            $query->whereDate('production_date', '<=', $request->prod_to);
        }

        // Chef filter
        if ($request->filled('chef')) {
            $query->whereHas('chef', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->chef . '%');
            });
        }

        // Product filter (if applicable to production)
        if ($request->filled('product')) {
            $query->where('product_type', 'like', '%' . $request->product . '%');
        }
    }
}