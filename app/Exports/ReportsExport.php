<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $headings;
    protected $type;

    public function __construct($data, $headings, $type = 'computers')
    {
        $this->data = $data;
        $this->headings = $headings;
        $this->type = $type;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        // بر اساس نوع گزارش، داده‌ها را مپ می‌کنیم
        switch ($this->type) {
            case 'printers':
                return [
                    $row['model'] ?? '-',
                    $row['quantity'] ?? 0,
                    $row['ip_address'] ?? '-',
                    $row['type'] ?? '-',
                    $row['is_color'] ? 'رنگی' : 'سیاه سفید',
                    $row['is_network'] ? 'شبکه‌ای' : 'محلی',
                    $row['branch_name'] ?? '-',
                    $row['city_name'] ?? '-',
                ];

            case 'scanners':
                return [
                    $row['model'] ?? '-',
                    $row['quantity'] ?? 0,
                    $row['type'] ?? '-',
                    $row['branch_name'] ?? '-',
                    $row['city_name'] ?? '-',
                ];

            default: // computers
                return [
                    $row['name'] ?? '-',
                    $row['employee_name'] ?? '-',
                    $row['branch_name'] ?? '-',
                    $row['city_name'] ?? '-',
                    $row['os'] ?? '-',
                    $row['ram'] ?? '-',
                    $row['cpu'] ?? '-',
                    $row['monitor'] ?? '-',
                    $row['mb'] ?? '-',
                    $row['antivirus'] ? 'فعال' : 'غیرفعال',
                    $row['hard'] ? 'SSD' : 'HDD',
                ];
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // استایل برای هدر
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1976D2'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
