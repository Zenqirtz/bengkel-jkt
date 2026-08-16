<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Carbon\Carbon;

class LaporanRincianEstimasiExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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

  public function query()
  {
    $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
    $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
      
    $query = DB::table('v_rep_estimasi_period as k')
    ->where('k.kode_cabang', $this->cabang['kode'])
    ->whereDate('k.tanggal', '>=', $startDate)
    ->whereDate('k.tanggal', '<=', $endDate)
    ->select([
      'k.kode_estimasi',
      'k.tanggal',
      'k.no_polisi',
      'k.tipe_kendaraan',
      'k.perbaikan_r',
      'k.perbaikan_s',
      'k.perbaikan_t',
      'k.total_perbaikan',
      'k.sparepart_r',
      'k.sparepart_s',
      'k.sparepart_t',
      'k.total_sparepart',
      'k.lain_r',
      'k.lain_s',
      'k.lain_t',
      'k.total_lain',
      'k.nama_pelanggan',
      'k.total',
      'k.ppn',
      'k.kode_spk',
      'k.nama_cabang',
      'k.kode_cabang',
      'k.disc_perbaikan',
      'k.disc_sparepart',
      'k.disc_lain',
    ])
    ->orderBy('k.tanggal', 'asc')
    ->orderBy('k.kode_estimasi', 'asc');

    return $query;
  }

  public function headings(): array
  {
    return [
      [$this->cabang['nama']],
      ['Laporan Rincian Estimasi'],
      ['Periode : ' . $this->periode],
      [''],
      [
        'No',
        'Nama Asuransi',
        'No. Estimasi',
        'Tanggal Estimasi',
        'No. SPK',
        'No. Polisi',
        'Tipe Kendaraan',
        'Perbaikan',
        'Spare Part',
        'Lain-lain',
        'Total R',
        'Total S',
        'Total T',
        'PPN',
        'Total',
      ]
    ];
  }

  public function map($row): array
  {
    $this->rowNumber++;

    // Hitung total R, S, T
    $totalR = ($row->perbaikan_r ?? 0) + ($row->sparepart_r ?? 0) + ($row->lain_r ?? 0);
    $totalS = ($row->perbaikan_s ?? 0) + ($row->sparepart_s ?? 0) + ($row->lain_s ?? 0);
    $totalT = ($row->perbaikan_t ?? 0) + ($row->sparepart_t ?? 0) + ($row->lain_t ?? 0);

    return [
      $this->rowNumber,
      $row->nama_pelanggan,
      $row->kode_estimasi,
      $row->tanggal ? date("d/m/Y", strtotime($row->tanggal)) : '',
      $row->kode_spk, // No. SPK akan di-format sebagai text di AfterSheet
      $row->no_polisi,
      $row->tipe_kendaraan,
      (float) ($row->total_perbaikan ?? 0),
      (float) ($row->total_sparepart ?? 0),
      (float) ($row->total_lain ?? 0),
      (float) $totalR,
      (float) $totalS,
      (float) $totalT,
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
        ],
      ],
    ];
  }

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $sheet = $event->sheet->getDelegate();

        $sheet->mergeCells('A1:O1');
        $sheet->mergeCells('A2:O2');
        $sheet->mergeCells('A3:O3');

        $highestRow = $sheet->getHighestRow();

        // --- TAMBAHAN UNTUK GRAND TOTAL ---
        // Kita cek apakah ada data (baris data dimulai dari baris 7)
        if ($highestRow >= 6) {
            $totalRow = $highestRow + 1; // Baris untuk grand total
            
            // Gabungkan kolom A dan B untuk label "Grand Total"
            $sheet->mergeCells("A{$totalRow}:G{$totalRow}");
            $sheet->setCellValue("A{$totalRow}", 'Grand Total');
            
            // Set Alignment teks Grand Total ke Kanan dan tebalkan barisnya
            $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("A{$totalRow}:O{$totalRow}")->getFont()->setBold(true);

            // Buat rumus SUM otomatis untuk kolom C sampai N
            foreach (range('H', 'O') as $col) {
                $sheet->setCellValue("{$col}{$totalRow}", "=SUM({$col}6:{$col}{$highestRow})");
            }

            // Perbarui nilai $highestRow agar baris Grand Total ikut terkena border & styling di bawahnya
            $highestRow = $totalRow; 
        }
        // --- END TAMBAHAN GRAND TOTAL ---

        // Border untuk seluruh data
        $sheet->getStyle('A5:O' . $highestRow)->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        // Alignment
        $sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // $sheet->getStyle('D6:D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H6:O' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Format kolom No. SPK (E) sebagai TEXT untuk mencegah scientific notation
        for ($row = 6; $row <= $highestRow; $row++) {
          $cellValue = $sheet->getCell('E' . $row)->getValue();
          if (!empty($cellValue)) {
            $sheet->setCellValueExplicit('E' . $row, $cellValue, DataType::TYPE_STRING);
          }

          // Isi 0 untuk semua kolom angka yang kosong (H-O)
          $numericColumns = ['H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'];
          foreach ($numericColumns as $col) {
            $value = $sheet->getCell($col . $row)->getValue();
            if ($value === null || $value === '') {
              $sheet->setCellValue($col . $row, 0);
            }
          }
        }

        // Format number untuk kolom angka (H-O)
        $sheet->getStyle('H6:O' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');
      },
    ];
  }
}
