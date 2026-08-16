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

class LaporanOutstandingExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
        if($this->params['jenis_laporan'] == "periode") {
            // Query sama seperti sebelumnya
            $query = DB::table('v_rpt_outstanding_or as k')
            ->where('k.kode_cabang', $this->cabang['kode']) // Sesuaikan jika ini array/string
            ->select([
                'k.kode_spk',
                'k.no_polisi',
                'k.merek_tipe',
                'k.nama_pelanggan',
                'k.tertanggung',
                'k.no_invoice',
                'k.tgl_invoice',
                'k.total_or',
                'k.kode_keluar',
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
        } elseif($this->params['jenis_laporan'] == "tahun") {
            // Query sama seperti sebelumnya
            $query = DB::table('v_rpt_outstanding_or')
            ->select(
                'nama_pelanggan',
                // Gunakan DB::raw untuk CASE WHEN expression
                DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 1 THEN total_or ELSE 0 END) AS JAN"),
                DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 2 THEN total_or ELSE 0 END) AS FEB"),
                DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 3 THEN total_or ELSE 0 END) AS MAR"),
                DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 4 THEN total_or ELSE 0 END) AS APR"),
                DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 5 THEN total_or ELSE 0 END) AS MEI"),
                DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 6 THEN total_or ELSE 0 END) AS JUN"),
                DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 7 THEN total_or ELSE 0 END) AS JUL"),
                DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 8 THEN total_or ELSE 0 END) AS AGS"),
                DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 9 THEN total_or ELSE 0 END) AS SEP"),
                DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 10 THEN total_or ELSE 0 END) AS OKT"),
                DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 11 THEN total_or ELSE 0 END) AS NOV"),
                DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 12 THEN total_or ELSE 0 END) AS DES"),
                DB::raw("SUM(total_or) AS Total")
            )
            ->where('kode_cabang', $this->cabang['kode'])
            ->whereYear('tgl_invoice', $this->params['tahun']) // Helper Laravel untuk YEAR()
            ->groupBy('nama_pelanggan')
            ->orderBy('nama_pelanggan', 'asc');
        }

        return $query;
    }

    // Sesuaikan Header dengan layout Excel yang diminta
    public function headings(): array
    {
        if($this->params['jenis_laporan'] == "periode") {
            return [
                ['Laporan Outstanding OR'],                                // Baris 1: Judul
                ['Cabang : ' . $this->cabang['nama']],         // Baris 2: Nama Bengkel
                ['Periode : ' . $this->periode],                // Baris 3: Periode
                [''],                                           // Baris 4: Kosong (Spacer)
                [                                               // Baris 5: Header Tabel
                    'No',
                    'No. SPK',
                    'No. Polisi',
                    'Tipe Kendaraan',
                    'Nama Asuransi',
                    'Tertanggung',
                    'No. Invoice',
                    'Tanggal Invoice',
                    'Total OR',
                    'No. Keluar',
                ]
            ];
        } elseif($this->params['jenis_laporan'] == "tahun") {
            return [
                ['Laporan Rekap Outstanding OR per Tahun'],                                // Baris 1: Judul
                ['Cabang : ' . $this->cabang['nama']],         // Baris 2: Nama Bengkel
                ['Periode : ' . $this->periode],                // Baris 3: Periode
                [''],                                           // Baris 4: Kosong (Spacer)
                [                                               // Baris 5: Header Tabel
                    'No',
                    'Nama Asuransi',
                    'JAN',
                    'FEB',
                    'MAR',
                    'APR',
                    'MEI',
                    'JUN',
                    'JUL',
                    'AGS',
                    'SEP',
                    'OKT',
                    'NOV',
                    'DES',
                    'Total',
                ]
            ];
        }
    }

    public function map($row): array
    {
        // Increment nomor urut setiap baris data
        $this->rowNumber++;

        if($this->params['jenis_laporan'] == "periode") {
            return [
                $this->rowNumber, // Kolom No
                $row->kode_spk,
                $row->no_polisi,
                $row->merek_tipe,
                $row->nama_pelanggan,
                $row->tertanggung,
                $row->no_invoice,
                $row->tgl_invoice ? date("d/m/Y", strtotime($row->tgl_invoice)) : '',
                $row->total_or,
                $row->kode_keluar,
            ];
        } elseif($this->params['jenis_laporan'] == "tahun") {
            return [
                $this->rowNumber, // Kolom No
                $row->nama_pelanggan,
                $row->JAN,
                $row->FEB,
                $row->MAR,
                $row->APR,
                $row->MEI,
                $row->JUN,
                $row->JUL,
                $row->AGS,
                $row->SEP,
                $row->OKT,
                $row->NOV,
                $row->DES,
                $row->Total,
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
        if($this->params['jenis_laporan'] == "periode") {
            return [
                AfterSheet::class => function(AfterSheet $event) {
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
        } elseif($this->params['jenis_laporan'] == "tahun") {
            return [
                AfterSheet::class => function(AfterSheet $event) {
                    // Merge Cell A1 sampai O1 (sesuai jumlah kolom) untuk Judul
                    $event->sheet->mergeCells('A1:O1');
                    
                    // Merge Cell A2 sampai O2 untuk Bengkel
                    $event->sheet->mergeCells('A2:O2');
                    
                    // Merge Cell A3 sampai O3 untuk Periode
                    $event->sheet->mergeCells('A3:O3');
    
                    // Tambahkan Border untuk seluruh data (mulai baris 5 sampai data terakhir)
                    $highestRow = $event->sheet->getHighestRow();
                    $event->sheet->getStyle('A5:O' . $highestRow)->applyFromArray([
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