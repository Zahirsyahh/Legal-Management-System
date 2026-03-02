<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class ContractsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $contracts;

    public function __construct(Collection $contracts)
    {
        $this->contracts = $contracts;
    }

    public function collection()
    {
        return $this->contracts;
    }

    public function headings(): array
    {
        return [
            'NO',
            'CONTRACT/LETTER NUMBERING',
            'DEPARTMENT',
            'COUNTERPARTY',
            'DESCRIPTION',
            'EFFECTIVE DATE',
            'EXPIRY DATE',
            'SUBMITTED AT',
            'REQUESTED BY',
            'STATUS',
            'DOCUMENT TYPE'
        ];
    }

    public function map($contract): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $contract->contract_number ?? '-',
            $contract->department_code ?? '-',
            $contract->counterparty_name ?? '-',
            $contract->description ?? '-',
            $contract->effective_date
                ? Carbon::parse($contract->effective_date)->format('d/m/Y')
                : '-',
            $contract->expiry_date
                ? Carbon::parse($contract->expiry_date)->format('d/m/Y')
                : '-',
            $contract->created_at
                ? Carbon::parse($contract->created_at)->format('d/m/Y H:i')
                : '-',
            optional($contract->user)->nama_user ?? '-',
            ucfirst(str_replace('_', ' ', $contract->status)),
            ucfirst($contract->contract_type),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Header Style
        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F2937'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Border semua cell
        $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Freeze header
        $sheet->freezePane('A2');

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Auto filter
                $sheet->setAutoFilter("A1:{$highestColumn}{$highestRow}");

                // Warna status
                for ($row = 2; $row <= $highestRow; $row++) {
                    $cell = "J{$row}";
                    $value = strtolower($sheet->getCell($cell)->getValue());

                    if (str_contains($value, 'approved')) {
                        $sheet->getStyle($cell)->getFont()->getColor()->setRGB('16A34A');
                    }

                    if (str_contains($value, 'rejected') || str_contains($value, 'declined')) {
                        $sheet->getStyle($cell)->getFont()->getColor()->setRGB('DC2626');
                    }

                    if (str_contains($value, 'pending')) {
                        $sheet->getStyle($cell)->getFont()->getColor()->setRGB('D97706');
                    }
                }
            }
        ];
    }
}
