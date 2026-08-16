<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
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

class LaporanEstimasiBelumDibuatExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
  protected $params;
  protected $cabang;
  protected $periode;
  protected $rowNumber = 0;

  public function __construct(array $params, $cabang, $periode)
  {
    $this->params = $params;
    $this->cabang = $cabang;
    $this->periode = $periode;
  }

  public function query()
  {
    $query = DB::table('v_rep_estimasi_belum_dibuat as k')
      ->where('k.kode_cabang', $this->cabang['kode'])
      ->select([
        'k.kode_spk',
        'k.tgl_masuk',
        'k.no_polisi',
        'k.tipe_kendaraan',
        'k.nama_pelanggan',
        'k.tertanggung',
        'k.no_polis',
      ])
      ->orderBy('k.tgl_masuk', 'asc');

    // Filtering berdasarkan tanggal masuk
    if (!empty($this->params['tanggal'])) {
      try {
        $tanggal = Carbon::createFromFormat('d/m/Y', $this->params['tanggal'])->format('Y-m-d');
        $query->whereDate('k.tgl_masuk', '<=', $tanggal);
      } catch (\Exception $e) {
      }
    }

    return $query;
  }

  public function headings(): array
  {
    return [
      [$this->cabang['nama']],
      ['Laporan Estimasi Belum Dibuat'],
      ['Periode : ' . $this->periode],
      [''],
      [
        'No',
        'No. SPK',
        'Tanggal Masuk',
        'No. Polisi',
        'Tipe Kendaraan',
        'Nama Asuransi',
        'Tertanggung',
        'No. Polis',
      ]
    ];
  }

  public function map($row): array
  {
    $this->rowNumber++;

    return [
      $this->rowNumber,
      $row->kode_spk,
      $row->tgl_masuk ? date("d/m/Y", strtotime($row->tgl_masuk)) : '',
      $row->no_polisi,
      $row->tipe_kendaraan,
      $row->nama_pelanggan,
      $row->tertanggung,
      $row->no_polis,
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

        $highestRow = $event->sheet->getHighestRow();

        // Apply border ke semua data
        $event->sheet->getStyle('A5:H' . $highestRow)->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        // Center align untuk kolom No
        $event->sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      },
    ];
  }
}
