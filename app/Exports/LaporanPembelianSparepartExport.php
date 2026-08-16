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

class LaporanPembelianSparepartExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
    $query = DB::table('v_rep_pembelian_sparepart')
      ->where('kode_cabang', $this->cabang['kode'])
      ->select([
        'tanggal',
        'kode_input',
        'nama_pemasok',
        'nama_sparepart',
        'qty',
        'harga',
        'jumlah',
        'kode_spk',
        'no_po',
        'merek_tipe',
        'no_polisi'
      ]);

    // Filter supplier
    if (!empty($this->params['supplier'])) {
      $query->where('kode_pemasok', $this->params['supplier']);
    }

    // Filter no SPK
    if (!empty($this->params['no_spk'])) {
      $query->where('kode_spk', 'like', '%' . $this->params['no_spk'] . '%');
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
      ['Laporan Pembelian Sparepart'],
      ['Periode : ' . $this->periode],
      [''],
      [
        'No',
        'Tanggal',
        'No. IG',
        'Nama Supplier',
        'Nama Barang',
        'Qty',
        'Harga Satuan',
        'Jumlah',
        'Total A/P',
        'No. SPK',
        'No. PO',
        'Merek Tipe',
        'No. Polisi'
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
      $row->nama_pemasok,
      $row->nama_sparepart,
      number_format($row->qty, 2, '.', '.'),
      number_format($row->harga, 0, '.', '.'),
      number_format($row->jumlah, 0, '.', '.'),
      number_format($row->jumlah, 0, '.', '.'),
      $row->kode_spk ?? '',
      $row->no_po ?? '',
      $row->merek_tipe ?? '',
      $row->no_polisi ?? '',
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
  //       $event->sheet->getStyle('F' . $dataStartRow . ':F' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
  //       $event->sheet->getStyle('G' . $dataStartRow . ':I' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
  //     },
  //   ];
  // }
  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $lastColumn = 'M';
        $event->sheet->mergeCells('A1:' . $lastColumn . '1');
        $event->sheet->mergeCells('A2:' . $lastColumn . '2');
        $event->sheet->mergeCells('A3:' . $lastColumn . '3');
        $highestRow = $event->sheet->getHighestRow();
        $dataStartRow = 6;
        $event->sheet->getStyle('A5:' . $lastColumn . $highestRow)->applyFromArray([
          'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $event->sheet->getStyle('A' . $dataStartRow . ':A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('B' . $dataStartRow . ':B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('F' . $dataStartRow . ':F' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $event->sheet->getStyle('G' . $dataStartRow . ':I' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $setTextCell = function ($cellRef, $value) use ($event) {
          $cell = $event->sheet->getCell($cellRef);
          $cell->setValueExplicit($value, DataType::TYPE_STRING);
          $cell->getIgnoredErrors()->setNumberStoredAsText(true);
        };

        foreach ($this->data as $i => $row) {
          $excelRow = $dataStartRow + $i;
          $setTextCell('F' . $excelRow, number_format((float) $row->qty, 2, '.', '.'));
          $setTextCell('G' . $excelRow, number_format((float) $row->harga, 0, '.', '.'));
          $setTextCell('H' . $excelRow, number_format((float) $row->jumlah, 0, '.', '.'));
          $setTextCell('I' . $excelRow, number_format((float) $row->jumlah, 0, '.', '.'));
        }

        // Baris Total
        if ($this->data->count() > 0) {
          $totalRow = $highestRow + 1;

          $event->sheet->setCellValue('E' . $totalRow, 'Grand Total');

          $setTextCell('F' . $totalRow, number_format($this->data->sum('qty'), 2, '.', '.'));
          $setTextCell('H' . $totalRow, number_format($this->data->sum('jumlah'), 0, '.', '.'));
          $setTextCell('I' . $totalRow, number_format($this->data->sum('jumlah'), 0, '.', '.'));

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

          $event->sheet->getStyle('F' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $event->sheet->getStyle('H' . $totalRow . ':I' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
      },
    ];
  }
}
