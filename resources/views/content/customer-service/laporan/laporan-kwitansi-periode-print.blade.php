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
                <th colspan="14" style="border: none !important; text-align: left; padding-bottom: 20px; background: #fff;">
                  <div style="font-weight: bold; font-size: 18px;">{{ $namaCabang }}</div>
                  <div style="font-weight: bold; font-size: 18px;">{{ $title }}</div>
                  <div style="font-weight: normal; font-size: 14px;">Periode : {{ $periodeStr ?? '' }}</div>
                </th>
            </tr>
            <tr>
                <th>No</th>
                <th>Tanggal Lunas</th>
                <th>No. Voucher</th>
                <th>No. Kwitansi</th>
                <th>No. SPK</th>
                <th>No. Polisi</th>
                <th>Tertanggung</th>
                <th>Nama Asuransi</th>
                <th>No. Invoice</th>
                <th>Tanggal Invoice</th>
                <th>Kas</th>
                <th>Free</th>
                <th>Bank</th>
                <th>Total OR</th>
            </tr>
        </thead>
        <tbody>
            @php
            $grandTotalKas = 0;
            $grandTotalFree = 0;
            $grandTotalBank = 0;
            $grandTotalOR = 0;
            @endphp
            @foreach ($datas as $row)
                <tr>
                  <td>{{ $no++ }}</td>
                  <td>{{ blank($row->tanggal_lunas_or) ? '' : date("d/m/Y", strtotime($row->tanggal_lunas_or ))}}</td>
                  <td>{{ $row->kode_voucher }}</td>
                  <td>{{ $row->no_kwitansi }}</td>
                  <td>{{ $row->kode_spk }}</td>
                  <td>{{ $row->no_polisi }}</td>
                  <td>{{ $row->tertanggung }}</td>
                  <td>{{ $row->nama_pelanggan }}</td>
                  <td>{{ $row->no_invoice }}</td>
                  <td>{{ blank($row->tgl_invoice) ? '' : date("d/m/Y", strtotime($row->tgl_invoice ))}}</td>
                  <td align="right">{{ number_format($row->kas,0,".",",") }}</td>
                  <td align="right">{{ number_format($row->free,0,".",",") }}</td>
                  <td align="right">{{ number_format($row->bank,0,".",",") }}</td>
                  <td align="right">{{ number_format($row->total_or,0,".",",") }}</td>
                </tr>
                @php
                $grandTotalKas += $row->kas;
                $grandTotalFree += $row->free;
                $grandTotalBank += $row->bank;
                $grandTotalOR += $row->total_or;
                @endphp
            @endforeach
            <!-- GRAND TOTAL -->
            <tr>
              <td class="footer-total" colspan="10" align="center">Grand Total</td>
              <td class="footer-total" align="right">{{ number_format($grandTotalKas, 0, '.', ',') }}</td>
              <td class="footer-total" align="right">{{ number_format($grandTotalFree, 0, '.', ',') }}</td>
              <td class="footer-total" align="right">{{ number_format($grandTotalBank, 0, '.', ',') }}</td>
              <td class="footer-total" align="right">{{ number_format($grandTotalOR, 0, '.', ',') }}</td>
            </tr>
        </tbody>
        </table>
	</section>
</body>

</html>