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
use Maatwebsite\Excel\Concerns\WithChunkReading; // 1. Tambahkan Import ini
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

// 2. Tambahkan 'WithChunkReading' di sini
class PosisiPerbaikanExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents, WithChunkReading
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

        // 3. PENTING: Bypass limit waktu eksekusi PHP (0 = unlimited)
        // Chunking menghemat RAM, tapi proses tetap butuh waktu jika data banyak.
        // Ini mencegah error "Maximum execution time of 60 seconds exceeded".
        set_time_limit(0); 
        ini_set('memory_limit', '512M'); // Opsional: Jaga-jaga buffer memory
    }

    public function query()
    {
        // Pastikan query builder dikembalikan, bukan hasil get()
        $query =  DB::table('v_posisi_perbaikan as k')
        ->where('k.kode_cabang', $this->cabang['kode']);

        if ($this->params['kode_spk']) {
            $query->where('k.kode_spk', 'like', '%' . $this->params['kode_spk'] . '%');
        }
        if ($this->params['no_polisi']) {
            $query->where('k.no_polisi', 'like', '%' . $this->params['no_polisi'] . '%');
        }
        if ($this->params['tgl_masuk_awal']) {
            $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_masuk_awal'], 'Asia/Jakarta')->format('Y-m-d');
            $query->whereDate('k.tgl_masuk', '>=', $startDate);
        }
        if ($this->params['tgl_masuk_akhir']) {
            $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_masuk_akhir'], 'Asia/Jakarta')->format('Y-m-d');
            $query->whereDate('k.tgl_masuk', '<=', $endDate);
        }
        if ($this->params['nama_pelanggan']) {
            $query->where('k.nama_pelanggan', 'like', '%' . $this->params['nama_pelanggan'] . '%');
        }
        if ($this->params['nama_pemilik']) {
            $query->where('k.pemilik', 'like', '%' . $this->params['nama_pemilik'] . '%');
        }

        $query = $query->select('k.*')->orderBy('k.tgl_masuk', 'desc');

        return $query;

        // return DB::table('v_posisi_perbaikan as k')
        //     ->where('k.kode_cabang', $this->cabang['kode'])
        //     ->select('k.*') // Disarankan select spesifik atau wildcard
        //     ->orderBy('k.tgl_masuk', 'desc');
    }

    // 4. Method Baru: Tentukan jumlah baris per proses (Chunking)
    public function chunkSize(): int
    {
        // Proses 1.000 baris per batch agar RAM server tidak penuh
        // Jika server kuat, bisa dinaikkan ke 2000 atau 5000
        return 1000; 
    }

    public function headings(): array
    {
        return [
            ['Laporan Posisi Perbaikan di Lapangan'],
            ['Cabang : ' . $this->cabang['nama']],
            ['Periode : ' . $this->periode],
            [''],
            [
                'No',
                'No. SPK',
                'Nama Pemilik',
                'Nama Asuransi',
                'No. Polisi',
                'Tipe Kendaraan',
                'Tgl Turun Lap.',
                'Tgl Rencana Selesai',
                'Bongkar',
                'Las',
                'Dempul',
                'Mixing',
                'Cat',
                'Poles',
                'Finishing',
            ]
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        
        return [
            $this->rowNumber,
            $row->kode_spk,
            $row->pemilik,
            $row->nama_pelanggan,
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
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->mergeCells('A1:O1');
                $event->sheet->mergeCells('A2:O2');
                $event->sheet->mergeCells('A3:O3');

                $highestRow = $event->sheet->getHighestRow();
                
                // Cek agar tidak error jika data kosong (hanya header)
                if ($highestRow >= 5) {
                    $event->sheet->getStyle('A5:O' . $highestRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);
                    
                    $event->sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}