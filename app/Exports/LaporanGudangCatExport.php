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
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Carbon\Carbon;

class LaporanGudangCatExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
  protected $params;
  protected $cabang;
  protected $periode;
  protected $rowNumber = 0;
  protected $data;
  // protected $currentSupplier = null;
  protected $currentKodeInput = null;
  protected $rowValuesList = [];
  protected $subtotal = [
    'harga' => 0,
    'jumlah_sebelum' => 0,
    'ppn' => 0,
    'jumlah' => 0,
    'cash' => 0,
    'credit' => 0
  ];
  protected $grandTotal = [
    'jumlah_sebelum' => 0,
    'ppn' => 0,
    'jumlah' => 0,
    'cash' => 0,
    'credit' => 0
  ];
  protected $subtotalRows = [];
  protected $repeatHeaderRows = [];
  protected $grandTotalRow = null;

  public function __construct(array $params, $cabang, $periode)
  {
    $this->params = $params;
    $this->cabang = $cabang;
    $this->periode = $periode;
  }

  public function collection()
  {
    $query = DB::table('v_rpt_gudang_cat')
      ->where('kode_cabang', $this->cabang['kode'])
      ->select([
        'tanggal',
        'kode_input',
        'nama_pemasok',
        'nama_bahan',
        'group_bahan',
        'no_po',
        'qty',
        'kode_satuan',
        'harga',
        'jumlah_sebelum',
        'ppn',
        'jumlah',
        'cash',
        'credit'
      ]);

    if (!empty($this->params['tgl_awal'])) {
      try {
        $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
        $query->whereDate('tanggal', '>=', $startDate);
      } catch (\Exception $e) {
      }
    }

    if (!empty($this->params['tgl_akhir'])) {
      try {
        $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
        $query->whereDate('tanggal', '<=', $endDate);
      } catch (\Exception $e) {
      }
    }

    // $this->data = $query
    //   ->orderBy('nama_pemasok', 'asc')
    //   ->orderBy('tanggal', 'asc')
    //   ->orderBy('kode_input', 'asc')
    //   ->get();
    $this->data = $query
      ->orderBy('tanggal', 'asc')
      ->orderBy('kode_input', 'asc')
      ->get();

    $processedData = collect();
    $currentRow = 6;

    foreach ($this->data as $row) {
      // if ($this->currentSupplier !== null && $this->currentSupplier !== $row->nama_pemasok) {
      if ($this->currentKodeInput !== null && $this->currentKodeInput !== $row->kode_input) {
        // Baris subtotal
        $processedData->push((object) [
          'is_subtotal' => true,
          'is_repeat_header' => false,
          'is_grandtotal' => false,
          'jumlah_sebelum' => $this->subtotal['jumlah_sebelum'],
          'ppn' => $this->subtotal['ppn'],
          'jumlah' => $this->subtotal['jumlah'],
          'cash' => $this->subtotal['cash'],
          'credit' => $this->subtotal['credit'],
        ]);
        $this->subtotalRows[] = $currentRow++;

        $this->grandTotal['jumlah_sebelum'] += $this->subtotal['jumlah_sebelum'];
        $this->grandTotal['ppn'] += $this->subtotal['ppn'];
        $this->grandTotal['jumlah'] += $this->subtotal['jumlah'];
        $this->grandTotal['cash'] += $this->subtotal['cash'];
        $this->grandTotal['credit'] += $this->subtotal['credit'];

        // Baris header ulang
        $processedData->push((object) [
          'is_subtotal' => false,
          'is_repeat_header' => true,
          'is_grandtotal' => false,
        ]);
        $this->repeatHeaderRows[] = $currentRow++;

        $this->subtotal = [
          'harga' => 0,
          'jumlah_sebelum' => 0,
          'ppn' => 0,
          'jumlah' => 0,
          'cash' => 0,
          'credit' => 0,
        ];
      }

      // $this->currentSupplier = $row->nama_pemasok;
      $this->currentKodeInput = $row->kode_input;
      $this->subtotal['harga'] += $row->harga;
      $this->subtotal['jumlah_sebelum'] += $row->jumlah_sebelum;
      $this->subtotal['ppn'] += $row->ppn;
      $this->subtotal['jumlah'] += $row->jumlah;
      $this->subtotal['cash'] += $row->cash;
      $this->subtotal['credit'] += $row->credit;

      $row->is_subtotal = false;
      $row->is_repeat_header = false;
      $row->is_grandtotal = false;
      $processedData->push($row);
      $currentRow++;
    }

    // Subtotal terakhir
    if ($this->data->count() > 0) {
      $processedData->push((object) [
        'is_subtotal' => true,
        'is_repeat_header' => false,
        'is_grandtotal' => false,
        'jumlah_sebelum' => $this->subtotal['jumlah_sebelum'],
        'ppn' => $this->subtotal['ppn'],
        'jumlah' => $this->subtotal['jumlah'],
        'cash' => $this->subtotal['cash'],
        'credit' => $this->subtotal['credit'],
      ]);
      $this->subtotalRows[] = $currentRow++;

      $this->grandTotal['jumlah_sebelum'] += $this->subtotal['jumlah_sebelum'];
      $this->grandTotal['ppn'] += $this->subtotal['ppn'];
      $this->grandTotal['jumlah'] += $this->subtotal['jumlah'];
      $this->grandTotal['cash'] += $this->subtotal['cash'];
      $this->grandTotal['credit'] += $this->subtotal['credit'];

      // Baris Grand Total
      $processedData->push((object) [
        'is_subtotal' => false,
        'is_repeat_header' => false,
        'is_grandtotal' => true,
        'jumlah_sebelum' => $this->grandTotal['jumlah_sebelum'],
        'ppn' => $this->grandTotal['ppn'],
        'jumlah' => $this->grandTotal['jumlah'],
        'cash' => $this->grandTotal['cash'],
        'credit' => $this->grandTotal['credit'],
      ]);
      $this->grandTotalRow = $currentRow;
    }

    return $processedData;
  }

  public function headings(): array
  {
    return [
      [$this->cabang['nama']],
      ['Laporan Input Gudang Cat'],
      ['Periode : ' . $this->periode],
      [''],
      [
        'No',
        'Tanggal',
        'No. Input Gudang',
        'Supplier',
        'Nama Bahan',
        'Tipe',
        'No. PO',
        'Qty',
        'Satuan',
        'Harga',
        'Jumlah',
        'PPN',
        'Total',
        'Kas',
        'Kredit'
      ]
    ];
  }

  // public function map($row): array
  // {
  //   if ($row->is_subtotal) {
  //     return [
  //       '',
  //       '',
  //       '',
  //       '',
  //       '',
  //       '',
  //       '',
  //       '',
  //       '',
  //       'Total',
  //       number_format($row->jumlah_sebelum, 0, '.', '.'),
  //       number_format($row->ppn, 0, '.', '.'),
  //       number_format($row->jumlah, 0, '.', '.'),
  //       number_format($row->cash, 0, '.', '.'),
  //       number_format($row->credit, 0, '.', '.'),
  //     ];
  //   }

  //   if ($row->is_repeat_header) {
  //     return [
  //       'No',
  //       'Tanggal',
  //       'No. Input Gudang',
  //       'Supplier',
  //       'Nama Bahan',
  //       'Tipe',
  //       'No. PO',
  //       'Qty',
  //       'Satuan',
  //       'Harga',
  //       'Jumlah',
  //       'PPN',
  //       'Total',
  //       'Kas',
  //       'Kredit'
  //     ];
  //   }

  //   $this->rowNumber++;
  //   return [
  //     $this->rowNumber,
  //     Carbon::parse($row->tanggal)->format('d/m/Y'),
  //     $row->kode_input,
  //     $row->nama_pemasok,
  //     $row->nama_bahan,
  //     $row->group_bahan,
  //     $row->no_po ?? '',
  //     number_format($row->qty, 2, '.', '.'),
  //     $row->kode_satuan ?? '',
  //     number_format($row->harga, 0, '.', '.'),
  //     number_format($row->jumlah_sebelum, 0, '.', '.'),
  //     number_format($row->ppn, 0, '.', '.'),
  //     number_format($row->jumlah, 0, '.', '.'),
  //     number_format($row->cash, 0, '.', '.'),
  //     number_format($row->credit, 0, '.', '.'),
  //   ];
  // }
  public function map($row): array
  {
    if ($row->is_subtotal) {
      $vals = [
        'type' => 'subtotal',
        'K' => number_format($row->jumlah_sebelum, 0, '.', '.'),
        'L' => number_format($row->ppn, 0, '.', '.'),
        'M' => number_format($row->jumlah, 0, '.', '.'),
        'N' => number_format($row->cash, 0, '.', '.'),
        'O' => number_format($row->credit, 0, '.', '.'),
      ];
      $this->rowValuesList[] = $vals;

      return [
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        'Total',
        $vals['K'],
        $vals['L'],
        $vals['M'],
        $vals['N'],
        $vals['O'],
      ];
    }

    if (isset($row->is_grandtotal) && $row->is_grandtotal) {
      $vals = [
        'type' => 'grandtotal',
        'K' => number_format($row->jumlah_sebelum, 0, '.', '.'),
        'L' => number_format($row->ppn, 0, '.', '.'),
        'M' => number_format($row->jumlah, 0, '.', '.'),
        'N' => number_format($row->cash, 0, '.', '.'),
        'O' => number_format($row->credit, 0, '.', '.'),
      ];
      $this->rowValuesList[] = $vals;

      return [
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        'Grand Total',
        $vals['K'],
        $vals['L'],
        $vals['M'],
        $vals['N'],
        $vals['O'],
      ];
    }

    if ($row->is_repeat_header) {
      $this->rowValuesList[] = ['type' => 'header'];

      return [
        'No',
        'Tanggal',
        'No. Input Gudang',
        'Supplier',
        'Nama Bahan',
        'Tipe',
        'No. PO',
        'Qty',
        'Satuan',
        'Harga',
        'Jumlah',
        'PPN',
        'Total',
        'Kas',
        'Kredit'
      ];
    }

    $this->rowNumber++;

    $vals = [
      'type' => 'data',
      'H' => number_format($row->qty, 2, '.', '.'),
      'J' => number_format($row->harga, 0, '.', '.'),
      'K' => number_format($row->jumlah_sebelum, 0, '.', '.'),
      'L' => number_format($row->ppn, 0, '.', '.'),
      'M' => number_format($row->jumlah, 0, '.', '.'),
      'N' => number_format($row->cash, 0, '.', '.'),
      'O' => number_format($row->credit, 0, '.', '.'),
    ];
    $this->rowValuesList[] = $vals;

    return [
      $this->rowNumber,
      Carbon::parse($row->tanggal)->format('d/m/Y'),
      $row->kode_input,
      $row->nama_pemasok,
      $row->nama_bahan,
      $row->group_bahan,
      $row->no_po ?? '',
      $vals['H'],
      $row->kode_satuan ?? '',
      $vals['J'],
      $vals['K'],
      $vals['L'],
      $vals['M'],
      $vals['N'],
      $vals['O'],
    ];
  }
  public function styles(Worksheet $sheet)
  {
    return [
      1 => ['font' => ['bold' => true, 'size' => 14]],
      2 => ['font' => ['bold' => true]],
      3 => ['font' => ['bold' => true]],
      5 => [
        'font' => ['bold' => true],
        'alignment' => [
          'horizontal' => Alignment::HORIZONTAL_CENTER,
          'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
          'allBorders' => ['borderStyle' => Border::BORDER_THIN],
        ],
      ],
    ];
  }

  // public function registerEvents(): array
  // {
  //   return [
  //     AfterSheet::class => function (AfterSheet $event) {
  //       $lastColumn = 'O';

  //       // Merge cells header laporan
  //       $event->sheet->mergeCells('A1:' . $lastColumn . '1');
  //       $event->sheet->mergeCells('A2:' . $lastColumn . '2');
  //       $event->sheet->mergeCells('A3:' . $lastColumn . '3');

  //       $highestRow = $event->sheet->getHighestRow();

  //       // Border semua data
  //       $event->sheet->getStyle('A5:' . $lastColumn . $highestRow)->applyFromArray([
  //         'borders' => [
  //           'allBorders' => ['borderStyle' => Border::BORDER_THIN],
  //         ],
  //       ]);

  //       // Alignment
  //       $dataStartRow = 6;
  //       $event->sheet->getStyle('A' . $dataStartRow . ':B' . $highestRow)
  //         ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
  //       $event->sheet->getStyle('H' . $dataStartRow . ':' . $lastColumn . $highestRow)
  //         ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

  //       // Style baris subtotal — biru muda + font biru tua + border tebal
  //       foreach ($this->subtotalRows as $rowNum) {
  //         $event->sheet->getStyle('A' . $rowNum . ':' . $lastColumn . $rowNum)->applyFromArray([
  //           'font' => [
  //             'bold' => true,
  //             'color' => ['argb' => 'FF1E40AF'],
  //           ],
  //           'fill' => [
  //             'fillType' => Fill::FILL_SOLID,
  //             'startColor' => ['argb' => 'FFDBEAFE'],
  //           ],
  //           'borders' => [
  //             'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF3B82F6']],
  //             'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF3B82F6']],
  //           ],
  //         ]);
  //       }

  //       // Style baris header ulang — bold saja, tanpa warna background
  //       foreach ($this->repeatHeaderRows as $rowNum) {
  //         $event->sheet->getStyle('A' . $rowNum . ':' . $lastColumn . $rowNum)->applyFromArray([
  //           'font' => ['bold' => true],
  //           'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
  //         ]);
  //       }
  //     },
  //   ];
  // }
  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $lastColumn = 'O';

        // Merge cells header laporan
        $event->sheet->mergeCells('A1:' . $lastColumn . '1');
        $event->sheet->mergeCells('A2:' . $lastColumn . '2');
        $event->sheet->mergeCells('A3:' . $lastColumn . '3');

        $highestRow = $event->sheet->getHighestRow();

        // Border semua data
        $event->sheet->getStyle('A5:' . $lastColumn . $highestRow)->applyFromArray([
          'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
          ],
        ]);

        // Alignment
        $dataStartRow = 6;
        $event->sheet->getStyle('A' . $dataStartRow . ':B' . $highestRow)
          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('H' . $dataStartRow . ':' . $lastColumn . $highestRow)
          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // ⬇️ TAMBAHAN: paksa semua kolom angka (H, J, K, L, M, N, O) jadi text
        $setTextCell = function ($cellRef, $value) use ($event) {
          $cell = $event->sheet->getCell($cellRef);
          $cell->setValueExplicit($value, DataType::TYPE_STRING);
          $cell->getIgnoredErrors()->setNumberStoredAsText(true);
        };

        foreach ($this->rowValuesList as $i => $vals) {
          $excelRow = $dataStartRow + $i;

          if ($vals['type'] === 'header') {
            continue;
          }

          foreach ($vals as $col => $val) {
            if ($col === 'type') {
              continue;
            }
            $setTextCell($col . $excelRow, $val);
          }
        }
        // ⬆️ AKHIR TAMBAHAN

        // Style baris subtotal — biru muda + font biru tua + border tebal
        foreach ($this->subtotalRows as $rowNum) {
          $event->sheet->getStyle('A' . $rowNum . ':' . $lastColumn . $rowNum)->applyFromArray([
            'font' => [
              'bold' => true,
              'color' => ['argb' => 'FF1E40AF'],
            ],
            'fill' => [
              'fillType' => Fill::FILL_SOLID,
              'startColor' => ['argb' => 'FFDBEAFE'],
            ],
            'borders' => [
              'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF3B82F6']],
              'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF3B82F6']],
            ],
          ]);
        }

        // Style baris header ulang — bold saja, tanpa warna background
        foreach ($this->repeatHeaderRows as $rowNum) {
          $event->sheet->getStyle('A' . $rowNum . ':' . $lastColumn . $rowNum)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
          ]);
        }

        // Style baris Grand Total — bold, warna kuning
        if ($this->grandTotalRow) {
          $event->sheet->getStyle('A' . $this->grandTotalRow . ':' . $lastColumn . $this->grandTotalRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
              'fillType' => Fill::FILL_SOLID,
              'startColor' => ['argb' => 'FFFFEB3B'], // Yellow
            ],
            'borders' => [
              'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => '00000000']],
              'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => '00000000']],
            ],
          ]);
        }
      },
    ];
  }
}
