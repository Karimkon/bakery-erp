<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DispatchItem;
use Illuminate\Http\Request;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DispatchItemsExport;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $items = $this->filteredItems($request)->paginate(30);

        return view('admin.reports.index', [
            'items'   => $items,
            'driver'  => $request->input('driver'),
            'from'    => $request->input('from_date'),
            'to'      => $request->input('to_date'),
            'product' => $request->input('product'),
        ]);
    }

    public function exportPdf(Request $request)
    {
        $items = $this->filteredItems($request)->get();

        $pdf = PDF::loadView('admin.reports.pdf', compact('items'))
                  ->setPaper('A4', 'landscape');

        return $pdf->download('report_' . now()->format('YmdHis') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $items = $this->filteredItems($request)->get();

        return Excel::download(new DispatchItemsExport($items),
            'report_' . now()->format('Ymd_His') . '.xlsx');
    }

    protected function filteredItems(Request $request)
    {
        $driver  = $request->input('driver');
        $from    = $request->input('from_date');
        $to      = $request->input('to_date');
        $product = $request->input('product');

        $query = DispatchItem::with(['dispatch.driver'])
            ->select('dispatch_items.*');

        if ($from || $to) {
            $query->whereHas('dispatch', function ($q) use ($from, $to) {
                if ($from) $q->whereDate('dispatch_date', '>=', $from);
                if ($to)   $q->whereDate('dispatch_date', '<=', $to);
            });
        }

        if ($driver) {
            $query->whereHas('dispatch.driver', function ($q) use ($driver) {
                $q->where('name', 'like', "%{$driver}%");
            });
        }

        if ($product) {
            $query->where('product', $product);
        }

        return $query->latest();
    }
}