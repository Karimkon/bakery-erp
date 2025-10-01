<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class ManagerProductionExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
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
            '#', 'Production Date', 'Product', 'Produced', 'Used', 'Remaining'
        ];
    }

    public function map($item): array
    {
        $this->row++;
        return [
            $this->row,
            $item->production_date,
            $item->product,
            (int) $item->produced_qty,
            (int) $item->used_qty,
            (int) $item->remaining_qty
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER
        ];
    }
}
