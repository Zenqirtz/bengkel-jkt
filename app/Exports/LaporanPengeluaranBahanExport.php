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

class LaporanPengeluaranBahanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
  protected $params;
  protected $cabang;
  protected $periode;
  protected $rowNumber = 0;
  protected $data;
  // protected $currentBahan = null;
  protected $currentPengeluaran = null;
  protected $subtotal = [
    'qty' => 0,
    'harga_lama' => 0,
    'harga' => 0,
    'jumlah' => 0,
    'satuan' => ''
  ];
  protected $exportData = [];
  protected $subtotalRows = []; // Menyimpan nomor baris subtotal

  public function __construct(array $params, $cabang, $periode)
  {
    $this->params = $params;
    $this->cabang = $cabang;
    $this->periode = $periode;
  }

  public function collection()
  {
    $query = DB::table('v_rep_pengeluaran_bahan')
      ->where('kode_cabang', $this->cabang['kode'])
      ->select([
        'kode_pengeluaran',
        'tgl_pengeluaran',
        'no_bon',
        'kode_spk',
        'no_polisi',
        'posisi_pekerjaan',
        'point_panel',
        'kode_barang',
        'nama_bahan',
        'qty',
        'satuan',
        'harga_lama',
        'harga',
        'jumlah'
      ]);

    // Filter tanggal
    if (!empty($this->params['tgl_awal'])) {
      try {
        $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
        $query->whereDate('tgl_pengeluaran', '>=', $startDate);
      } catch (\Exception $e) {
        // Handle error
      }
    }

    if (!empty($this->params['tgl_akhir'])) {
      try {
        $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
        $query->whereDate('tgl_pengeluaran', '<=', $endDate);
      } catch (\Exception $e) {
        // Handle error
      }
    }

    // $this->data = $query
    //   ->orderBy('nama_bahan', 'asc')
    //   ->orderBy('tgl_pengeluaran', 'asc')
    //   ->orderBy('kode_pengeluaran', 'asc')
    //   ->get();
    $this->data = $query
      ->orderBy('tgl_pengeluaran', 'asc')
      ->orderBy('kode_pengeluaran', 'asc')
      ->get();

    // Process data untuk include subtotal
    $processedData = collect();
    $currentRow = 6; // Mulai dari baris 6 (setelah header)

    foreach ($this->data as $row) {
      // Jika nama bahan berubah dan bukan data pertama
      // if ($this->currentBahan !== null && $this->currentBahan !== $row->nama_bahan) {
      if ($this->currentPengeluaran !== null && $this->currentPengeluaran !== $row->kode_pengeluaran) {
        // Harga Lama berisi penjumlahan harga_lama + harga
        $totalHarga = $this->subtotal['harga_lama'] + $this->subtotal['harga'];

        // Tambahkan subtotal row
        $subtotalRow = (object) [
          'kode_pengeluaran' => null,
          'tgl_pengeluaran' => null,
          'no_bon' => null,
          'kode_spk' => null,
          'no_polisi' => null,
          'posisi_pekerjaan' => null,
          'point_panel' => null,
          'kode_barang' => null,
          'nama_bahan' => 'Grand Total',
          'qty' => $this->subtotal['qty'],
          'satuan' => $this->subtotal['satuan'],
          'harga_lama' => $totalHarga,
          'harga' => null,
          'jumlah' => $this->subtotal['jumlah'],
          'is_subtotal' => true
        ];
        $processedData->push($subtotalRow);
        $this->subtotalRows[] = $currentRow; // Simpan nomor baris subtotal
        $currentRow++;

        // Reset subtotal
        $this->subtotal = [
          'qty' => 0,
          'harga_lama' => 0,
          'harga' => 0,
          'jumlah' => 0,
          'satuan' => ''
        ];
      }

      // $this->currentBahan = $row->nama_bahan;
      $this->currentPengeluaran = $row->kode_pengeluaran;

      // Tambah ke subtotal
      $this->subtotal['qty'] += $row->qty;
      $this->subtotal['harga_lama'] += $row->harga_lama;
      $this->subtotal['harga'] += $row->harga;
      $this->subtotal['jumlah'] += $row->jumlah;
      $this->subtotal['satuan'] = $row->satuan ?? '';

      $row->is_subtotal = false;
      $processedData->push($row);
      $currentRow++;
    }

    // Tambahkan subtotal terakhir
    if ($this->data->count() > 0) {
      // Harga Lama berisi penjumlahan harga_lama + harga
      $totalHarga = $this->subtotal['harga_lama'] + $this->subtotal['harga'];

      $subtotalRow = (object) [
        'kode_pengeluaran' => null,
        'tgl_pengeluaran' => null,
        'no_bon' => null,
        'kode_spk' => null,
        'no_polisi' => null,
        'posisi_pekerjaan' => null,
        'point_panel' => null,
        'kode_barang' => null,
        'nama_bahan' => 'Total',
        'qty' => $this->subtotal['qty'],
        'satuan' => $this->subtotal['satuan'],
        'harga_lama' => $totalHarga,
        'harga' => null,
        'jumlah' => $this->subtotal['jumlah'],
        'is_subtotal' => true
      ];
      $processedData->push($subtotalRow);
      $this->subtotalRows[] = $currentRow; // Simpan nomor baris subtotal terakhir
    }

    $this->exportData = $processedData; // ⬅️ TAMBAHAN PENTING

    return $processedData;
  }

  public function headings(): array
  {
    return [
      [$this->cabang['nama']],
      ['Laporan Pengeluaran Bahan'],
      ['Per Tanggal : ' . $this->periode],
      [''],
      [
        'No',
        'No. Pengeluaran',
        'Tanggal',
        'Bon',
        'SPK',
        'No. Polisi',
        'Bagian',
        'Point Panel',
        'Kode Bahan',
        'Nama Bahan',
        'Qty',
        'Satuan',
        'Harga Lama',
        'Harga Baru',
        'Jumlah'
      ]
    ];
  }

  // public function map($row): array
  // {
  //   if (isset($row->is_subtotal) && $row->is_subtotal) {
  //     // Baris subtotal
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
  //       $row->nama_bahan, // "Total"
  //       number_format($row->qty, 2, '.', '.'),
  //       $row->satuan,
  //       number_format($row->harga_lama, 0, '.', '.'), // Total harga_lama + harga
  //       '', // Kosong (merged)
  //       number_format($row->jumlah, 0, '.', '.'),
  //     ];
  //   }

  //   // Baris data normal
  //   $this->rowNumber++;

  //   return [
  //     $this->rowNumber,
  //     $row->kode_pengeluaran,
  //     Carbon::parse($row->tgl_pengeluaran)->format('d/m/Y'),
  //     $row->no_bon ?? '',
  //     $row->kode_spk ?? '',
  //     $row->no_polisi ?? '',
  //     $row->posisi_pekerjaan ?? '',
  //     $row->point_panel ?? '',
  //     $row->kode_barang,
  //     $row->nama_bahan,
  //     number_format($row->qty, 2, '.', '.'),
  //     $row->satuan ?? '',
  //     number_format($row->harga_lama, 0, '.', '.'),
  //     number_format($row->harga, 0, '.', '.'),
  //     number_format($row->jumlah, 0, '.', '.'),
  //   ];
  // }
  public function map($row): array
  {
    if (isset($row->is_subtotal) && $row->is_subtotal) {
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
        $row->nama_bahan,
        number_format($row->qty, 2, '.', '.'),
        $row->satuan,
        number_format($row->harga_lama, 0, '.', '.'),
        '',
        number_format($row->jumlah, 0, '.', '.'),
      ];
    }

    $this->rowNumber++;

    return [
      $this->rowNumber,
      $row->kode_pengeluaran,
      Carbon::parse($row->tgl_pengeluaran)->format('d/m/Y'),
      $row->no_bon ?? '',
      $row->kode_spk ?? '',
      $row->no_polisi ?? '',
      $row->posisi_pekerjaan ?? '',
      $row->point_panel ?? '',
      $row->kode_barang,
      $row->nama_bahan,
      number_format($row->qty, 2, '.', '.'),
      $row->satuan ?? '',
      number_format($row->harga_lama, 0, '.', '.'),
      number_format($row->harga, 0, '.', '.'),
      number_format($row->jumlah, 0, '.', '.'),
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

  // public function registerEvents(): array
  // {
  //   return [
  //     AfterSheet::class => function (AfterSheet $event) {
  //       $lastColumn = 'O'; // Column O for 15 columns

  //       // Merge cells untuk header
  //       $event->sheet->mergeCells('A1:' . $lastColumn . '1');
  //       $event->sheet->mergeCells('A2:' . $lastColumn . '2');
  //       $event->sheet->mergeCells('A3:' . $lastColumn . '3');

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
  //       $dataStartRow = 6;
  //       // Center alignment untuk No dan Tanggal
  //       $event->sheet->getStyle('A' . $dataStartRow . ':A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
  //       $event->sheet->getStyle('C' . $dataStartRow . ':C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
  //       // Right alignment untuk Qty, Harga Lama, Harga Baru, Jumlah
  //       $event->sheet->getStyle('K' . $dataStartRow . ':O' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

  //       // Apply bold style to subtotal rows
  //       foreach ($this->subtotalRows as $rowNumber) {
  //         $event->sheet->getStyle('A' . $rowNumber . ':' . $lastColumn . $rowNumber)->applyFromArray([
  //           'font' => [
  //             'bold' => true
  //           ]
  //         ]);

  //         // Merge cells M dan N untuk subtotal (Harga Lama dan Harga Baru)
  //         $event->sheet->mergeCells('M' . $rowNumber . ':N' . $rowNumber);
  //       }
  //     },
  //   ];
  // }
  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $lastColumn = 'O';

        $event->sheet->mergeCells('A1:' . $lastColumn . '1');
        $event->sheet->mergeCells('A2:' . $lastColumn . '2');
        $event->sheet->mergeCells('A3:' . $lastColumn . '3');

        $highestRow = $event->sheet->getHighestRow();

        $event->sheet->getStyle('A5:' . $lastColumn . $highestRow)->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        $dataStartRow = 6;
        $event->sheet->getStyle('A' . $dataStartRow . ':A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('C' . $dataStartRow . ':C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('K' . $dataStartRow . ':O' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $setTextCell = function ($cellRef, $value) use ($event) {
          $cell = $event->sheet->getCell($cellRef);
          $cell->setValueExplicit($value, DataType::TYPE_STRING);
          $cell->getIgnoredErrors()->setNumberStoredAsText(true);
        };

        $rowIndex = $dataStartRow;
        foreach ($this->exportData as $row) {
          if (isset($row->is_subtotal) && $row->is_subtotal) {
            $setTextCell('K' . $rowIndex, number_format((float) $row->qty, 2, '.', '.'));
            $setTextCell('M' . $rowIndex, number_format((float) $row->harga_lama, 0, '.', '.'));
            $setTextCell('O' . $rowIndex, number_format((float) $row->jumlah, 0, '.', '.'));
          } else {
            $setTextCell('H' . $rowIndex, number_format((float) $row->point_panel, 6, '.', '.'));
            $setTextCell('K' . $rowIndex, number_format((float) $row->qty, 2, '.', '.'));
            $setTextCell('M' . $rowIndex, number_format((float) $row->harga_lama, 0, '.', '.'));
            $setTextCell('N' . $rowIndex, number_format((float) $row->harga, 0, '.', '.'));
            $setTextCell('O' . $rowIndex, number_format((float) $row->jumlah, 0, '.', '.'));
          }
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

          $event->sheet->mergeCells('M' . $rowNumber . ':N' . $rowNumber);
        }
      },
    ];
  }
}
