<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class DispatchItemsExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    protected $items;
    protected $row = 0;

    public function __construct($items)
    {
        // $items is expected to be an Eloquent Collection
        $this->items = $items;
    }

    public function collection()
    {
        return $this->items instanceof Collection ? $this->items : collect($this->items);
    }

    public function headings(): array
    {
        return [
            '#',
            'Dispatch Date',
            'Driver',
            'Product',
            'Dispatched',
            'Sold Cash',
            'Sold Credit',
            'Sold Qty',
            'Remaining',
            'Unit Price',
            'Total'
        ];
    }

    public function map($item): array
    {
        $this->row++;

        return [
            $this->row,
            $item->dispatch->dispatch_date ?? '',
            $item->dispatch->driver->name ?? '',
            $item->product,
            (int) ($item->dispatched_qty ?? 0),
            (int) ($item->sold_cash ?? 0),
            (int) ($item->sold_credit ?? 0),
            (int) ($item->sold_qty ?? (($item->sold_cash ?? 0) + ($item->sold_credit ?? 0))),
            (int) ($item->remaining_qty ?? 0),
            is_null($item->unit_price) ? 0.0 : (float) $item->unit_price,
            is_null($item->line_total) ? round(((int)($item->sold_qty ?? 0)) * ((float)($item->unit_price ?? 0)), 2) : (float) $item->line_total,
        ];
    }

    public function columnFormats(): array
    {
        // Formats: J = Unit Price, K = Total (A=1 .. K=11)
        return [
            'J' => NumberFormat::FORMAT_NUMBER_00,
            'K' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
