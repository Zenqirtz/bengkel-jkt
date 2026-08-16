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
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Carbon\Carbon;

// class LaporanAnalisaPemakaianBahanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
class LaporanAnalisaPemakaianBahanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents, WithCustomValueBinder
{
  protected $params;
  protected $cabang;
  protected $periode;
  protected $rowNumber = 0;
  protected $data;
  protected $jumlahPanel = 0;

  // public function __construct(array $params, $cabang, $periode)
  // {
  //   $this->params = $params;
  //   $this->cabang = $cabang;
  //   $this->periode = $periode;
  // }

  // public function collection()
  // {
  //   $query = DB::table('v_rep_rekap_analisa_bahan')
  //     ->where('kode_cabang', $this->cabang['kode']);

  //   // Filter berdasarkan tahun dan bulan
  //   $bulan = $this->params['bulan'] ?? date('m');
  //   $jenis_report = $this->params['jenis_report'] ?? 'Rekap';

  //   if (!empty($tahun) && !empty($bulan)) {
  //     $query->whereYear('tanggal', $tahun)
  //       ->whereMonth('tanggal', $bulan);
  //   }

  //   // Hitung jumlah panel
  //   $this->jumlahPanel = DB::table('v_rep_rekap_analisa_bahan')
  //     ->where('kode_cabang', $this->cabang['kode'])
  //     ->whereYear('tanggal', $tahun)
  //     ->whereMonth('tanggal', $bulan)
  //     ->value('jumlah_panel') ?? 0;

  //   if ($jenis_report === 'Rekap') {
  //     $this->data = $query
  //       ->select([
  //         'nama_bahan',
  //         DB::raw('SUM(qty) as qty'),
  //         DB::raw('AVG(harga) as harga'),
  //         DB::raw('SUM(jumlah) as jumlah'),
  //         'satuan',
  //         DB::raw('SUM(qty_per_point) as qty_per_point'),
  //         DB::raw('SUM(rupiah_per_point) as rupiah_per_point')
  //       ])
  //       ->groupBy('nama_bahan', 'satuan')
  //       ->orderBy('nama_bahan', 'asc')
  //       ->get();
  //   } else {
  //     $this->data = $query
  //       ->select([
  //         'nama_bahan',
  //         'qty',
  //         'harga',
  //         'jumlah',
  //         'satuan',
  //         'qty_per_point',
  //         'rupiah_per_point'
  //       ])
  //       ->orderBy('nama_bahan', 'asc')
  //       ->get();
  //   }

  //   return $this->data;
  // }
  public function __construct(array $params, $cabang, $periode)
  {
    $this->params = $params;
    $this->cabang = $cabang;
    $this->periode = $periode;

    $kodeCabang = $this->cabang['kode'];
    $tahun = $this->params['tahun'] ?: date('Y');
    $bulan = $this->params['bulan'] ?: date('m');

    $this->jumlahPanel = DB::table('v_rep_rekap_analisa_bahan')
      ->where('kode_cabang', $kodeCabang)
      ->whereYear('tanggal', $tahun)
      ->whereMonth('tanggal', $bulan)
      ->value('jumlah_panel') ?? 0;
  }

