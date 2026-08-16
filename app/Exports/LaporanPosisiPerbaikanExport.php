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

class LaporanPosisiPerbaikanExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
        
        // Query sama seperti sebelumnya
        $query = DB::table('v_posisi_turun_lapangan as k')
        ->where('k.kode_cabang', $this->cabang['kode']) // Sesuaikan jika ini array/string
        ->select([
            'k.kode_spk',
            'k.no_polisi',
            'k.merek_tipe',
            'k.tgl_turun_lapangan',
            'k.tgl_rencana_selesai',
            'k.tgl_bongkar2',
            'k.tgl_las2',
            'k.tgl_dempul2',
            'k.tgl_mixing2',
            'k.tgl_cat2',
            'k.tgl_poles2',
            'k.tgl_finishing2',
            'k.nama_pelanggan',
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

        return $query;
    }

    // Sesuaikan Header dengan layout Excel yang diminta
    public function headings(): array
    {
        return [
            ['Laporan Posisi Perbaikan di Lapangan'],                                // Baris 1: Judul
            ['Cabang : ' . $this->cabang['nama']],         // Baris 2: Nama Bengkel
            ['Periode : ' . $this->periode],                // Baris 3: Periode
            [''],                                           // Baris 4: Kosong (Spacer)
            [                                               // Baris 5: Header Tabel
                'No',
                'No. SPK',
                'No. Polisi',
                'Tipe Kendaraan',
                'Tanggal Turun Lap.',
                'Rencana Selesai',
                'Bongkar',
                'Las',
                'Dempul',
                'Mixing',
                'Cat',
                'Poles',
                'Finishing',
                'Nama Asuransi',
            ]
        ];
    }

    public function map($row): array
    {
        // Increment nomor urut setiap baris data
        $this->rowNumber++;

        return [
            $this->rowNumber, // Kolom No
            $row->kode_spk,
            $row->no_polisi,
            $row->merek_tipe,
            $row->tgl_turun_lapangan ? date("d/m/Y", strtotime($row->tgl_turun_lapangan)) : '',
            $row->tgl_rencana_selesai ? date("d/m/Y", strtotime($row->tgl_rencana_selesai)) : '',
            $row->tgl_bongkar2 ? date("d/m/Y", strtotime($row->tgl_bongkar2)) : '',
            $row->tgl_las2 ? date("d/m/Y", strtotime($row->tgl_las2)) : '',
            $row->tgl_dempul2 ? date("d/m/Y", strtotime($row->tgl_dempul2)) : '',
            $row->tgl_mixing2 ? date("d/m/Y", strtotime($row->tgl_mixing2)) : '',
            $row->tgl_cat2 ? date("d/m/Y", strtotime($row->tgl_cat2)) : '',
            $row->tgl_poles2 ? date("d/m/Y", strtotime($row->tgl_poles2)) : '',
            $row->tgl_finishing2 ? date("d/m/Y", strtotime($row->tgl_finishing2)) : '',
            $row->nama_pelanggan,
        ];
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
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Merge Cell A1 sampai N1 (sesuai jumlah kolom) untuk Judul
                $event->sheet->mergeCells('A1:N1');
                
                // Merge Cell A2 sampai N2 untuk Bengkel
                $event->sheet->mergeCells('A2:N2');
                
                // Merge Cell A3 sampai N3 untuk Periode
                $event->sheet->mergeCells('A3:N3');

                // Tambahkan Border untuk seluruh data (mulai baris 5 sampai data terakhir)
                $highestRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle('A5:N' . $highestRow)->applyFromArray([
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