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
            font-size: 12px;
        }
        .table-custom td {
            border: 1px solid #000 !important;
            padding: 6px;
            font-size: 11px;
        }

        .table-custom2 {
            border-collapse: collapse; /* Ini kunci agar border menyatu */
            width: 100%; /* Lebar bisa dipindah ke sini agar HTML lebih bersih */
        }
        
        .table-custom2 th {
            border: 0px solid #000 !important;
            padding: 2px;
            font-size: 12px;
        }
        .table-custom2 td {
            border: 0px solid #000 !important;
            padding: 2px;
            font-size: 11px;
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
                    <div style="font-weight: bold; font-size: 18px;">{{ $title }}</div>
                    <div style="font-weight: bold; font-size: 18px;">Bank {{ $nama_bank }}</div>
                    <div style="font-weight: normal; font-size: 14px;">Per Tanggal : {{ $periodeStr ?? '' }}</div>
                    
                </th>
            </tr>
            <tr>
                <th>No</th>
                <th>Tanggal Voucher</th>
                <th>Tanggal CH BG</th>
                <th>Pelanggan &amp; Memo</th>
                <th>No. CH BG</th>
                <th>Voucher Masuk</th>
                <th>Voucher Keluar</th>
                <th>Debit</th>
                <th>Kredit</th>
                <th>Saldo</th>
                <th>Tanggal Kliring</th>
            </tr>
        </thead>
        <tbody>
            @php
            $saldo = 0;
            $grandTotalDebet = 0;
            $grandTotalKredit = 0;
            @endphp
            @foreach ($datas as $row)
                @php 
                $saldo = ($no > 0) ? ($saldo + $row->debit + $row->kredit) : $row->amount; 
                $grandTotalDebet += $row->debit;
                $grandTotalKredit += $row->kredit;
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ blank($row->tanggal) ? '' : date('d/m/Y', strtotime($row->tanggal)) }}</td>
                    <td>{{ blank($row->tanggal_ch_bg) ? '' : date('d/m/Y', strtotime($row->tanggal_ch_bg)) }}</td>
                    <td>{{ $row->memo }}</td>
                    <td>{{ $row->no_ch_bg }}</td>
                    <td>{{ $row->no_voucher_in }}</td>
                    <td>{{ $row->no_voucher_out }}</td>
                    <td align="right">{{ number_format($row->debit, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($row->kredit * -1, 0, '.', ',') }}</td>
                    <td align="right">{{ number_format($saldo, 0, '.', ',') }}</td>
                    <td>{{ blank($row->tanggal_kliring) ? '' : date('d/m/Y', strtotime($row->tanggal_kliring)) }}</td>
                </tr>
            @endforeach
            <!-- GRAND TOTAL -->
            <tr>
                <th colspan="7" align="center">Total</th>
                <th align="right">{{ number_format($grandTotalDebet, 0, '.', ',') }}</th>
                <th align="right">{{ number_format($grandTotalKredit * -1, 0, '.', ',') }}</th>
                <th align="right">{{ number_format($saldo, 0, '.', ',') }}</th>
                <th>&nbsp;</th>
            </tr>
        </tbody>
        </table>
	</section>
</body>

</html>