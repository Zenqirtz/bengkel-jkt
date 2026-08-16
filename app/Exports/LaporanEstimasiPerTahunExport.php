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
use Carbon\Carbon;

class LaporanEstimasiPerTahunExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
  protected $params;
  protected $cabang;
  protected $periode;
  protected $rowNumber = 0;
  protected $data;

  public function __construct(array $params, $cabang, $periode)
  {
    $this->params = $params;
    $this->cabang = $cabang;
    $this->periode = $periode;
  }

  public function collection()
  {
    $tahun = $this->params['tahun'] ?? date('Y');
    $bulan = $this->params['bulan'] ?? date('m');

    $query = DB::table('v_rep_estimasi_period as k')
      ->where('k.kode_cabang', $this->cabang['kode'])
      ->whereYear('k.tanggal', $tahun)
      ->whereMonth('k.tanggal', $bulan)
      ->select([
        'k.nama_pelanggan',
        DB::raw('COUNT(DISTINCT k.kode_spk) as unit'),
        DB::raw('SUM(k.perbaikan_r) as perbaikan_r'),
        DB::raw('SUM(k.perbaikan_s) as perbaikan_s'),
        DB::raw('SUM(k.perbaikan_t) as perbaikan_t'),
        DB::raw('SUM(k.total_perbaikan) as total_perbaikan'),
        DB::raw('SUM(k.sparepart_r) as sparepart_r'),
        DB::raw('SUM(k.sparepart_s) as sparepart_s'),
        DB::raw('SUM(k.sparepart_t) as sparepart_t'),
        DB::raw('SUM(k.total_sparepart) as total_sparepart'),
        DB::raw('SUM(k.lain_r) as lain_r'),
        DB::raw('SUM(k.lain_s) as lain_s'),
        DB::raw('SUM(k.lain_t) as lain_t'),
        DB::raw('SUM(k.total_lain) as total_lain'),
        DB::raw('SUM(k.perbaikan_r + k.sparepart_r + k.lain_r) as total_r'),
        DB::raw('SUM(k.perbaikan_s + k.sparepart_s + k.lain_s) as total_s'),
        DB::raw('SUM(k.perbaikan_t + k.sparepart_t + k.lain_t) as total_t'),
        DB::raw('SUM(k.ppn) as ppn'),
        DB::raw('SUM(k.total) as total')
      ])
      ->groupBy('k.nama_pelanggan')
      ->orderBy('k.nama_pelanggan', 'asc');

    $this->data = $query->get();

    return $this->data;
  }

  public function headings(): array
  {
    return [
      [$this->cabang['nama']],
      ['Laporan Estimasi Per Tahun'],
      ['Periode : ' . $this->periode],
      [''],
      [
        'No',
        'Nama Asuransi',
        'Unit',
        'Perbaikan',
        '',
        '',
        '',
        'Sparepart',
        '',
        '',
        '',
        'Lain-lain',
        '',
        '',
        '',
        'Total R',
        'Total S',
        'Total T',
        'PPN',
        'Total',
      ],
      [
        '',
        '',
        '',
        'R',
        'S',
        'T',
        'Total',
        'R',
        'S',
        'T',
        'Total',
        'R',
        'S',
        'T',
        'Total',
        '',
        '',
        '',
        '',
        '',
      ]
    ];
  }

  public function map($row): array
  {
    $this->rowNumber++;

    return [
      $this->rowNumber,
      $row->nama_pelanggan,
      (int) $row->unit,
      (float) ($row->perbaikan_r ?? 0),
      (float) ($row->perbaikan_s ?? 0),
      (float) ($row->perbaikan_t ?? 0),
      (float) ($row->total_perbaikan ?? 0),
      (float) ($row->sparepart_r ?? 0),
      (float) ($row->sparepart_s ?? 0),
      (float) ($row->sparepart_t ?? 0),
      (float) ($row->total_sparepart ?? 0),
      (float) ($row->lain_r ?? 0),
      (float) ($row->lain_s ?? 0),
      (float) ($row->lain_t ?? 0),
      (float) ($row->total_lain ?? 0),
      (float) ($row->total_r ?? 0),
      (float) ($row->total_s ?? 0),
      (float) ($row->total_t ?? 0),
      (float) ($row->ppn ?? 0),
      (float) ($row->total ?? 0),
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
      6 => [
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
        $sheet = $event->sheet->getDelegate();

        // Merge cells untuk header
        $sheet->mergeCells('A1:T1');
        $sheet->mergeCells('A2:T2');
        $sheet->mergeCells('A3:T3');

        // Merge cells untuk kolom yang tidak memiliki sub-kategori (row 5 ke 6)
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');
        $sheet->mergeCells('C5:C6');
        $sheet->mergeCells('P5:P6');
        $sheet->mergeCells('Q5:Q6');
        $sheet->mergeCells('R5:R6');
        $sheet->mergeCells('S5:S6');
        $sheet->mergeCells('T5:T6');

        // Merge cells untuk kategori "Perbaikan"
        $sheet->mergeCells('D5:G5');

        // Merge cells untuk kategori "Sparepart"
        $sheet->mergeCells('H5:K5');

        // Merge cells untuk kategori "Lain-lain"
        $sheet->mergeCells('L5:O5');

        $highestRow = $sheet->getHighestRow();

        // Apply borders ke semua data
        $sheet->getStyle('A5:T' . $highestRow)->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        // Isi 0 untuk kolom angka yang kosong
        for ($row = 7; $row <= $highestRow; $row++) {
          $numericColumns = ['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T'];
          foreach ($numericColumns as $col) {
            $value = $sheet->getCell($col . $row)->getValue();
            if ($value === null || $value === '') {
              $sheet->setCellValue($col . $row, 0);
            }
          }
        }

        // Alignment untuk kolom tertentu
        $sheet->getStyle('A7:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C7:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D7:T' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Format number
        $sheet->getStyle('D7:T' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');

        // Tambahkan baris Grand Total
        $totalRow = $highestRow + 1;
        $sheet->setCellValue('A' . $totalRow, '');
        $sheet->setCellValue('B' . $totalRow, '');
        $sheet->setCellValue('C' . $totalRow, '=SUM(C7:C' . $highestRow . ')');
        $sheet->setCellValue('D' . $totalRow, '=SUM(D7:D' . $highestRow . ')');
        $sheet->setCellValue('E' . $totalRow, '=SUM(E7:E' . $highestRow . ')');
        $sheet->setCellValue('F' . $totalRow, '=SUM(F7:F' . $highestRow . ')');
        $sheet->setCellValue('G' . $totalRow, '=SUM(G7:G' . $highestRow . ')');
        $sheet->setCellValue('H' . $totalRow, '=SUM(H7:H' . $highestRow . ')');
        $sheet->setCellValue('I' . $totalRow, '=SUM(I7:I' . $highestRow . ')');
        $sheet->setCellValue('J' . $totalRow, '=SUM(J7:J' . $highestRow . ')');
        $sheet->setCellValue('K' . $totalRow, '=SUM(K7:K' . $highestRow . ')');
        $sheet->setCellValue('L' . $totalRow, '=SUM(L7:L' . $highestRow . ')');
        $sheet->setCellValue('M' . $totalRow, '=SUM(M7:M' . $highestRow . ')');
        $sheet->setCellValue('N' . $totalRow, '=SUM(N7:N' . $highestRow . ')');
        $sheet->setCellValue('O' . $totalRow, '=SUM(O7:O' . $highestRow . ')');
        $sheet->setCellValue('P' . $totalRow, '=SUM(P7:P' . $highestRow . ')');
        $sheet->setCellValue('Q' . $totalRow, '=SUM(Q7:Q' . $highestRow . ')');
        $sheet->setCellValue('R' . $totalRow, '=SUM(R7:R' . $highestRow . ')');
        $sheet->setCellValue('S' . $totalRow, '=SUM(S7:S' . $highestRow . ')');
        $sheet->setCellValue('T' . $totalRow, '=SUM(T7:T' . $highestRow . ')');

        // Style untuk baris total
        $sheet->getStyle('A' . $totalRow . ':T' . $totalRow)->applyFromArray([
          'font' => ['bold' => true],
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        // Alignment untuk baris total
        $sheet->getStyle('C' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $totalRow . ':T' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Format number untuk baris total
        $sheet->getStyle('C' . $totalRow . ':T' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
      },
    ];
  }
}
