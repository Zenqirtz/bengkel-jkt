<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class EstimasiExport implements WithEvents, WithTitle
{
    protected $data;
    protected $data_perbaikan;
    protected $data_sparepart;
    protected $data_lain;
    protected $cabang;

    public function __construct($data, $data_perbaikan, $data_sparepart, $data_lain, $cabang)
    {
        $this->data           = $data;
        $this->data_perbaikan = $data_perbaikan;
        $this->data_sparepart = $data_sparepart;
        $this->data_lain      = $data_lain;
        $this->cabang         = $cabang;
    }

    public function title(): string
    {
        return 'Estimasi';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet  = $event->sheet->getDelegate();
                $data   = $this->data;
                $cabang = $this->cabang;

                // ─── FORMAT ANGKA ───────────────────────────────────────
                $fmt = '#,##0';

                // ─── LOGO ────────────────────────────────────────────────
                $logoPath = public_path('assets/img/cabang')
                    . DIRECTORY_SEPARATOR
                    . ($cabang->logo_cabang ?? '');
                $hasLogo  = is_file($logoPath);

                // ─── KOLOM WIDTH ─────────────────────────────────────────
                if ($hasLogo) {
                    $sheet->getColumnDimension('A')->setWidth(18);
                } else {
                    $sheet->getColumnDimension('A')->setWidth(7);
                }
                $sheet->getColumnDimension('B')->setWidth(35);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(8);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(18);
                $sheet->getColumnDimension('G')->setWidth(0.5); // diperkecil agar page break line tidak terlihat

                // Baris 1: khusus logo — tinggi 70pt
                $sheet->getRowDimension(1)->setRowHeight(70);

                if ($hasLogo) {
                    $drawing = new Drawing();
                    $drawing->setName('Logo');
                    $drawing->setDescription('Logo Bengkel');
                    $drawing->setPath($logoPath);
                    $drawing->setHeight(80);
                    $drawing->setResizeProportional(true);
                    $drawing->setCoordinates('A1');
                    $drawing->setOffsetX(6);
                    $drawing->setOffsetY(36);
                    $drawing->setWorksheet($sheet);
                    $sheet->mergeCells('A1:A4');
                    $sheet->getStyle('A1')->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $hdrStart = 'B';
                } else {
                    $hdrStart = 'A';
                }

                // ─── BARIS 1-4: HEADER PERUSAHAAN ────────────────────────
                $sheet->mergeCells($hdrStart . '1:F1');
                $sheet->setCellValue($hdrStart . '1', $cabang->nama_cabang ?? '');
                $sheet->getStyle($hdrStart . '1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 16, 'name' => 'Arial'],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->getRowDimension(2)->setRowHeight(16);
                $sheet->mergeCells($hdrStart . '2:F2');
                $sheet->setCellValue($hdrStart . '2', $cabang->tagline ?? 'CAR BODY REPAIR & PAINT SPECIALIST');
                $sheet->getStyle($hdrStart . '2')->getFont()->setName('Arial')->setSize(10);

                $sheet->getRowDimension(3)->setRowHeight(14);
                $alamat = trim(
                    ($cabang->alamat1 ?? '') . ' '
                        . ($cabang->alamat2 ?? '') . ' '
                        . ($cabang->alamat3 ?? '')
                );
                $sheet->mergeCells($hdrStart . '3:F3');
                $sheet->setCellValue($hdrStart . '3', $alamat);
                $sheet->getStyle($hdrStart . '3')->getFont()->setName('Arial')->setSize(10);

                $sheet->getRowDimension(4)->setRowHeight(14);
                $telp = trim($cabang->telp1 ?? $cabang->telp ?? '');
                $fax  = trim($cabang->fax1  ?? $cabang->fax  ?? '');
                $sheet->mergeCells($hdrStart . '4:F4');
                $sheet->setCellValue($hdrStart . '4', 'Telp : ' . $telp . '  Fax : ' . $fax);
                $sheet->getStyle($hdrStart . '4')->getFont()->setName('Arial')->setSize(10);

                $sheet->getStyle('A4:F4')->getBorders()
                    ->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

                // ─── BARIS 5: spasi ──────────────────────────────────────
                $sheet->getRowDimension(5)->setRowHeight(6);

                // ─── BARIS 6-9: INFO ESTIMASI ────────────────────────────
                $sheet->getRowDimension(6)->setRowHeight(14);
                $sheet->setCellValue('A6', 'Nomor');
                $sheet->setCellValue('B6', ': ' . ($data->kode_estimasi ?? ''));
                $sheet->getStyle('B6')->getFont()->setBold(true)->setSize(10)->setName('Arial');
                $sheet->mergeCells('B6:D6');
                $sheet->setCellValue('E6', 'Hal');
                $sheet->setCellValue('F6', ': Taksasi perbaikan kendaraan');

                $sheet->getRowDimension(7)->setRowHeight(14);
                $sheet->setCellValue('A7', 'Tertanggung');
                $sheet->setCellValue('B7', ': ' . ($data->tertanggung ?? $data->pemilik ?? ''));
                $sheet->mergeCells('B7:D7');
                $sheet->setCellValue('E7', 'Jenis Kendaraan');
                $sheet->setCellValue('F7', ': ' . ($data->merek_tipe ?? ''));

                $sheet->getRowDimension(8)->setRowHeight(14);
                $sheet->setCellValue('A8', 'No. Polis / No. Tiket');
                $sheet->setCellValue('B8', ': ' . ($data->no_polis ?? ''));
                $sheet->mergeCells('B8:D8');
                $sheet->setCellValue('E8', 'No. Polisi');
                $sheet->setCellValue('F8', ': ' . ($data->no_polisi ?? ''));

                $sheet->getRowDimension(9)->setRowHeight(14);
                $sheet->setCellValue('A9', 'Surveyor');
                $sheet->setCellValue('B9', ': ' . ($data->nama_surveyor ?? ''));
                $sheet->mergeCells('B9:D9');
                $sheet->setCellValue('E9', 'Klaim / SPK Ass');
                $sheet->setCellValue('F9', ': ' . ($data->kode_claim ?? ''));

                $sheet->getStyle('A6:F9')->getFont()->setName('Arial')->setSize(10);

                // ─── BARIS 10-11: TEKS PEMBUKA ───────────────────────────
                $sheet->getRowDimension(10)->setRowHeight(14);
                $sheet->mergeCells('A10:F10');
                $sheet->setCellValue('A10', 'Dengan hormat,');
                $sheet->getStyle('A10')->getFont()->setName('Arial')->setSize(10);

                $sheet->getRowDimension(11)->setRowHeight(14);
                $sheet->mergeCells('A11:F11');
                $sheet->setCellValue('A11', 'Bersama ini kami sampaikan taksasi perbaikan kendaraan dengan perincian sebagai berikut');
                $sheet->getStyle('A11')->getFont()->setName('Arial')->setSize(10);

                // ─── TRACKING BARIS ──────────────────────────────────────
                $r = 12;

                // ╔══════════════════════════╗
                // ║  SECTION 1: PERBAIKAN    ║
                // ╚══════════════════════════╝

                $sheet->getRowDimension($r)->setRowHeight(16);
                $sheet->mergeCells("A{$r}:F{$r}");
                $sheet->setCellValue("A{$r}", '1 PERBAIKAN');
                $sheet->getStyle("A{$r}")->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 11, 'name' => 'Arial'],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BFBFBF']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $r++;

                // Sub-header baris 1: No. | (span B-E) | Jumlah
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->setCellValue("A{$r}", 'No.');
                $sheet->mergeCells("B{$r}:E{$r}");
                $sheet->setCellValue("F{$r}", 'Jumlah');
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D0D0D0']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $r++;

                // Sub-header baris 2: Keterangan
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->mergeCells("B{$r}:E{$r}");
                $sheet->setCellValue("B{$r}", 'Keterangan');
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D0D0D0']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $r++;

                // Data perbaikan
                $no = 1;
                foreach ($this->data_perbaikan as $item) {
                    $label = $item->jenis_pekerjaan
                        . ($item->panel_pekerjaan ? ' - ' . $item->panel_pekerjaan : '');
                    $sheet->getRowDimension($r)->setRowHeight(14);
                    $sheet->setCellValue("A{$r}", $no++);
                    $sheet->mergeCells("B{$r}:E{$r}");
                    $sheet->setCellValue("B{$r}", $label);
                    $sheet->setCellValue("F{$r}", $item->harga ?? 0);
                    $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                        'font'    => ['size' => 10, 'name' => 'Arial'],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode($fmt);
                    $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $r++;
                }

                $totalPerbaikan = $data->total_perbaikan ?? 0;
                $discPerbaikan  = $data->disc_perbaikan  ?? 0;
                $subPerbaikan   = $totalPerbaikan - $discPerbaikan;
                $discPct        = ($totalPerbaikan > 0 && $discPerbaikan > 0)
                    ? number_format(($discPerbaikan / $totalPerbaikan) * 100, 0) . '%'
                    : '0%';

                // Total
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->mergeCells("C{$r}:E{$r}");
                $sheet->setCellValue("C{$r}", 'Total');
                $sheet->setCellValue("F{$r}", $totalPerbaikan);
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode($fmt);
                $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $r++;

                // Discount
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->mergeCells("C{$r}:D{$r}");
                $sheet->setCellValue("C{$r}", 'Discount');
                $sheet->setCellValue("E{$r}", $discPct);
                $sheet->setCellValue("F{$r}", $discPerbaikan);
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'    => ['size' => 10, 'name' => 'Arial'],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode($fmt);
                $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $r++;

                // Subtotal
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->setCellValue("F{$r}", $subPerbaikan);
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode($fmt);
                $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $r++;

                // Spasi
                $sheet->getRowDimension($r)->setRowHeight(6);
                $r++;

                // ╔══════════════════════════╗
                // ║  SECTION 2: SPAREPART    ║
                // ╚══════════════════════════╝

                $sheet->getRowDimension($r)->setRowHeight(16);
                $sheet->mergeCells("A{$r}:F{$r}");
                $sheet->setCellValue("A{$r}", '2 SPAREPART');
                $sheet->getStyle("A{$r}")->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 11, 'name' => 'Arial'],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BFBFBF']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $r++;

                // Sub-header baris 1
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->setCellValue("A{$r}", 'No.');
                $sheet->setCellValue("D{$r}", 'Qty');
                $sheet->setCellValue("E{$r}", 'Harga');
                $sheet->setCellValue("F{$r}", 'Jumlah');
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D0D0D0']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $r++;

                // Sub-header baris 2
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->mergeCells("B{$r}:C{$r}");
                $sheet->setCellValue("B{$r}", 'Keterangan');
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D0D0D0']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $r++;

                // Data sparepart
                $no = 1;
                foreach ($this->data_sparepart as $item) {
                    $sheet->getRowDimension($r)->setRowHeight(14);
                    $sheet->setCellValue("A{$r}", $no++);
                    $sheet->setCellValue("B{$r}", $item->nama_sparepart ?? '');
                    $sheet->setCellValue("C{$r}", $item->no_sparepart ?? '');
                    $sheet->setCellValue("D{$r}", $item->qty ?? 0);
                    $sheet->setCellValue("E{$r}", $item->harga ?? 0);
                    $sheet->setCellValue("F{$r}", $item->jumlah ?? 0);
                    $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                        'font'    => ['size' => 10, 'name' => 'Arial'],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    foreach (['E', 'F'] as $col) {
                        $sheet->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode($fmt);
                        $sheet->getStyle("{$col}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }
                    $r++;
                }

                $totalSparepart = $data->total_sparepart ?? 0;
                $discSparepart  = $data->disc_sparepart  ?? 0;
                $subSparepart   = $totalSparepart - $discSparepart;
                $discPctS       = ($totalSparepart > 0 && $discSparepart > 0)
                    ? number_format(($discSparepart / $totalSparepart) * 100, 0) . '%'
                    : '0%';

                // Total sparepart
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->mergeCells("C{$r}:E{$r}");
                $sheet->setCellValue("C{$r}", 'Total');
                $sheet->setCellValue("F{$r}", $totalSparepart);
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode($fmt);
                $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $r++;

                // Discount sparepart
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->mergeCells("C{$r}:D{$r}");
                $sheet->setCellValue("C{$r}", 'Discount');
                $sheet->setCellValue("E{$r}", $discPctS);
                $sheet->setCellValue("F{$r}", $discSparepart);
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'    => ['size' => 10, 'name' => 'Arial'],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode($fmt);
                $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $r++;

                // Subtotal sparepart
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->setCellValue("F{$r}", $subSparepart);
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode($fmt);
                $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $r++;

                // Spasi
                $sheet->getRowDimension($r)->setRowHeight(6);
                $r++;

                // ╔══════════════════════════╗
                // ║  SECTION 3: LAIN-LAIN    ║
                // ╚══════════════════════════╝

                $sheet->getRowDimension($r)->setRowHeight(16);
                $sheet->mergeCells("A{$r}:F{$r}");
                $sheet->setCellValue("A{$r}", '3 LAIN-LAIN');
                $sheet->getStyle("A{$r}")->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 11, 'name' => 'Arial'],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BFBFBF']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $r++;

                // Sub-header baris 1
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->setCellValue("A{$r}", 'No.');
                $sheet->setCellValue("F{$r}", 'Jumlah');
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D0D0D0']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $r++;

                // Sub-header baris 2
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->mergeCells("B{$r}:E{$r}");
                $sheet->setCellValue("B{$r}", 'Keterangan');
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D0D0D0']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $r++;

                // Data lain-lain
                $no = 1;
                foreach ($this->data_lain as $item) {
                    $sheet->getRowDimension($r)->setRowHeight(14);
                    $sheet->setCellValue("A{$r}", $no++);
                    $sheet->mergeCells("B{$r}:E{$r}");
                    $sheet->setCellValue("B{$r}", $item->memo ?? '');
                    $sheet->setCellValue("F{$r}", $item->harga ?? 0);
                    $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                        'font'    => ['size' => 10, 'name' => 'Arial'],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode($fmt);
                    $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $r++;
                }

                $totalLain = $data->total_lain ?? 0;
                $discLain  = $data->disc_lain  ?? 0;
                $subLain   = $totalLain - $discLain;
                $discPctL  = ($totalLain > 0 && $discLain > 0)
                    ? number_format(($discLain / $totalLain) * 100, 0) . '%'
                    : '0%';

                // Total lain
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->mergeCells("C{$r}:E{$r}");
                $sheet->setCellValue("C{$r}", 'Total');
                $sheet->setCellValue("F{$r}", $totalLain);
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode($fmt);
                $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $r++;

                // Discount lain
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->mergeCells("C{$r}:D{$r}");
                $sheet->setCellValue("C{$r}", 'Discount');
                $sheet->setCellValue("E{$r}", $discPctL);
                $sheet->setCellValue("F{$r}", $discLain);
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'    => ['size' => 10, 'name' => 'Arial'],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode($fmt);
                $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $r++;

                // Subtotal lain
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->setCellValue("F{$r}", $subLain);
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode($fmt);
                $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $r++;

                // Spasi
                $sheet->getRowDimension($r)->setRowHeight(6);
                $r++;

                // ╔══════════════════════════════════╗
                // ║  GRAND TOTAL, PPN, TOTAL ESTIMASI║
                // ╚══════════════════════════════════╝
                $grandTotal = $data->total ?? 0;
                $ppn        = $data->ppn   ?? 0;
                $totalAkhir = $data->total ?? 0;
                $grandTotal = $grandTotal - $ppn;

                // Grand Total
                $sheet->getRowDimension($r)->setRowHeight(16);
                $sheet->mergeCells("C{$r}:D{$r}");
                $sheet->setCellValue("C{$r}", 'Grand Total');
                $sheet->setCellValue("E{$r}", $grandTotal);
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 11, 'name' => 'Arial'],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode($fmt);
                $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $r++;

                // PPN
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->mergeCells("C{$r}:D{$r}");
                $sheet->setCellValue("C{$r}", 'PPN');
                $sheet->setCellValue("E{$r}", $ppn);
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode($fmt);
                $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $r++;

                // Total Estimasi
                $sheet->getRowDimension($r)->setRowHeight(18);
                $sheet->mergeCells("C{$r}:D{$r}");
                $sheet->setCellValue("C{$r}", 'Total Estimasi');
                $sheet->setCellValue("E{$r}", $totalAkhir);
                $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 12, 'name' => 'Arial'],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D0D0D0']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                ]);
                $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode($fmt);
                $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $r++;

                // Spasi
                $sheet->getRowDimension($r)->setRowHeight(6);
                $r++;

                // ─── TERBILANG ───────────────────────────────────────────
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->setCellValue("A{$r}", 'Terbilang   :');
                $sheet->getStyle("A{$r}")->getFont()->setName('Arial')->setSize(10)->setBold(true);
                $r++;

                $sheet->getRowDimension($r)->setRowHeight(22);
                $sheet->mergeCells("A{$r}:F{$r}");
                $sheet->setCellValue("A{$r}", strtoupper(\Helper::terbilang_rupiah($totalAkhir)));
                $sheet->getStyle("A{$r}")->applyFromArray([
                    'font'      => ['italic' => true, 'bold' => true, 'size' => 9, 'name' => 'Arial'],
                    'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $r++;

                // Spasi
                $sheet->getRowDimension($r)->setRowHeight(6);
                $r++;

                // ─── PENUTUP SURAT ───────────────────────────────────────
                $sheet->getRowDimension($r)->setRowHeight(28);
                $sheet->mergeCells("A{$r}:F{$r}");
                $sheet->setCellValue(
                    "A{$r}",
                    'Demikian taksasi perbaikan kendaraan tersebut di atas kami sampaikan, '
                        . 'atas perhatian dan persetujuannya kami ucapkan terima kasih.'
                );
                $sheet->getStyle("A{$r}")->applyFromArray([
                    'font'      => ['size' => 10, 'name' => 'Arial'],
                    'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $r++;

                // Spasi
                $sheet->getRowDimension($r)->setRowHeight(6);
                $r++;

                // ─── TANDA TANGAN ────────────────────────────────────────
                $sheet->getRowDimension($r)->setRowHeight(14);
                $tglHariIni = 'Jakarta, ' . date('d-M-Y', strtotime($data->tgl_estimasi));
                $sheet->setCellValue("A{$r}", $tglHariIni);
                $sheet->getStyle("A{$r}")->getFont()->setName('Arial')->setSize(10);
                $r++;

                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->setCellValue("A{$r}", 'Hormat kami,');
                $sheet->getStyle("A{$r}")->getFont()->setName('Arial')->setSize(10);
                $r++;

                // Spasi TTD (4 baris)
                for ($i = 0; $i < 4; $i++) {
                    $sheet->getRowDimension($r)->setRowHeight(14);
                    $r++;
                }

                // Nama penandatangan
                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->setCellValue("A{$r}", 'AGUS RAHMAT');
                $sheet->getStyle("A{$r}")->getFont()->setName('Arial')->setSize(10)->setBold(true);
                $r++;

                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->setCellValue("A{$r}", 'Workshop Manager');
                $sheet->getStyle("A{$r}")->getFont()->setName('Arial')->setSize(10);
                $r++;

                $sheet->getRowDimension($r)->setRowHeight(14);
                $sheet->setCellValue("A{$r}", 'Catatan :');
                $sheet->getStyle("A{$r}")->getFont()->setName('Arial')->setSize(10);
                $r++;

                // ─── PRINT SETTINGS ──────────────────────────────────────
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);

                $sheet->getPageMargins()
                    ->setTop(0.75)->setBottom(0.75)
                    ->setLeft(0.7)->setRight(0.7);

                // $sheet->getHeaderFooter()
                //     ->setOddHeader('&C&"Arial,Bold"&12' . ($cabang->nama_cabang ?? ''));
            },
        ];
    }
}