  public function collection()
  {
    $kodeCabang = $this->cabang['kode'];
    $tahun = $this->params['tahun'] ?: date('Y');
    $bulan = $this->params['bulan'] ?: date('m');
    $jenis_report = $this->params['jenis_report'] ?: 'Rekap';

    $query = DB::table('v_rep_rekap_analisa_bahan')
      ->where('kode_cabang', $kodeCabang)
      ->whereYear('tanggal', $tahun)
      ->whereMonth('tanggal', $bulan);

    if ($jenis_report === 'Rekap') {
      $rows = (clone $query)
        ->select([
          'nama_bahan',
          DB::raw('SUM(qty) as qty'),
          DB::raw('AVG(harga) as harga'),
          DB::raw('SUM(jumlah) as jumlah'),
          'satuan',
        ])
        ->groupBy('nama_bahan', 'satuan')
        ->orderBy('nama_bahan', 'asc')
        ->get();
    } else {
      $rows = (clone $query)
        ->select(['nama_bahan', 'qty', 'harga', 'jumlah', 'satuan'])
        ->orderBy('nama_bahan', 'asc')
        ->get();
    }

    $this->data = $rows->map(function ($row) {
      $row->qty_per_point = $this->jumlahPanel > 0 ? $row->qty / $this->jumlahPanel : 0;
      $row->rupiah_per_point = $this->jumlahPanel > 0 ? $row->jumlah / $this->jumlahPanel : 0;
      return $row;
    });

    return $this->data;
  }
  public function headings(): array
  {
    return [
      [$this->cabang['nama']],
      ['Laporan Analisa Pemakaian Bahan'],
      ['Bulan / Tahun : ' . $this->periode],
      ['Jumlah Panel : ' . number_format($this->jumlahPanel, 2, '.', '.')],
      [''],
      [
        'No',
        'Nama Bahan',
        'Qty',
        'Harga',
        'Jumlah',
        'Satuan',
        'Qty/ Point Panel',
        'Rupiah/ Point Panel'
      ]
    ];
  }

