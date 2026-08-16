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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class LaporanInsentifKwitansiExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
    $query = DB::table('v_rep_insentif_kwitansi as k')
      ->where('k.kode_cabang', $this->cabang['kode'])
      ->select([
        'k.kode_kwitansi',
        'k.kode_estimasi',
        'k.kode_spk',
        'k.no_polisi',
        'k.merek_tipe',
        'k.nama_pelanggan',
        'k.jasa',
        'k.sparepart',
        'k.tgl_pengiriman',
        'k.tgl_kwitansi',
        'k.hari',
      ])
      ->orderBy('k.tgl_kwitansi', 'asc');

    // Filtering berdasarkan tanggal
    if (!empty($this->params['tanggal_dari']) && !empty($this->params['tanggal_sampai'])) {
      try {
        $tglDari = Carbon::createFromFormat('d/m/Y', $this->params['tanggal_dari'])->format('Y-m-d');
        $tglSampai = Carbon::createFromFormat('d/m/Y', $this->params['tanggal_sampai'])->format('Y-m-d');
        $query->whereBetween('k.tgl_kwitansi', [$tglDari, $tglSampai]);
      } catch (\Exception $e) {
      }
    }

    return $query;
  }

  public function headings(): array
  {
    return [
      [$this->cabang['nama']],
      ['Laporan Insentif Kwitansi'],
      ['Periode : ' . $this->periode],
      [''],
      [
        'No',
        'No. Kwitansi',
        'No. Estimasi',
        'No. SPK',
        'No. Polisi',
        'Merek Tipe',
        'Nama Asuransi',
        'Jasa',
        'Sparepart',
        'Jumlah',
        'Tgl Kirim Estimasi',
        'Tanggal Kwitansi',
        'Hari',
      ]
    ];
  }

  public function map($row): array
  {
    $this->rowNumber++;

    return [
      $this->rowNumber,
      $row->kode_kwitansi,
      $row->kode_estimasi,
      $row->kode_spk,
      $row->no_polisi,
      $row->merek_tipe,
      $row->nama_pelanggan,
      $row->jasa,
      $row->sparepart,
      $row->jasa + $row->sparepart,
      $row->tgl_pengiriman ? date("d/m/Y", strtotime($row->tgl_pengiriman)) : '',
      $row->tgl_kwitansi ? date("d/m/Y", strtotime($row->tgl_kwitansi)) : '',
      $row->hari,
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
        $event->sheet->mergeCells('A1:M1');
        $event->sheet->mergeCells('A2:M2');
        $event->sheet->mergeCells('A3:M3');

        $highestRow = $event->sheet->getHighestRow();

        // Apply border ke semua data
        $event->sheet->getStyle('A5:M' . $highestRow)->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        // Center align untuk kolom No, Hari
        $event->sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('M6:M' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Right align untuk kolom angka (Jasa, Sparepart, Jumlah)
        $event->sheet->getStyle('H6:J' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Format angka dengan pemisah ribuan
        $event->sheet->getStyle('H6:J' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');
      },
    ];
  }
}
