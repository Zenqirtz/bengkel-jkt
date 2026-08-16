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
use Carbon\Carbon;

class LaporanInsentifMarketingExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
  protected $params;
  protected $cabang;
  protected $periode;
  protected $namaMarketing;
  protected $rowNumber = 0;
  protected $data;
  protected $totalEstimasi = 0;

  public function __construct(array $params, $cabang, $periode, $namaMarketing = '-')
  {
    $this->params = $params;
    $this->cabang = $cabang;
    $this->periode = $periode;
    $this->namaMarketing = $namaMarketing;
  }

  public function collection()
  {
    $query = DB::table('v_rep_incentive_marketing as k')
      ->where('k.kode_cabang', $this->cabang['kode'])
      ->select([
        'k.tanggal',
        'k.kode_spk',
        'k.no_polisi',
        'k.tipe_kendaraan',
        'k.nama_pelanggan',
        'k.kode_estimasi',
        'k.total_perbaikan',
        'k.total_sparepart',
        'k.total_lain',
        'k.ppn',
        'k.kode_claim',
        'k.nama_marketing',
      ])
      ->orderBy('k.tanggal', 'asc');

    // Filtering berdasarkan tanggal
    if (!empty($this->params['tanggal_dari']) && !empty($this->params['tanggal_sampai'])) {
      try {
        $tglDari = Carbon::createFromFormat('d/m/Y', $this->params['tanggal_dari'])->format('Y-m-d');
        $tglSampai = Carbon::createFromFormat('d/m/Y', $this->params['tanggal_sampai'])->format('Y-m-d');
        $query->whereBetween('k.tanggal', [$tglDari, $tglSampai]);
      } catch (\Exception $e) {
      }
    }

    $this->data = $query->get();
    return $this->data;
  }

  public function headings(): array
  {
    return [
      [$this->cabang['nama']],
      ['Laporan Insentif Marketing'],
      ['Periode : ' . $this->periode],
      [''],
      ['Nama Marketing : ' . $this->namaMarketing],
      [''],
      [
        'No',
        'Tanggal',
        'No. SPK',
        'No. Polisi',
        'Merek Tipe',
        'Nama Asuransi',
        'No. Estimasi',
        'Total Estimasi',
      ]
    ];
  }

  public function map($row): array
  {
    $this->rowNumber++;

    $totalEstimasi = $row->total_perbaikan + $row->total_sparepart + $row->total_lain + $row->ppn;
    $this->totalEstimasi += $totalEstimasi;

    return [
      $this->rowNumber,
      $row->tanggal ? date("d/m/Y", strtotime($row->tanggal)) : '',
      $row->kode_spk,
      $row->no_polisi,
      $row->tipe_kendaraan,
      $row->nama_pelanggan,
      $row->kode_estimasi,
      $totalEstimasi,
    ];
  }

  public function styles(Worksheet $sheet)
  {
    return [
      1 => [
        'font' => ['bold' => true, 'size' => 14],
      ],
      2 => ['font' => ['bold' => true, 'size' => 12]],
      3 => ['font' => ['bold' => true]],
      5 => ['font' => ['bold' => true]],
      7 => [
        'font' => ['bold' => true],
        'fill' => [
          'fillType' => Fill::FILL_SOLID,
          'startColor' => ['rgb' => 'E7E6E6']
        ],
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
        // Merge cells untuk header
        $event->sheet->mergeCells('A1:H1');
        $event->sheet->mergeCells('A2:H2');
        $event->sheet->mergeCells('A3:H3');
        $event->sheet->mergeCells('A5:H5');

        // Center align untuk header
        $event->sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $event->sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $dataRowStart = 8;
        $dataRowEnd = 7 + $this->data->count();

        // Apply border ke semua data
        $event->sheet->getStyle('A7:H' . $dataRowEnd)->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        // Center align untuk kolom No, Tanggal
        $event->sheet->getStyle('A' . $dataRowStart . ':A' . $dataRowEnd)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('B' . $dataRowStart . ':B' . $dataRowEnd)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Right align untuk kolom Total Estimasi
        $event->sheet->getStyle('H' . $dataRowStart . ':H' . $dataRowEnd)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Format angka dengan pemisah ribuan untuk kolom Total Estimasi
        $event->sheet->getStyle('H' . $dataRowStart . ':H' . $dataRowEnd)->getNumberFormat()->setFormatCode('#,##0');

        // Tambahkan baris Total Insentif
        $totalRow = $dataRowEnd + 1;
        $event->sheet->setCellValue('G' . $totalRow, 'Total Insentif');
        $event->sheet->setCellValue('H' . $totalRow, $this->totalEstimasi);

        // Style untuk baris total
        $event->sheet->getStyle('G' . $totalRow . ':H' . $totalRow)->applyFromArray([
          'font' => ['bold' => true],
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        $event->sheet->getStyle('G' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $event->sheet->getStyle('H' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $event->sheet->getStyle('H' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
      },
    ];
  }
}
