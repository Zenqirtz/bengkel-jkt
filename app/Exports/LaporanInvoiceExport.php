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

class LaporanInvoiceExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
        if($this->params['tipe_laporan'] == "inv_belum_terbit") {
            // Query sama seperti sebelumnya
            $query = DB::table('v_rpt_belum_ada_or as k')
            ->where('k.kode_cabang', $this->cabang['kode']) // Sesuaikan jika ini array/string
            ->select([
                'k.id',
                'k.tgl_masuk',
                'k.kode_spk',
                'k.no_polisi',
                'k.merek_tipe',
                'k.pemilik',
                'k.nama_pelanggan',
                'k.status_spk',
            ])
            ->orderBy('k.tgl_masuk', 'asc');

            // --- FILTERING LOGIC (Sama seperti sebelumnya) ---
            if (!empty($this->params['jenis_laporan'])) {
                if($this->params['jenis_laporan'] == "periode") {
                    try {
                        $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
                        $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
                        $query->whereDate('k.tgl_masuk', '>=', $startDate);
                        $query->whereDate('k.tgl_masuk', '<=', $endDate);
                    } catch (\Exception $e) {}
                } elseif($this->params['jenis_laporan'] == "bulan") {
                    $query->whereMonth('k.tgl_masuk', $this->params['bulan']);
                    $query->whereYear('k.tgl_masuk', $this->params['tahun2']);
                } elseif($this->params['jenis_laporan'] == "tahun") {
                    $query->whereYear('k.tgl_masuk', $this->params['tahun']);
                }
            }
        } elseif($this->params['tipe_laporan'] == "inv_terbit") {
            // Query sama seperti sebelumnya
            $query = DB::table('v_rpt_terbit_kwitansi_or as k')
            ->where('k.kode_cabang', $this->cabang['kode']) // Sesuaikan jika ini array/string
            ->select([
                'k.tgl_invoice',
                'k.no_invoice',
                'k.kode_spk',
                'k.tertanggung',
                'k.no_polisi',
                'k.merek_tipe',
                'k.nama_pelanggan',
                'k.total_or',
            ])
            ->orderBy('k.tgl_invoice', 'asc');

            // --- FILTERING LOGIC (Sama seperti sebelumnya) ---
            if (!empty($this->params['jenis_laporan'])) {
                if($this->params['jenis_laporan'] == "periode") {
                    try {
                        $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
                        $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
                        $query->whereDate('k.tgl_invoice', '>=', $startDate);
                        $query->whereDate('k.tgl_invoice', '<=', $endDate);
                    } catch (\Exception $e) {}
                } elseif($this->params['jenis_laporan'] == "bulan") {
                    $query->whereMonth('k.tgl_invoice', $this->params['bulan']);
                    $query->whereYear('k.tgl_invoice', $this->params['tahun2']);
                } elseif($this->params['jenis_laporan'] == "tahun") {
                    $query->whereYear('k.tgl_invoice', $this->params['tahun']);
                }
            }
        } elseif($this->params['tipe_laporan'] == "inv_belum_tagih") {
            // Query sama seperti sebelumnya
            $query = DB::table('v_rep_kwt_or_belum_ditagih as k')
            ->where('k.kode_cabang', $this->cabang['kode']) // Sesuaikan jika ini array/string
            ->select([
                'k.tgl_invoice',
                'k.no_invoice',
                'k.kode_spk',
                'k.tertanggung',
                'k.no_polisi',
                'k.merek_tipe',
                'k.nama_pelanggan',
                'k.total_or',
            ])
            ->orderBy('k.tgl_invoice', 'asc');

            // --- FILTERING LOGIC (Sama seperti sebelumnya) ---
            if (!empty($this->params['jenis_laporan'])) {
                if($this->params['jenis_laporan'] == "periode") {
                    try {
                        $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
                        $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
                        $query->whereDate('k.tgl_invoice', '>=', $startDate);
                        $query->whereDate('k.tgl_invoice', '<=', $endDate);
                    } catch (\Exception $e) {}
                } elseif($this->params['jenis_laporan'] == "bulan") {
                    $query->whereMonth('k.tgl_invoice', $this->params['bulan']);
                    $query->whereYear('k.tgl_invoice', $this->params['tahun2']);
                } elseif($this->params['jenis_laporan'] == "tahun") {
                    $query->whereYear('k.tgl_invoice', $this->params['tahun']);
                }
            }
        } elseif($this->params['tipe_laporan'] == "inv_belum_lunas") {
            // Query sama seperti sebelumnya
            $query = DB::table('v_rpt_or_belum_lunas as k')
            ->where('k.kode_cabang', $this->cabang['kode']) // Sesuaikan jika ini array/string
            ->select([
                'k.tgl_invoice',
                'k.no_invoice',
                'k.kode_spk',
                'k.tertanggung',
                'k.no_polisi',
                'k.merek_tipe',
                'k.nama_pelanggan',
                'k.total_or',
                'k.kode_keluar',
                'k.keterangan',
            ])
            ->orderBy('k.tgl_invoice', 'asc');

            // --- FILTERING LOGIC (Sama seperti sebelumnya) ---
            if (!empty($this->params['jenis_laporan'])) {
                if($this->params['jenis_laporan'] == "periode") {
                    try {
                        $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
                        $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
                        $query->whereDate('k.tgl_invoice', '>=', $startDate);
                        $query->whereDate('k.tgl_invoice', '<=', $endDate);
                    } catch (\Exception $e) {}
                } elseif($this->params['jenis_laporan'] == "bulan") {
                    $query->whereMonth('k.tgl_invoice', $this->params['bulan']);
                    $query->whereYear('k.tgl_invoice', $this->params['tahun2']);
                } elseif($this->params['jenis_laporan'] == "tahun") {
                    $query->whereYear('k.tgl_invoice', $this->params['tahun']);
                }
            }
        }

        return $query;
    }

    // Sesuaikan Header dengan layout Excel yang diminta
    public function headings(): array
    {
        if($this->params['tipe_laporan'] == "inv_belum_terbit") {
            return [
                ['Laporan Invoice OR Belum Terbit'],                                // Baris 1: Judul
                ['Cabang : ' . $this->cabang['nama']],         // Baris 2: Nama Bengkel
                ['Periode : ' . $this->periode],                // Baris 3: Periode
                [''],                                           // Baris 4: Kosong (Spacer)
                [                                               // Baris 5: Header Tabel
                    'No',
                    'Tanggal Masuk',
                    'No. SPK',
                    'No. Polisi',
                    'Tipe Kendaraan',
                    'Nama Pemilik',
                    'Nama Asuransi',
                    'Status',
                ]
            ];
        } elseif($this->params['tipe_laporan'] == "inv_terbit") {
            return [
                ['Laporan Invoice OR Terbit'],                                // Baris 1: Judul
                ['Cabang : ' . $this->cabang['nama']],         // Baris 2: Nama Bengkel
                ['Periode : ' . $this->periode],                // Baris 3: Periode
                [''],                                           // Baris 4: Kosong (Spacer)
                [                                               // Baris 5: Header Tabel
                    'No',
                    'Tanggal Invoice',
                    'No. Invoice',
                    'No. SPK',
                    'Tertanggung',
                    'No. Polisi',
                    'Tipe Kendaraan',
                    'Nama Asuransi',
                    'Nilai OR',
                ]
            ];
        } elseif($this->params['tipe_laporan'] == "inv_belum_tagih") {
            return [
                ['Laporan Invoice OR Belum Ditagih'],                                // Baris 1: Judul
                ['Cabang : ' . $this->cabang['nama']],         // Baris 2: Nama Bengkel
                ['Periode : ' . $this->periode],                // Baris 3: Periode
                [''],                                           // Baris 4: Kosong (Spacer)
                [                                               // Baris 5: Header Tabel
                    'No',
                    'Tanggal Invoice',
                    'No. Invoice',
                    'No. SPK',
                    'Tertanggung',
                    'No. Polisi',
                    'Tipe Kendaraan',
                    'Nama Asuransi',
                    'Nilai OR',
                ]
            ];
        } elseif($this->params['tipe_laporan'] == "inv_belum_lunas") {
            return [
                ['Laporan Invoice OR Belum Lunas'],                                // Baris 1: Judul
                ['Cabang : ' . $this->cabang['nama']],         // Baris 2: Nama Bengkel
                ['Periode : ' . $this->periode],                // Baris 3: Periode
                [''],                                           // Baris 4: Kosong (Spacer)
                [                                               // Baris 5: Header Tabel
                    'No',
                    'Tanggal Invoice',
                    'No. Invoice',
                    'No. SPK',
                    'Tertanggung',
                    'No. Polisi',
                    'Tipe Kendaraan',
                    'Nama Asuransi',
                    'Nilai OR',
                    'No. Keluar',
                    'Keterangan',
                ]
            ];
        }
    }

    public function map($row): array
    {
        // Increment nomor urut setiap baris data
        $this->rowNumber++;

        if($this->params['tipe_laporan'] == "inv_belum_terbit") {
            return [
                $this->rowNumber, // Kolom No
                $row->tgl_masuk ? date("d/m/Y", strtotime($row->tgl_masuk)) : '',
                $row->kode_spk,
                $row->no_polisi,
                $row->merek_tipe,
                $row->pemilik,
                $row->nama_pelanggan,
                $row->status_spk,
            ];
        } elseif($this->params['tipe_laporan'] == "inv_terbit") {
            return [
                $this->rowNumber, // Kolom No
                $row->tgl_invoice ? date('d/m/Y', strtotime($row->tgl_invoice)) : '',
                $row->no_invoice,
                $row->kode_spk,
                $row->tertanggung,
                $row->no_polisi,
                $row->merek_tipe,
                $row->nama_pelanggan,
                $row->total_or,
            ];
        } elseif($this->params['tipe_laporan'] == "inv_belum_tagih") {
            return [
                $this->rowNumber, // Kolom No
                $row->tgl_invoice ? date('d/m/Y', strtotime($row->tgl_invoice)) : '',
                $row->no_invoice,
                $row->kode_spk,
                $row->tertanggung,
                $row->no_polisi,
                $row->merek_tipe,
                $row->nama_pelanggan,
                $row->total_or,
            ];
        } elseif($this->params['tipe_laporan'] == "inv_belum_lunas") {
            return [
                $this->rowNumber, // Kolom No
                $row->tgl_invoice ? date('d/m/Y', strtotime($row->tgl_invoice)) : '',
                $row->no_invoice,
                $row->kode_spk,
                $row->tertanggung,
                $row->no_polisi,
                $row->merek_tipe,
                $row->nama_pelanggan,
                $row->total_or,
                $row->kode_keluar,
                $row->keterangan,
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
        if($this->params['tipe_laporan'] == "inv_belum_terbit") {
            return [
                AfterSheet::class => function(AfterSheet $event) {
                    // Merge Cell A1 sampai H1 (sesuai jumlah kolom) untuk Judul
                    $event->sheet->mergeCells('A1:H1');
                    
                    // Merge Cell A2 sampai H2 untuk Bengkel
                    $event->sheet->mergeCells('A2:H2');
                    
                    // Merge Cell A3 sampai H3 untuk Periode
                    $event->sheet->mergeCells('A3:H3');
    
                    // Tambahkan Border untuk seluruh data (mulai baris 5 sampai data terakhir)
                    $highestRow = $event->sheet->getHighestRow();
                    $event->sheet->getStyle('A5:H' . $highestRow)->applyFromArray([
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
        } elseif($this->params['tipe_laporan'] == "inv_terbit") {
            return [
                AfterSheet::class => function(AfterSheet $event) {
                    // Merge Cell A1 sampai I1 (sesuai jumlah kolom) untuk Judul
                    $event->sheet->mergeCells('A1:I1');
                    
                    // Merge Cell A2 sampai I2 untuk Bengkel
                    $event->sheet->mergeCells('A2:I2');
                    
                    // Merge Cell A3 sampai I3 untuk Periode
                    $event->sheet->mergeCells('A3:I3');
    
                    // Tambahkan Border untuk seluruh data (mulai baris 5 sampai data terakhir)
                    $highestRow = $event->sheet->getHighestRow();
                    $event->sheet->getStyle('A5:I' . $highestRow)->applyFromArray([
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
        } elseif($this->params['tipe_laporan'] == "inv_belum_tagih") {
            return [
                AfterSheet::class => function(AfterSheet $event) {
                    // Merge Cell A1 sampai I1 (sesuai jumlah kolom) untuk Judul
                    $event->sheet->mergeCells('A1:I1');
                    
                    // Merge Cell A2 sampai I2 untuk Bengkel
                    $event->sheet->mergeCells('A2:I2');
                    
                    // Merge Cell A3 sampai I3 untuk Periode
                    $event->sheet->mergeCells('A3:I3');
    
                    // Tambahkan Border untuk seluruh data (mulai baris 5 sampai data terakhir)
                    $highestRow = $event->sheet->getHighestRow();
                    $event->sheet->getStyle('A5:I' . $highestRow)->applyFromArray([
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
        } elseif($this->params['tipe_laporan'] == "inv_belum_lunas") {
            return [
                AfterSheet::class => function(AfterSheet $event) {
                    // Merge Cell A1 sampai K1 (sesuai jumlah kolom) untuk Judul
                    $event->sheet->mergeCells('A1:K1');
                    
                    // Merge Cell A2 sampai K2 untuk Bengkel
                    $event->sheet->mergeCells('A2:K2');
                    
                    // Merge Cell A3 sampai K3 untuk Periode
                    $event->sheet->mergeCells('A3:K3');
    
                    // Tambahkan Border untuk seluruh data (mulai baris 5 sampai data terakhir)
                    $highestRow = $event->sheet->getHighestRow();
                    $event->sheet->getStyle('A5:K' . $highestRow)->applyFromArray([
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