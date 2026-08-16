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
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Carbon\Carbon;

class LaporanReturSparepartExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
  protected $params;
  protected $cabang;
  protected $periode;
  protected $rowNumber = 0;
  protected $data;
  // protected $currentSparepart = null;
  protected $currentNoRetur = null;
  protected $subtotal = [
    'jumlah' => 0,
    'harga' => 0,
    'total' => 0
  ];
  protected $exportData = [];

  protected $subtotalRows = [];

  public function __construct(array $params, $cabang, $periode)
  {
    $this->params = $params;
    $this->cabang = $cabang;
    $this->periode = $periode;

  }

  // public function collection()
  // {
  //   $query = DB::table('v_rep_retur_sparepart')
  //     ->where('kode_cabang', $this->cabang['kode'])
  //     ->select([
  //       'tgl_retur',
  //       'no_retur',
  //       'tgl_input_gudang',
  //       'no_input_gudang',
  //       'nama_supplier',
  //       'nama_sparepart',
  //       'no_spk',
  //       'no_polisi',
  //       'jumlah',
  //       'harga',
  //       'total'
  //     ]);

  //   // Filter tanggal
  //   if (!empty($this->params['tgl_awal'])) {
  //     try {
  //       $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
  //       $query->whereDate('tgl_retur', '>=', $startDate);
  //     } catch (\Exception $e) {
  //       // Handle error
  //     }
  //   }

  //   if (!empty($this->params['tgl_akhir'])) {
  //     try {
  //       $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
  //       $query->whereDate('tgl_retur', '<=', $endDate);
  //     } catch (\Exception $e) {
  //       // Handle error
  //     }
  //   }

  //   $this->data = $query
  //     ->orderBy('nama_sparepart', 'asc')
  //     ->orderBy('tgl_retur', 'asc')
  //     ->orderBy('no_retur', 'asc')
  //     ->get();

  //   // Process data untuk include subtotal
  //   $processedData = collect();
  //   $currentRow = 7; // Mulai dari baris 7 (setelah header dengan 2 baris)

  //   foreach ($this->data as $row) {
  //     // Jika nama sparepart berubah dan bukan data pertama
  //     if ($this->currentSparepart !== null && $this->currentSparepart !== $row->nama_sparepart) {
  //       // Tambahkan subtotal row
  //       $subtotalRow = (object) [
  //         'tgl_retur' => null,
  //         'no_retur' => null,
  //         'tgl_input_gudang' => null,
  //         'no_input_gudang' => null,
  //         'nama_supplier' => null,
  //         'nama_sparepart' => 'Total',
  //         'no_spk' => null,
  //         'no_polisi' => null,
  //         'jumlah' => $this->subtotal['jumlah'],
  //         'harga' => $this->subtotal['harga'],
  //         'total' => $this->subtotal['total'],
  //         'is_subtotal' => true
  //       ];
  //       $processedData->push($subtotalRow);
  //       $this->subtotalRows[] = $currentRow;
  //       $currentRow++;

  //       // Reset subtotal
  //       $this->subtotal = [
  //         'jumlah' => 0,
  //         'harga' => 0,
  //         'total' => 0
  //       ];
  //     }

  //     $this->currentSparepart = $row->nama_sparepart;

  //     // Tambah ke subtotal
  //     $this->subtotal['jumlah'] += $row->jumlah;
  //     $this->subtotal['harga'] += $row->harga;
  //     $this->subtotal['total'] += $row->total;

  //     $row->is_subtotal = false;
  //     $processedData->push($row);
  //     $currentRow++;
  //   }

  //   // Tambahkan subtotal terakhir
  //   if ($this->data->count() > 0) {
  //     $subtotalRow = (object) [
  //       'tgl_retur' => null,
  //       'no_retur' => null,
  //       'tgl_input_gudang' => null,
  //       'no_input_gudang' => null,
  //       'nama_supplier' => null,
  //       'nama_sparepart' => 'Total',
  //       'no_spk' => null,
  //       'no_polisi' => null,
  //       'jumlah' => $this->subtotal['jumlah'],
  //       'harga' => $this->subtotal['harga'],
  //       'total' => $this->subtotal['total'],
  //       'is_subtotal' => true
  //     ];
  //     $processedData->push($subtotalRow);
  //     $this->subtotalRows[] = $currentRow;
  //   }

  //   return $processedData;
  // }

  public function collection()
  {
    $query = DB::table('v_rep_retur_sparepart')
      ->where('kode_cabang', $this->cabang['kode'])
      ->select([
        'tgl_retur',
        'no_retur',
        'tgl_input_gudang',
        'no_input_gudang',
        'nama_supplier',
        'nama_sparepart',
        'no_spk',
        'no_polisi',
        'jumlah',
        'harga',
        'total'
      ]);

    // Filter tanggal
    if (!empty($this->params['tgl_awal'])) {
      try {
        $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
        $query->whereDate('tgl_retur', '>=', $startDate);
      } catch (\Exception $e) {
        // Handle error
      }
    }

    if (!empty($this->params['tgl_akhir'])) {
      try {
        $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
        $query->whereDate('tgl_retur', '<=', $endDate);
      } catch (\Exception $e) {
        // Handle error
      }
    }

    $this->data = $query
      ->orderBy('tgl_retur', 'asc')
      ->orderBy('no_retur', 'asc')
      ->get();

    // Process data untuk include subtotal
    $processedData = collect();
    $currentRow = 7; // Mulai dari baris 7 (setelah header dengan 2 baris)

    foreach ($this->data as $row) {
      // Jika no_retur berubah dan bukan data pertama
      if ($this->currentNoRetur !== null && $this->currentNoRetur !== $row->no_retur) {
        // Tambahkan subtotal row (per grup no_retur)
        $subtotalRow = (object) [
          'tgl_retur' => null,
          'no_retur' => null,
          'tgl_input_gudang' => null,
          'no_input_gudang' => null,
          'nama_supplier' => null,
          'nama_sparepart' => 'Total',
          'no_spk' => null,
          'no_polisi' => null,
          'jumlah' => $this->subtotal['jumlah'],
          'harga' => $this->subtotal['harga'],
          'total' => $this->subtotal['total'],
          'is_subtotal' => true
        ];
        $processedData->push($subtotalRow);
        $this->subtotalRows[] = $currentRow;
        $currentRow++;

        // Reset subtotal
        $this->subtotal = [
          'jumlah' => 0,
          'harga' => 0,
          'total' => 0
        ];
      }

      $this->currentNoRetur = $row->no_retur;

      // Tambah ke subtotal
      $this->subtotal['jumlah'] += $row->jumlah;
      $this->subtotal['harga'] += $row->harga;
      $this->subtotal['total'] += $row->total;

      $row->is_subtotal = false;
      $processedData->push($row);
      $currentRow++;
    }

    // Tambahkan subtotal terakhir (Grand Total)
    if ($this->data->count() > 0) {
      $subtotalRow = (object) [
        'tgl_retur' => null,
        'no_retur' => null,
        'tgl_input_gudang' => null,
        'no_input_gudang' => null,
        'nama_supplier' => null,
        'nama_sparepart' => 'Grand Total',
        'no_spk' => null,
        'no_polisi' => null,
        'jumlah' => $this->subtotal['jumlah'],
        'harga' => $this->subtotal['harga'],
        'total' => $this->subtotal['total'],
        'is_subtotal' => true
      ];
      $processedData->push($subtotalRow);
      $this->subtotalRows[] = $currentRow;
    }

    // WAJIB: simpan supaya bisa dipakai di registerEvents() untuk setValueExplicit
    $this->exportData = $processedData;

    return $processedData;
  }
  public function headings(): array
  {
    return [
      [$this->cabang['nama']],
      ['Laporan Retur Sparepart'],
      ['Periode : ' . $this->periode],
      [''],
      [
        'No',
        'Retur',
        '',
        'Input Gudang',
        '',
        'Nama Supplier',
        'Nama Sparepart',
        'No. SPK',
        'No. Polisi',
        'Jumlah',
        'Harga',
        'Total'
      ],
      [
        '',
        'Tanggal',
        'Nomor',
        'Tanggal',
        'Nomor',
        '',
        '',
        '',
        '',
        '',
        '',
        ''
      ]
    ];
  }

  public function map($row): array
  {
    if (isset($row->is_subtotal) && $row->is_subtotal) {
      // Baris subtotal
      return [
        '',
        '',
        '',
        '',
        '',
        '',
        $row->nama_sparepart, // "Total"
        '',
        '',
        number_format($row->jumlah, 0, '.', '.'),
        number_format($row->harga, 0, '.', '.'),
        number_format($row->total, 0, '.', '.'),
      ];
    }

    // Baris data normal
    $this->rowNumber++;

    return [
      $this->rowNumber,
      $row->tgl_retur ? Carbon::parse($row->tgl_retur)->format('d/m/Y') : '',
      $row->no_retur ?? '',
      $row->tgl_input_gudang ? Carbon::parse($row->tgl_input_gudang)->format('d/m/Y') : '',
      $row->no_input_gudang ?? '',
      $row->nama_supplier ?? '',
      $row->nama_sparepart,
      $row->no_spk ?? '',
      $row->no_polisi ?? '',
      number_format($row->jumlah, 0, '.', '.'),
      number_format($row->harga, 0, '.', '.'),
      number_format($row->total, 0, '.', '.'),
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

  // public function registerEvents(): array
  // {
  //   return [
  //     AfterSheet::class => function (AfterSheet $event) {
  //       $lastColumn = 'L'; // Column L for 12 columns

  //       // Merge cells untuk header
  //       $event->sheet->mergeCells('A1:' . $lastColumn . '1');
  //       $event->sheet->mergeCells('A2:' . $lastColumn . '2');
  //       $event->sheet->mergeCells('A3:' . $lastColumn . '3');

  //       // Merge untuk header tabel
  //       $event->sheet->mergeCells('A5:A6'); // No
  //       $event->sheet->mergeCells('B5:C5'); // Retur
  //       $event->sheet->mergeCells('D5:E5'); // Input Gudang
  //       $event->sheet->mergeCells('F5:F6'); // Nama Supplier
  //       $event->sheet->mergeCells('G5:G6'); // Nama Sparepart
  //       $event->sheet->mergeCells('H5:H6'); // No. SPK
  //       $event->sheet->mergeCells('I5:I6'); // No. Polisi
  //       $event->sheet->mergeCells('J5:J6'); // Jumlah
  //       $event->sheet->mergeCells('K5:K6'); // Harga
  //       $event->sheet->mergeCells('L5:L6'); // Total

  //       $highestRow = $event->sheet->getHighestRow();

  //       // Apply borders ke semua data
  //       $event->sheet->getStyle('A5:' . $lastColumn . $highestRow)->applyFromArray([
  //         'borders' => [
  //           'allBorders' => [
  //             'borderStyle' => Border::BORDER_THIN,
  //           ],
  //         ],
  //       ]);

  //       // Alignment
  //       $dataStartRow = 7;
  //       // Center alignment untuk No dan Tanggal
  //       $event->sheet->getStyle('A' . $dataStartRow . ':A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
  //       $event->sheet->getStyle('B' . $dataStartRow . ':B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
  //       $event->sheet->getStyle('D' . $dataStartRow . ':D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
  //       // Right alignment untuk Jumlah, Harga, Total
  //       $event->sheet->getStyle('J' . $dataStartRow . ':L' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

  //       // Apply bold style to subtotal rows
  //       foreach ($this->subtotalRows as $rowNumber) {
  //         $event->sheet->getStyle('A' . $rowNumber . ':' . $lastColumn . $rowNumber)->applyFromArray([
  //           'font' => [
  //             'bold' => true
  //           ]
  //         ]);
  //       }
  //     },
  //   ];
  // }
  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $lastColumn = 'L';

        $event->sheet->mergeCells('A1:' . $lastColumn . '1');
        $event->sheet->mergeCells('A2:' . $lastColumn . '2');
        $event->sheet->mergeCells('A3:' . $lastColumn . '3');

        $event->sheet->mergeCells('A5:A6');
        $event->sheet->mergeCells('B5:C5');
        $event->sheet->mergeCells('D5:E5');
        $event->sheet->mergeCells('F5:F6');
        $event->sheet->mergeCells('G5:G6');
        $event->sheet->mergeCells('H5:H6');
        $event->sheet->mergeCells('I5:I6');
        $event->sheet->mergeCells('J5:J6');
        $event->sheet->mergeCells('K5:K6');
        $event->sheet->mergeCells('L5:L6');

        $highestRow = $event->sheet->getHighestRow();

        $event->sheet->getStyle('A5:' . $lastColumn . $highestRow)->applyFromArray([
          'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
          ],
        ]);

        $dataStartRow = 7;
        $event->sheet->getStyle('A' . $dataStartRow . ':A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('B' . $dataStartRow . ':B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('D' . $dataStartRow . ':D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('J' . $dataStartRow . ':L' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $setTextCell = function ($cellRef, $value) use ($event) {
          $cell = $event->sheet->getCell($cellRef);
          $cell->setValueExplicit($value, DataType::TYPE_STRING);
          $cell->getIgnoredErrors()->setNumberStoredAsText(true);
        };

        $rowIndex = $dataStartRow;
        foreach ($this->exportData as $row) {
          $setTextCell('J' . $rowIndex, number_format((float) $row->jumlah, 0, '.', '.'));
          $setTextCell('K' . $rowIndex, number_format((float) $row->harga, 0, '.', '.'));
          $setTextCell('L' . $rowIndex, number_format((float) $row->total, 0, '.', '.'));
          $rowIndex++;
        }

        foreach ($this->subtotalRows as $rowNumber) {
          $totalRange = 'A' . $rowNumber . ':' . $lastColumn . $rowNumber;

          $event->sheet->getStyle($totalRange)->applyFromArray([
            'font' => [
              'bold' => true,
              'color' => ['argb' => 'FF1E40AF'],
            ],
            'fill' => [
              'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
              'startColor' => ['argb' => 'FFDBEAFE'],
            ],
          ]);

          $event->sheet->getStyle($totalRange)->getBorders()->getTop()
            ->setBorderStyle(Border::BORDER_MEDIUM)
            ->getColor()->setARGB('FF3B82F6');

          $event->sheet->getStyle($totalRange)->getBorders()->getBottom()
            ->setBorderStyle(Border::BORDER_MEDIUM)
            ->getColor()->setARGB('FF3B82F6');
        }
      },
    ];
  }
}
