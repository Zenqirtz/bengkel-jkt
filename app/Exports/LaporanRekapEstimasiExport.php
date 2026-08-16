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
// use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class LaporanRekapEstimasiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
  protected $params;
  protected $cabang;
  protected $periode;
  protected $rowNumber = 0;

  public function __construct(array $params, $cabang, $periode)
  {
    $this->params = $params;
    $this->cabang = $cabang;
    $this->periode = $periode;
  }

  public function collection()
  {
    $query = DB::table('v_rep_estimasi_period as k')
      ->where('k.kode_cabang', $this->cabang['kode'])
      ->select([
        'k.nama_pelanggan',
        DB::raw('COUNT(1) as unit'),
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
        DB::raw('SUM(k.ppn) as ppn'),
        DB::raw('SUM(k.total) as total'),
      ])
      ->groupBy('k.nama_pelanggan');

    // Filtering
    if (!empty($this->params['tgl_awal'])) {
      try {
        $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
        $query->whereDate('k.tanggal', '>=', $startDate);
      } catch (\Exception $e) {
      }
    }

    if (!empty($this->params['tgl_akhir'])) {
      try {
        $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
        $query->whereDate('k.tanggal', '<=', $endDate);
      } catch (\Exception $e) {
      }
    }

    return $query->orderBy('k.nama_pelanggan', 'asc')->get();
  }

  public function headings(): array
  {
    return [
      [$this->cabang['nama']],
      ['Laporan Rekap Estimasi'],
      ['Periode : ' . $this->periode],
      [''],
      [
        'No',
        'Nama Asuransi',
        'Unit',
        'Perbaikan',
        '',
        '',
        'Sparepart',
        '',
        '',
        'Lain-lain',
        '',
        '',
        'PPN',
        'Total'
      ],
      [
        '',
        '',
        '',
        'R',
        'S',
        'T',
        'R',
        'S',
        'T',
        'R',
        'S',
        'T',
        '',
        ''
      ]
    ];
  }

  public function map($row): array
  {
    $this->rowNumber++;

    return [
      $this->rowNumber,
      $row->nama_pelanggan,
      $row->unit,
      $row->perbaikan_r,
      $row->perbaikan_s,
      $row->perbaikan_t,
      $row->sparepart_r,
      $row->sparepart_s,
      $row->sparepart_t,
      $row->lain_r,
      $row->lain_s,
      $row->lain_t,
      $row->ppn,
      $row->total,
    ];
  }

  public function styles(Worksheet $sheet)
  {
    return [
      1 => [
        'font' => ['bold' => true, 'size' => 14],
      ],
      2 => [
        'font' => ['bold' => true],
      ],
      3 => [
        'font' => ['bold' => true],
      ],
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
        ],
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
        ],
      ],
    ];
  }

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $sheet = $event->sheet->getDelegate();

        // Merge cells untuk header
        $sheet->mergeCells('A1:N1');
        $sheet->mergeCells('A2:N2');
        $sheet->mergeCells('A3:N3');

        // Merge cells untuk kolom header
        $sheet->mergeCells('A5:A6'); // No
        $sheet->mergeCells('B5:B6'); // Nama Asuransi
        $sheet->mergeCells('C5:C6'); // Unit
        $sheet->mergeCells('D5:F5'); // Perbaikan (R,S,T)
        $sheet->mergeCells('G5:I5'); // Sparepart (R,S,T)
        $sheet->mergeCells('J5:L5'); // Lain-lain (R,S,T)
        $sheet->mergeCells('M5:M6'); // PPN
        $sheet->mergeCells('N5:N6'); // Total

        $highestRow = $sheet->getHighestRow();  

        // --- TAMBAHAN UNTUK GRAND TOTAL ---
        // Kita cek apakah ada data (baris data dimulai dari baris 7)
        if ($highestRow >= 7) {
            $totalRow = $highestRow + 1; // Baris untuk grand total
            
            // Gabungkan kolom A dan B untuk label "Grand Total"
            $sheet->mergeCells("A{$totalRow}:B{$totalRow}");
            $sheet->setCellValue("A{$totalRow}", 'Grand Total');
            
            // Set Alignment teks Grand Total ke Kanan dan tebalkan barisnya
            $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("A{$totalRow}:N{$totalRow}")->getFont()->setBold(true);

            // Buat rumus SUM otomatis untuk kolom C sampai N
            foreach (range('C', 'N') as $col) {
                $sheet->setCellValue("{$col}{$totalRow}", "=SUM({$col}7:{$col}{$highestRow})");
            }

            // Perbarui nilai $highestRow agar baris Grand Total ikut terkena border & styling di bawahnya
            $highestRow = $totalRow; 
        }
        // --- END TAMBAHAN GRAND TOTAL ---

        // Border untuk seluruh data
        $sheet->getStyle('A5:N' . $highestRow)->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        // Alignment untuk kolom No dan Unit (center)
        $sheet->getStyle('A7:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C7:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Format angka untuk kolom D sampai N (format ribuan)
        $sheet->getStyle('D7:N' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');
      },
    ];
  }
}
