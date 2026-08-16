<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray; // Ganti FromQuery menjadi FromArray
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class LaporanMobilMasukExport implements FromArray, ShouldAutoSize, WithStyles, WithEvents
{
    protected $params;
    protected $cabang;
    protected $periode;
    
    // Variabel untuk menyimpan hasil olahan data
    protected $dataMatrix = [];
    protected $columnDates = [];
    protected $totalPerDate = [];
    protected $rowCount = 0;
    protected $colCount = 0;

    public function __construct(array $params, $cabang, $periode)
    {
        $this->params = $params;
        $this->cabang = $cabang;
        $this->periode = $periode;

        // Jalankan logika pengolahan data saat class di-inisiasi
        $this->processData();
    }

    /**
     * Logika utama yang disalin dan disesuaikan dari getRepMobilMasuk Controller
     */
    private function processData()
    {
        // 1. Base Query
        $query = DB::table('v_rep_mobil_masuk as k')
            ->where('k.kode_cabang', $this->cabang['kode']); // Sesuaikan akses array/object

        // 2. Filtering Logic
        if (!empty($this->params['jenis_laporan'])) {
            if ($this->params['jenis_laporan'] == "periode") {
                if (!empty($this->params['tgl_awal'])) {
                    $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
                    $query->whereDate('k.tgl_masuk', '>=', $startDate);
                }
                if (!empty($this->params['tgl_akhir'])) {
                    $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
                    $query->whereDate('k.tgl_masuk', '<=', $endDate);
                }
            } elseif ($this->params['jenis_laporan'] == "bulan") {
                if (!empty($this->params['bulan'])) {
                    $query->whereMonth('k.tgl_masuk', $this->params['bulan']);
                    $query->whereYear('k.tgl_masuk', $this->params['tahun2']);
                }
            } elseif ($this->params['jenis_laporan'] == "tahun") {
                if (!empty($this->params['tahun'])) {
                    $query->whereYear('k.tgl_masuk', $this->params['tahun']);
                }
            }
        }

        // 3. Select & Grouping (Sama seperti Controller)
        if (isset($this->params['jenis_laporan']) && $this->params['jenis_laporan'] == "tahun") {
            $datas = $query
                ->select([
                    'k.nama_pelanggan',
                    DB::raw("DATE_FORMAT(k.tgl_masuk,'%Y-%m-01') as tgl_masuk"),
                    DB::raw("count(1) as jum")
                ])
                ->groupBy([
                    DB::raw("DATE_FORMAT(k.tgl_masuk,'%Y-%m-01')"),
                    'k.nama_pelanggan'
                ])
                ->orderBy('k.nama_pelanggan', 'asc')
                ->orderBy('k.tgl_masuk', 'asc')
                ->get();
        } else {
            $datas = $query
                ->select([
                    'k.nama_pelanggan',
                    DB::raw("DATE_FORMAT(k.tgl_masuk,'%Y-%m-%d') as tgl_masuk"),
                    DB::raw("count(1) as jum")
                ])
                ->groupBy([
                    DB::raw("DATE_FORMAT(k.tgl_masuk,'%Y-%m-%d')"),
                    'k.nama_pelanggan'
                ])
                ->orderBy('k.nama_pelanggan', 'asc')
                ->orderBy('k.tgl_masuk', 'asc')
                ->get();
        }

        // 4. Pivoting Data (Mengubah menjadi Matrix)
        $tmp = [];
        $fields = []; // Ini akan jadi header tanggal (Kolom)
        $total = [];  // Ini total bawah

        foreach ($datas as $row) {
            // Format Tanggal untuk Header
            if (isset($this->params['jenis_laporan']) && $this->params['jenis_laporan'] == "tahun") {
                $rawTgl = $row->tgl_masuk;
                $displayTgl = blank($rawTgl) ? '' : date("M Y", strtotime($rawTgl)); // Format: Jan 2024
            } else {
                $rawTgl = $row->tgl_masuk;
                $displayTgl = blank($rawTgl) ? '' : date("d/m/Y", strtotime($rawTgl)); // Format: 01/01/2024
            }

            // Simpan daftar kolom unik
            $fields[$rawTgl] = $displayTgl; 

            // Simpan data per Pelanggan dan Tanggal
            $tmp[$row->nama_pelanggan][$rawTgl] = $row->jum;

            // Hitung Total Per Kolom (Tanggal)
            if (isset($total[$rawTgl])) {
                $total[$rawTgl] += $row->jum;
            } else {
                $total[$rawTgl] = $row->jum;
            }
        }

        // Sortir Tanggal agar urut
        ksort($fields);
        ksort($total);

        $this->columnDates = $fields;
        $this->dataMatrix = $tmp;
        $this->totalPerDate = $total;
        
        // Hitung jumlah kolom dinamis (Kolom Nama + Jumlah Tanggal)
        $this->colCount = count($fields) + 1; 
    }

    public function array(): array
    {
        $output = [];

        // --- BAGIAN HEADER LAPORAN ---
        $output[] = ['Laporan Mobil Masuk per Asuransi'];
        $output[] = ['Cabang : ' . $this->cabang['nama']];
        $output[] = ['Periode : ' . $this->periode];
        $output[] = ['']; // Spacer

        // --- BAGIAN HEADER TABEL (Baris 5) ---
        $headerRow = ['Nama Asuransi']; // Kolom pertama
        foreach ($this->columnDates as $dateKey => $dateLabel) {
            $headerRow[] = $dateLabel;
        }
        $headerRow[] = 'Total'; // Kolom Total per Baris (Opsional, tapi bagus ada)
        $output[] = $headerRow;

        // --- BAGIAN ISI DATA ---
        if (empty($this->dataMatrix)) {
            $output[] = ['Data Tidak Ditemukan'];
        } else {
            foreach ($this->dataMatrix as $pelanggan => $datesData) {
                $row = [];
                $row[] = $pelanggan; // Kolom 1: Nama Pelanggan
                
                $rowTotal = 0;
                // Loop sesuai kolom header agar urutan tanggal konsisten
                foreach ($this->columnDates as $dateKey => $dateLabel) {
                    $val = isset($datesData[$dateKey]) ? $datesData[$dateKey] : 0; // Jika kosong isi 0
                    $row[] = $val == 0 ? '0' : $val; // Kosongkan jika 0 agar bersih, atau biarkan 0
                    $rowTotal += $val;
                }
                
                $row[] = $rowTotal; // Kolom terakhir di baris ini (Total per Pelanggan)
                $output[] = $row;
            }

            // --- BAGIAN FOOTER (TOTAL BAWAH) ---
            $footerRow = ['GRAND TOTAL'];
            $grandTotalAll = 0;
            foreach ($this->columnDates as $dateKey => $dateLabel) {
                $val = isset($this->totalPerDate[$dateKey]) ? $this->totalPerDate[$dateKey] : 0;
                $footerRow[] = $val;
                $grandTotalAll += $val;
            }
            $footerRow[] = $grandTotalAll; // Total pojok kanan bawah
            $output[] = $footerRow;
        }

        // Update row count untuk keperluan styling
        $this->rowCount = count($output);
        // Update col count (karena ada tambahan kolom Total di kanan)
        $this->colCount = count($headerRow);

        return $output;
    }

    // Styling
    public function styles(Worksheet $sheet)
    {
        // Konversi angka ke Huruf Kolom Excel (misal 1 -> A, 27 -> AA)
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($this->colCount);

        return [
            // Baris 1 (Judul Utama)
            1 => ['font' => ['bold' => true, 'size' => 14]],
            // Baris 2 & 3 (Info)
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            // Baris 5 (Header Tabel)
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
            // Baris Terakhir (Grand Total) - Bold
            $this->rowCount => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFEFEFEF'],
                ],
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Hitung Huruf Kolom Terakhir secara dinamis
                $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($this->colCount);
                
                // Merge Cell Judul (A1 sampai kolom terakhir)
                $event->sheet->mergeCells("A1:{$lastColLetter}1");
                $event->sheet->mergeCells("A2:{$lastColLetter}2");
                $event->sheet->mergeCells("A3:{$lastColLetter}3");

                // Border untuk seluruh area tabel (mulai baris 5 sampai baris terakhir)
                $rangeData = "A5:{$lastColLetter}" . $this->rowCount;
                $event->sheet->getStyle($rangeData)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Alignment Center untuk data angka (Kolom B sampai kolom terakhir)
                $rangeAngka = "B6:{$lastColLetter}" . $this->rowCount;
                $event->sheet->getStyle($rangeAngka)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}