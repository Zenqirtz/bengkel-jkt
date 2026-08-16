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

class LaporanUnitRawatJalanExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
    $query = DB::table('t_spk_master as k')
      ->leftJoin('m_tipe_kendaraan as b', function ($j) {
        $j->on('b.kode_tipe', '=', 'k.kode_tipe')
          ->on('b.kode_merek', '=', 'k.kode_merek');
      })
      ->leftJoin('m_merek_kendaraan as mk', 'mk.kode_merek', '=', 'k.kode_merek')
      ->leftJoin('m_pelanggan_hdr as c', function ($j) {
        $j->on('c.kode_pelanggan', '=', 'k.kode_pelanggan')
          ->on('c.kode_cabang', '=', 'k.kode_cabang');
      })
      ->leftJoin('parameter as d', function ($j) {
        $j->on('d.kode', '=', 'k.kode_status_spk')
          ->where('d.nama_tabel', '=', 'STATUS_SPK');
      })
      ->leftJoin('parameter as e', function ($j) {
        $j->on('e.kode', '=', 'k.status_spk')
          ->where('e.nama_tabel', '=', 'STATUS_SPK_KET');
      })
      ->where('k.kode_cabang', $this->cabang['kode'])
      ->where('k.ada_rawat_jalan', '1')
      ->select([
        'k.kode_spk',
        'k.tgl_masuk',
        'k.no_polisi',
        'mk.nama_merek',
        'b.nama_tipe',
        'k.pemilik',
        'c.nama_pelanggan',
        'k.no_polis',
        'k.kode_claim',
        'k.tgl_rawat_jalan1',
        'k.tgl_rawat_jalan2',
        'd.keterangan as status_spk',
        'e.keterangan as keterangan',
      ])
      ->orderBy('k.tgl_masuk', 'asc');

    $p = $this->params;
    if (!empty($p['tanggal'])) {
      try {
        $query->whereDate(
          'k.tgl_masuk',
          '<=',
          Carbon::createFromFormat('d/m/Y', $p['tanggal'])->format('Y-m-d')
        );
      } catch (\Exception $e) {
      }
    }

    return $query;
  }

  public function headings(): array
  {
    return [
      [$this->cabang['nama']],
      ['Laporan Unit Rawat Jalan'],
      ['Periode : ' . $this->periode],
      [''],
      [
        'No',
        'No. SPK',
        'Tanggal Masuk',
        'Tanggal Rawat Jalan',
        'Tanggal Selesai',
        'No. Polisi',
        'Merek / Tipe',
        'Pemilik',
        'Nama Asuransi',
        'No. Polis',
        'No. Klaim',
      ],
    ];
  }

  public function map($row): array
  {
    $this->rowNumber++;
    return [
      $this->rowNumber,
      $row->kode_spk,
      $row->tgl_masuk ? date('d/m/Y', strtotime($row->tgl_masuk)) : '',
      $row->tgl_rawat_jalan1 ? date('d/m/Y', strtotime($row->tgl_rawat_jalan1)) : '',
      $row->tgl_rawat_jalan2 ? date('d/m/Y', strtotime($row->tgl_rawat_jalan2)) : '',
      $row->no_polisi,
      trim(($row->nama_merek ?? '') . ' ' . ($row->nama_tipe ?? '')),
      $row->pemilik,
      $row->nama_pelanggan,
      $row->no_polis,
      $row->kode_claim,
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
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
      ],
    ];
  }

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $event->sheet->mergeCells('A1:K1');
        $event->sheet->mergeCells('A2:K2');
        $event->sheet->mergeCells('A3:K3');

        $highestRow = $event->sheet->getHighestRow();
        $event->sheet->getStyle('A5:K' . $highestRow)->applyFromArray([
          'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $event->sheet->getStyle('A6:A' . $highestRow)
          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      },
    ];
  }
}
