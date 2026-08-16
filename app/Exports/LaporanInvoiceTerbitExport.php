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
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class LaporanInvoiceTerbitExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents, WithCustomValueBinder
{
  protected $params;
  protected $cabang;
  protected $periode;
  protected $rowNumber = 0;
  protected $data;
  protected $jenisReport;

  public function __construct(array $params, $cabang, $periode)
  {
    $this->params = $params;
    $this->cabang = $cabang;
    $this->periode = $periode;
    $this->jenisReport = $params['jenis_report'] ?: 'Rekap';
  }

  /**
   * Tentukan jenis identitas berdasarkan jenis pelanggan.
   * Asuransi -> NPWP, selain itu (Perorangan) -> KTP.
   */
  private function resolveJenisIdentitas($jenisPelanggan): string
  {
    return strtolower(trim((string) $jenisPelanggan)) === 'asuransi' ? 'NPWP' : 'KTP';
  }

  // public function collection()
  // {
  //   $tahun = $this->params['tahun'] ?: date('Y');
  //   $bulan = $this->params['bulan'] ?: date('m');

  //   $query = DB::table('v_rpt_invoice_terbit')
  //     ->where('kode_cabang', $this->cabang['kode'])
  //     ->whereYear('tgl_invoice', $tahun)
  //     ->whereMonth('tgl_invoice', $bulan);
  public function collection()
  {
    $tglAwal = $this->params['tgl_awal'] ?: date('d/m/Y');
    $tglAkhir = $this->params['tgl_akhir'] ?: date('d/m/Y');

    $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', $tglAwal)->startOfDay();
    $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', $tglAkhir)->endOfDay();

    $query = DB::table('v_rpt_invoice_terbit')
      ->where('kode_cabang', $this->cabang['kode'])
      ->whereBetween('tgl_invoice', [$startDate, $endDate]);

    if ($this->jenisReport === 'Rekap') {
      $this->data = $query
        ->select([
          'nama_pelanggan',
          DB::raw('COUNT(id) as unit'),
          DB::raw('SUM(jasa) as jasa'),
          DB::raw('SUM(bahan) as bahan'),
          DB::raw('SUM(sparepart) as sparepart'),
          DB::raw('SUM(ppn) as ppn'),
          DB::raw('SUM(total_lain) as total_lain'),
          DB::raw('SUM(total_invoice) as total_invoice'),
          DB::raw('SUM(total_or) as total_or'),
          DB::raw('SUM(tagihan) as tagihan'),
        ])
        ->groupBy('nama_pelanggan')
        ->orderBy('nama_pelanggan', 'asc')
        ->get();
    } else {
      $this->data = $query
        ->select([
          'no_invoice',
          'kode_spk',
          'no_polisi',
          'nama_pelanggan',
          'jenis_identitas',
          'no_identitas',
          'jasa',
          'bahan',
          'sparepart',
          'ppn',
          'total_lain',
          'total_invoice',
          'total_or',
          'tagihan',
        ])
        ->orderBy('nama_pelanggan', 'asc')
        ->orderBy('no_invoice', 'asc')
        ->get();
    }

    return $this->data;
  }

  public function headings(): array
  {
    if ($this->jenisReport === 'Rekap') {
      return [
        [$this->cabang['nama']],
        ['Laporan Invoice Terbit - Rekap'],
        ['Periode : ' . $this->periode],
        [''],
        ['No', 'Nama Asuransi', 'Unit', 'Jasa', 'Bahan', 'Sparepart', 'PPN', 'Total Lain', 'Total Invoice', 'Total OR', 'Tagihan'],
      ];
    }

    return [
      [$this->cabang['nama']],
      ['Laporan Invoice Terbit - Rinci'],
      ['Periode : ' . $this->periode],
      [''],
      [
        'No',
        'No Invoice',
        'No SPK',
        'No Polisi',
        'Nama Asuransi',
        'NPWP/KTP',
        'Jasa',
        'Bahan',
        'Sparepart',
        'PPN',
        'Total Lain',
        'Total Invoice',
        'Total OR',
        'Tagihan',
      ],
    ];
  }

  public function map($row): array
  {
    $this->rowNumber++;

    if ($this->jenisReport === 'Rekap') {
      return [
        $this->rowNumber,
        $row->nama_pelanggan,
        number_format($row->unit, 0, '.', '.'),
        number_format($row->jasa, 0, '.', '.'),
        number_format($row->bahan, 0, '.', '.'),
        number_format($row->sparepart, 0, '.', '.'),
        number_format($row->ppn, 0, '.', '.'),
        number_format($row->total_lain, 0, '.', '.'),
        number_format($row->total_invoice, 0, '.', '.'),
        number_format($row->total_or, 0, '.', '.'),
        number_format($row->tagihan, 0, '.', '.'),
      ];
    }

    return [
      $this->rowNumber,
      $row->no_invoice,
      $row->kode_spk,
      $row->no_polisi,
      $row->nama_pelanggan,
      $row->no_identitas,
      number_format($row->jasa, 0, '.', '.'),
      number_format($row->bahan, 0, '.', '.'),
      number_format($row->sparepart, 0, '.', '.'),
      number_format($row->ppn, 0, '.', '.'),
      number_format($row->total_lain, 0, '.', '.'),
      number_format($row->total_invoice, 0, '.', '.'),
      number_format($row->total_or, 0, '.', '.'),
      number_format($row->tagihan, 0, '.', '.'),
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
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
      ],
    ];
  }

  public function bindValue(Cell $cell, $value)
  {
    $col = $cell->getColumn();
    $numericCols = $this->jenisReport === 'Rekap'
      ? ['C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K']
      : ['G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];

    if (in_array($col, $numericCols)) {
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
        $lastColumn = $this->jenisReport === 'Rekap' ? 'K' : 'N';
        $headerRow = 5;

        $event->sheet->mergeCells('A1:' . $lastColumn . '1');
        $event->sheet->mergeCells('A2:' . $lastColumn . '2');
        $event->sheet->mergeCells('A3:' . $lastColumn . '3');
        $event->sheet->mergeCells('A4:' . $lastColumn . '4');

        $highestRow = $event->sheet->getHighestRow();

        $event->sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $highestRow)->applyFromArray([
          'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $dataStartRow = $headerRow + 1;
        $event->sheet->getStyle('A' . $dataStartRow . ':A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        if ($this->data->count() > 0) {
          $totalRow = $highestRow + 1;

          if ($this->data->count() > 0) {
            $totalRow = $highestRow + 1;

            if ($this->jenisReport === 'Rekap') {
              $totalUnit = $this->data->sum('unit');
              $totalJasa = $this->data->sum('jasa');
              $totalBahan = $this->data->sum('bahan');
              $totalSparepart = $this->data->sum('sparepart');
              $totalPpn = $this->data->sum('ppn');
              $totalLain = $this->data->sum('total_lain');
              $totalInvoice = $this->data->sum('total_invoice');
              $totalOr = $this->data->sum('total_or');
              $totalTagihan = $this->data->sum('tagihan');

              $event->sheet->setCellValue('B' . $totalRow, 'Grand Total');
              $event->sheet->setCellValue('C' . $totalRow, number_format($totalUnit, 0, '.', '.'));
              $event->sheet->setCellValue('D' . $totalRow, number_format($totalJasa, 0, '.', '.'));
              $event->sheet->setCellValue('E' . $totalRow, number_format($totalBahan, 0, '.', '.'));
              $event->sheet->setCellValue('F' . $totalRow, number_format($totalSparepart, 0, '.', '.'));
              $event->sheet->setCellValue('G' . $totalRow, number_format($totalPpn, 0, '.', '.'));
              $event->sheet->setCellValue('H' . $totalRow, number_format($totalLain, 0, '.', '.'));
              $event->sheet->setCellValue('I' . $totalRow, number_format($totalInvoice, 0, '.', '.'));
              $event->sheet->setCellValue('J' . $totalRow, number_format($totalOr, 0, '.', '.'));
              $event->sheet->setCellValue('K' . $totalRow, number_format($totalTagihan, 0, '.', '.'));
            } else {
              $totalJasa = $this->data->sum('jasa');
              $totalBahan = $this->data->sum('bahan');
              $totalSparepart = $this->data->sum('sparepart');
              $totalPpn = $this->data->sum('ppn');
              $totalLain = $this->data->sum('total_lain');
              $totalInvoice = $this->data->sum('total_invoice');
              $totalOr = $this->data->sum('total_or');
              $totalTagihan = $this->data->sum('tagihan');

              $event->sheet->setCellValue('F' . $totalRow, 'Grand Total');
              $event->sheet->setCellValue('G' . $totalRow, number_format($totalJasa, 0, '.', '.'));
              $event->sheet->setCellValue('H' . $totalRow, number_format($totalBahan, 0, '.', '.'));
              $event->sheet->setCellValue('I' . $totalRow, number_format($totalSparepart, 0, '.', '.'));
              $event->sheet->setCellValue('J' . $totalRow, number_format($totalPpn, 0, '.', '.'));
              $event->sheet->setCellValue('K' . $totalRow, number_format($totalLain, 0, '.', '.'));
              $event->sheet->setCellValue('L' . $totalRow, number_format($totalInvoice, 0, '.', '.'));
              $event->sheet->setCellValue('M' . $totalRow, number_format($totalOr, 0, '.', '.'));
              $event->sheet->setCellValue('N' . $totalRow, number_format($totalTagihan, 0, '.', '.'));
            }
          } else {
            $totalOr = $this->data->sum('total_or');
            $totalFree = $this->data->sum('free');

            $event->sheet->setCellValue('F' . $totalRow, 'Grand Total');
            $event->sheet->setCellValue('M' . $totalRow, number_format($totalOr, 0, '.', '.'));
            $event->sheet->setCellValue('N' . $totalRow, number_format($totalFree, 0, '.', '.'));
          }

          $totalRange = 'A' . $totalRow . ':' . $lastColumn . $totalRow;
          $event->sheet->getStyle($totalRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF1E40AF']],
            'fill' => [
              'fillType' => Fill::FILL_SOLID,
              'startColor' => ['argb' => 'FFDBEAFE'],
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
          ]);

          $event->sheet->getStyle($totalRange)->getBorders()->getTop()
            ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setARGB('FF3B82F6');
          $event->sheet->getStyle($totalRange)->getBorders()->getBottom()
            ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setARGB('FF3B82F6');
        }
      },
    ];
  }
}
