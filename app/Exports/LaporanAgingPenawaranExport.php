<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents; // Tambahan untuk event merging
use Maatwebsite\Excel\Events\AfterSheet;   // Tambahan untuk event merging
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Carbon\Carbon;

// class LaporanAgingPenawaranExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
class LaporanAgingPenawaranExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents, WithCustomValueBinder
{
    protected $params;
    protected $cabang;
    protected $periode;
    protected $rowNumber = 0; // Untuk nomor urut manual
    protected $grandTotals = [];

    protected $asuransiRows = [];
    protected $mingguRows = [];
    protected $headerRows = [];
    protected $subTotalRows = [];
    protected $totalPelangganRows = [];

    // Terima parameter tambahan untuk Header Laporan
    public function __construct(array $params, $cabang, $periode)
    {
        $this->params = $params;
        $this->cabang = $cabang;
        $this->periode = $periode;
    }

    public function collection()
    {
        $datas = [];
        if($this->params['jenis_laporan'] == "rekap") {
          $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');

          $results = DB::select('CALL up_apl_rep_os_penawaran_rkp(?, ?)', [
            $this->cabang['kode'],
            $startDate,
          ]);

          $temp = [];
          $grandTotalUnit = 0;
          $grandTotalNilai = 0;

          foreach ($results as $row) {
            $grandTotalUnit += $row->unit_total;
            $grandTotalNilai += $row->nilai_total;

            $temp[] = [
              'nama_pelanggan' => $row->nama_pelanggan ?? '',
              'nama_cabang' => $row->nama_cabang ?? '',
              'unit_1_2' => $row->unit_1_2 ?? 0,
              'nilai_1_2' => $row->nilai_1_2 ?? 0,
              'unit_3_5' => $row->unit_3_5 ?? 0,
              'nilai_3_5' => $row->nilai_3_5 ?? 0,
              'unit_5' => $row->unit_5 ?? 0,
              'nilai_5' => $row->nilai_5 ?? 0,
              'unit_blm_dikirim' => $row->unit_blm_dikirim ?? 0,
              'nilai_blm_dikirim' => $row->nilai_blm_dikirim ?? 0,
              'unit_total' => $row->unit_total ?? 0,
              'nilai_total' => $row->nilai_total ?? 0,
            ];
          }

          $tot_unit_1_2 = 0;
          $tot_unit_1_2_persen = 0;
          $tot_nilai_1_2 = 0;
          $tot_nilai_1_2_persen = 0;
          $tot_unit_3_5 = 0;
          $tot_unit_3_5_persen = 0;
          $tot_nilai_3_5 = 0;
          $tot_nilai_3_5_persen = 0;
          $tot_unit_5 = 0;
          $tot_unit_5_persen = 0;
          $tot_nilai_5 = 0;
          $tot_nilai_5_persen = 0;
          $tot_unit_blm_dikirim = 0;
          $tot_unit_blm_dikirim_persen = 0;
          $tot_nilai_blm_dikirim = 0;
          $tot_nilai_blm_dikirim_persen = 0;
          $tot_unit_total = 0;
          $tot_unit_total_persen = 0;
          $tot_nilai_total = 0;
          $tot_nilai_total_persen = 0;

          $fake = 0; //$start;
          foreach ($temp as $row) {

            $unit_1_2_persen = ($grandTotalUnit > 0) ? (($row['unit_1_2'] / $grandTotalUnit) * 100) : 0;
            $nilai_1_2_persen = ($grandTotalNilai > 0) ? (($row['nilai_1_2'] / $grandTotalNilai) * 100) : 0;
            $unit_3_5_persen = ($grandTotalUnit > 0) ? (($row['unit_3_5'] / $grandTotalUnit) * 100) : 0;
            $nilai_3_5_persen = ($grandTotalNilai > 0) ? (($row['nilai_3_5'] / $grandTotalNilai) * 100) : 0;
            $unit_5_persen = ($grandTotalUnit > 0) ? (($row['unit_5'] / $grandTotalUnit) * 100) : 0;
            $nilai_5_persen = ($grandTotalNilai > 0) ? (($row['nilai_5'] / $grandTotalNilai) * 100) : 0;
            $unit_blm_dikirim_persen = ($grandTotalUnit > 0) ? (($row['unit_blm_dikirim'] / $grandTotalUnit) * 100) : 0;
            $nilai_blm_dikirim_persen = ($grandTotalNilai > 0) ? (($row['nilai_blm_dikirim'] / $grandTotalNilai) * 100) : 0;
            $unit_total_persen = ($grandTotalUnit > 0) ? (($row['unit_total'] / $grandTotalUnit) * 100) : 0;
            $nilai_total_persen = ($grandTotalNilai > 0) ? (($row['nilai_total'] / $grandTotalNilai) * 100) : 0;

            $tot_unit_1_2 += $row['unit_1_2'];
            $tot_unit_1_2_persen += $unit_1_2_persen;
            $tot_nilai_1_2 += $row['nilai_1_2'];
            $tot_nilai_1_2_persen += $nilai_1_2_persen;
            $tot_unit_3_5 += $row['unit_3_5'];
            $tot_unit_3_5_persen += $unit_3_5_persen;
            $tot_nilai_3_5 += $row['nilai_3_5'];
            $tot_nilai_3_5_persen += $nilai_3_5_persen;
            $tot_unit_5 += $row['unit_5'];
            $tot_unit_5_persen += $unit_5_persen;
            $tot_nilai_5 += $row['nilai_5'];
            $tot_nilai_5_persen += $nilai_5_persen;
            $tot_unit_blm_dikirim += $row['unit_blm_dikirim'];
            $tot_unit_blm_dikirim_persen += $unit_blm_dikirim_persen;
            $tot_nilai_blm_dikirim += $row['nilai_blm_dikirim'];
            $tot_nilai_blm_dikirim_persen += $nilai_blm_dikirim_persen;
            $tot_unit_total += $row['unit_total'];
            $tot_unit_total_persen += $unit_total_persen;
            $tot_nilai_total += $row['nilai_total'];
            $tot_nilai_total_persen += $nilai_total_persen;

            $datas[] = [
              'no' => ++$fake,
              'nama_pelanggan' => $row['nama_pelanggan'],
              'nama_cabang' => $row['nama_cabang'],
              'unit_1_2' => $row['unit_1_2'],
              'unit_1_2_persen' => $unit_1_2_persen,
              'nilai_1_2' => $row['nilai_1_2'],
              'nilai_1_2_persen' => $nilai_1_2_persen,
              'unit_3_5' => $row['unit_3_5'],
              'unit_3_5_persen' => $unit_3_5_persen,
              'nilai_3_5' => $row['nilai_3_5'],
              'nilai_3_5_persen' => $nilai_3_5_persen,
              'unit_5' => $row['unit_5'],
              'unit_5_persen' => $unit_5_persen,
              'nilai_5' => $row['nilai_5'],
              'nilai_5_persen' => $nilai_5_persen,
              'unit_blm_dikirim' => $row['unit_blm_dikirim'],
              'unit_blm_dikirim_persen' => $unit_blm_dikirim_persen,
              'nilai_blm_dikirim' => $row['nilai_blm_dikirim'],
              'nilai_blm_dikirim_persen' => $nilai_blm_dikirim_persen,
              'unit_total' => $row['unit_total'],
              'unit_total_persen' => $unit_total_persen,
              'nilai_total' => $row['nilai_total'],
              'nilai_total_persen' => $nilai_total_persen,
            ];
            
          }

          $this->grandTotals = [
            'unit_1_2' => $tot_unit_1_2,
            'unit_1_2_persen' => number_format($tot_unit_1_2_persen,2,".",","),
            'nilai_1_2' => $tot_nilai_1_2,
            'nilai_1_2_persen' => number_format($tot_nilai_1_2_persen,2,".",","),
            'unit_3_5' => $tot_unit_3_5,
            'unit_3_5_persen' => number_format($tot_unit_3_5_persen,2,".",","),
            'nilai_3_5' => $tot_nilai_3_5,
            'nilai_3_5_persen' => number_format($tot_nilai_3_5_persen,2,".",","),
            'unit_5' => $tot_unit_5,
            'unit_5_persen' => number_format($tot_unit_5_persen,2,".",","),
            'nilai_5' => $tot_nilai_5,
            'nilai_5_persen' => number_format($tot_nilai_5_persen,2,".",","),
            'unit_blm_dikirim' => $tot_unit_blm_dikirim,
            'unit_blm_dikirim_persen' => number_format($tot_unit_blm_dikirim_persen,2,".",","),
            'nilai_blm_dikirim' => $tot_nilai_blm_dikirim,
            'nilai_blm_dikirim_persen' => number_format($tot_nilai_blm_dikirim_persen,2,".",","),
            'unit_total' => $tot_unit_total,
            'unit_total_persen' => number_format($tot_unit_total_persen,2,".",","),
            'nilai_total' => $tot_nilai_total,
            'nilai_total_persen' => number_format($tot_nilai_total_persen,2,".",","),
          ];

        } elseif($this->params['jenis_laporan'] == "rinci") {
            $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');

            $results = DB::select('CALL up_apl_rep_os_penawaran_dtl(?, ?, ?)', [
              $this->cabang['kode'],
              $startDate,
              'tanggal',
            ]);

            // $datas = [];
            // foreach ($results as $row) {
            //     $nama_pelanggan = $row->nama_pelanggan ?? '';
            //     $minggu = $row->minggu ?? '';
                
            //     if($nama_pelanggan && $minggu) {
            //         $datas[$nama_pelanggan][$minggu][] = (array) $row;
            //     }
            // }

            $grouped = collect($results)->groupBy('nama_pelanggan');

            $exportData = [];
            $rowNum = 1; // Pelacak baris Excel saat ini

            // --- HEADER DOKUMEN ---
            $exportData[] = ["Laporan Rincian Estimasi Belum Ditawar [Outstanding Penawaran]"];
            $exportData[] = ["Cabang : " . ($this->cabang['nama'] ?? 'Semua Cabang')];
            $exportData[] = ["Periode : " . $this->periode];
            $exportData[] = [""];
            $rowNum += 4;

            foreach ($grouped as $asuransi => $asuransiGroup) {
            
                // Cetak Baris Nama Asuransi
                $exportData[] = [$asuransi];
                $this->asuransiRows[] = $rowNum++;
                
                // Grouping Level 2 (Berdasarkan Minggu/Keterangan)
                $mingguGrouped = $asuransiGroup->groupBy('minggu');
                
                $totalPelangganUnit = 0;
                $totalPelangganNominal = 0;

                foreach ($mingguGrouped as $minggu => $items) {
                    
                    // Cetak Baris Keterangan Minggu
                    $exportData[] = [$minggu];
                    $this->mingguRows[] = $rowNum++;
                    
                    $exportData[] = [""]; // Baris kosong sebelum tabel
                    $rowNum++;
                    
                    // Cetak Baris Header Tabel
                    $exportData[] = [
                        "No", "Tanggal Estimasi", "No. Estimasi", "No. SPK", "No. Polisi", 
                        "Merek Tipe", "No. Klaim", "No. Polis / No. Tiket", "Tertanggung", "Total", 
                        "Tanggal Kirim", "Hari", "No. Keluar"
                    ];
                    $this->headerRows[] = $rowNum++;

                    $no = 1;
                    $subTotalUnit = 0;
                    $subTotalNominal = 0;

                    // Looping Data Asli
                    foreach ($items as $item) {
                        $exportData[] = [
                            $no++,
                            $item->tanggal ? date("d/m/Y", strtotime($item->tanggal)) : '',
                            $item->kode_estimasi,
                            $item->kode_spk,
                            $item->no_polisi,
                            $item->merek_tipe,
                            $item->kode_claim,
                            $item->no_polis,
                            $item->tertanggung,
                            $item->total,
                            $item->tgl_pengiriman ? date("d/m/Y", strtotime($item->tgl_pengiriman)) : '',
                            $item->hari,
                            $item->kode_keluar
                        ];
                        $rowNum++;
                        $subTotalUnit++;
                        $subTotalNominal += (float) $item->total;
                    }

                    // Cetak Baris Sub Total
                    $exportData[] = [
                        "", "", "", "", "", "", "", "Sub Total", $subTotalUnit . " Unit", $subTotalNominal, "", "", ""
                    ];
                    $this->subTotalRows[] = $rowNum++;
                    
                    $exportData[] = [""]; // Baris kosong setelah subtotal
                    $rowNum++;

                    // Akumulasikan ke Total Pelanggan
                    $totalPelangganUnit += $subTotalUnit;
                    $totalPelangganNominal += $subTotalNominal;
                }

                // Cetak Baris Total Per Pelanggan
                $exportData[] = [
                    "", "", "", "", "", "", "", "Total Per Pelanggan", $totalPelangganUnit . " Unit", $totalPelangganNominal, "", "", ""
                ];
                $this->totalPelangganRows[] = $rowNum++;
                
                $exportData[] = [""]; // Baris kosong antar asuransi
                $rowNum++;
            }

            return collect($exportData);
        }

        return collect($datas);
    }

