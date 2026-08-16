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

class LaporanKwitansiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
    $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
    $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');

    $query = DB::table('v_rpt_pelunasan_or as k')
    ->where('k.kode_cabang', $this->cabang['kode']) // Sesuaikan jika ini array/string
    ->whereDate('k.tgl_invoice', '>=', $startDate)
    ->whereDate('k.tgl_invoice', '<=', $endDate)
    ->select([
      'k.tanggal_lunas_or',
      'k.kode_voucher',
      'k.no_kwitansi',
      'k.kode_spk',
      'k.no_polisi',
      'k.tertanggung',
      'k.nama_pelanggan',
      'k.no_invoice',
      'k.tgl_invoice',
      'k.kas',
      'k.free',
      'k.bank',
      'k.total_or',
    ])
    ->orderBy('k.tgl_invoice', 'asc')
    ->orderBy('k.kode_voucher', 'asc');

    return $query->get();
  }

  public function headings(): array
  {
    return [
      [$this->cabang['nama']],
      ['Laporan Kwitansi OR Lunas'],
      ['Periode : ' . $this->periode],
      [''],
      [
        'No',
        'Tanggal Lunas',
        'No. Voucher',
        'No. Kwitansi',
        'No. SPK',
        'No. Polisi',
        'Tertanggung',
        'Nama Asuransi',
        'No. Invoice',
        'Tanggal Invoice',
        'Kas',
        'Free',
        'Bank',
        'Total OR',
      ]
    ];
  }

  public function map($row): array
  {
    $this->rowNumber++;

    return [
      $this->rowNumber,
      $row->tanggal_lunas_or ? date("d/m/Y", strtotime($row->tanggal_lunas_or)) : '',
      $row->kode_voucher,
      $row->no_kwitansi,
      $row->kode_spk,
      $row->no_polisi,
      $row->tertanggung,
      $row->nama_pelanggan,
      $row->no_invoice,
      $row->tgl_invoice ? date("d/m/Y", strtotime($row->tgl_invoice)) : '',
      $row->kas,
      $row->free,
      $row->bank,
      $row->total_or,
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
      ]
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

        $highestRow = $sheet->getHighestRow();  

        // --- TAMBAHAN UNTUK GRAND TOTAL ---
        // Kita cek apakah ada data (baris data dimulai dari baris 7)
        if ($highestRow >= 6) {
            $totalRow = $highestRow + 1; // Baris untuk grand total
            
            // Gabungkan kolom A dan B untuk label "Grand Total"
            $sheet->mergeCells("A{$totalRow}:J{$totalRow}");
            $sheet->setCellValue("A{$totalRow}", 'Grand Total');
            
            // Set Alignment teks Grand Total ke Kanan dan tebalkan barisnya
            $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("A{$totalRow}:N{$totalRow}")->getFont()->setBold(true);

            // Buat rumus SUM otomatis untuk kolom C sampai N
            foreach (range('K', 'N') as $col) {
                $sheet->setCellValue("{$col}{$totalRow}", "=SUM({$col}6:{$col}{$highestRow})");
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
        $sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Format angka untuk kolom D sampai N (format ribuan)
        $sheet->getStyle('K6:N' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');
      },
    ];
  }
}
