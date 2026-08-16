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
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class LaporanPembelianBahanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
    $query = DB::table('v_rep_pembelian_bahan')
      ->where('kode_cabang', $this->cabang['kode'])
      ->select([
        'tanggal',
        'kode_input',
        'no_po',
        'nama_pemasok',
        'kode_bahan',
        'nama_bahan',
        'qty',
        'kode_satuan',
        'harga',
        'jumlah_sebelum',
        'ppn',
        'jumlah'
      ]);

    // Filter supplier
    if (!empty($this->params['supplier'])) {
      $query->where('kode_pemasok', $this->params['supplier']);
    }

    // Filter tanggal
    if (!empty($this->params['tgl_awal'])) {
      try {
        $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
        $query->whereDate('tanggal', '>=', $startDate);
      } catch (\Exception $e) {
        // Handle error
      }
    }

    if (!empty($this->params['tgl_akhir'])) {
      try {
        $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
        $query->whereDate('tanggal', '<=', $endDate);
      } catch (\Exception $e) {
        // Handle error
      }
    }

    $this->data = $query->orderBy('tanggal', 'asc')->orderBy('kode_input', 'asc')->get();

    return $this->data;
  }

  public function headings(): array
  {
    return [
      [$this->cabang['nama']],
      ['Laporan Pembelian Bahan'],
      ['Periode : ' . $this->periode],
      [''],
      [
        'No',
        'Tanggal',
        'No Input Gudang',
        'No. PO',
        'Nama Pemasok',
        'Kode',
        'Nama Bahan',
        'Unit',
        'Satuan',
        'Harga',
        'Jumlah',
        'PPN',
        'Total'
      ]
    ];
  }

  public function map($row): array
  {
    $this->rowNumber++;

    return [
      $this->rowNumber,
      Carbon::parse($row->tanggal)->format('d/m/Y'),
      $row->kode_input,
      $row->no_po ?? '',
      $row->nama_pemasok,
      $row->kode_bahan,
      $row->nama_bahan,
      // number_format($row->qty, 2, '.', '.'),
      // $row->kode_satuan,
      // number_format($row->harga, 0, '.', '.'),
      // number_format($row->jumlah_sebelum, 0, '.', '.'),
      // number_format($row->ppn, 0, '.', '.'),
      // number_format($row->jumlah, 0, '.', '.'),
      (float) $row->qty,
      $row->kode_satuan,
      (float) $row->harga,
      (float) $row->jumlah_sebelum,
      (float) $row->ppn,
      (float) $row->jumlah,
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
          'allBorders' => ['borderStyle' => Border::BORDER_THIN],
        ],
      ],
    ];
  }

  // public function registerEvents(): array
  // {
  //   return [
  //     AfterSheet::class => function (AfterSheet $event) {
  //       $lastColumn = 'M';

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
  //       $event->sheet->getStyle('A' . $dataStartRow . ':A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
  //       $event->sheet->getStyle('B' . $dataStartRow . ':B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
  //       $event->sheet->getStyle('H' . $dataStartRow . ':H' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
  //       $event->sheet->getStyle('I' . $dataStartRow . ':I' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
  //       $event->sheet->getStyle('J' . $dataStartRow . ':M' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
  //     },
  //   ];
  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $lastColumn = 'M';

        // Merge cells untuk header
        $event->sheet->mergeCells('A1:' . $lastColumn . '1');
        $event->sheet->mergeCells('A2:' . $lastColumn . '2');
        $event->sheet->mergeCells('A3:' . $lastColumn . '3');

        $highestRow = $event->sheet->getHighestRow();
        $dataStartRow = 6;

        // Apply borders ke semua data
        $event->sheet->getStyle('A5:' . $lastColumn . $highestRow)->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        // Alignment
        $event->sheet->getStyle('A' . $dataStartRow . ':A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('B' . $dataStartRow . ':B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('H' . $dataStartRow . ':H' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $event->sheet->getStyle('I' . $dataStartRow . ':I' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('J' . $dataStartRow . ':M' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Helper: tulis angka sebagai teks dengan titik, dan matikan warning "Number Stored as Text"
        $setTextCell = function ($cellRef, $value) use ($event) {
          $cell = $event->sheet->getCell($cellRef);
          $cell->setValueExplicit($value, DataType::TYPE_STRING);
          $cell->getIgnoredErrors()->setNumberStoredAsText(true);
        };

        foreach ($this->data as $i => $row) {
          $excelRow = $dataStartRow + $i;

          $setTextCell('H' . $excelRow, number_format((float) $row->qty, 2, '.', '.'));
          $setTextCell('J' . $excelRow, number_format((float) $row->harga, 0, '.', '.'));
          $setTextCell('K' . $excelRow, number_format((float) $row->jumlah_sebelum, 0, '.', '.'));
          $setTextCell('L' . $excelRow, number_format((float) $row->ppn, 0, '.', '.'));
          $setTextCell('M' . $excelRow, number_format((float) $row->jumlah, 0, '.', '.'));
        }

        // Tambah baris Total (style disamakan dengan LaporanGudangBahanExport)
        if ($this->data->count() > 0) {
          $totalRow = $highestRow + 1;

          $event->sheet->setCellValue('G' . $totalRow, 'Grand Total');

          $setTextCell('H' . $totalRow, number_format($this->data->sum('qty'), 2, '.', '.'));
          $setTextCell('K' . $totalRow, number_format($this->data->sum('jumlah_sebelum'), 0, '.', '.'));
          $setTextCell('L' . $totalRow, number_format($this->data->sum('ppn'), 0, '.', '.'));
          $setTextCell('M' . $totalRow, number_format($this->data->sum('jumlah'), 0, '.', '.'));

          $totalRange = 'A' . $totalRow . ':' . $lastColumn . $totalRow;

          $event->sheet->getStyle($totalRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFDBEAFE');

          $event->sheet->getStyle($totalRange)->getFont()
            ->setBold(true)
            ->getColor()->setARGB('FF1E40AF');

          $event->sheet->getStyle($totalRange)->getBorders()->getOutline()
            ->setBorderStyle(Border::BORDER_MEDIUM)
            ->getColor()->setARGB('FF3B82F6');

          $event->sheet->getStyle($totalRange)->getBorders()->getInside()
            ->setBorderStyle(Border::BORDER_THIN);
          $event->sheet->getStyle('H' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $event->sheet->getStyle('K' . $totalRow . ':M' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
      },
    ];
  }
}
