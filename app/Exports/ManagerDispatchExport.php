<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class ManagerDispatchExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    protected $items;
    protected $row = 0;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        return $this->items instanceof Collection ? $this->items : collect($this->items);
    }

    public function headings(): array
    {
        return [
            '#','Dispatch Date','Driver','Product','Dispatched','Sold Cash',
            'Sold Credit','Remaining','Unit Price','Total'
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
            (int) $item->dispatched_qty,
            (int) $item->sold_cash,
            (int) $item->sold_credit,
            (int) $item->remaining_qty,
            is_null($item->unit_price) ? 0.0 : (float) $item->unit_price,
            is_null($item->line_total) ? round((int)$item->dispatched_qty * (float)$item->unit_price,2) : (float)$item->line_total
        ];
    }

    public function columnFormats(): array
    {
        return ['I'=>NumberFormat::FORMAT_NUMBER_00,'J'=>NumberFormat::FORMAT_NUMBER_00];
    }
}
