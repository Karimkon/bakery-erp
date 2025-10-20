<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ManagerProductionExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnFormatting
{
    protected $items;

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
            'Production Date',
            'Chef Name',
            'Total Chefs',
            'Flour Bags',
            'Total Value',
            'Buns',
            'Small Breads',
            'Big Breads',
            'Donuts',
            'Half Cakes',
            'Block Cakes',
            'Slab Cakes',
            'Birthday Cakes'
        ];
    }

    public function map($item): array
    {
        static $row = 0;
        $row++;
        
        return [
            $row,
            $item->production_date ?? 'N/A',
            $item->chef_name ?? 'N/A',
            (int) ($item->total_chefs ?? 1),
            (float) ($item->total_flour_bags ?? 0),
            (float) ($item->total_value ?? 0),
            (int) ($item->buns ?? 0),
            (int) ($item->small_breads ?? 0),
            (int) ($item->big_breads ?? 0),
            (int) ($item->donuts ?? 0),
            (int) ($item->half_cakes ?? 0),
            (int) ($item->block_cakes ?? 0),
            (int) ($item->slab_cakes ?? 0),
            (int) ($item->birthday_cakes ?? 0)
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER_00,
            'F' => NumberFormat::FORMAT_NUMBER_00,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_NUMBER,
            'L' => NumberFormat::FORMAT_NUMBER,
            'M' => NumberFormat::FORMAT_NUMBER,
            'N' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text with background
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E6F7E6']
                ]
            ],
            // Add borders to all cells
            'A1:N' . ($this->items->count() + 1) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Production Summary';
    }
}