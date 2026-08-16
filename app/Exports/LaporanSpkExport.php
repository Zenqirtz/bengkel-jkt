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

class LaporanSpkExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
        $query = '';
        if ($this->params['tipe_laporan'] == "spk") {
            $query = DB::table('v_rep_spk_master as k')
                ->where('k.kode_cabang', $this->cabang['kode'])
                ->select([
                    'k.id',
                    'k.kode_spk',
                    'k.tgl_masuk',
                    'k.no_polisi',
                    'k.status',
                    'k.status_spk',
                    'k.merek_tipe',
                    'k.pemilik',
                    'k.telepon',
                    'k.jenis_perbaikan',
                    'k.nama_pelanggan',
                    'k.tgl_estimasi',
                    'k.kode_estimasi',
                    'k.nilai_estimasi',
                    'k.tgl_pengiriman',
                    'k.tgl_turun_lapangan',
                    'k.tgl_rencana_selesai',
                    'k.tgl_keluar',
                    'k.tanggal_or',
                    'k.kode_or',
                    'k.total_or',
                    'k.tgl_invoice',
                    'k.no_invoice',
                    'k.nilai_tawar',
                    'k.tgl_kwitansi',
                    'k.kode_kwitansi',
                    'k.nilai_kwitansi',
                    'k.nama_surveyor',
                    'k.nama_marketing',
                    'k.nama_perantara',
                ])
                ->orderBy('k.tgl_masuk', 'asc')
                ->orderBy('k.kode_spk', 'asc');

            if (!empty($this->params['jenis_laporan'])) {
                if ($this->params['jenis_laporan'] == "periode") {
                    try {
                        $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
                        $endDate   = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
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

            // *** filter customer / tertanggung ***
            if (!empty($this->params['nama_customer'])) {
                $query->where('k.pemilik', 'like', '%' . $this->params['nama_customer'] . '%');
            }
        } elseif ($this->params['tipe_laporan'] == "spk_tutup") {
            $query = DB::table('v_rep_spk_tutup as k')
                ->where('k.kode_cabang', $this->cabang['kode'])
                ->select([
                    'k.kode_tutup',
                    'k.tanggal_tutup',
                    'k.kode_spk',
                    'k.pemilik',
                    'k.no_polisi',
                    'k.merek_tipe',
                ])
                ->orderBy('k.tanggal_tutup', 'asc')
                ->orderBy('k.kode_tutup', 'asc');

            if (!empty($this->params['jenis_laporan'])) {
                if ($this->params['jenis_laporan'] == "periode") {
                    try {
                        $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
                        $endDate   = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
                        $query->whereDate('k.tanggal_tutup', '>=', $startDate);
                        $query->whereDate('k.tanggal_tutup', '<=', $endDate);
                    } catch (\Exception $e) {
                    }
                } elseif ($this->params['jenis_laporan'] == "bulan") {
                    $query->whereMonth('k.tanggal_tutup', $this->params['bulan']);
                    $query->whereYear('k.tanggal_tutup', $this->params['tahun2']);
                } elseif ($this->params['jenis_laporan'] == "tahun") {
                    $query->whereYear('k.tanggal_tutup', $this->params['tahun']);
                }
            }

            // *** filter customer / tertanggung ***
            if (!empty($this->params['nama_customer'])) {
                $query->where('k.pemilik', 'like', '%' . $this->params['nama_customer'] . '%');
            }
        } elseif ($this->params['tipe_laporan'] == "spk_batal") {
            $query = DB::table('v_rep_spk_batal as k')
                ->where('k.kode_cabang', $this->cabang['kode'])
                ->select([
                    'k.tgl_batal',
                    'k.kode_spk',
                    'k.merek_tipe',
                    'k.no_polisi',
                    'k.nama_pelanggan',
                    'k.pemilik',
                    'k.batal_by',
                    'k.memo_batal',
                ])
                ->orderBy('k.tgl_batal', 'asc')
                ->orderBy('k.kode_spk', 'asc');

            if (!empty($this->params['jenis_laporan'])) {
                if ($this->params['jenis_laporan'] == "periode") {
                    try {
                        $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
                        $endDate   = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
                        $query->whereDate('k.tgl_batal', '>=', $startDate);
                        $query->whereDate('k.tgl_batal', '<=', $endDate);
                    } catch (\Exception $e) {
                    }
                } elseif ($this->params['jenis_laporan'] == "bulan") {
                    $query->whereMonth('k.tgl_batal', $this->params['bulan']);
                    $query->whereYear('k.tgl_batal', $this->params['tahun2']);
                } elseif ($this->params['jenis_laporan'] == "tahun") {
                    $query->whereYear('k.tgl_batal', $this->params['tahun']);
                }
            }

            // *** filter customer / tertanggung ***
            if (!empty($this->params['nama_customer'])) {
                $query->where('k.pemilik', 'like', '%' . $this->params['nama_customer'] . '%');
            }
        } elseif ($this->params['tipe_laporan'] == "spk_keluar") {
            $query = DB::table('v_rep_spk_keluar as k')
                ->where('k.kode_cabang', $this->cabang['kode'])
                ->select([
                    'k.tgl_keluar',
                    'k.kode_keluar',
                    'k.kode_spk',
                    'k.no_polisi',
                    'k.merek_tipe',
                    'k.pemilik',
                    'k.tgl_tanda_terima',
                    'k.nama_penerima',
                    'k.nama_pengantar',
                ])
                ->orderBy('k.tgl_keluar', 'asc')
                ->orderBy('k.kode_keluar', 'asc');

            if (!empty($this->params['jenis_laporan'])) {
                if ($this->params['jenis_laporan'] == "periode") {
                    try {
                        $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
                        $endDate   = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
                        $query->whereDate('k.tgl_keluar', '>=', $startDate);
                        $query->whereDate('k.tgl_keluar', '<=', $endDate);
                    } catch (\Exception $e) {
                    }
                } elseif ($this->params['jenis_laporan'] == "bulan") {
                    $query->whereMonth('k.tgl_keluar', $this->params['bulan']);
                    $query->whereYear('k.tgl_keluar', $this->params['tahun2']);
                } elseif ($this->params['jenis_laporan'] == "tahun") {
                    $query->whereYear('k.tgl_keluar', $this->params['tahun']);
                }
            }

            // *** filter customer / tertanggung ***
            if (!empty($this->params['nama_customer'])) {
                $query->where('k.pemilik', 'like', '%' . $this->params['nama_customer'] . '%');
            }
        }

        return $query;
    }

    public function headings(): array
    {
        if ($this->params['tipe_laporan'] == "spk") {
            return [
                ['Laporan SPK Master'],
                ['Cabang : ' . $this->cabang['nama']],
                ['Periode : ' . $this->periode],
                [''],
                [
                    'No',
                    'No SPK',
                    'Tgl Masuk',
                    'No. Polisi',
                    'Keterangan',
                    'Status SPK',
                    'Tipe Kendaraan',
                    'Nama Pemilik',
                    'No. Telepon',
                    'Jenis Perbaikan',
                    'Nama Asuransi',
                    'Tgl. Estimasi',
                    'No. Estimasi',
                    'Nilai Estimasi',
                    'Tgl. Kirim Estimasi',
                    'Tgl. Turun Lap.',
                    'Tgl. Rencana Selesai',
                    'Tgl. Keluar',
                    'Tgl. Inv. OR',
                    'No. Inv. OR',
                    'Nilai OR',
                    'Tgl. Inv. Asuransi',
                    'No. Inv. Asuransi',
                    'Nilai Inv. Asuransi',
                    'Tgl. Kwitansi',
                    'No. Kwitansi',
                    'Nilai Kwitansi',
                    'Nama Surveyor',
                    'Nama Marketing',
                    'Nama Perantara',
                ]
            ];
        } elseif ($this->params['tipe_laporan'] == "spk_tutup") {
            return [
                ['Laporan SPK Tutup'],
                ['Cabang : ' . $this->cabang['nama']],
                ['Periode : ' . $this->periode],
                [''],
                [
                    'No',
                    'Tanggal Tutup',
                    'No. Tutup',
                    'No. SPK',
                    'Nama Pemilik',
                    'No. Polisi',
                    'Tipe Kendaraan',
                ]
            ];
        } elseif ($this->params['tipe_laporan'] == "spk_batal") {
            return [
                ['Laporan SPK Batal'],
                ['Cabang : ' . $this->cabang['nama']],
                ['Periode : ' . $this->periode],
                [''],
                [
                    'No',
                    'Tanggal Batal',
                    'No. SPK',
                    'Tipe Kendaraan',
                    'No. Polisi',
                    'Nama Asuransi',
                    'Nama Pemilik',
                    'Dibatalkan Oleh',
                    'Alasan Pembatalan',
                ]
            ];
        } elseif ($this->params['tipe_laporan'] == "spk_keluar") {
            return [
                ['Laporan SPK Keluar'],
                ['Cabang : ' . $this->cabang['nama']],
                ['Periode : ' . $this->periode],
                [''],
                [
                    'No',
                    'Tanggal Keluar',
                    'No. Keluar',
                    'No. SPK',
                    'No. Polisi',
                    'Tipe Kendaraan',
                    'Nama Pemilik',
                    'Tgl Tanda Terima',
                    // 'Nama Penerima',
                    // 'Nama Pengantar',
                ]
            ];
        }
    }

    public function map($row): array
    {
        $this->rowNumber++;

        if ($this->params['tipe_laporan'] == "spk") {
            return [
                $this->rowNumber,
                $row->kode_spk,
                $row->tgl_masuk ? date("d/m/Y", strtotime($row->tgl_masuk)) : '',
                $row->no_polisi,
                $row->status,
                $row->status_spk,
                $row->merek_tipe,
                $row->pemilik,
                $row->telepon,
                $row->jenis_perbaikan,
                $row->nama_pelanggan,
                $row->tgl_estimasi ? date("d/m/Y", strtotime($row->tgl_estimasi)) : '',
                $row->kode_estimasi,
                $row->nilai_estimasi,
                $row->tgl_pengiriman ? date("d/m/Y", strtotime($row->tgl_pengiriman)) : '',
                $row->tgl_turun_lapangan ? date("d/m/Y", strtotime($row->tgl_turun_lapangan)) : '',
                $row->tgl_rencana_selesai ? date("d/m/Y", strtotime($row->tgl_rencana_selesai)) : '',
                $row->tgl_keluar ? date("d/m/Y", strtotime($row->tgl_keluar)) : '',
                $row->tanggal_or ? date("d/m/Y", strtotime($row->tanggal_or)) : '',
                $row->kode_or,
                $row->total_or,
                $row->tgl_invoice ? date("d/m/Y", strtotime($row->tgl_invoice)) : '',
                $row->no_invoice,
                $row->nilai_tawar,
                $row->tgl_kwitansi ? date("d/m/Y", strtotime($row->tgl_kwitansi)) : '',
                $row->kode_kwitansi,
                $row->nilai_kwitansi,
                $row->nama_surveyor,
                $row->nama_marketing,
                $row->nama_perantara,
            ];
        } elseif ($this->params['tipe_laporan'] == "spk_tutup") {
            return [
                $this->rowNumber,
                $row->tanggal_tutup ? date('d/m/Y', strtotime($row->tanggal_tutup)) : '',
                $row->kode_tutup,
                $row->kode_spk,
                $row->pemilik,
                $row->no_polisi,
                $row->merek_tipe,
            ];
        } elseif ($this->params['tipe_laporan'] == "spk_batal") {
            return [
                $this->rowNumber,
                $row->tgl_batal ? date('d/m/Y', strtotime($row->tgl_batal)) : '',
                $row->kode_spk,
                $row->merek_tipe,
                $row->no_polisi,
                $row->nama_pelanggan,
                $row->pemilik,
                $row->batal_by,
                $row->memo_batal,
            ];
        } elseif ($this->params['tipe_laporan'] == "spk_keluar") {
            return [
                $this->rowNumber,
                $row->tgl_keluar ? date('d/m/Y', strtotime($row->tgl_keluar)) : '',
                $row->kode_keluar,
                $row->kode_spk,
                $row->no_polisi,
                $row->merek_tipe,
                $row->pemilik,
                $row->tgl_tanda_terima ? date('d/m/Y', strtotime($row->tgl_tanda_terima)) : '',
                // $row->nama_penerima,
                // $row->nama_pengantar,
            ];
        }
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
                    'vertical'   => Alignment::VERTICAL_CENTER,
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
        if ($this->params['tipe_laporan'] == "spk") {
            return [
                AfterSheet::class => function (AfterSheet $event) {
                    $event->sheet->mergeCells('A1:AD1');
                    $event->sheet->mergeCells('A2:AD2');
                    $event->sheet->mergeCells('A3:AD3');

                    $highestRow = $event->sheet->getHighestRow();
                    $event->sheet->getStyle('A5:AD' . $highestRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);
                    $event->sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                },
            ];
        } elseif ($this->params['tipe_laporan'] == "spk_tutup") {
            return [
                AfterSheet::class => function (AfterSheet $event) {
                    $event->sheet->mergeCells('A1:G1');
                    $event->sheet->mergeCells('A2:G2');
                    $event->sheet->mergeCells('A3:G3');

                    $highestRow = $event->sheet->getHighestRow();
                    $event->sheet->getStyle('A5:G' . $highestRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);
                    $event->sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                },
            ];
        } elseif ($this->params['tipe_laporan'] == "spk_batal") {
            return [
                AfterSheet::class => function (AfterSheet $event) {
                    $event->sheet->mergeCells('A1:I1');
                    $event->sheet->mergeCells('A2:I2');
                    $event->sheet->mergeCells('A3:I3');

                    $highestRow = $event->sheet->getHighestRow();
                    $event->sheet->getStyle('A5:I' . $highestRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);
                    $event->sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                },
            ];
        } elseif ($this->params['tipe_laporan'] == "spk_keluar") {
            return [
                AfterSheet::class => function (AfterSheet $event) {
                    $event->sheet->mergeCells('A1:H1');
                    $event->sheet->mergeCells('A2:H2');
                    $event->sheet->mergeCells('A3:H3');

                    $highestRow = $event->sheet->getHighestRow();
                    $event->sheet->getStyle('A5:H' . $highestRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);
                    $event->sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                },
            ];
        }
    }
}
