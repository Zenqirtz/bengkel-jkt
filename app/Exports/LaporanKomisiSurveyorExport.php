<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class LaporanKomisiSurveyorExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents, WithColumnFormatting
{
  protected $params;
  protected $cabang;
  protected $periode;
  protected $rowNumber = 0;
  protected $data;

  public function __construct(array $params, $cabang, $periode)
  {
    $this->params = $params;
    $this->cabang = $cabang;
    $this->periode = $periode;
  }

  public function collection()
  {
    $query = DB::table('v_rep_komisi_surveyor as k')
      ->where('k.kode_cabang', $this->cabang['kode'])
      ->select([
        'k.kode_spk',
        'k.tgl_masuk',
        'k.no_polisi',
        'k.merek_tipe',
        'k.nama_pelanggan',
        'k.no_polis',
        'k.kode_estimasi',
        'k.total_sparepart',
        'k.total_lain',
        'k.total_perbaikan',
      ]);

    // Filtering
    if (!empty($this->params['tgl_awal'])) {
      try {
        $startDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_awal'])->format('Y-m-d');
        $query->whereDate('k.tgl_masuk', '>=', $startDate);
      } catch (\Exception $e) {
      }
    }

    if (!empty($this->params['tgl_akhir'])) {
      try {
        $endDate = Carbon::createFromFormat('d/m/Y', $this->params['tgl_akhir'])->format('Y-m-d');
        $query->whereDate('k.tgl_masuk', '<=', $endDate);
      } catch (\Exception $e) {
      }
    }

    $this->data = $query->orderBy('k.tgl_masuk', 'asc')->get();

    return $this->data;
  }

  public function headings(): array
  {
    return [
      [$this->cabang['nama']],
      ['Laporan Komisi Surveyor'],
      ['Periode : ' . $this->periode],
      [''],
      [
        'No',
        'No. SPK',
        'Tanggal Masuk',
        'No. Polisi',
        'Tipe Kendaraan',
        'Nama Asuransi',
        'No. Polis',
        'No. Estimasi',
        'Jasa',
        'Sparepart',
        'Lain',
        'Jumlah',
      ]
    ];
  }

  public function map($row): array
  {
    $this->rowNumber++;

    // Hitung jasa = total_perbaikan - (total_sparepart + total_lain)
    // Gunakan abs() untuk mengambil nilai absolut (menghilangkan minus)
    $totalJasa = abs($row->total_perbaikan - ($row->total_sparepart + $row->total_lain));

    return [
      $this->rowNumber,
      $row->kode_spk,
      $row->tgl_masuk ? date("d/m/Y", strtotime($row->tgl_masuk)) : '',
      $row->no_polisi,
      $row->merek_tipe,
      $row->nama_pelanggan,
      $row->no_polis, // Tanpa kutip, akan diformat di AfterSheet
      $row->kode_estimasi,
      $totalJasa,
      $row->total_sparepart,
      $row->total_lain,
      $row->total_perbaikan,
    ];
  }

  public function columnFormats(): array
  {
    return [
      'G' => '@', // No. Polis sebagai text (@ adalah format code untuk text)
      'I' => '#,##0', // Jasa - format angka dengan pemisah ribuan
      'J' => '#,##0', // Sparepart
      'K' => '#,##0', // Lain
      'L' => '#,##0', // Jumlah
    ];
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
          'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
          'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
          ],
        ]
      ],
    ];
  }

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        // Merge cells untuk header
        $event->sheet->mergeCells('A1:L1');
        $event->sheet->mergeCells('A2:L2');
        $event->sheet->mergeCells('A3:L3');

        $highestRow = $event->sheet->getHighestRow();

        // Apply borders ke semua data
        $event->sheet->getStyle('A5:L' . $highestRow)->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        // Alignment untuk kolom tertentu
        $event->sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('C6:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle('I6:L' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Set lebar kolom No. Polis agar cukup lebar
        $event->sheet->getColumnDimension('G')->setWidth(25);

        // Format No. Polis sebagai text dengan cara set explicit text
        for ($row = 6; $row <= $highestRow; $row++) {
          $cellValue = $event->sheet->getCell('G' . $row)->getValue();
          $event->sheet->setCellValueExplicit(
            'G' . $row,
            $cellValue,
            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
          );
        }

        // Hitung total
        $grandJasa = 0;
        $grandSparepart = 0;
        $grandLain = 0;
        $grandJumlah = 0;

        foreach ($this->data as $row) {
          // Gunakan abs() untuk nilai absolut
          $totalJasa = abs($row->total_perbaikan - ($row->total_sparepart + $row->total_lain));
          $grandJasa += $totalJasa;
          $grandSparepart += $row->total_sparepart;
          $grandLain += $row->total_lain;
          $grandJumlah += $row->total_perbaikan;
        }

        // Tambahkan baris TOTAL
        $totalRow = $highestRow + 1;
        $event->sheet->mergeCells('A' . $totalRow . ':H' . $totalRow);
        $event->sheet->setCellValue('A' . $totalRow, 'TOTAL');
        $event->sheet->setCellValue('I' . $totalRow, $grandJasa);
        $event->sheet->setCellValue('J' . $totalRow, $grandSparepart);
        $event->sheet->setCellValue('K' . $totalRow, $grandLain);
        $event->sheet->setCellValue('L' . $totalRow, $grandJumlah);

        // Format angka untuk baris total
        $event->sheet->getStyle('I' . $totalRow . ':L' . $totalRow)
          ->getNumberFormat()
          ->setFormatCode('#,##0');

        // Style untuk baris TOTAL
        $event->sheet->getStyle('A' . $totalRow . ':L' . $totalRow)->applyFromArray([
          'font' => ['bold' => true],
          'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_RIGHT,
          ],
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
            ],
          ],
        ]);

        // Alignment khusus untuk cell TOTAL (rata kanan)
        $event->sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      },
    ];
  }
}
