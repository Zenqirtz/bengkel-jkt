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

class LaporanKendaraanExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
    if ($this->params['tipe_laporan'] == "mobil_belum_turun") {
      // Query sama seperti sebelumnya
      $query = DB::table('v_rep_belum_turun_lapangan as k')
        ->where('k.kode_cabang', $this->cabang['kode']) // Sesuaikan jika ini array/string
        ->select([
          'k.tgl_masuk',
          'k.kode_spk',
          'k.no_polisi',
          'k.merek_tipe',
          'k.nama_pelanggan',
          'k.pemilik',
        ])
        ->orderBy('k.tgl_masuk', 'asc');

      // --- FILTERING LOGIC (Sama seperti sebelumnya) ---
      if (!empty($this->params['jenis_laporan'])) {
        if ($this->params['jenis_laporan'] == "periode") {
          try {
            $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
            $query->whereDate('k.tgl_masuk', '>=', $startDate);
            $query->whereDate('k.tgl_masuk', '<=', $endDate);
          } catch (\Exception $e) {
          }
        } elseif ($this->params['jenis_laporan'] == "bulan") {
          $query->whereMonth('k.tgl_masuk', $this->params['bulan']);
          $query->whereYear('k.tgl_masuk', $this->params['tahun2']);
        } elseif ($this->params['jenis_laporan'] == "tahun") {
          $query->whereYear('k.tgl_masuk', $this->params['tahun']);
        }
      }
    } elseif ($this->params['tipe_laporan'] == "mobil_turun") {
      // Query sama seperti sebelumnya
      $query = DB::table('v_rep_turun_lapangan as k')
        ->where('k.kode_cabang', $this->cabang['kode']) // Sesuaikan jika ini array/string
        ->select([
          'k.tgl_turun_lapangan',
          'k.kode_turun_lapangan',
          'k.kode_spk',
          'k.no_polisi',
          'k.merek_tipe',
          'k.nama_pelanggan',
          'k.pemilik',
          'k.tgl_rencana_selesai',
          'k.status',
        ])
        // ->orderBy('k.tgl_turun_lapangan', 'asc');
        ->orderBy('k.tgl_turun_lapangan', 'desc');

      // --- FILTERING LOGIC (Sama seperti sebelumnya) ---
      if (!empty($this->params['jenis_laporan'])) {
        if ($this->params['jenis_laporan'] == "periode") {
          try {
            $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
            $query->whereDate('k.tgl_turun_lapangan', '>=', $startDate);
            $query->whereDate('k.tgl_turun_lapangan', '<=', $endDate);
          } catch (\Exception $e) {
          }
        } elseif ($this->params['jenis_laporan'] == "bulan") {
          $query->whereMonth('k.tgl_turun_lapangan', $this->params['bulan']);
          $query->whereYear('k.tgl_turun_lapangan', $this->params['tahun2']);
        } elseif ($this->params['jenis_laporan'] == "tahun") {
          $query->whereYear('k.tgl_turun_lapangan', $this->params['tahun']);
        }
      }
    }

    return $query;
  }

  // Sesuaikan Header dengan layout Excel yang diminta
  public function headings(): array
  {
    if ($this->params['tipe_laporan'] == "mobil_belum_turun") {
      return [
        ['Laporan Mobil Belum Turun Lapangan'],                                // Baris 1: Judul
        ['Cabang : ' . $this->cabang['nama']],         // Baris 2: Nama Bengkel
        ['Periode : ' . $this->periode],                // Baris 3: Periode
        [''],                                           // Baris 4: Kosong (Spacer)
        [                                               // Baris 5: Header Tabel
          'No',
          'Tanggal Masuk',
          'No. SPK',
          'No. Polisi',
          'Tipe Kendaraan',
          'Nama Asuransi',
          'Nama Pemilik',
        ]
      ];
    } elseif ($this->params['tipe_laporan'] == "mobil_turun") {
      return [
        ['Laporan Mobil Turun Lapangan'],                                // Baris 1: Judul
        ['Cabang : ' . $this->cabang['nama']],         // Baris 2: Nama Bengkel
        ['Periode : ' . $this->periode],                // Baris 3: Periode
        [''],                                           // Baris 4: Kosong (Spacer)
        [                                               // Baris 5: Header Tabel
          'No',
          'Turun Lapangan',
          'No. Turun Lapangan',
          'No. SPK',
          'No. Polisi',
          'Tipe Kendaraan',
          'Nama Asuransi',
          'Nama Pemilik',
          'Rencana Selesai',
          'Ket.',
        ]
      ];
    }
  }

  public function map($row): array
  {
    // Increment nomor urut setiap baris data
    $this->rowNumber++;

    if ($this->params['tipe_laporan'] == "mobil_belum_turun") {
      return [
        $this->rowNumber, // Kolom No
        $row->tgl_masuk ? date("d/m/Y", strtotime($row->tgl_masuk)) : '',
        $row->kode_spk,
        $row->no_polisi,
        $row->merek_tipe,
        $row->nama_pelanggan,
        $row->pemilik,
      ];
    } elseif ($this->params['tipe_laporan'] == "mobil_turun") {
      return [
        $this->rowNumber, // Kolom No
        $row->tgl_turun_lapangan ? date("d/m/Y", strtotime($row->tgl_turun_lapangan)) : '',
        $row->kode_turun_lapangan,
        $row->kode_spk,
        $row->no_polisi,
        $row->merek_tipe,
        $row->nama_pelanggan,
        $row->pemilik,
        $row->tgl_rencana_selesai ? date("d/m/Y", strtotime($row->tgl_rencana_selesai)) : '',
        $row->status,
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
    ];
  }

  // Event untuk Merge Cells (Penyatuan Kolom Judul)
  public function registerEvents(): array
  {
    if ($this->params['tipe_laporan'] == "mobil_belum_turun") {
      return [
        AfterSheet::class => function (AfterSheet $event) {
          // Merge Cell A1 sampai G1 (sesuai jumlah kolom) untuk Judul
          $event->sheet->mergeCells('A1:G1');

          // Merge Cell A2 sampai G2 untuk Bengkel
          $event->sheet->mergeCells('A2:G2');

          // Merge Cell A3 sampai G3 untuk Periode
          $event->sheet->mergeCells('A3:G3');

          // Tambahkan Border untuk seluruh data (mulai baris 5 sampai data terakhir)
          $highestRow = $event->sheet->getHighestRow();
          $event->sheet->getStyle('A5:G' . $highestRow)->applyFromArray([
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
    } elseif ($this->params['tipe_laporan'] == "mobil_turun") {
      return [
        AfterSheet::class => function (AfterSheet $event) {
          // Merge Cell A1 sampai J1 (sesuai jumlah kolom) untuk Judul
          $event->sheet->mergeCells('A1:J1');

          // Merge Cell A2 sampai J2 untuk Bengkel
          $event->sheet->mergeCells('A2:J2');

          // Merge Cell A3 sampai J3 untuk Periode
          $event->sheet->mergeCells('A3:J3');

          // Tambahkan Border untuk seluruh data (mulai baris 5 sampai data terakhir)
          $highestRow = $event->sheet->getHighestRow();
          $event->sheet->getStyle('A5:J' . $highestRow)->applyFromArray([
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
