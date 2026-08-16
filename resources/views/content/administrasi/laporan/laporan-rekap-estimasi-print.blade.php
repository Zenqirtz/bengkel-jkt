<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<title>{{ @$title }}</title>

	<link href="{{ asset('assets/css/paper.css') }}" rel="stylesheet" type="text/css">
	<script>
		function printData() {
			window.print();
		}
	</script>
	<style>
		.label-col { width: 130px; }
		.sep-col { width: 10px; text-align: center; }
		.val-col { font-weight: 500; }
		.title { font-size: 18px; }
		.subtitle { font-size: 12px; }

         /* Table overrides */
        .table-custom {
            border-collapse: collapse; /* Ini kunci agar border menyatu */
            width: 100%; /* Lebar bisa dipindah ke sini agar HTML lebih bersih */
        }
        
        .table-custom th {
            border: 1px solid #000 !important;
            padding: 6px;
        }
        .table-custom td {
            border: 1px solid #000 !important;
            padding: 6px;
        }

        .table-custom2 {
            border-collapse: collapse; /* Ini kunci agar border menyatu */
            width: 100%; /* Lebar bisa dipindah ke sini agar HTML lebih bersih */
        }
        
        .table-custom2 th {
            border: 0px solid #000 !important;
            padding: 2px;
        }
        .table-custom2 td {
            border: 0px solid #000 !important;
            padding: 2px;
        }

        /* --- TAMBAHAN UNTUK FIX PAGINATION --- */
        /* 1. Bebaskan tinggi .sheet agar tidak memotong isi (berlaku di layar & cetak) */
        .sheet {
            height: auto !important;
            min-height: 209mm; /* Tinggi minimal A4 Landscape */
            overflow: visible !important;
        }

        /* 2. Aturan khusus saat jendela Print terbuka */
        @media print {
            @page {
                size: F4 landscape; /* Opsional: Sesuaikan ukuran kertas jika perlu */
                margin: 10mm;       /* Ini yang akan memberi jarak aman di halaman 1, 2, 3, dst */
            }

            body, .sheet {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            /* Mengulang header tabel di bagian atas setiap halaman baru */
            thead {
                display: table-header-group;
            }

            /* Mencegah satu baris data (TR) terpotong separuh di antara 2 halaman */
            tr {
                page-break-inside: avoid;
            }
        }
	</style>
</head>

<body class="F4 landscape" onload="printData()">
	<section class="sheet padding-10mm">
        {{-- <table class="table" width="100%" align="left" border="0">
        <thead>
            <tr>
                <th align="left">{{ $namaCabang }}</th>
            </tr>
            <tr>
                <th align="left">Laporan Rekap Estimasi</th>
            </tr>
            <tr>
                <th align="left">Periode : {{ $periodeStr }}</th>
            </tr>
        </thead>
        </table> --}}
        <table class="table-custom" width="100%" align="center" border="0">
        <thead>
            <tr>
                <th colspan="14" style="border: none !important; text-align: left; padding-bottom: 20px; background: #fff;">
                    
                    <div style="font-weight: bold; font-size: 18px;">{{ $namaCabang }}</div>
                    <div style="font-weight: bold; font-size: 18px;">Laporan Rekap Estimasi</div>
                    <div style="font-weight: normal; font-size: 14px;">Periode : {{ $periodeStr ?? '' }}</div>
                    
                </th>
            </tr>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Nama Asuransi</th>
                <th rowspan="2">Unit</th>
                <th colspan="3" align="center">Perbaikan</th>
                <th colspan="3" align="center">Sparepart</th>
                <th colspan="3" align="center">Lain-lain</th>
                <th rowspan="2">PPN</th>
                <th rowspan="2">Total</th>
            </tr>
            <tr>
                <th align="center">R</th>
                <th align="center">S</th>
                <th align="center">T</th>
                <th align="center">R</th>
                <th align="center">S</th>
                <th align="center">T</th>
                <th align="center">R</th>
                <th align="center">S</th>
                <th align="center">T</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandUnit = 0;
                $grandPerbaikanR = 0;
                $grandPerbaikanS = 0;
                $grandPerbaikanT = 0;
                $grandSparepartR = 0;
                $grandSparepartS = 0;
                $grandSparepartT = 0;
                $grandLainR = 0;
                $grandLainS = 0;
                $grandLainT = 0;
                $grandPPN = 0;
                $grandTotal = 0;
            @endphp
            @foreach ($datas as $row)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $row->nama_pelanggan }}</td>
                    <td align="center">{{ $row->unit }}</td>
                    <td align="right">{{ number_format($row->perbaikan_r, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->perbaikan_s, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->perbaikan_t, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->sparepart_r, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->sparepart_s, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->sparepart_t, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->lain_r, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->lain_s, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->lain_t, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->ppn, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->total, 0, '.', ',') }}</td>
                </tr>
                @php
                    $grandUnit += $row->unit;
                    $grandPerbaikanR += $row->perbaikan_r;
                    $grandPerbaikanS += $row->perbaikan_s;
                    $grandPerbaikanT += $row->perbaikan_t;
                    $grandSparepartR += $row->sparepart_r;
                    $grandSparepartS += $row->sparepart_s;
                    $grandSparepartT += $row->sparepart_t;
                    $grandLainR += $row->lain_r;
                    $grandLainS += $row->lain_s;
                    $grandLainT += $row->lain_t;
                    $grandPPN += $row->ppn;
                    $grandTotal += $row->total;
                @endphp
            @endforeach
            <!-- GRAND TOTAL -->
            <tr>
                <th colspan="2" align="center">Grand Total</th>
                <th id="footer-unit" align="center">{{ number_format($grandUnit, 0, '.', ',') }}</th>
                <th id="footer-perbaikan-r" align="right">{{ number_format($grandPerbaikanR, 0, '.', ',') }}</th>
                <th id="footer-perbaikan-s" align="right">{{ number_format($grandPerbaikanS, 0, '.', ',') }}</th>
                <th id="footer-perbaikan-t" align="right">{{ number_format($grandPerbaikanT, 0, '.', ',') }}</th>
                <th id="footer-sparepart-r" align="right">{{ number_format($grandSparepartR, 0, '.', ',') }}</th>
                <th id="footer-sparepart-s" align="right">{{ number_format($grandSparepartS, 0, '.', ',') }}</th>
                <th id="footer-sparepart-t" align="right">{{ number_format($grandSparepartT, 0, '.', ',') }}</th>
                <th id="footer-lain-r" align="right">{{ number_format($grandLainR, 0, '.', ',') }}</th>
                <th id="footer-lain-s" align="right">{{ number_format($grandLainS, 0, '.', ',') }}</th>
                <th id="footer-lain-t" align="right">{{ number_format($grandLainT, 0, '.', ',') }}</th>
                <th id="footer-ppn" align="right">{{ number_format($grandPPN, 0, '.', ',') }}</th>
                <th id="footer-total" align="right">{{ number_format($grandTotal, 0, '.', ',') }}</th>
            </tr>
        </tbody>
        {{-- <tfoot>
            <tr>
                <th colspan="2" align="center">Grand Total</th>
                <th id="footer-unit" align="center">{{ number_format($grandUnit, 0, '.', ',') }}</th>
                <th id="footer-perbaikan-r" align="right">{{ number_format($grandPerbaikanR, 0, '.', ',') }}</th>
                <th id="footer-perbaikan-s" align="right">{{ number_format($grandPerbaikanS, 0, '.', ',') }}</th>
                <th id="footer-perbaikan-t" align="right">{{ number_format($grandPerbaikanT, 0, '.', ',') }}</th>
                <th id="footer-sparepart-r" align="right">{{ number_format($grandSparepartR, 0, '.', ',') }}</th>
                <th id="footer-sparepart-s" align="right">{{ number_format($grandSparepartS, 0, '.', ',') }}</th>
                <th id="footer-sparepart-t" align="right">{{ number_format($grandSparepartT, 0, '.', ',') }}</th>
                <th id="footer-lain-r" align="right">{{ number_format($grandLainR, 0, '.', ',') }}</th>
                <th id="footer-lain-s" align="right">{{ number_format($grandLainS, 0, '.', ',') }}</th>
                <th id="footer-lain-t" align="right">{{ number_format($grandLainT, 0, '.', ',') }}</th>
                <th id="footer-ppn" align="right">{{ number_format($grandPPN, 0, '.', ',') }}</th>
                <th id="footer-total" align="right">{{ number_format($grandTotal, 0, '.', ',') }}</th>
            </tr>
        </tfoot> --}}
        </table>
	</section>
</body>

</html>