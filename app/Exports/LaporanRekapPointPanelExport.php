<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LaporanRekapPointPanelExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
  protected $params;
  protected $cabang;
  protected $tahun;
  protected $rowNumber = 0;
  protected $data;

  public function __construct(array $params, $cabang, $tahun)
  {
    $this->params = $params;
    $this->cabang = $cabang;
    $this->tahun = $tahun;
  }

  public function collection()
  {
    $query = DB::table('v_rep_point_panel as p')
      ->where('p.kode_cabang', $this->cabang['kode'])
      ->select([
        'p.bulan',
        'p.jumlah_spk',
        'p.total_panel',
      ])
      ->orderBy('p.bulan', 'asc');

    $this->data = $query->get();

    return $this->data;
  }

  public function headings(): array
  {
    return [
      [$this->cabang['nama']],
      ['Laporan Rekap Point Panel'],
      ['Tahun : ' . $this->tahun],
      [''],
      [
        'No',
        'Bulan',
        'Jumlah Unit',
        'Jumlah Panel',
      ]
    ];
  }

  public function map($row): array
  {
    $this->rowNumber++;

    $bulanNama = [
      1 => 'Januari',
      2 => 'Februari',
      3 => 'Maret',
      4 => 'April',
      5 => 'Mei',
      6 => 'Juni',
      7 => 'Juli',
      8 => 'Agustus',
      9 => 'September',
      10 => 'Oktober',
      11 => 'November',
      12 => 'Desember'
    ];

    $bulanAngka = (int) $row->bulan;

    return [
      $this->rowNumber,
      $bulanNama[$bulanAngka] ?? '',
      (float) $row->jumlah_spk,        // Ubah ini - kirim sebagai angka
      (float) $row->total_panel,        // Ubah ini - kirim sebagai angka
    ];
  }

  public function styles(Worksheet $sheet)
  {
    return [
      1 => [
        'font' => ['bold' => true, 'size' => 14],
      ],
      2 => ['font' => ['bold' => true]],
      3 => ['font' => ['bold' => true]],
      5 => [
        'font' => ['bold' => true],
        'alignment' => [
          'horizontal' => Alignment::HORIZONTAL_CENTER,
          'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
          'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
          ],
        ]
      ],
    ];
  }

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        // Merge cells untuk header
        $event->sheet->mergeCells('A1:D1');
        $event->sheet->mergeCells('A2:D2');
        $event->sheet->mergeCells('A3:D3');

        $highestRow = $event->sheet->getHighestRow();

        // Apply borders ke semua data
        $event->sheet->getStyle('A5:D' . $highestRow)->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        // Tambahkan baris total
        $totalRow = $highestRow + 1;
        $event->sheet->setCellValue('A' . $totalRow, '');
        $event->sheet->setCellValue('B' . $totalRow, ''); // Hapus teks "Total:"

        // Formula untuk total
        $event->sheet->setCellValue('C' . $totalRow, '=SUM(C6:C' . $highestRow . ')');
        $event->sheet->setCellValue('D' . $totalRow, '=SUM(D6:D' . $highestRow . ')');

        // Style untuk baris total
        $event->sheet->getStyle('A' . $totalRow . ':D' . $totalRow)->applyFromArray([
          'font' => ['bold' => true],
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        // Alignment untuk kolom tertentu
        $event->sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('C6:D' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Format number untuk kolom angka
        $event->sheet->getStyle('C6:C' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
        $event->sheet->getStyle('D6:D' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
      },
    ];
  }
}
