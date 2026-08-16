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

class LaporanKontrolPemakaianBahanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
  protected $no_spk;
  protected $cabang;
  protected $rowNumber = 0;
  protected $data;
  protected $meta = null;

  public function __construct(string $no_spk, array $cabang)
  {
    $this->no_spk = $no_spk;
    $this->cabang = $cabang;

    // Query di constructor agar meta sudah tersedia saat headings() dipanggil
    $results = DB::select('CALL up_apl_rep_kontrol_pemakaian_bahan(?, ?)', [
      $this->cabang['kode'],
      $this->no_spk,
    ]);

    foreach ($results as $row) {
      if ($this->meta === null) {
        $this->meta = [
          'no_spk' => $this->no_spk,
          'point_panel' => $row->point_panel ?? '',
          'nama_pemilik' => $row->nama_pemilik ?? '',
          'merek_tipe' => $row->merek_tipe ?? '',
        ];
      }
    }

    if ($this->meta === null) {
      $this->meta = [
        'no_spk' => $this->no_spk,
        'point_panel' => '',
        'nama_pemilik' => '',
        'merek_tipe' => '',
      ];
    }

    $this->data = collect($results);
  }

  public function collection()
  {
    return $this->data;
  }

  public function headings(): array
  {
    $noSpk = $this->meta['no_spk'] ?? $this->no_spk;
    $pointPanel = $this->meta['point_panel'] ?? '';
    $pemilik = $this->meta['nama_pemilik'] ?? '';
    $merekTipe = $this->meta['merek_tipe'] ?? '';

    return [
      // Row 1: Judul
      ['FORMULIR KONTROL PEMAKAIAN BAHAN', '', '', '', '', '', '', '', '', ''],
      // Row 2: kosong
      ['', '', '', '', '', '', '', '', '', ''],
      // Row 3: No SPK & Pemilik
      ['No. SPK', ': ' . $noSpk, '', 'Pemilik', ': ' . $pemilik, '', '', '', '', ''],
      // Row 4: Point Panel & Merek Tipe
      ['Point Panel', ': ' . $pointPanel, '', 'Merek Tipe', ': ' . $merekTipe, '', '', '', '', ''],
      // Row 5: kosong
      ['', '', '', '', '', '', '', '', '', ''],
      // Row 6: Header baris 1
      ['No', 'Bagian', 'Nama Bahan', 'Standard Pemakaian', '', 'Aktual Pemakaian', '', '', 'Variance', ''],
      // Row 7: Header baris 2 (sub-header)
      ['', '', '', 'Qty', 'Harga', 'Qty', 'Harga', 'Total', 'Qty', 'Harga'],
    ];
  }

  // public function map($row): array
  // {
  //   $this->rowNumber++;

  //   return [
  //     $this->rowNumber,
  //     $row->posisi_pekerjaan ?? '',
  //     $row->nama_bahan ?? '',
  //     number_format($row->qty ?? 0, 2, '.', '.'),
  //     number_format($row->harga ?? 0, 0, '.', '.'),
  //     number_format($row->qty_actual ?? 0, 2, '.', '.'),
  //     number_format($row->harga_actual ?? 0, 0, '.', '.'),
  //     number_format($row->tot_harga_actual ?? 0, 0, '.', '.'),
  //     number_format($row->qty_variance ?? 0, 2, '.', '.'),
  //     number_format($row->tot_harga_variance ?? 0, 0, '.', '.'),
  //   ];
  // }
  public function map($row): array
  {
    $this->rowNumber++;

    return [
      $this->rowNumber,
      $row->posisi_pekerjaan ?? '',
      $row->nama_bahan ?? '',
      (float) ($row->qty ?? 0),
      (float) ($row->harga ?? 0),
      (float) ($row->qty_actual ?? 0),
      (float) ($row->harga_actual ?? 0),
      (float) ($row->tot_harga_actual ?? 0),
      (float) ($row->qty_variance ?? 0),
      (float) ($row->tot_harga_variance ?? 0),
    ];
  }

  public function styles(Worksheet $sheet)
  {
    return [
      1 => ['font' => ['bold' => true, 'size' => 12]],
    ];
  }

  // public function registerEvents(): array
  // {
  //   return [
  //     AfterSheet::class => function (AfterSheet $event) {
  //       $sheet = $event->sheet->getDelegate();
  //       $dataStart = 8; // data mulai baris 8 (setelah 7 baris heading)

  //       // Merge judul
  //       $sheet->mergeCells('A1:J1');

  //       // Merge header tabel 2 baris (row 6-7)
  //       $sheet->mergeCells('A6:A7'); // No (rowspan 2)
  //       $sheet->mergeCells('B6:B7'); // Bagian (rowspan 2)
  //       $sheet->mergeCells('C6:C7'); // Nama Bahan (rowspan 2)
  //       $sheet->mergeCells('D6:E6'); // Standard Pemakaian (colspan 2)
  //       $sheet->mergeCells('F6:H6'); // Aktual Pemakaian (colspan 3)
  //       $sheet->mergeCells('I6:J6'); // Variance (colspan 2)

  //       // Style header tabel
  //       $sheet->getStyle('A6:J7')->applyFromArray([
  //         'font' => ['bold' => true],
  //         'alignment' => [
  //           'horizontal' => Alignment::HORIZONTAL_CENTER,
  //           'vertical' => Alignment::VERTICAL_CENTER,
  //           'wrapText' => true,
  //         ],
  //         'borders' => [
  //           'allBorders' => ['borderStyle' => Border::BORDER_THIN],
  //         ],
  //       ]);

  //       $sheet->getRowDimension(6)->setRowHeight(20);
  //       $sheet->getRowDimension(7)->setRowHeight(18);

  //       // Borders & alignment data
  //       $highestRow = $sheet->getHighestRow();

  //       if ($highestRow >= $dataStart) {
  //         $sheet->getStyle('A' . $dataStart . ':J' . $highestRow)->applyFromArray([
  //           'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
  //         ]);

  //         // Kolom angka rata kanan (D s/d J)
  //         foreach (['D', 'E', 'F', 'G', 'H', 'I', 'J'] as $col) {
  //           $sheet->getStyle($col . $dataStart . ':' . $col . $highestRow)
  //             ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
  //         }

  //         // Kolom No rata tengah
  //         $sheet->getStyle('A' . $dataStart . ':A' . $highestRow)
  //           ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
  //       }

  //       // Grand Total row
  //       $grandRow = $highestRow + 1;
  //       $sheet->setCellValue('A' . $grandRow, 'Grand Total');
  //       $sheet->mergeCells('A' . $grandRow . ':C' . $grandRow); // No + Bagian + Nama Bahan

  //       foreach (['D', 'E', 'F', 'G', 'H', 'I', 'J'] as $col) {
  //         if ($highestRow >= $dataStart) {
  //           $sheet->setCellValue($col . $grandRow, '=SUM(' . $col . $dataStart . ':' . $col . $highestRow . ')');
  //         } else {
  //           $sheet->setCellValue($col . $grandRow, 0);
  //         }
  //         $sheet->getStyle($col . $grandRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
  //       }

  //       $sheet->getStyle('A' . $grandRow . ':J' . $grandRow)->applyFromArray([
  //         'font' => ['bold' => true],
  //         'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
  //         'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
  //       ]);
  //       $sheet->getStyle('A' . $grandRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

  //       // Bold meta labels
  //       foreach ([3, 4] as $r) {
  //         $sheet->getStyle('A' . $r)->getFont()->setBold(true);
  //         $sheet->getStyle('D' . $r)->getFont()->setBold(true);
  //       }
  //     },
  //   ];
  // }
  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $sheet = $event->sheet->getDelegate();
        $dataStart = 8; // data mulai baris 8 (setelah 7 baris heading)

        // Merge judul
        $sheet->mergeCells('A1:J1');

        // Merge header tabel 2 baris (row 6-7)
        $sheet->mergeCells('A6:A7'); // No (rowspan 2)
        $sheet->mergeCells('B6:B7'); // Bagian (rowspan 2)
        $sheet->mergeCells('C6:C7'); // Nama Bahan (rowspan 2)
        $sheet->mergeCells('D6:E6'); // Standard Pemakaian (colspan 2)
        $sheet->mergeCells('F6:H6'); // Aktual Pemakaian (colspan 3)
        $sheet->mergeCells('I6:J6'); // Variance (colspan 2)

        // Style header tabel
        $sheet->getStyle('A6:J7')->applyFromArray([
          'font' => ['bold' => true],
          'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true,
          ],
          'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
          ],
        ]);

        $sheet->getRowDimension(6)->setRowHeight(20);
        $sheet->getRowDimension(7)->setRowHeight(18);

        // Borders & alignment data
        $highestRow = $sheet->getHighestRow();

        if ($highestRow >= $dataStart) {
          $sheet->getStyle('A' . $dataStart . ':J' . $highestRow)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
          ]);

          // Kolom angka rata kanan (D s/d J)
          foreach (['D', 'E', 'F', 'G', 'H', 'I', 'J'] as $col) {
            $sheet->getStyle($col . $dataStart . ':' . $col . $highestRow)
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          }

          // Kolom No rata tengah
          $sheet->getStyle('A' . $dataStart . ':A' . $highestRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

          // Format angka kolom data: Qty (D, F, I) 2 desimal, Harga/Total (E, G, H, J) tanpa desimal
          foreach (['D', 'F', 'I'] as $col) {
            $sheet->getStyle($col . $dataStart . ':' . $col . $highestRow)
              ->getNumberFormat()->setFormatCode('#,##0.00');
          }
          foreach (['E', 'G', 'H', 'J'] as $col) {
            $sheet->getStyle($col . $dataStart . ':' . $col . $highestRow)
              ->getNumberFormat()->setFormatCode('#,##0');
          }
        }

        // Grand Total row
        $grandRow = $highestRow + 1;
        $sheet->setCellValue('A' . $grandRow, 'Grand Total');
        $sheet->mergeCells('A' . $grandRow . ':C' . $grandRow); // No + Bagian + Nama Bahan

        foreach (['D', 'E', 'F', 'G', 'H', 'I', 'J'] as $col) {
          if ($highestRow >= $dataStart) {
            $sheet->setCellValue($col . $grandRow, '=SUM(' . $col . $dataStart . ':' . $col . $highestRow . ')');
          } else {
            $sheet->setCellValue($col . $grandRow, 0);
          }
          $sheet->getStyle($col . $grandRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // Format angka Grand Total (samakan dengan kolom data)
        foreach (['D', 'F', 'I'] as $col) {
          $sheet->getStyle($col . $grandRow)->getNumberFormat()->setFormatCode('#,##0.00');
        }
        foreach (['E', 'G', 'H', 'J'] as $col) {
          $sheet->getStyle($col . $grandRow)->getNumberFormat()->setFormatCode('#,##0');
        }

        // Styling warna biru untuk baris Grand Total
        $totalRange = 'A' . $grandRow . ':J' . $grandRow;
        $sheet->getStyle($totalRange)->applyFromArray([
          'font' => [
            'bold' => true,
            'color' => ['argb' => 'FF1E40AF'],
          ],
          'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FFDBEAFE'],
          ],
          'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
          'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle($totalRange)->getBorders()->getTop()
          ->setBorderStyle(Border::BORDER_MEDIUM)
          ->getColor()->setARGB('FF3B82F6');

        $sheet->getStyle($totalRange)->getBorders()->getBottom()
          ->setBorderStyle(Border::BORDER_MEDIUM)
          ->getColor()->setARGB('FF3B82F6');

        $sheet->getStyle('A' . $grandRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Bold meta labels
        foreach ([3, 4] as $r) {
          $sheet->getStyle('A' . $r)->getFont()->setBold(true);
          $sheet->getStyle('D' . $r)->getFont()->setBold(true);
        }
      },
    ];
  }
}
