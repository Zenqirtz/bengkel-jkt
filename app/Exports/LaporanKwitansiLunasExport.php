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
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Carbon\Carbon;

class LaporanKwitansiLunasExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
  protected $params;
  protected $cabang;
  protected $periode;
  protected $rowNumber = 0; // Untuk nomor urut manual
  protected $data;          // Menyimpan collection hasil query, dipakai untuk Grand Total

  // Terima parameter tambahan untuk Header Laporan
  public function __construct(array $params, $cabang, $periode)
  {
    $this->params = $params;
    $this->cabang = $cabang;
    $this->periode = $periode;
  }

  /**
   * Ganti dari FromQuery -> FromCollection supaya $this->data
   * bisa dipakai untuk menghitung Grand Total di registerEvents().
   */
  public function collection()
  {
    $query = null;

    if ($this->params['jenis_laporan'] == "voucher") {
      $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
      $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');

      $query = DB::table('v_rep_kwitansi_lunas as k')
        ->whereBetween('k.tgl_lunas', [$startDate, $endDate])
        ->where('k.kode_cabang', $this->cabang['kode']);

      if (strlen($this->params['no_voucher'])) {
        $query->where('k.kode_voucher', $this->params['no_voucher']);
      }

      $query->select([
        'k.kode_voucher',
        'k.tgl_lunas',
        'k.kode_lunas_kwitansi',
        'k.kode_spk',
        'k.kode_kwitansi',
        'k.kode_estimasi',
        'k.no_polisi',
        'k.nama_pelanggan',   // tambahan: buat header grup
        'k.pembayaran',       // tambahan: buat baris info
        'k.memo',             // tambahan: buat baris info
        'k.jasa',
        'k.bahan',
        'k.total_sparepart_s',
        'k.ppn',
        'k.total_lain_s',
        'k.total_or_ass',
        'k.tagihan',
        'k.pph',
        'k.materai',
        'k.uang_muka',
        'k.diterima',
        'k.tot_estimasi',
        'k.biaya_real'
      ])
        ->orderBy('k.nama_pelanggan', 'asc')
        ->orderBy('k.kode_voucher', 'asc')
        ->orderBy('k.tgl_lunas', 'asc');
    } elseif ($this->params['jenis_laporan'] == "rekap") {
      $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
      $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');

      $query = DB::table('v_rekap_kwitansi_lunas as k')
        ->whereBetween('k.tanggal', [$startDate, $endDate])
        ->where('k.kode_cabang', $this->cabang['kode']);

      $query->select([
        'k.kode_pembayaran',
        'k.nama_pelanggan',
        'k.nama_cabang',
        DB::raw('SUM(unit) AS unit'),
        DB::raw('SUM(pph) AS pph'),
        DB::raw('SUM(materai) AS materai'),
        DB::raw('SUM(tagihan) AS tagihan'),
        DB::raw('SUM(tagihan) - SUM(uang_muka) - SUM(pph) - SUM(materai) AS diterima'),
        DB::raw('SUM(tot_estimasi) AS tot_estimasi'),
        DB::raw('SUM(perbaikan) AS perbaikan'),
        DB::raw('SUM(sparepart) AS sparepart'),
        DB::raw('SUM(lain) AS lain'),
        DB::raw('SUM(or_asuransi) AS or_asuransi'),
        DB::raw("SUM(CASE WHEN kode_pembayaran = '00002' THEN (diterima - uang_muka) ELSE 0 END) AS tunai"),
        DB::raw("SUM(CASE WHEN kode_pembayaran IN ('00001', '00003') THEN (diterima - uang_muka) ELSE 0 END) AS bank"),
        DB::raw("SUM(CASE WHEN kode_pembayaran = '00005' THEN (diterima - uang_muka) ELSE 0 END) AS free"),
        DB::raw('SUM(uang_muka) AS uang_muka')
      ])
        ->groupBy(
          'nama_pelanggan',
          'kode_pembayaran',
          'nama_cabang'
        )
        ->orderBy('k.nama_pelanggan', 'asc');
    } elseif ($this->params['jenis_laporan'] == "rinci") {
      $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
      $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');

      $query = DB::table('v_rep_kwitansi_lunas as k')
        ->whereBetween('k.tgl_lunas', [$startDate, $endDate])
        ->where('k.kode_cabang', $this->cabang['kode']);

      $query->select([
        'k.kode_voucher',
        'k.tgl_lunas',
        'k.kode_lunas_kwitansi',
        'k.kode_spk',
        'k.kode_kwitansi',
        'k.kode_estimasi',
        'k.no_polisi',
        'k.merek_tipe',
        'k.nama_pelanggan',
        DB::raw("CASE WHEN kode_pembayaran = '00002' THEN (diterima - uang_muka) ELSE 0 END AS tunai"),
        DB::raw("CASE WHEN kode_pembayaran IN ('00001', '00003') THEN (diterima - uang_muka) ELSE 0 END AS bank"),
        DB::raw("CASE WHEN kode_pembayaran = '00005' THEN (diterima - uang_muka) ELSE 0 END AS free"),
        'k.uang_muka',
        'k.pph',
        'k.materai',
        'k.tagihan',
        'k.diterima',
        'k.tot_estimasi',
        DB::raw("(nilai_jasa + nilai_sparepart + nilai_lain + nilai_upah_borongan + nilai_komisi + nilai_or_free) AS biaya"),
      ])
        ->orderBy('k.kode_voucher', 'asc')
        ->orderBy('k.tgl_lunas', 'asc');
    }

    // Simpan hasil query ke $this->data supaya bisa dipakai untuk Grand Total
    $this->data = $query ? $query->get() : collect();

    return $this->data;
  }

  // Sesuaikan Header dengan layout Excel yang diminta
  public function headings(): array
  {
    if ($this->params['jenis_laporan'] == "voucher") {
      return [
        ['Laporan Kwitansi Lunas Voucher'],
        ['Cabang : ' . $this->cabang['nama']],
        ['Periode : ' . $this->periode],
        [''],
        [
          'No',
          'No. Voucher',
          'Tanggal Lunas',
          'No. Kwitansi',
          'No. SPK',
          'No. Invoice',
          'No. Estimasi',
          'No. Polisi',
          'Jasa',
          'Bahan',
          'Sparepart',
          'PPN',
          'Lain',
          'OR',
          'Tagihan',
          'PPh',
          'Materai & Transfer',
          'Uang Muka',
          'Diterima',
          'Total Estimasi',
          'Biaya Real',
        ]
      ];
    } elseif ($this->params['jenis_laporan'] == "rekap") {
      return [
        ['Laporan Kwitansi Lunas Rekap'],
        ['Cabang : ' . $this->cabang['nama']],
        ['Periode : ' . $this->periode],
        [''],
        [
          'No',
          'Nama Asuransi',
          'Unit',
          'Penerimaan Via',
          '',
          '',
          'Uang Muka',
          'PPh',
          'Materai & Transfer',
          'Tagihan',
          'Diterima',
          'Estimasi',
          'Perbaikan',
          'Sparepart',
          'Lain',
        ],
        [
          '',
          '',
          '',
          'Tunai',
          'Bank',
          'Free',
          '',
          '',
          '',
          '',
          '',
          '',
          '',
          '',
          '',
        ]
      ];
    } elseif ($this->params['jenis_laporan'] == "rinci") {
      return [
        ['Laporan Kwitansi Lunas Rinci'],
        ['Cabang : ' . $this->cabang['nama']],
        ['Periode : ' . $this->periode],
        [''],
        [
          'No',
          'No. Voucher',
          'Tanggal Lunas',
          'No. Kwitansi',
          'No. SPK',
          'No. Invoice',
          'No. Estimasi',
          'No. Polisi',
          'Merek Tipe',
          'Nama Asuransi',
          'Pembayaran Via',
          '',
          '',
          'Uang Muka',
          'PPh',
          'Materai & Transfer',
          'Tagihan',
          'Diterima',
          'Estimasi',
          'Biaya',
        ],
        [
          '',
          '',
          '',
          '',
          '',
          '',
          '',
          '',
          '',
          '',
          'Tunai',
          'Bank',
          'Free',
          '',
          '',
          '',
          '',
          '',
          '',
          '',
        ]
      ];
    }

    return [];
  }

  public function map($row): array
  {
    $this->rowNumber++;

    if ($this->params['jenis_laporan'] == "voucher") {
      return [
        $this->rowNumber,
        $row->kode_voucher,
        $row->tgl_lunas ? date("d/m/Y", strtotime($row->tgl_lunas)) : '',
        $row->kode_lunas_kwitansi,
        $row->kode_spk,
        $row->kode_kwitansi,
        $row->kode_estimasi,
        $row->no_polisi,
        (float) $row->jasa,
        (float) $row->bahan,
        (float) $row->total_sparepart_s,
        (float) $row->ppn,
        (float) $row->total_lain_s,
        (float) $row->total_or_ass,
        (float) $row->tagihan,
        (float) $row->pph,
        (float) $row->materai,
        (float) $row->uang_muka,
        (float) $row->diterima,
        (float) $row->tot_estimasi,
        (float) $row->biaya_real,
      ];
    } elseif ($this->params['jenis_laporan'] == "rekap") {
      return [
        $this->rowNumber,
        $row->nama_pelanggan,
        (float) $row->unit,
        (float) $row->tunai,
        (float) $row->bank,
        (float) $row->free,
        (float) $row->uang_muka,
        (float) $row->pph,
        (float) $row->materai,
        (float) $row->tagihan,
        (float) $row->diterima,
        (float) $row->tot_estimasi,
        (float) $row->perbaikan,
        (float) $row->sparepart,
        (float) $row->lain,
      ];
    } elseif ($this->params['jenis_laporan'] == "rinci") {
      return [
        $this->rowNumber,
        $row->kode_voucher,
        $row->tgl_lunas ? date("d/m/Y", strtotime($row->tgl_lunas)) : '',
        $row->kode_lunas_kwitansi,
        $row->kode_spk,
        $row->kode_kwitansi,
        $row->kode_estimasi,
        $row->no_polisi,
        $row->merek_tipe,
        $row->nama_pelanggan,
        (float) $row->tunai,
        (float) $row->bank,
        (float) $row->free,
        (float) $row->uang_muka,
        (float) $row->pph,
        (float) $row->materai,
        (float) $row->tagihan,
        (float) $row->diterima,
        (float) $row->tot_estimasi,
        (float) $row->biaya,
      ];
    }

    return [];
  }

  // Styling untuk Header dan Judul
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

  /**
   * Helper: tulis angka sebagai teks berformat (titik ribuan) dan
   * matikan warning "Number Stored as Text". Dipakai khusus untuk
   * baris Grand Total, sama seperti di LaporanPembelianBahanExport.
   */
  private function setTextCell(AfterSheet $event, string $cellRef, string $value): void
  {
    $cell = $event->sheet->getCell($cellRef);
    $cell->setValueExplicit($value, DataType::TYPE_STRING);
    $cell->getIgnoredErrors()->setNumberStoredAsText(true);
  }

  /**
   * Helper: styling baris Grand Total (biru), sama seperti di
   * LaporanPembelianBahanExport.
   */
  private function styleTotalRow(AfterSheet $event, string $range): void
  {
    $event->sheet->getStyle($range)->getFill()
      ->setFillType(Fill::FILL_SOLID)
      ->getStartColor()->setARGB('FFDBEAFE');

    $event->sheet->getStyle($range)->getFont()
      ->setBold(true)
      ->getColor()->setARGB('FF1E40AF');

    $event->sheet->getStyle($range)->getBorders()->getOutline()
      ->setBorderStyle(Border::BORDER_MEDIUM)
      ->getColor()->setARGB('FF3B82F6');

    $event->sheet->getStyle($range)->getBorders()->getInside()
      ->setBorderStyle(Border::BORDER_THIN);
  }

  // Event untuk Merge Cells + Grand Total
  public function registerEvents(): array
  {
    if ($this->params['jenis_laporan'] == "voucher") {
      return [
        AfterSheet::class => function (AfterSheet $event) {
          $event->sheet->mergeCells('A1:U1');
          $event->sheet->mergeCells('A2:U2');
          $event->sheet->mergeCells('A3:U3');

          $event->sheet->mergeCells('A5:A6');
          $event->sheet->mergeCells('B5:B6');
          $event->sheet->mergeCells('C5:C6');
          $event->sheet->mergeCells('D5:D6');
          $event->sheet->mergeCells('E5:E6');
          $event->sheet->mergeCells('F5:F6');
          $event->sheet->mergeCells('G5:G6');
          $event->sheet->mergeCells('H5:H6');
          $event->sheet->mergeCells('I5:I6');
          $event->sheet->mergeCells('J5:J6');
          $event->sheet->mergeCells('K5:K6');
          $event->sheet->mergeCells('L5:L6');
          $event->sheet->mergeCells('M5:M6');
          $event->sheet->mergeCells('N5:N6');
          $event->sheet->mergeCells('O5:O6');
          $event->sheet->mergeCells('P5:P6');
          $event->sheet->mergeCells('Q5:Q6');
          $event->sheet->mergeCells('R5:R6');
          $event->sheet->mergeCells('S5:S6');
          $event->sheet->mergeCells('T5:T6');
          $event->sheet->mergeCells('U5:U6');

          $moneyCols = [
            'I' => 'jasa',
            'J' => 'bahan',
            'K' => 'total_sparepart_s',
            'L' => 'ppn',
            'M' => 'total_lain_s',
            'N' => 'total_or_ass',
            'O' => 'tagihan',
            'P' => 'pph',
            'Q' => 'materai',
            'R' => 'uang_muka',
            'S' => 'diterima',
            'T' => 'tot_estimasi',
            'U' => 'biaya_real',
          ];

          // Hapus baris data lama hasil WithMapping (karena kita tulis ulang manual per grup)
          $lastMappedRow = 6 + $this->data->count();
          $event->sheet->removeRow(7, $this->data->count());

          $grouped = $this->data->groupBy('kode_voucher');
          $row = 7;
          $grandTotal = array_fill_keys($moneyCols, 0);
          $grandTotal = array_fill_keys(array_values($moneyCols), 0);
          foreach ($grouped as $kodeVoucher => $rows) {
            // 1) Header kolom berulang DULU dihapus dari sini, dipindah ke bawah

            // 2) Baris label Asuransi (tetap di atas)
            $event->sheet->mergeCells("A{$row}:U{$row}");
            $event->sheet->setCellValue("A{$row}", 'Asuransi : ' . $rows->first()->nama_pelanggan . ' | No. Voucher : ' . $kodeVoucher);
            $event->sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;

            // 3) BARU header kolom, di bawah label Asuransi
            $headerLabels = [
              'No',
              'No. Voucher',
              'Tanggal Lunas',
              'No. Kwitansi',
              'No. SPK',
              'No. Invoice',
              'No. Estimasi',
              'No. Polisi',
              'Jasa',
              'Bahan',
              'Sparepart',
              'PPN',
              'Lain',
              'OR',
              'Tagihan',
              'PPh',
              'Materai & Transfer',
              'Uang Muka',
              'Diterima',
              'Total Estimasi',
              'Biaya Real'
            ];
            $col = 'A';
            foreach ($headerLabels as $label) {
              $event->sheet->setCellValue("{$col}{$row}", $label);
              $col++;
            }
            $event->sheet->getStyle("A{$row}:U{$row}")->getFont()->setBold(true);
            $row++;

            $subTotal = array_fill_keys(array_values($moneyCols), 0);

            foreach ($rows as $i => $dataRow) {
              $event->sheet->setCellValue("A{$row}", $i + 1);
              $event->sheet->setCellValue("B{$row}", $dataRow->kode_voucher);
              $event->sheet->setCellValue("C{$row}", $dataRow->tgl_lunas ? date('d/m/Y', strtotime($dataRow->tgl_lunas)) : '');
              $event->sheet->setCellValue("D{$row}", $dataRow->kode_lunas_kwitansi);
              $event->sheet->setCellValue("E{$row}", $dataRow->kode_spk);
              $event->sheet->setCellValue("F{$row}", $dataRow->kode_kwitansi);
              $event->sheet->setCellValue("G{$row}", $dataRow->kode_estimasi);
              $event->sheet->setCellValue("H{$row}", $dataRow->no_polisi);

              foreach ($moneyCols as $col => $field) {
                $subTotal[$field] += $dataRow->$field;
                $grandTotal[$field] += $dataRow->$field;
                $this->setTextCell($event, "{$col}{$row}", number_format((float) $dataRow->$field, 0, '.', ','));
              }
              $row++;
            }

            // Baris subtotal per voucher
            $event->sheet->mergeCells("A{$row}:H{$row}");
            $event->sheet->setCellValue("A{$row}", 'Sub Total ' . $kodeVoucher);
            $event->sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            foreach ($moneyCols as $col => $field) {
              $this->setTextCell($event, "{$col}{$row}", number_format($subTotal[$field], 0, '.', ','));
            }
            $this->styleTotalRow($event, "A{$row}:U{$row}");
            $row++;

            // Baris info tambahan
            $lastRow = $rows->last();
            $event->sheet->mergeCells("A{$row}:U{$row}");
            $infoText = 'Tanggal Lunas  ' . (blank($lastRow->tgl_lunas) ? '' : date('d-M-Y', strtotime($lastRow->tgl_lunas)))
              . ' | Pembayaran  ' . $lastRow->pembayaran
              . ' | ' . $lastRow->memo;
            $event->sheet->setCellValue("A{$row}", $infoText);
            $row++;
          }

          // Grand Total keseluruhan
          if ($this->data->count() > 0) {
            $event->sheet->mergeCells("A{$row}:H{$row}");
            $event->sheet->setCellValue("A{$row}", 'Grand Total');
            $event->sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            foreach ($moneyCols as $col => $field) {
              $this->setTextCell($event, "{$col}{$row}", number_format($grandTotal[$field], 0, '.', ','));
            }
            $this->styleTotalRow($event, "A{$row}:U{$row}");
          }

          $highestRow = $row;
          $event->sheet->getStyle("A5:U{$highestRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
          ]);
          $event->sheet->getStyle("I7:U{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        },
      ];
    } elseif ($this->params['jenis_laporan'] == "rekap") {
      return [
        AfterSheet::class => function (AfterSheet $event) {
          $event->sheet->mergeCells('A1:O1');
          $event->sheet->mergeCells('A2:O2');
          $event->sheet->mergeCells('A3:O3');

          $event->sheet->mergeCells('A5:A6');
          $event->sheet->mergeCells('B5:B6');
          $event->sheet->mergeCells('C5:C6');
          $event->sheet->mergeCells('D5:F5');
          $event->sheet->mergeCells('G5:G6');
          $event->sheet->mergeCells('H5:H6');
          $event->sheet->mergeCells('I5:I6');
          $event->sheet->mergeCells('J5:J6');
          $event->sheet->mergeCells('K5:K6');
          $event->sheet->mergeCells('L5:L6');
          $event->sheet->mergeCells('M5:M6');
          $event->sheet->mergeCells('N5:N6');
          $event->sheet->mergeCells('O5:O6');

          $highestRow = $event->sheet->getHighestRow();
          $event->sheet->getStyle('A5:O' . $highestRow)->applyFromArray([
            'borders' => [
              'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
              ],
            ],
          ]);

          $event->sheet->getStyle('A7:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

          // ===== Format semua baris data jadi teks berkoma, biar konsisten dengan Grand Total =====
          $dataStartRow = 7;
          $moneyCols = [
            'C' => 'unit',
            'D' => 'tunai',
            'E' => 'bank',
            'F' => 'free',
            'G' => 'uang_muka',
            'H' => 'pph',
            'I' => 'materai',
            'J' => 'tagihan',
            'K' => 'diterima',
            'L' => 'tot_estimasi',
            'M' => 'perbaikan',
            'N' => 'sparepart',
            'O' => 'lain',
          ];
          foreach ($this->data as $i => $dataRow) {
            $excelRow = $dataStartRow + $i;
            foreach ($moneyCols as $col => $field) {
              $this->setTextCell($event, $col . $excelRow, number_format((float) $dataRow->$field, 0, '.', ','));
            }
          }
          $event->sheet->getStyle('C' . $dataStartRow . ':O' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

          // ===== Grand Total (biru), label cuma A-B; semua kolom angka (C-O) ikut ditotal =====
          // Disamakan dengan pola Pembelian Bahan: hanya kolom teks/ID yang dikosongkan,
          // kolom Unit/Tunai/Bank/Free tetap dijumlahkan seperti kolom angka lainnya.
          if ($this->data->count() > 0) {
            $totalRow = $highestRow + 1;

            $event->sheet->mergeCells('A' . $totalRow . ':B' . $totalRow);
            $event->sheet->setCellValue('A' . $totalRow, 'Grand Total');
            $event->sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $this->setTextCell($event, 'C' . $totalRow, number_format($this->data->sum('unit'), 0, '.', ','));
            $this->setTextCell($event, 'D' . $totalRow, number_format($this->data->sum('tunai'), 0, '.', ','));
            $this->setTextCell($event, 'E' . $totalRow, number_format($this->data->sum('bank'), 0, '.', ','));
            $this->setTextCell($event, 'F' . $totalRow, number_format($this->data->sum('free'), 0, '.', ','));
            $this->setTextCell($event, 'G' . $totalRow, number_format($this->data->sum('uang_muka'), 0, '.', ','));
            $this->setTextCell($event, 'H' . $totalRow, number_format($this->data->sum('pph'), 0, '.', ','));
            $this->setTextCell($event, 'I' . $totalRow, number_format($this->data->sum('materai'), 0, '.', ','));
            $this->setTextCell($event, 'J' . $totalRow, number_format($this->data->sum('tagihan'), 0, '.', ','));
            $this->setTextCell($event, 'K' . $totalRow, number_format($this->data->sum('diterima'), 0, '.', ','));
            $this->setTextCell($event, 'L' . $totalRow, number_format($this->data->sum('tot_estimasi'), 0, '.', ','));
            $this->setTextCell($event, 'M' . $totalRow, number_format($this->data->sum('perbaikan'), 0, '.', ','));
            $this->setTextCell($event, 'N' . $totalRow, number_format($this->data->sum('sparepart'), 0, '.', ','));
            $this->setTextCell($event, 'O' . $totalRow, number_format($this->data->sum('lain'), 0, '.', ','));

            $this->styleTotalRow($event, 'A' . $totalRow . ':O' . $totalRow);

            $event->sheet->getStyle('C' . $totalRow . ':O' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          }
        },
      ];
    } elseif ($this->params['jenis_laporan'] == "rinci") {
      return [
        AfterSheet::class => function (AfterSheet $event) {
          $event->sheet->mergeCells('A1:T1');
          $event->sheet->mergeCells('A2:T2');
          $event->sheet->mergeCells('A3:T3');

          $event->sheet->mergeCells('A5:A6');
          $event->sheet->mergeCells('B5:B6');
          $event->sheet->mergeCells('C5:C6');
          $event->sheet->mergeCells('D5:D6');
          $event->sheet->mergeCells('E5:E6');
          $event->sheet->mergeCells('F5:F6');
          $event->sheet->mergeCells('G5:G6');
          $event->sheet->mergeCells('H5:H6');
          $event->sheet->mergeCells('I5:I6');
          $event->sheet->mergeCells('J5:J6');
          $event->sheet->mergeCells('K5:M5');
          $event->sheet->mergeCells('N5:N6');
          $event->sheet->mergeCells('O5:O6');
          $event->sheet->mergeCells('P5:P6');
          $event->sheet->mergeCells('Q5:Q6');
          $event->sheet->mergeCells('R5:R6');
          $event->sheet->mergeCells('S5:S6');
          $event->sheet->mergeCells('T5:T6');

          $highestRow = $event->sheet->getHighestRow();
          $event->sheet->getStyle('A5:T' . $highestRow)->applyFromArray([
            'borders' => [
              'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
              ],
            ],
          ]);

          $event->sheet->getStyle('A7:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

          // ===== Format semua baris data jadi teks berkoma, biar konsisten dengan Grand Total =====
          $dataStartRow = 7;
          $moneyCols = [
            'K' => 'tunai',
            'L' => 'bank',
            'M' => 'free',
            'N' => 'uang_muka',
            'O' => 'pph',
            'P' => 'materai',
            'Q' => 'tagihan',
            'R' => 'diterima',
            'S' => 'tot_estimasi',
            'T' => 'biaya',
          ];
          foreach ($this->data as $i => $dataRow) {
            $excelRow = $dataStartRow + $i;
            foreach ($moneyCols as $col => $field) {
              $this->setTextCell($event, $col . $excelRow, number_format((float) $dataRow->$field, 0, '.', ','));
            }
          }
          $event->sheet->getStyle('K' . $dataStartRow . ':T' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

          // ===== Grand Total (biru), kolom A-J digabung jadi label =====
          if ($this->data->count() > 0) {
            $totalRow = $highestRow + 1;

            $event->sheet->mergeCells('A' . $totalRow . ':J' . $totalRow);
            $event->sheet->setCellValue('A' . $totalRow, 'Grand Total');
            $event->sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $this->setTextCell($event, 'K' . $totalRow, number_format($this->data->sum('tunai'), 0, '.', ','));
            $this->setTextCell($event, 'L' . $totalRow, number_format($this->data->sum('bank'), 0, '.', ','));
            $this->setTextCell($event, 'M' . $totalRow, number_format($this->data->sum('free'), 0, '.', ','));
            $this->setTextCell($event, 'N' . $totalRow, number_format($this->data->sum('uang_muka'), 0, '.', ','));
            $this->setTextCell($event, 'O' . $totalRow, number_format($this->data->sum('pph'), 0, '.', ','));
            $this->setTextCell($event, 'P' . $totalRow, number_format($this->data->sum('materai'), 0, '.', ','));
            $this->setTextCell($event, 'Q' . $totalRow, number_format($this->data->sum('tagihan'), 0, '.', ','));
            $this->setTextCell($event, 'R' . $totalRow, number_format($this->data->sum('diterima'), 0, '.', ','));
            $this->setTextCell($event, 'S' . $totalRow, number_format($this->data->sum('tot_estimasi'), 0, '.', ','));
            $this->setTextCell($event, 'T' . $totalRow, number_format($this->data->sum('biaya'), 0, '.', ','));

            $this->styleTotalRow($event, 'A' . $totalRow . ':T' . $totalRow);

            $event->sheet->getStyle('K' . $totalRow . ':T' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          }
        },
      ];
    }

    return [];
  }
}
