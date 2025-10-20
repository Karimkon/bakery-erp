<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ManagerDispatchExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles
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
            '#',
            'Dispatch Date',
            'Driver Name',
            'Quantity Dispatched',
            'Cash Sales',
            'Credit Sales',
            'Remaining Quantity',
            'Total Value'
        ];
    }

    public function map($item): array
    {
        $this->row++;
        return [
            $this->row,
            $item->date ?? 'N/A',
            $item->driver_name ?? 'N/A',
            (int) ($item->total_qty ?? 0),
            (int) ($item->total_cash ?? 0),
            (int) ($item->total_credit ?? 0),
            (int) ($item->total_remaining ?? 0),
            (float) ($item->total_value ?? 0)
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E6E6FA']
                ]
            ],
            // Add borders to all cells
            'A1:H' . ($this->items->count() + 1) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
        ];
    }
}