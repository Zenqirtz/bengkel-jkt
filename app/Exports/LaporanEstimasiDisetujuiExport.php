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
use Carbon\Carbon;

class LaporanEstimasiDisetujuiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
    $query = DB::table('v_rep_estimasi_disetujui as k')
      ->where('k.kode_cabang', $this->cabang['kode'])
      ->select([
        'k.kode_spk',
        'k.no_polisi',
        'k.kode_estimasi',
        'k.nama_pelanggan',
        'k.tgl_konsep',
        'k.tgl_estimasi',
        'k.tgl_persetujuan',
        'k.total_perbaikan',
        'k.total_sparepart',
        'k.total_lain',
        'k.total',
        'k.total_perbaikan_s',
        'k.total_sparepart_s',
        'k.total_lain_s',
        'k.total_s',
        'k.total_or_ass',
      ]);

    // Filtering
    if (!empty($this->params['tgl_awal'])) {
      try {
        $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
        $query->whereDate('k.tgl_estimasi', '>=', $startDate);
      } catch (\Exception $e) {
      }
    }

    if (!empty($this->params['tgl_akhir'])) {
      try {
        $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
        $query->whereDate('k.tgl_estimasi', '<=', $endDate);
      } catch (\Exception $e) {
      }
    }

    $this->data = $query->orderBy('k.tgl_estimasi', 'asc')->get();

    return $this->data;
  }

  public function headings(): array
  {
    return [
      [$this->cabang['nama']],
      ['Laporan Estimasi Disetujui'],
      ['Periode : ' . $this->periode],
      [''],
      [
        'No',
        'No. SPK',
        'No. Polisi',
        'No. Estimasi',
        'Nama Asuransi',
        'Tanggal SPK',
        'Tanggal Estimasi',
        'Tanggal Disetujui',
        'Estimasi Belum Ditawar',
        '',
        '',
        '',
        'Estimasi Disetujui',
        '',
        '',
        '',
        'Total OR',
      ],
      [
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        'Jasa',
        'Sparepart',
        'Lain-lain',
        'Total',
        'Jasa',
        'Sparepart',
        'Lain-lain',
        'Total',
        '',
      ]
    ];
  }

  public function map($row): array
  {
    $this->rowNumber++;

    return [
      $this->rowNumber,
      $row->kode_spk,
      $row->no_polisi,
      $row->kode_estimasi,
      $row->nama_pelanggan,
      $row->tgl_konsep ? date("d/m/Y", strtotime($row->tgl_konsep)) : '',
      $row->tgl_estimasi ? date("d/m/Y", strtotime($row->tgl_estimasi)) : '',
      $row->tgl_persetujuan ? date("d/m/Y", strtotime($row->tgl_persetujuan)) : '',
      number_format($row->total_perbaikan, 0, ',', '.'),
      number_format($row->total_sparepart, 0, ',', '.'),
      number_format($row->total_lain, 0, ',', '.'),
      number_format($row->total, 0, ',', '.'),
      number_format($row->total_perbaikan_s, 0, ',', '.'),
      number_format($row->total_sparepart_s, 0, ',', '.'),
      number_format($row->total_lain_s, 0, ',', '.'),
      number_format($row->total_s, 0, ',', '.'),
      number_format($row->total_or_ass, 0, ',', '.'),
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

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        // Merge cells untuk header
        $event->sheet->mergeCells('A1:Q1');
        $event->sheet->mergeCells('A2:Q2');
        $event->sheet->mergeCells('A3:Q3');

        // Merge cells untuk kolom yang tidak memiliki sub-kategori (row 5 ke 6)
        $event->sheet->mergeCells('A5:A6');
        $event->sheet->mergeCells('B5:B6');
        $event->sheet->mergeCells('C5:C6');
        $event->sheet->mergeCells('D5:D6');
        $event->sheet->mergeCells('E5:E6');
        $event->sheet->mergeCells('F5:F6');
        $event->sheet->mergeCells('G5:G6');
        $event->sheet->mergeCells('H5:H6');
        $event->sheet->mergeCells('Q5:Q6');

        // Merge cells untuk kategori "Estimasi Belum Ditawar"
        $event->sheet->mergeCells('I5:L5');

        // Merge cells untuk kategori "Estimasi Disetujui"
        $event->sheet->mergeCells('M5:P5');

        $highestRow = $event->sheet->getHighestRow();

        // Apply borders ke semua data
        $event->sheet->getStyle('A5:Q' . $highestRow)->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        // Alignment untuk kolom tertentu
        $event->sheet->getStyle('A7:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('C7:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('F7:H' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('I7:Q' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      },
    ];
  }
}
