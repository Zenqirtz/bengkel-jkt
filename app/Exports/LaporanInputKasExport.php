<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LaporanInputKasExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
  protected array $filters;
  protected array $cabangData;
  protected string $periodeStr;
  protected int $grandTotalRowIndex = 0; // posisi baris grand total di dalam array data (1-based, tanpa heading)

  public function __construct(array $filters, array $cabangData, string $periodeStr)
  {
    $this->filters = $filters;
    $this->cabangData = $cabangData;
    $this->periodeStr = $periodeStr;
  }

  /**
   * Query data + hitung saldo berjalan.
   * Meniru pola LaporanGudangBahanController::exportExcel() -> query di-inline,
   * bedanya di sini query-nya ada di dalam Export class (sesuai FromArray).
   */
  public function array(): array
  {
    $user_cabang = $this->cabangData['kode'] ?? null;

    $query = DB::table('v_rpt_kas_harian')
      ->where('kode_cabang', $user_cabang)
      ->select([
        'tanggal',
        'no_bukti',
        'memo',
        'no_spk',
        'no_input_gudang',
        'debet',
        'kredit',
        'or_free',
      ]);

    $startDate = null;

    // Filter tanggal
    if (!empty($this->filters['tgl_awal'])) {
      $startDate = Carbon::createFromFormat('d/m/Y', $this->filters['tgl_awal'])->format('Y-m-d');
      $query->whereDate('tanggal', '>=', $startDate);
    }

    if (!empty($this->filters['tgl_akhir'])) {
      $endDate = Carbon::createFromFormat('d/m/Y', $this->filters['tgl_akhir'])->format('Y-m-d');
      $query->whereDate('tanggal', '<=', $endDate);
    }

    $datas = $query->orderBy('tanggal', 'asc')->orderBy('no_bukti', 'asc')->get();

    // Saldo awal = akumulasi (debet - kredit) sebelum tgl_awal
    $saldoAwal = 0;
    if ($startDate) {
      $row = DB::table('v_rpt_kas_harian')
        ->where('kode_cabang', $user_cabang)
        ->whereDate('tanggal', '<', $startDate)
        ->selectRaw('COALESCE(SUM(debet),0) - COALESCE(SUM(kredit),0) as saldo')
        ->first();
      $saldoAwal = (float) ($row->saldo ?? 0);
    }

    $no = 0;
    $saldo = $saldoAwal;
    $rows = [];

    // Baris Saldo Awal
    $rows[] = [
      ++$no,
      $startDate ? Carbon::parse($startDate)->format('d/m/Y') : '',
      '',
      'Saldo Awal',
      '',
      '',
      number_format($saldoAwal, 0, '.', '.'),
      number_format(0, 0, '.', '.'),
      number_format($saldo, 0, '.', '.'),
      '',
    ];

    foreach ($datas as $row) {
      $debet = (float) $row->debet;
      $kredit = (float) $row->kredit;
      $saldo = $saldo + $debet - $kredit;

      $rows[] = [
        ++$no,
        Carbon::parse($row->tanggal)->format('d/m/Y'),
        $row->no_bukti,
        $row->memo,
        $row->no_spk ?? '',
        $row->no_input_gudang ?? '',
        number_format($debet, 0, '.', '.'),
        number_format($kredit, 0, '.', '.'),
        number_format($saldo, 0, '.', '.'),
        $row->or_free ?? '',
      ];
    }

    // Baris Grand Total / Saldo Akhir - SELALU ditampilkan
    $rows[] = [
      '',
      '',
      '',
      'Grand Total',
      '',
      '',
      '',
      '',
      number_format($saldo, 0, '.', '.'),
      '',
    ];

    // simpan posisi baris terakhir (index array, dipakai di registerEvents)
    $this->grandTotalRowIndex = count($rows);

    return $rows;
  }

  public function headings(): array
  {
    return [
      'No',
      'Tanggal',
      'No. Bukti',
      'Memo',
      'No. SPK',
      'No. Input Gudang',
      'Debet',
      'Kredit',
      'Saldo',
      'OR Free/Lain-lain',
    ];
  }

  public function styles(Worksheet $sheet)
  {
    return [
      1 => ['font' => ['bold' => true]],
    ];
  }

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $sheet = $event->sheet->getDelegate();

        // Insert baris header laporan di atas
        $sheet->insertNewRowBefore(1, 3);
        $sheet->setCellValue('A1', 'Laporan Kas Harian ' . ($this->cabangData['nama'] ?? ''));
        $sheet->setCellValue('A2', 'Periode : ' . $this->periodeStr);
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        // Border tipis untuk seluruh area data (sama seperti gudang-bahan)
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A4:J' . $highestRow)->applyFromArray([
          'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
          ],
        ]);

        // Style baris Grand Total — biru muda + font biru tua + border tebal
        // (persis pola subtotalRows di LaporanGudangBahanExport)
        if ($this->grandTotalRowIndex > 0) {
          // +3 baris header laporan yang baru di-insert, +1 baris heading kolom
          $grandTotalSheetRow = $this->grandTotalRowIndex + 3 + 1;

          $sheet->getStyle('A' . $grandTotalSheetRow . ':J' . $grandTotalSheetRow)
            ->applyFromArray([
              'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF1E40AF'],
              ],
              'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFDBEAFE'],
              ],
              'borders' => [
                'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF3B82F6']],
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF3B82F6']],
              ],
            ]);
        }

        foreach (range('A', 'J') as $col) {
          $sheet->getColumnDimension($col)->setAutoSize(true);
        }
      },
    ];
  }
}
