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
        <table class="table-custom" width="100%" align="center" border="0">
        <thead>
            <tr>
                <th colspan="15" style="border: none !important; text-align: left; padding-bottom: 20px; background: #fff;">
                    
                    <div style="font-weight: bold; font-size: 18px;">{{ $namaCabang }}</div>
                    <div style="font-weight: bold; font-size: 18px;">Laporan Rincian Estimasi</div>
                    <div style="font-weight: normal; font-size: 14px;">Periode : {{ $periodeStr ?? '' }}</div>
                    
                </th>
            </tr>
            <tr>
                <th>No</th>
                <th>Nama Asuransi</th>
                <th>No. Estimasi</th>
                <th>Tanggal Estimasi</th>
                <th>No. SPK</th>
                <th>No. Polisi</th>
                <th>Tipe Kendaraan</th>
                <th>Perbaikan</th>
                <th>Spare Part</th>
                <th>Lain-lain</th>
                <th>Total R</th>
                <th>Total S</th>
                <th>Total T</th>
                <th>PPN</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandPerbaikan = 0;
                $grandSparepart = 0;
                $grandLain = 0;
                $grandTotalR = 0;
                $grandTotalS = 0;
                $grandTotalT = 0;
                $grandPPN = 0;
                $grandTotal = 0;
            @endphp
            @foreach ($datas as $row)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $row->nama_pelanggan }}</td>
                    <td>{{ $row->kode_estimasi }}</td>
                    <td>{{ blank($row->tanggal) ? '' : date('d/m/Y', strtotime($row->tanggal)) }}</td>
                    <td>{{ $row->kode_spk }}</td>
                    <td>{{ $row->no_polisi }}</td>
                    <td>{{ $row->tipe_kendaraan }}</td>
                    <td align="right">{{ number_format($row->total_perbaikan, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->total_sparepart, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->total_lain, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->perbaikan_r + $row->sparepart_r + $row->lain_r, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->perbaikan_s + $row->sparepart_s + $row->lain_s, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->perbaikan_t + $row->sparepart_t + $row->lain_t, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->ppn, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->total, 0, '.', ',') }}</td>
                </tr>
                @php
                    $grandPerbaikan += $row->total_perbaikan;
                    $grandSparepart += $row->total_sparepart;
                    $grandLain += $row->total_lain;
                    $grandTotalR += ($row->perbaikan_r + $row->sparepart_r + $row->lain_r);
                    $grandTotalS += ($row->perbaikan_s + $row->sparepart_s + $row->lain_s);
                    $grandTotalT += ($row->perbaikan_t + $row->sparepart_t + $row->lain_t);
                    $grandPPN += $row->ppn;
                    $grandTotal += $row->total;
                @endphp
            @endforeach
            <!-- GRAND TOTAL -->
            <tr>
                <th colspan="7" align="center">Grand Total</th>
                <th id="footer-perbaikan" align="right">{{ number_format($grandPerbaikan, 0, '.', ',') }}</th>
                <th id="footer-sparepart" align="right">{{ number_format($grandSparepart, 0, '.', ',') }}</th>
                <th id="footer-lain" align="right">{{ number_format($grandLain, 0, '.', ',') }}</th>
                <th id="footer-total-r" align="right">{{ number_format($grandTotalR, 0, '.', ',') }}</th>
                <th id="footer-total-s" align="right">{{ number_format($grandTotalS, 0, '.', ',') }}</th>
                <th id="footer-total-t" align="right">{{ number_format($grandTotalT, 0, '.', ',') }}</th>
                <th id="footer-ppn" align="right">{{ number_format($grandPPN, 0, '.', ',') }}</th>
                <th id="footer-total" align="right">{{ number_format($grandTotal, 0, '.', ',') }}</th>
            </tr>
        </tbody>
        </table>
	</section>
</body>

</html>