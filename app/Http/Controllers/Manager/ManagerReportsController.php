<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DispatchItem;
use App\Models\Production;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ManagerDispatchExport;
use App\Exports\ManagerProductionExport;

class ManagerReportsController extends Controller
{
    // Show combined report filter page
   public function index(Request $request)
{
    $dispatches  = $this->filterDispatches($request)->paginate(30);

    // Get flattened productions (one row per product per date)
    $productions = $this->getFlattenedProductions($request);

    return view('manager.reports.index', [
        'dispatches'  => $dispatches,
        'productions' => $productions, // flattened, ready for Blade
        'from'        => $request->input('from_date'),
        'to'          => $request->input('to_date'),
        'driver'      => $request->input('driver'),
        'product'     => $request->input('product'),
        'type'        => $request->input('type', 'daily'),
    ]);
}


    // PDF Export
    public function exportPdf(Request $request, $reportType)
{
    if ($reportType === 'dispatch') {
        $items = $this->filterDispatches($request)->get();
        $pdf = PDF::loadView('manager.reports.pdf_dispatch', compact('items'))
                  ->setPaper('A4', 'landscape');
    } else {
        $items = $this->getFlattenedProductions($request);
        $pdf = PDF::loadView('manager.reports.pdf_production', compact('items'))
                  ->setPaper('A4', 'landscape');
    }

    return $pdf->download($reportType . '_report_' . now()->format('YmdHis') . '.pdf');
}


    // Excel Export
    public function exportExcel(Request $request, $reportType)
    {
        if ($reportType === 'dispatch') {
            $items = $this->filterDispatches($request)->get();
            return Excel::download(new ManagerDispatchExport($items),
                'dispatch_report_' . now()->format('Ymd_His') . '.xlsx');
        } else {
            $items = $this->filterProductions($request)->get();
            return Excel::download(new ManagerProductionExport($items),
                'production_report_' . now()->format('Ymd_His') . '.xlsx');
        }
    }

    // Filter dispatches
    protected function filterDispatches(Request $request)
    {
        $driver  = $request->input('driver');
        $from    = $request->input('from_date');
        $to      = $request->input('to_date');
        $product = $request->input('product');

        $query = DispatchItem::with(['dispatch.driver'])
            ->select('dispatch_items.*');

        if ($from || $to) {
            $query->whereHas('dispatch', function($q) use ($from, $to) {
                if ($from) $q->whereDate('dispatch_date', '>=', $from);
                if ($to)   $q->whereDate('dispatch_date', '<=', $to);
            });
        }

        if ($driver) {
            $query->whereHas('dispatch.driver', fn($q) => $q->where('name', 'like', "%{$driver}%"));
        }

        if ($product) {
            $query->where('product', $product);
        }

        return $query->latest();
    }

    // Filter productions
    protected function filterProductions(Request $request)
    {
        $from    = $request->input('from_date');
        $to      = $request->input('to_date');
        $product = $request->input('product');

        $query = Production::query();

        if ($from) $query->whereDate('production_date', '>=', $from);
        if ($to)   $query->whereDate('production_date', '<=', $to);
        if ($product) $query->where('product', $product);

        return $query->latest();
    }

    // Inside ManagerReportsController
protected function getFlattenedProductions(Request $request)
{
    $productions = $this->filterProductions($request)->get();
    $flattened = collect();

    foreach ($productions as $prod) {
        foreach (['buns','small_breads','big_breads','donuts','half_cakes','block_cakes','slab_cakes','birthday_cakes'] as $product) {
            $qty = $prod->$product ?? 0;

            // Calculate dispatched quantity for this product and date
            $dispatchedQty = \App\Models\DispatchItem::whereHas('dispatch', function($q) use ($prod) {
                    $q->whereDate('dispatch_date', $prod->production_date);
                })
                ->where('product', $product)
                ->sum('dispatched_qty');

            // Only include if produced or dispatched
            if ($qty > 0 || $dispatchedQty > 0) {
                $flattened->push((object)[
                    'production_date' => $prod->production_date,
                    'product'         => $product,
                    'produced_qty'    => $qty,
                    'used_qty'        => $dispatchedQty,
                    'remaining_qty'   => $qty - $dispatchedQty
                ]);
            }
        }
    }

    return $flattened;
}



}