    // Sesuaikan Header dengan layout Excel yang diminta
    public function headings(): array
    {
        if($this->params['jenis_laporan'] == "rekap") {
            return [
                ['Laporan Rekap Estimasi Belum Ditawar [Outstanding Penawaran]'],                                // Baris 1: Judul
                ['Cabang : ' . $this->cabang['nama']],         // Baris 2: Nama Bengkel
                ['Periode : ' . $this->periode],                // Baris 3: Periode
                [''],                                           // Baris 4: Kosong (Spacer)
                [
                  'No',
                  'Nama Asuransi',
                  '1 - 2 Minggu',
                  '',
                  '',
                  '',
                  '3 - 5 Minggu',
                  '',
                  '',
                  '',
                  '> 5 Minggu',
                  '',
                  '',
                  '',
                  'Belum Diterima',
                  '',
                  '',
                  '',
                  'Total',
                  '',
                  '',
                  ''
                ],
                [
                  '',
                  '',
                  'Unit',
                  '%',
                  'Rupiah',
                  '%',
                  'Unit',
                  '%',
                  'Rupiah',
                  '%',
                  'Unit',
                  '%',
                  'Rupiah',
                  '%',
                  'Unit',
                  '%',
                  'Rupiah',
                  '%',
                  'Unit',
                  '%',
                  'Rupiah',
                  '%'
                ]
            ];
        } elseif($this->params['jenis_laporan'] == "rinci") {
            return [];
        }
    }

