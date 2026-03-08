<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AbsensiExport implements FromArray, WithCustomStartCell, WithEvents, WithStyles, ShouldAutoSize
{
    public function __construct(
        private Collection $rows,
        private string $tanggal
    ) {}

    public function startCell(): string
    {
        return 'A5';
    }

    public function array(): array
    {
        $data = [];
        $data[] = ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Tanggal Absen', 'Tipe Absensi'];

        $no = 1;
        foreach ($this->rows as $r) {
            $data[] = [
                $no++,
                $r['nis'],
                $r['nama'],
                $r['kelas'],
                $r['tanggal_absen'],
                $r['type_absensi'],
            ];
        }
        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            5 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ====== MERGE A1:F1 dan A2:F2 + CENTER ======
                $sheet->setCellValue('A1', 'LAPORAN SISWA');
                $sheet->mergeCells('A1:F1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue('A2', "Tanggal : {$this->tanggal} ");
                $sheet->mergeCells('A2:F2');
                $sheet->getStyle('A2')->getFont()->setBold(true);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // ====== BORDER untuk tabel A5:F(lastRow) ======
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A5:F{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Header tabel center
                $sheet->getStyle('A5:F5')->getAlignment()->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );

                $sheet->getStyle("A6:F{$lastRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

                // ====== Lebar kolom (sesuaikan kalau mau) ======
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(22);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(14);
                $sheet->getColumnDimension('F')->setWidth(14);

            }
        ];
    }

}