  public function map($row): array
  {
    $this->rowNumber++;

    return [
      $this->rowNumber,
      $row->nama_bahan,
      number_format($row->qty, 2, '.', '.'),
      number_format($row->harga, 0, '.', '.'),
      number_format($row->jumlah, 0, '.', '.'),
      $row->satuan ?? '',
      number_format($row->qty_per_point, 2, '.', '.'),
      number_format($row->rupiah_per_point, 0, '.', '.')
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
      4 => ['font' => ['bold' => true]],
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

  // public function registerEvents(): array
  // {
  //   return [
  //     AfterSheet::class => function (AfterSheet $event) {
  //       $lastColumn = 'H'; // Column H for 8 columns

  //       // Merge cells untuk header
  //       $event->sheet->mergeCells('A1:' . $lastColumn . '1');
  //       $event->sheet->mergeCells('A2:' . $lastColumn . '2');
  //       $event->sheet->mergeCells('A3:' . $lastColumn . '3');
  //       $event->sheet->mergeCells('A4:' . $lastColumn . '4');

  //       $highestRow = $event->sheet->getHighestRow();

  //       // Apply borders ke semua data
  //       $event->sheet->getStyle('A6:' . $lastColumn . $highestRow)->applyFromArray([
  //         'borders' => [
  //           'allBorders' => [
  //             'borderStyle' => Border::BORDER_THIN,
  //           ],
  //         ],
  //       ]);

  //       // Alignment
  //       $dataStartRow = 7;
  //       // Center alignment untuk No
  //       $event->sheet->getStyle('A' . $dataStartRow . ':A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
  //       // Right alignment untuk Qty, Harga, Jumlah, Qty/Point, Rupiah/Point
  //       $event->sheet->getStyle('C' . $dataStartRow . ':E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
  //       $event->sheet->getStyle('G' . $dataStartRow . ':H' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

  //       // Add total row
  //       if ($this->data->count() > 0) {
  //         $totalRow = $highestRow + 1;
  //         $totalJumlah = $this->data->sum('jumlah');
  //         $totalQtyPoint = $this->data->sum('qty_per_point');
  //         $totalRupiahPoint = $this->data->sum('rupiah_per_point');

  //         $event->sheet->setCellValue('A' . $totalRow, '');
  //         $event->sheet->setCellValue('B' . $totalRow, '');
  //         $event->sheet->setCellValue('C' . $totalRow, '');
  //         $event->sheet->setCellValue('D' . $totalRow, '');
  //         $event->sheet->setCellValue('E' . $totalRow, number_format($totalJumlah, 0, '.', '.'));
  //         $event->sheet->setCellValue('F' . $totalRow, '');
  //         $event->sheet->setCellValue('G' . $totalRow, number_format($totalQtyPoint, 2, '.', '.'));
  //         $event->sheet->setCellValue('H' . $totalRow, number_format($totalRupiahPoint, 0, '.', '.'));

  //         // Style for total row
  //         $event->sheet->getStyle('A' . $totalRow . ':' . $lastColumn . $totalRow)->applyFromArray([
  //           'font' => ['bold' => true],
  //           'borders' => [
  //             'allBorders' => [
  //               'borderStyle' => Border::BORDER_THIN,
  //             ],
  //           ]
  //         ]);

  //         $event->sheet->getStyle('E' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
  //         $event->sheet->getStyle('G' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
  //         $event->sheet->getStyle('H' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
  //       }
  //     },
  //   ];
  // }
  public function bindValue(Cell $cell, $value)
  {
    $col = $cell->getColumn();
    if (in_array($col, ['C', 'D', 'E', 'G', 'H'])) { // kolom Qty, Harga, Jumlah, Qty/Point, Rupiah/Point
      $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
      $cell->getIgnoredErrors()->setNumberStoredAsText(true);
      return true;
    }

    return (new DefaultValueBinder())->bindValue($cell, $value);
  }
  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $lastColumn = 'H'; // Column H for 8 columns

        // Merge cells untuk header
        $event->sheet->mergeCells('A1:' . $lastColumn . '1');
        $event->sheet->mergeCells('A2:' . $lastColumn . '2');
        $event->sheet->mergeCells('A3:' . $lastColumn . '3');
        $event->sheet->mergeCells('A4:' . $lastColumn . '4');

        $highestRow = $event->sheet->getHighestRow();

        // Apply borders ke semua data
        $event->sheet->getStyle('A6:' . $lastColumn . $highestRow)->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        // Alignment
        $dataStartRow = 7;
        // Center alignment untuk No
        $event->sheet->getStyle('A' . $dataStartRow . ':A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // Right alignment untuk Qty, Harga, Jumlah, Qty/Point, Rupiah/Point
        $event->sheet->getStyle('C' . $dataStartRow . ':E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $event->sheet->getStyle('G' . $dataStartRow . ':H' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Add total row
        if ($this->data->count() > 0) {
          $totalRow = $highestRow + 1;
          $totalJumlah = $this->data->sum('jumlah');
          $totalQtyPoint = $this->data->sum('qty_per_point');
          $totalRupiahPoint = $this->data->sum('rupiah_per_point');

          $event->sheet->setCellValue('A' . $totalRow, '');
          $event->sheet->setCellValue('B' . $totalRow, 'Grand Total');
          $event->sheet->setCellValue('C' . $totalRow, '');
          $event->sheet->setCellValue('D' . $totalRow, '');
          $event->sheet->setCellValue('E' . $totalRow, number_format($totalJumlah, 0, '.', '.'));
          $event->sheet->setCellValue('F' . $totalRow, '');
          $event->sheet->setCellValue('G' . $totalRow, number_format($totalQtyPoint, 2, '.', '.'));
          $event->sheet->setCellValue('H' . $totalRow, number_format($totalRupiahPoint, 0, '.', '.'));

          // Style for total row - warna biru
          $totalRange = 'A' . $totalRow . ':' . $lastColumn . $totalRow;

          $event->sheet->getStyle($totalRange)->applyFromArray([
            'font' => [
              'bold' => true,
              'color' => ['argb' => 'FF1E40AF'],
            ],
            'fill' => [
              'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
              'startColor' => ['argb' => 'FFDBEAFE'],
            ],
            'borders' => [
              'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
              ],
            ]
          ]);

          $event->sheet->getStyle($totalRange)->getBorders()->getTop()
            ->setBorderStyle(Border::BORDER_MEDIUM)
            ->getColor()->setARGB('FF3B82F6');

          $event->sheet->getStyle($totalRange)->getBorders()->getBottom()
            ->setBorderStyle(Border::BORDER_MEDIUM)
            ->getColor()->setARGB('FF3B82F6');

          $event->sheet->getStyle('E' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $event->sheet->getStyle('G' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $event->sheet->getStyle('H' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
      },
    ];
  }
}
