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

class LaporanInsentifSTExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
    $query = DB::table('v_rep_insentif_all as k')
      ->where('k.kode_cabang', $this->cabang['kode'])
      ->select([
        'k.kode_spk',
        'k.no_polisi',
        'k.kode_estimasi',
        'k.nama_asuransi',
        'k.tgl_estimasi',
        'k.insentif_s',
        'k.insentif_t',
        'k.wm_s',
        'k.marketing_s',
        'k.kabeng_s',
        'k.sa_s',
        'k.wm_t',
        'k.marketing_t',
        'k.kabeng_t',
        'k.sa_t',
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
      ['Laporan Insentif S & T'],
      ['Periode : ' . $this->periode],
      [''],
      [
        'No',
        'No. SPK',
        'No. Polisi',
        'No. Estimasi',
        'Nama Asuransi',
        'Tanggal Estimasi',
        'Insentif S',
        '',
        '',
        '',
        '',
        'Insentif T',
        '',
        '',
        '',
      ],
      [
        '',
        '',
        '',
        '',
        '',
        '',
        'Nilai',
        'WM',
        'Marketing',
        'Kabeng',
        'SA',
        'Nilai',
        'WM',
        'Marketing',
        'Kabeng',
        'SA',
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
      $row->nama_asuransi,
      $row->tgl_estimasi ? date("d/m/Y", strtotime($row->tgl_estimasi)) : '',
      number_format($row->insentif_s, 0, ',', '.'),
      number_format($row->wm_s, 0, ',', '.'),
      number_format($row->marketing_s, 0, ',', '.'),
      number_format($row->kabeng_s, 0, ',', '.'),
      number_format($row->sa_s, 0, ',', '.'),
      number_format($row->insentif_t, 0, ',', '.'),
      number_format($row->wm_t, 0, ',', '.'),
      number_format($row->marketing_t, 0, ',', '.'),
      number_format($row->kabeng_t, 0, ',', '.'),
      number_format($row->sa_t, 0, ',', '.'),
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
        $event->sheet->mergeCells('A1:P1');
        $event->sheet->mergeCells('A2:P2');
        $event->sheet->mergeCells('A3:P3');

        // Merge cells untuk kolom yang tidak memiliki sub-kategori (row 5 ke 6)
        $event->sheet->mergeCells('A5:A6');
        $event->sheet->mergeCells('B5:B6');
        $event->sheet->mergeCells('C5:C6');
        $event->sheet->mergeCells('D5:D6');
        $event->sheet->mergeCells('E5:E6');
        $event->sheet->mergeCells('F5:F6');

        // Merge cells untuk kategori "Insentif S"
        $event->sheet->mergeCells('G5:K5');

        // Merge cells untuk kategori "Insentif T"
        $event->sheet->mergeCells('L5:P5');

        $highestRow = $event->sheet->getHighestRow();

        // Apply borders ke semua data
        $event->sheet->getStyle('A5:P' . $highestRow)->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        // Alignment untuk kolom tertentu
        $event->sheet->getStyle('A7:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('C7:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('F7:F' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('G7:P' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      },
    ];
  }
}
