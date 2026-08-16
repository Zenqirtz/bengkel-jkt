<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents; // Tambahan untuk event merging
use Maatwebsite\Excel\Events\AfterSheet;   // Tambahan untuk event merging
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class StockOpnameExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
  protected $params;
  protected $cabang;
  protected $periode;
  protected $rowNumber = 0; // Untuk nomor urut manual

  // Terima parameter tambahan untuk Header Laporan
  public function __construct(array $params, $cabang, $periode)
  {
    $this->params = $params;
    $this->cabang = $cabang;
    $this->periode = $periode;
  }

  public function query()
  {
    $query = '';
    if ($this->params['tipe'] == "P") {
      // Query sama seperti sebelumnya
      $query = DB::table('t_saldo_bahan as a')
        ->leftJoin('m_bahan as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_bahan', '=', 'a.kode_bahan'); // syarat di JOIN
        })
        ->leftJoin('parameter as c', function ($join) {
          $join->on('c.kode', '=', 'b.kode_satuan')
            ->where('c.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
        })
        ->where('a.bulan', (int) $this->params['bulan'])
        ->where('a.tahun', $this->params['tahun'])
        ->where('a.kode_group_bahan', '00001')
        ->where('a.kode_cabang', $this->cabang['kode'])
        // saldo akhir = 0 tidak perlu ditampilkan
        ->where(function ($q) {
          $q->where('a.unit_akhir', '!=', 0)
            ->orWhere('a.harga_akhir', '!=', 0)
            ->orWhere('a.jumlah_akhir', '!=', 0);
        })
        ->select([
          'a.id',
          'b.nama_bahan',
          'c.keterangan as satuan',
          'a.unit_awal',
          'a.harga_awal',
          'a.jumlah_awal',
          'a.unit_tambah',
          'a.harga_tambah',
          'a.jumlah_tambah',
          'a.unit_kurang',
          'a.harga_kurang',
          'a.jumlah_kurang',
          'a.unit_retur',
          'a.harga_retur',
          'a.jumlah_retur',
          'a.unit_adjust',
          'a.harga_adjust',
          'a.jumlah_adjust',
          'a.unit_akhir',
          'a.harga_akhir',
          'a.jumlah_akhir',
          'a.unit_so',
          'a.harga_so',
          'a.jumlah_so',
          'a.unit_selisih',
          'a.jumlah_selisih',
        ])
        ->orderBy('b.nama_bahan', 'asc');
    } elseif ($this->params['tipe'] == "C") {
      // Query sama seperti sebelumnya
      $query = DB::table('t_saldo_bahan as a')
        ->leftJoin('m_bahan as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_bahan', '=', 'a.kode_bahan'); // syarat di JOIN
        })
        ->leftJoin('parameter as c', function ($join) {
          $join->on('c.kode', '=', 'b.kode_satuan')
            ->where('c.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
        })
        ->where('a.bulan', (int) $this->params['bulan'])
        ->where('a.tahun', $this->params['tahun'])
        ->where('a.kode_group_bahan', '00002')
        ->where('a.kode_cabang', $this->cabang['kode'])
        // saldo akhir = 0 tidak perlu ditampilkan
        ->where(function ($q) {
          $q->where('a.unit_akhir', '!=', 0)
            ->orWhere('a.harga_akhir', '!=', 0)
            ->orWhere('a.jumlah_akhir', '!=', 0);
        })
        ->select([
          'a.id',
          'b.nama_bahan',
          'c.keterangan as satuan',
          'a.unit_awal',
          'a.harga_awal',
          'a.jumlah_awal',
          'a.unit_tambah',
          'a.harga_tambah',
          'a.jumlah_tambah',
          'a.unit_kurang',
          'a.harga_kurang',
          'a.jumlah_kurang',
          'a.unit_retur',
          'a.harga_retur',
          'a.jumlah_retur',
          'a.unit_adjust',
          'a.harga_adjust',
          'a.jumlah_adjust',
          'a.unit_akhir',
          'a.harga_akhir',
          'a.jumlah_akhir',
          'a.unit_so',
          'a.harga_so',
          'a.jumlah_so',
          'a.unit_selisih',
          'a.jumlah_selisih',
        ])
        ->orderBy('b.nama_bahan', 'asc');
    } elseif ($this->params['tipe'] == "S") {
      $query = DB::table('t_saldo_sparepart as a')
        ->leftJoin('m_sparepart as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_sparepart', '=', 'a.kode_sparepart'); // syarat di JOIN
        })
        ->leftJoin('parameter as c', function ($join) {
          $join->on('c.kode', '=', 'b.kode_satuan')
            ->where('c.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
        })
        ->leftJoin('m_tipe_kendaraan as d', function ($join) {
          $join->on('d.kode_merek', '=', 'a.kode_merek')
            ->on('d.kode_tipe', '=', 'a.kode_tipe'); // syarat di JOIN
        })
        ->leftJoin('m_merek_kendaraan as e', 'e.kode_merek', '=', 'a.kode_merek')
        ->where('a.periode_bulan', (int) $this->params['bulan'])
        ->where('a.periode_tahun', $this->params['tahun'])
        ->where('a.kode_cabang', $this->cabang['kode'])
        // saldo akhir = 0 tidak perlu ditampilkan
        ->where(function ($q) {
          $q->where('a.unit_akhir', '!=', 0)
            ->orWhere('a.harga_akhir', '!=', 0)
            ->orWhere('a.jumlah_akhir', '!=', 0);
        })
        ->select([
          'a.id',
          'a.bulan',
          'a.tahun',
          'e.nama_merek',
          'd.nama_tipe',
          'a.kode_input',
          'b.nama_sparepart',
          'c.keterangan as satuan',
          'a.unit_awal',
          'a.harga_awal',
          'a.jumlah_awal',
          'a.unit_tambah',
          'a.harga_tambah',
          'a.jumlah_tambah',
          'a.unit_kurang',
          'a.harga_kurang',
          'a.jumlah_kurang',
          'a.unit_retur',
          'a.harga_retur',
          'a.jumlah_retur',
          'a.unit_adjust',
          'a.harga_adjust',
          'a.jumlah_adjust',
          'a.unit_akhir',
          'a.harga_akhir',
          'a.jumlah_akhir',
          'a.unit_so',
          'a.harga_so',
          'a.jumlah_so',
          'a.unit_selisih',
          'a.jumlah_selisih',
        ])
        ->orderBy('b.nama_sparepart', 'asc');
    }

    return $query;
  }

  // Sesuaikan Header dengan layout Excel yang diminta
  public function headings(): array
  {
    if ($this->params['tipe'] == "P") {
      return [
        ['Stock dan Saldo Bahan'],                                // Baris 1: Judul
        ['Cabang : ' . $this->cabang['nama']],         // Baris 2: Nama Bengkel
        ['Periode : ' . $this->periode],                // Baris 3: Periode
        [''],                                           // Baris 4: Kosong (Spacer)
        [                                               // Baris 5: Header Tabel
          'No',
          'Nama Bahan',
          'Satuan',
          'Saldo Awal',
          '',
          '',
          'Penambahan',
          '',
          '',
          'Pengurangan',
          '',
          '',
          'Retur',
          '',
          '',
          'Saldo Akhir',
        ],
        [                                               // Baris 5: Header Tabel
          '',
          '',
          '',
          'Qty',
          'Harga',
          'Jumlah',
          'Qty',
          'Harga',
          'Jumlah',
          'Qty',
          'Harga',
          'Jumlah',
          'Qty',
          'Harga',
          'Jumlah',
          'Qty',
          'Harga',
          'Jumlah',
        ]
      ];
    } elseif ($this->params['tipe'] == "C") {
      return [
        ['Stock dan Saldo Cat'],                                // Baris 1: Judul
        ['Cabang : ' . $this->cabang['nama']],         // Baris 2: Nama Bengkel
        ['Periode : ' . $this->periode],                // Baris 3: Periode
        [''],                                           // Baris 4: Kosong (Spacer)
        [                                               // Baris 5: Header Tabel
          'No',
          'Nama Bahan',
          'Satuan',
          'Saldo Awal',
          '',
          '',
          'Penambahan',
          '',
          '',
          'Pengurangan',
          '',
          '',
          'Retur',
          '',
          '',
          'Saldo Akhir',
        ],
        [                                               // Baris 5: Header Tabel
          '',
          '',
          '',
          'Qty',
          'Harga',
          'Jumlah',
          'Qty',
          'Harga',
          'Jumlah',
          'Qty',
          'Harga',
          'Jumlah',
          'Qty',
          'Harga',
          'Jumlah',
          'Qty',
          'Harga',
          'Jumlah',
        ]
      ];
    } elseif ($this->params['tipe'] == "S") {
      return [
        ['Stock dan Saldo Sparepart'],                                // Baris 1: Judul
        ['Cabang : ' . $this->cabang['nama']],         // Baris 2: Nama Bengkel
        ['Periode : ' . $this->periode],                // Baris 3: Periode
        [''],                                           // Baris 4: Kosong (Spacer)
        [                                               // Baris 5: Header Tabel
          'No',
          'Bulan',
          'Tahun',
          'Merek Kendaraan',
          'Tipe Kendaraan',
          'No. Input',
          'Nama Sparepart',
          'Satuan',
          'Saldo Awal',
          '',
          '',
          'Penambahan',
          '',
          '',
          'Pengurangan',
          '',
          '',
          'Retur',
          '',
          '',
          'Saldo Akhir',
        ],
        [                                               // Baris 5: Header Tabel
          '',
          '',
          '',
          '',
          '',
          '',
          '',
          '',
          'Qty',
          'Harga',
          'Jumlah',
          'Qty',
          'Harga',
          'Jumlah',
          'Qty',
          'Harga',
          'Jumlah',
          'Qty',
          'Harga',
          'Jumlah',
          'Qty',
          'Harga',
          'Jumlah',
        ]
      ];
    }
  }

  public function map($row): array
  {
    // Increment nomor urut setiap baris data
    $this->rowNumber++;

    if ($this->params['tipe'] == "S") {
      return [
        $this->rowNumber, // Kolom No
        $row->bulan,
        $row->tahun,
        $row->nama_merek,
        $row->nama_tipe,
        $row->kode_input,
        $row->nama_sparepart,
        $row->satuan,
        $row->unit_awal,
        $row->harga_awal,
        $row->jumlah_awal,
        $row->unit_tambah,
        $row->harga_tambah,
        $row->jumlah_tambah,
        $row->unit_kurang,
        $row->harga_kurang,
        $row->jumlah_kurang,
        $row->unit_retur,
        $row->harga_retur,
        $row->jumlah_retur,
        $row->unit_akhir,
        $row->harga_akhir,
        $row->jumlah_akhir,
      ];
    } else {
      return [
        $this->rowNumber, // Kolom No
        $row->nama_bahan,
        $row->satuan,
        $row->unit_awal,
        $row->harga_awal,
        $row->jumlah_awal,
        $row->unit_tambah,
        $row->harga_tambah,
        $row->jumlah_tambah,
        $row->unit_kurang,
        $row->harga_kurang,
        $row->jumlah_kurang,
        $row->unit_retur,
        $row->harga_retur,
        $row->jumlah_retur,
        $row->unit_akhir,
        $row->harga_akhir,
        $row->jumlah_akhir,
      ];
    }
  }

  // Styling untuk Header dan Judul
  public function styles(Worksheet $sheet)
  {
    return [
      // Baris 1 (Judul Utama) -> Bold, Ukuran 14, Tengah
      1 => [
        'font' => ['bold' => true, 'size' => 14],
        // 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
      ],
      // Baris 2 & 3 (Info) -> Bold
      2 => ['font' => ['bold' => true]],
      3 => ['font' => ['bold' => true]],
      // Baris 5 (Header Tabel) -> Bold, Tengah, Border Bawah
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
        ],
      ],
    ];
  }

  // Event untuk Merge Cells (Penyatuan Kolom Judul)
  public function registerEvents(): array
  {
    if ($this->params['tipe'] == "S") {
      return [
        AfterSheet::class => function (AfterSheet $event) {
          // Merge Cell untuk Judul
          $event->sheet->mergeCells('A1:W1');

          // Merge Cell untuk Bengkel
          $event->sheet->mergeCells('A2:W2');

          // Merge Cell untuk Periode
          $event->sheet->mergeCells('A3:W3');

          // Merge Cell untuk Header
          $event->sheet->mergeCells('A5:A6');
          $event->sheet->mergeCells('B5:B6');
          $event->sheet->mergeCells('C5:C6');
          $event->sheet->mergeCells('D5:D6');
          $event->sheet->mergeCells('E5:E6');
          $event->sheet->mergeCells('F5:F6');
          $event->sheet->mergeCells('G5:G6');
          $event->sheet->mergeCells('H5:H6');

          $event->sheet->mergeCells('I5:K5');
          $event->sheet->mergeCells('L5:N5');
          $event->sheet->mergeCells('O5:Q5');
          $event->sheet->mergeCells('R5:T5');
          $event->sheet->mergeCells('U5:W5');

          // Tambahkan Border untuk seluruh data (mulai baris 5 sampai data terakhir)
          $highestRow = $event->sheet->getHighestRow();
          $event->sheet->getStyle('A5:W' . $highestRow)->applyFromArray([
            'borders' => [
              'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
              ],
            ],
          ]);

          // Set Kolom No (A) alignment Center
          $event->sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        },
      ];
    } else {
      return [
        AfterSheet::class => function (AfterSheet $event) {
          // Merge Cell untuk Judul
          $event->sheet->mergeCells('A1:R1');

          // Merge Cell untuk Bengkel
          $event->sheet->mergeCells('A2:R2');

          // Merge Cell untuk Periode
          $event->sheet->mergeCells('A3:R3');

          // Merge Cell untuk Header
          $event->sheet->mergeCells('A5:A6');
          $event->sheet->mergeCells('B5:B6');
          $event->sheet->mergeCells('C5:C6');
          $event->sheet->mergeCells('D5:F5');
          $event->sheet->mergeCells('G5:I5');
          $event->sheet->mergeCells('J5:L5');
          $event->sheet->mergeCells('M5:O5');
          $event->sheet->mergeCells('P5:R5');

          // Tambahkan Border untuk seluruh data (mulai baris 5 sampai data terakhir)
          $highestRow = $event->sheet->getHighestRow();
          $event->sheet->getStyle('A5:R' . $highestRow)->applyFromArray([
            'borders' => [
              'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
              ],
            ],
          ]);

          // Set Kolom No (A) alignment Center
          $event->sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        },
      ];
    }
  }
}