    public function map($row): array
    {
        // Increment nomor urut setiap baris data
        $this->rowNumber++;

        if($this->params['jenis_laporan'] == "rekap") {
          $row = (object) $row;
          return [
              $this->rowNumber, // Kolom No
              $row->nama_pelanggan,
              $row->unit_1_2,
              number_format($row->unit_1_2_persen,2,".",","),
              $row->nilai_1_2,
              number_format($row->nilai_1_2_persen,2,".",","),
              $row->unit_3_5,
              number_format($row->unit_3_5_persen,2,".",","),
              $row->nilai_3_5,
              number_format($row->nilai_3_5_persen,2,".",","),
              $row->unit_5,
              number_format($row->unit_5_persen,2,".",","),
              $row->nilai_5,
              number_format($row->nilai_5_persen,2,".",","),
              $row->unit_blm_dikirim,
              number_format($row->unit_blm_dikirim_persen,2,".",","),
              $row->nilai_blm_dikirim,
              number_format($row->nilai_blm_dikirim_persen,2,".",","),
              $row->unit_total,
              number_format($row->unit_total_persen,2,".",","),
              $row->nilai_total,
              number_format($row->nilai_total_persen,2,".",","),
          ];
        } elseif($this->params['jenis_laporan'] == "rinci") {
            return $row;
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
        if($this->params['jenis_laporan'] == "rekap") {
            return [
                AfterSheet::class => function(AfterSheet $event) {
                    // Merge Cell A1 sampai O1 (sesuai jumlah kolom) untuk Judul
                    $event->sheet->mergeCells('A1:V1');
                    
                    // Merge Cell A2 sampai O2 untuk Bengkel
                    $event->sheet->mergeCells('A2:V2');
                    
                    // Merge Cell A3 sampai O3 untuk Periode
                    $event->sheet->mergeCells('A3:V3');

                    // Merge Cell Kolom Header
                    $event->sheet->mergeCells('A5:A6');
                    $event->sheet->mergeCells('B5:B6');
                    $event->sheet->mergeCells('C5:F5');
                    $event->sheet->mergeCells('G5:J5');
                    $event->sheet->mergeCells('K5:N5');
                    $event->sheet->mergeCells('O5:R5');
                    $event->sheet->mergeCells('S5:V5');
    
                    // Tambahkan Border untuk seluruh data (mulai baris 5 sampai data terakhir)
                    $highestRow = $event->sheet->getHighestRow();
                    $totalRow = $highestRow + 1;
                    $event->sheet->getStyle('A5:V' . $highestRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                    
                    // Set Kolom No (A) alignment Center
                    $event->sheet->getStyle('A7:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Grand Total
                    $event->sheet->mergeCells("A{$totalRow}:B{$totalRow}");
                    $event->sheet->setCellValue("A{$totalRow}", 'Grand Total');

                    $event->sheet->setCellValue("C{$totalRow}", $this->grandTotals['unit_1_2']);
                    $event->sheet->setCellValue("D{$totalRow}", $this->grandTotals['unit_1_2_persen']);
                    $event->sheet->setCellValue("E{$totalRow}", $this->grandTotals['nilai_1_2']);
                    $event->sheet->setCellValue("F{$totalRow}", $this->grandTotals['nilai_1_2_persen']);
                    $event->sheet->setCellValue("G{$totalRow}", $this->grandTotals['unit_3_5']);
                    $event->sheet->setCellValue("H{$totalRow}", $this->grandTotals['unit_3_5_persen']);
                    $event->sheet->setCellValue("I{$totalRow}", $this->grandTotals['nilai_3_5']);
                    $event->sheet->setCellValue("J{$totalRow}", $this->grandTotals['nilai_3_5_persen']);
                    $event->sheet->setCellValue("K{$totalRow}", $this->grandTotals['unit_5']);
                    $event->sheet->setCellValue("L{$totalRow}", $this->grandTotals['unit_5_persen']);
                    $event->sheet->setCellValue("M{$totalRow}", $this->grandTotals['nilai_5']);
                    $event->sheet->setCellValue("N{$totalRow}", $this->grandTotals['nilai_5_persen']);
                    $event->sheet->setCellValue("O{$totalRow}", $this->grandTotals['unit_blm_dikirim']);
                    $event->sheet->setCellValue("P{$totalRow}", $this->grandTotals['unit_blm_dikirim_persen']);
                    $event->sheet->setCellValue("Q{$totalRow}", $this->grandTotals['nilai_blm_dikirim']);
                    $event->sheet->setCellValue("R{$totalRow}", $this->grandTotals['nilai_blm_dikirim_persen']);
                    $event->sheet->setCellValue("S{$totalRow}", $this->grandTotals['unit_total']);
                    $event->sheet->setCellValue("T{$totalRow}", $this->grandTotals['unit_total_persen']);
                    $event->sheet->setCellValue("U{$totalRow}", $this->grandTotals['nilai_total']);
                    $event->sheet->setCellValue("V{$totalRow}", $this->grandTotals['nilai_total_persen']);

                    // Berikan styling tebal (Bold) dan Border pada baris Grand Total
                    $styleArray = [
                      'font' => ['bold' => true],
                      'borders' => [
                          'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                      ],
                      'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ];
                    $event->sheet->getStyle("A{$totalRow}:V{$totalRow}")->applyFromArray($styleArray);

                    // Ketentuan Persentase
                    $totalRow++;
                    $event->sheet->mergeCells("A{$totalRow}:B{$totalRow}");
                    $event->sheet->setCellValue("A{$totalRow}", 'Ketentuan Persentase');

                    $event->sheet->mergeCells("C{$totalRow}:D{$totalRow}");
                    $event->sheet->mergeCells("E{$totalRow}:F{$totalRow}");
                    $event->sheet->mergeCells("G{$totalRow}:H{$totalRow}");
                    $event->sheet->mergeCells("I{$totalRow}:J{$totalRow}");
                    $event->sheet->mergeCells("K{$totalRow}:L{$totalRow}");
                    $event->sheet->mergeCells("M{$totalRow}:N{$totalRow}");
                    $event->sheet->mergeCells("O{$totalRow}:P{$totalRow}");
                    $event->sheet->mergeCells("Q{$totalRow}:R{$totalRow}");
                    $event->sheet->mergeCells("S{$totalRow}:T{$totalRow}");
                    $event->sheet->mergeCells("U{$totalRow}:V{$totalRow}");

                    $event->sheet->setCellValue("C{$totalRow}", 30);
                    $event->sheet->setCellValue("E{$totalRow}", 30);
                    $event->sheet->setCellValue("G{$totalRow}", 60);
                    $event->sheet->setCellValue("I{$totalRow}", 60);
                    $event->sheet->setCellValue("K{$totalRow}", 10);
                    $event->sheet->setCellValue("M{$totalRow}", 10);
                    $event->sheet->setCellValue("O{$totalRow}", 0);
                    $event->sheet->setCellValue("Q{$totalRow}", 0);
                    $event->sheet->setCellValue("S{$totalRow}", 100);
                    $event->sheet->setCellValue("U{$totalRow}", 100);

                    // Berikan styling tebal (Bold) dan Border pada baris Grand Total
                    $styleArray = [
                      'font' => ['bold' => true],
                      'borders' => [
                          'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                      ],
                      'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ];
                    $event->sheet->getStyle("A{$totalRow}:V{$totalRow}")->applyFromArray($styleArray);

                    // Lebih Kurang dari Ketentuan
                    $totalRow++;
                    $event->sheet->mergeCells("A{$totalRow}:B{$totalRow}");
                    $event->sheet->setCellValue("A{$totalRow}", 'Lebih Kurang dari Ketentuan');

                    $event->sheet->mergeCells("C{$totalRow}:D{$totalRow}");
                    $event->sheet->mergeCells("E{$totalRow}:F{$totalRow}");
                    $event->sheet->mergeCells("G{$totalRow}:H{$totalRow}");
                    $event->sheet->mergeCells("I{$totalRow}:J{$totalRow}");
                    $event->sheet->mergeCells("K{$totalRow}:L{$totalRow}");
                    $event->sheet->mergeCells("M{$totalRow}:N{$totalRow}");
                    $event->sheet->mergeCells("O{$totalRow}:P{$totalRow}");
                    $event->sheet->mergeCells("Q{$totalRow}:R{$totalRow}");
                    $event->sheet->mergeCells("S{$totalRow}:T{$totalRow}");
                    $event->sheet->mergeCells("U{$totalRow}:V{$totalRow}");

                    $event->sheet->setCellValue("C{$totalRow}", $this->grandTotals['unit_1_2_persen'] - 30);
                    $event->sheet->setCellValue("E{$totalRow}", $this->grandTotals['nilai_1_2_persen'] - 30);
                    $event->sheet->setCellValue("G{$totalRow}", $this->grandTotals['unit_3_5_persen'] - 60);
                    $event->sheet->setCellValue("I{$totalRow}", $this->grandTotals['nilai_3_5_persen'] - 60);
                    $event->sheet->setCellValue("K{$totalRow}", $this->grandTotals['unit_5_persen'] - 10);
                    $event->sheet->setCellValue("M{$totalRow}", $this->grandTotals['nilai_5_persen'] - 10);
                    $event->sheet->setCellValue("O{$totalRow}", $this->grandTotals['unit_blm_dikirim_persen'] - 0);
                    $event->sheet->setCellValue("Q{$totalRow}", $this->grandTotals['nilai_blm_dikirim_persen'] - 0);
                    $event->sheet->setCellValue("S{$totalRow}", $this->grandTotals['unit_total_persen'] - 100);
                    $event->sheet->setCellValue("U{$totalRow}", $this->grandTotals['nilai_total_persen'] - 100);

                    // Berikan styling tebal (Bold) dan Border pada baris Grand Total
                    $styleArray = [
                      'font' => ['bold' => true],
                      'borders' => [
                          'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                      ],
                      'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ];
                    $event->sheet->getStyle("A{$totalRow}:V{$totalRow}")->applyFromArray($styleArray);

                    // Format angka untuk kolom D sampai N (format ribuan)
                    $event->sheet->getStyle('C7:C' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
                    $event->sheet->getStyle('D7:D' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('E7:E' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
                    $event->sheet->getStyle('F7:F' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('G7:G' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
                    $event->sheet->getStyle('H7:H' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('I7:I' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
                    $event->sheet->getStyle('J7:J' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('K7:K' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
                    $event->sheet->getStyle('L7:L' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('M7:M' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
                    $event->sheet->getStyle('N7:N' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('O7:O' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
                    $event->sheet->getStyle('P7:P' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('Q7:Q' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
                    $event->sheet->getStyle('R7:R' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('S7:S' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
                    $event->sheet->getStyle('T7:T' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('U7:U' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
                    $event->sheet->getStyle('V7:V' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');

                    $event->sheet->getStyle('C' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('E' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('G' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('I' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('K' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('M' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('O' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('Q' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('S' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    $event->sheet->getStyle('U' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
                },
            ];
        } elseif($this->params['jenis_laporan'] == "rinci") {
            return [
                AfterSheet::class => function(AfterSheet $event) {
                    $sheet = $event->sheet->getDelegate();
                
                    // 1. Styling Judul Dokumen (Merge & Bold)
                    $sheet->mergeCells('A1:M1');
                    $sheet->mergeCells('A2:M2');
                    $sheet->mergeCells('A3:M3');
                    $sheet->getStyle('A1:A3')->getFont()->setBold(true);

                    // 2. Styling Judul Asuransi & Minggu (Bold)
                    foreach ($this->asuransiRows as $r) {
                        $sheet->mergeCells("A{$r}:M{$r}");
                        $sheet->getStyle("A$r")->getFont()->setBold(true);
                        $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    }
                    foreach ($this->mingguRows as $r) {
                        $sheet->mergeCells("A{$r}:M{$r}");
                        $sheet->getStyle("A$r")->getFont()->setBold(true);
                         $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    }

                    // 3. Styling Tabel (Header Bold & Border sampai batas subtotal)
                    foreach ($this->headerRows as $idx => $headerRow) {
                        $sheet->getStyle("A{$headerRow}:M{$headerRow}")->getFont()->setBold(true);
                        
                        $subTotalRow = $this->subTotalRows[$idx];
                        $sheet->getStyle("A{$headerRow}:M" . ($subTotalRow - 1))->applyFromArray([
                            'borders' => [
                                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                            ]
                        ]);
                    }

                    // 4. Styling Sub Total
                    foreach ($this->subTotalRows as $r) {
                        $sheet->getStyle("H$r")->getFont()->setBold(true);
                        $sheet->getStyle("I$r")->getFont()->setBold(true);
                        $sheet->getStyle("J$r")->getFont()->setBold(true);
                    }

                    // 5. Styling Total Per Pelanggan
                    foreach ($this->totalPelangganRows as $r) {
                        $sheet->getStyle("H$r")->getFont()->setBold(true);
                        $sheet->getStyle("I$r")->getFont()->setBold(true);
                        $sheet->getStyle("J$r")->getFont()->setBold(true);
                    }

                    // 6. Format Angka Ribuan untuk Kolom Total (J)
                    $highestRow = $sheet->getHighestRow();
                    $sheet->getStyle("J1:J{$highestRow}")->getNumberFormat()->setFormatCode('#,##0');
                    // $sheet->getStyle("G1:H{$highestRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                
                }
            ];
        }
    }

    public function bindValue(Cell $cell, $value)
    {
        $column = $cell->getColumn();

        // Jika kolom adalah G (No. Klaim) atau H (No. Polis), paksa jadikan Teks
        if (in_array($column, ['G', 'H'])) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        // Untuk kolom lain (seperti Total), biarkan Excel menebak otomatis (angka tetap angka)
        return parent::bindValue($cell, $value);
    }
}