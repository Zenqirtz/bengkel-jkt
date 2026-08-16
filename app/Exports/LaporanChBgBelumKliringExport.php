<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
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

class LaporanChBgBelumKliringExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
  protected $params;
  protected $cabang;
  protected $periode;
  protected $namaBank;
  protected $rowNumber = 0;
  protected $saldo = 0;

  public function __construct(array $params, $cabang, $periode, $namaBank = '')
  {
    $this->params = $params;
    $this->cabang = $cabang;
    $this->periode = $periode;
    $this->namaBank = $namaBank;
    $this->saldo = 0;
  }

  private function resolveKolomTanggal(?string $kategori): string
  {
    return match ($kategori) {
      'Tanggal CH/BG' => 'tanggal_ch_bg',
      'Tanggal Kliring' => 'tanggal_kliring',
      default => 'tanggal',
    };
  }

  public function collection()
  {
    $urutMap = [
      'tgl_voucher' => 'tanggal',
      'voucher_masuk' => 'no_voucher_in',
      'voucher_keluar' => 'no_voucher_out',
    ];

    $urut1 = $urutMap[$this->params['urut1'] ?? 'tgl_voucher'] ?? 'tanggal';
    $urut2 = $urutMap[$this->params['urut2'] ?? 'voucher_keluar'] ?? 'no_voucher_out';
    $urut3 = $urutMap[$this->params['urut3'] ?? 'voucher_masuk'] ?? 'no_voucher_in';

    $query = DB::table('tmp_all_transaksi')
      ->where('kode_cabang', $this->cabang['kode'])
      ->where('kode_bank', $this->params['kode_bank'])
      ->select([
        'kode_cabang',
        'kode_bank',
        'tanggal',
        'tanggal_ch_bg',
        'memo',
        'nama_bank',
        'no_ch_bg',
        'nama_cabang',
        'no_transaksi',
        'no_voucher_in',
        'no_voucher_out',
        'debit',
        'kredit',
        'amount',
        'tanggal_kliring',
        'no_voucher_cabang',
        'no_urut',
        'no_perkiraan',
      ]);


    $kolomTanggalExport = $this->resolveKolomTanggal($this->params['kategori'] ?? '');

    if (!empty($this->params['tgl_awal'])) {
      $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
      $query->whereDate($kolomTanggalExport, '>=', $startDate);
    }
    if (!empty($this->params['tgl_akhir'])) {
      $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
      $query->whereDate($kolomTanggalExport, '<=', $endDate);
    }

    $rawData = $query->orderBy($urut1)->orderBy($urut2)->orderBy($urut3)->get();

    // Pre-calculate totals and saldo per row
    // $processedData = collect();
    // $saldo = 0;
    // $no = 0;

    // foreach ($rawData as $row) {
    //   $saldo = ($no > 0) ? ($saldo + $row->debit + $row->kredit) : $row->amount;

    //   $row->saldo = $saldo;
    //   ++$no;
    // }

    return $rawData;
  }

  public function headings(): array
  {
    return [
      [$this->cabang['nama']],
      ['Laporan CH BG Belum Kliring'],
      ['Bank ' . (!empty($this->namaBank) ? $this->namaBank : 'Semua Bank')],
      ['Per Tanggal : ' . $this->periode],
      [''],
      [
        'No',
        'Tanggal Voucher',
        'Tanggal CH/BG',
        'Pelanggan & Memo',
        'No. CH/BG',
        'Voucher Masuk',
        'Voucher Keluar',
        'Debit',
        'Kredit',
        'Saldo',
        'Tanggal Kliring',
      ],
    ];
  }

  public function map($row): array
  {
    $this->rowNumber++;

    $this->saldo = ($this->rowNumber > 0) ? ($this->saldo + $row->debit + $row->kredit) : $row->amount;

    return [
      $this->rowNumber,
      $row->tanggal ? date("d/m/Y", strtotime($row->tanggal)) : '',
      $row->tanggal_ch_bg ? date("d/m/Y", strtotime($row->tanggal_ch_bg)) : '',
      $row->memo,
      $row->no_ch_bg,
      $row->no_voucher_in,
      $row->no_voucher_out,
      $row->debit,
      ($row->kredit * -1),
      $this->saldo,
      $row->tanggal_kliring ? date("d/m/Y", strtotime($row->tanggal_kliring)) : '',
    ];
  }

  public function styles(Worksheet $sheet)
  {
    return [
      1 => ['font' => ['bold' => true, 'size' => 14]],
      2 => ['font' => ['bold' => true]],
      3 => ['font' => ['bold' => true]],
      4 => ['font' => ['bold' => true]],
      6 => [
        'font' => ['bold' => true],
        'alignment' => [
          'horizontal' => Alignment::HORIZONTAL_CENTER,
          'vertical' => Alignment::VERTICAL_CENTER,
          'wrapText' => true,
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
      AfterSheet::class => function (AfterSheet $event) {
        $sheet = $event->sheet->getDelegate();

        // Merge cells untuk header
        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');
        $sheet->mergeCells('A3:K3');
        $sheet->mergeCells('A4:K4');

        $highestRow = $sheet->getHighestRow();

        // --- TAMBAHAN UNTUK GRAND TOTAL ---
        // Kita cek apakah ada data (baris data dimulai dari baris 7)
        if ($highestRow >= 7) {
          $totalRow = $highestRow + 1; // Baris untuk grand total

          // Gabungkan kolom A dan B untuk label "Grand Total"
          $sheet->mergeCells("A{$totalRow}:G{$totalRow}");
          $sheet->setCellValue("A{$totalRow}", 'Total');
          $sheet->setCellValue("J{$totalRow}", "=(H{$totalRow}-I{$totalRow})");

          // Set Alignment teks Grand Total ke Kanan dan tebalkan barisnya
          $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $sheet->getStyle("A{$totalRow}:K{$totalRow}")->getFont()->setBold(true);

          // Buat rumus SUM otomatis untuk kolom C sampai N
          foreach (range('H', 'I') as $col) {
            $sheet->setCellValue("{$col}{$totalRow}", "=SUM({$col}7:{$col}{$highestRow})");
          }

          // Perbarui nilai $highestRow agar baris Grand Total ikut terkena border & styling di bawahnya
          $highestRow = $totalRow;
        }
        // --- END TAMBAHAN GRAND TOTAL ---

        // Border untuk seluruh data
        $sheet->getStyle('A6:K' . $highestRow)->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        // Alignment untuk kolom No dan Unit (center)
        $sheet->getStyle('A7:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Format angka untuk kolom D sampai N (format ribuan)
        $sheet->getStyle('H7:J' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');
      },
    ];
  }
}
