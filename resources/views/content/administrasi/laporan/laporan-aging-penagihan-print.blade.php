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

@if($datafilter['jenis_laporan'] == "rekap")
<body class="F4 landscape" onload="printData()">
	<section class="sheet padding-10mm">
        <table class="table-custom" width="100%" align="center" border="0">
        <thead>
            <tr>
              <th colspan="22" style="border: none !important; text-align: left; padding-bottom: 20px; background: #fff;"> 
                <div style="font-weight: bold; font-size: 18px;">{{ $namaCabang }}</div>
                <div style="font-weight: bold; font-size: 18px;">Laporan Rekap Kwitansi Belum Ditagih [Outstanding Penagihan]</div>
                <div style="font-weight: normal; font-size: 14px;">Periode : {{ $periodeStr ?? '' }}</div>
              </th>
            </tr>
            <tr>
              <th rowspan="2">No</th>
              <th rowspan="2">Nama Asuransi</th>
              <th colspan="4">1 - 2 Minggu</th>
              <th colspan="4">3 - 5 Minggu</th>
              <th colspan="4">> 5 Minggu</th>
              <th colspan="4">Belum Diterima</th>
              <th colspan="4">Total</th>
            </tr>
            <tr>
              <!-- 1-2 Minggu -->
              <th>Unit</th>
              <th>%</th>
              <th>Rupiah</th>
              <th>%</th>
              <!-- 3-5 Minggu -->
              <th>Unit</th>
              <th>%</th>
              <th>Rupiah</th>
              <th>%</th>
              <!-- >5 Minggu -->
              <th>Unit</th>
              <th>%</th>
              <th>Rupiah</th>
              <th>%</th>
              <!-- Belum Diterima -->
              <th>Unit</th>
              <th>%</th>
              <th>Rupiah</th>
              <th>%</th>
              <!-- Total -->
              <th>Unit</th>
              <th>%</th>
              <th>Rupiah</th>
              <th>%</th>
            </tr>
        </thead>
        <tbody>
            @php
            $unit_1_2 = 0;
            $unit_1_2_persen = 0;
            $nilai_1_2 = 0;
            $nilai_1_2_persen = 0;
            $unit_3_5 = 0;
            $unit_3_5_persen = 0;
            $nilai_3_5 = 0;
            $nilai_3_5_persen = 0;
            $unit_5 = 0;
            $unit_5_persen = 0;
            $nilai_5 = 0;
            $nilai_5_persen = 0;
            $unit_blm_dikirim = 0;
            $unit_blm_dikirim_persen = 0;
            $nilai_blm_dikirim = 0;
            $nilai_blm_dikirim_persen = 0;
            $unit_total = 0;
            $unit_total_persen = 0;
            $nilai_total = 0;
            $nilai_total_persen = 0;
            @endphp

            @foreach ($datas as $row)
            @php
            $unit_1_2                   += $row['unit_1_2'];
            $unit_1_2_persen            += $row['unit_1_2_persen'];
            $nilai_1_2                  += $row['nilai_1_2'];
            $nilai_1_2_persen           += $row['nilai_1_2_persen'];
            $unit_3_5                   += $row['unit_3_5'];
            $unit_3_5_persen            += $row['unit_3_5_persen'];
            $nilai_3_5                  += $row['nilai_3_5'];
            $nilai_3_5_persen           += $row['nilai_3_5_persen'];
            $unit_5                     += $row['unit_5'];
            $unit_5_persen              += $row['unit_5_persen'];
            $nilai_5                    += $row['nilai_5'];
            $nilai_5_persen             += $row['nilai_5_persen'];
            $unit_blm_dikirim           += $row['unit_blm_dikirim'];
            $unit_blm_dikirim_persen    += $row['unit_blm_dikirim_persen'];
            $nilai_blm_dikirim          += $row['nilai_blm_dikirim'];
            $nilai_blm_dikirim_persen   += $row['nilai_blm_dikirim_persen'];
            $unit_total                 += $row['unit_total'];
            $unit_total_persen          += $row['unit_total_persen'];
            $nilai_total                += $row['nilai_total'];
            $nilai_total_persen         += $row['nilai_total_persen'];
            @endphp
            <tr>
                <td>{{ $no++ }}</td>
                <td nowrap>{{ $row['nama_pelanggan'] }}</td>
                <td align="center">{{ number_format($row['unit_1_2'],0,".",",") }}</td>
                <td align="center">{{ number_format($row['unit_1_2_persen'],2,".",",") }}</td>
                <td align="right">{{ number_format($row['nilai_1_2'],0,".",",") }}</td>
                <td align="center">{{ number_format($row['nilai_1_2_persen'],2,".",",") }}</td>
                <td align="center">{{ number_format($row['unit_3_5'],0,".",",") }}</td>
                <td align="center">{{ number_format($row['unit_3_5_persen'],2,".",",") }}</td>
                <td align="right">{{ number_format($row['nilai_3_5'],0,".",",") }}</td>
                <td align="center">{{ number_format($row['nilai_3_5_persen'],2,".",",") }}</td>
                <td align="center">{{ number_format($row['unit_5'],0,".",",") }}</td>
                <td align="center">{{ number_format($row['unit_5_persen'],2,".",",") }}</td>
                <td align="right">{{ number_format($row['nilai_5'],0,".",",") }}</td>
                <td align="center">{{ number_format($row['nilai_5_persen'],2,".",",") }}</td>
                <td align="center">{{ number_format($row['unit_blm_dikirim'],0,".",",") }}</td>
                <td align="center">{{ number_format($row['unit_blm_dikirim_persen'],2,".",",") }}</td>
                <td align="right">{{ number_format($row['nilai_blm_dikirim'],0,".",",") }}</td>
                <td align="center">{{ number_format($row['nilai_blm_dikirim_persen'],2,".",",") }}</td>
                <td align="center">{{ number_format($row['unit_total'],0,".",",") }}</td>
                <td align="center">{{ number_format($row['unit_total_persen'],2,".",",") }}</td>
                <td align="right">{{ number_format($row['nilai_total'],0,".",",") }}</td>
                <td align="center">{{ number_format($row['nilai_total_persen'],2,".",",") }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
        <tr>
            <th colspan="2">Grand Total</th>
            <td align="center">{{ number_format($unit_1_2,0,".",",") }}</td>
            <td align="center">{{ number_format($unit_1_2_persen,2,".",",") }}</td>
            <td align="right">{{ number_format($nilai_1_2,0,".",",") }}</td>
            <td align="center">{{ number_format($nilai_1_2_persen,2,".",",") }}</td>
            <td align="center">{{ number_format($unit_3_5,0,".",",") }}</td>
            <td align="center">{{ number_format($unit_3_5_persen,2,".",",") }}</td>
            <td align="right">{{ number_format($nilai_3_5,0,".",",") }}</td>
            <td align="center">{{ number_format($nilai_3_5_persen,2,".",",") }}</td>
            <td align="center">{{ number_format($unit_5,0,".",",") }}</td>
            <td align="center">{{ number_format($unit_5_persen,2,".",",") }}</td>
            <td align="right">{{ number_format($nilai_5,0,".",",") }}</td>
            <td align="center">{{ number_format($nilai_5_persen,2,".",",") }}</td>
            <td align="center">{{ number_format($unit_blm_dikirim,0,".",",") }}</td>
            <td align="center">{{ number_format($unit_blm_dikirim_persen,2,".",",") }}</td>
            <td align="right">{{ number_format($nilai_blm_dikirim,0,".",",") }}</td>
            <td align="center">{{ number_format($nilai_blm_dikirim_persen,2,".",",") }}</td>
            <td align="center">{{ number_format($unit_total,0,".",",") }}</td>
            <td align="center">{{ number_format($unit_total_persen,2,".",",") }}</td>
            <td align="right">{{ number_format($nilai_total,0,".",",") }}</td>
            <td align="center">{{ number_format($nilai_total_persen,2,".",",") }}</td>
        </tr>
        <tr>
            <th colspan="2">Ketentuan Persentase</th>
            <th colspan="2">30</th>
            <th colspan="2">30</th>
            <th colspan="2">60</th>
            <th colspan="2">60</th>
            <th colspan="2">10</th>
            <th colspan="2">10</th>
            <th colspan="2">0</th>
            <th colspan="2">0</th>
            <th colspan="2">100</th>
            <th colspan="2">100</th>
        </tr>
        <tr>
            <th colspan="2">Lebih Kurang dari Ketentuan</th>
            <th colspan="2">{{ number_format($unit_1_2_persen - 30,2,".",",") }}</th>
            <th colspan="2">{{ number_format($nilai_1_2_persen - 30,2,".",",") }}</th>
            <th colspan="2">{{ number_format($unit_3_5_persen - 60,2,".",",") }}</th>
            <th colspan="2">{{ number_format($nilai_3_5_persen - 60,2,".",",") }}</th>
            <th colspan="2">{{ number_format($unit_5_persen - 10,2,".",",") }}</th>
            <th colspan="2">{{ number_format($nilai_5_persen - 10,2,".",",") }}</th>
            <th colspan="2">{{ number_format($unit_blm_dikirim_persen - 0,2,".",",") }}</th>
            <th colspan="2">{{ number_format($nilai_blm_dikirim_persen - 0,2,".",",") }}</th>
            <th colspan="2">{{ number_format($unit_total_persen - 100,2,".",",") }}</th>
            <th colspan="2">{{ number_format($nilai_total_persen - 100,2,".",",") }}</th>
        </tr>
        </tfoot>
        </table>
	</section>
</body>
@elseif($datafilter['jenis_laporan'] == "rinci")
<body class="F4 landscape" onload="printData()">
    @foreach ($datas as $nama_pelanggan => $tmp)
        @php
        $grandUnitAll = 0;
        $grandTotalAll = 0;
        $grandTotalORAll = 0;
        $grandTotalUangMukaAll = 0;
        $grandTotalSisaAll = 0;
        @endphp
        <section class="sheet padding-10mm">
        <table class="table-custom" width="100%" align="center" border="0">
            <thead>
                <tr>
                    <th style="border: none !important; text-align: left; padding-bottom: 10px; background: #fff;"> 
                        <div style="font-weight: bold; font-size: 18px;">{{ $namaCabang }}</div>
                        <div style="font-weight: bold; font-size: 18px;">Laporan Rincian Kwitansi Belum Ditagih [Outstanding Penagihan]</div>
                        <div style="font-weight: normal; font-size: 14px;">Periode : {{ $periodeStr ?? '' }}</div>
                    </th>
                </tr>
                <tr>
                    <th style="border: none !important; text-align: left; padding-bottom: 5px; background: #fff;"> 
                        <div style="font-weight: bold; font-size: 12px;">{{ $nama_pelanggan }}</div>
                    </th>
                </tr>
            </thead>
        </table>
        @foreach ($tmp as $minggu => $rows)
            <table class="table-custom" width="100%" align="center" border="0">
            <tr>
                <td style="border: none !important; text-align: left; padding-bottom: 5px; background: #fff;"> 
                    <div style="font-weight: bold; font-size: 12px; margin-left: 15px;">{{ $minggu }} </div>
                </td>
            </tr>
            </table>
            <table class="table-custom" width="100%" align="center" border="0">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th width="100">Tanggal<br>Kwitansi</th>
                    <th width="100">No. Kwitansi</th>
                    <th width="100">No. SPK</th>
                    <th width="100">No. Polisi</th>
                    <th width="100">Merek Tipe</th>
                    <th width="100">No. Klaim</th>
                    <th width="100">No. Polis /<br>No. Tiket</th>
                    <th width="100">Tertanggung</th>
                    <th width="100">Total<br>Tagihan</th>
                    <th width="100">Total OR</th>
                    <th width="100">Uang Muka</th>
                    <th width="100">Sisa<br>Tagihan</th>
                    <th width="100">Tanggal<br>Kirim</th>
                    <th width="100">Hari</th>
                    <th width="100">No. Keluar</th>
                </tr>
            </thead>
            <tbody>
                @php
                $no = 1;
                $grandUnit = 0;
                $grandTotal = 0;
                $grandTotalOR = 0;
                $grandTotalUangMuka = 0;
                $grandTotalSisa = 0;
                @endphp
                @foreach ($rows as $rowdet)
                @php
                    $grandUnitAll++;
                    $grandTotalAll += $rowdet['total'];

                    $grandUnit++;
                    $grandTotal += $rowdet['total'];
                    $grandTotalOR += $rowdet['total_or_ass'];
                    $grandTotalUangMuka += $rowdet['uang_muka'];
                    $grandTotalSisa += $rowdet['sisa_tagihan'];
                @endphp
                <tr>
                    <td align="center">{{ $no++ }}</td>
                    <td>{{ blank($rowdet['tgl_kwitansi']) ? '' : date("d/m/Y", strtotime($rowdet['tgl_kwitansi'] ))}}</td>
                    <td>{{ $rowdet['kode_kwitansi'] }}</td>
                    <td>{{ $rowdet['kode_spk'] }}</td>
                    <td>{{ $rowdet['no_polisi'] }}</td>
                    <td>{{ $rowdet['merek_tipe'] }}</td>
                    <td>{{ $rowdet['kode_claim'] }}</td>
                    <td>{{ $rowdet['no_polis'] }}</td>
                    <td>{{ $rowdet['tertanggung'] }}</td>
                    <td align="right">{{ number_format($rowdet['total'],0,".",",") }}</td>
                    <td align="right">{{ number_format($rowdet['total_or_ass'],0,".",",") }}</td>
                    <td align="right">{{ number_format($rowdet['uang_muka'],0,".",",") }}</td>
                    <td align="right">{{ number_format($rowdet['sisa_tagihan'],0,".",",") }}</td>
                    <td>{{ blank($rowdet['tgl_pengiriman']) ? '' : date("d/m/Y", strtotime($rowdet['tgl_pengiriman'] ))}}</td>
                    <td>{{ $rowdet['hari'] }}</td>
                    <td>{{ $rowdet['kode_keluar'] }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5">&nbsp;</th>
                    <th colspan="2">Sub Total</th>
                    <th colspan="2">{{ $grandUnit }} Unit</th>
                    <th align="right">{{ number_format($grandTotal,0,".",",") }}</th>
                    <th align="right">{{ number_format($grandTotalOR,0,".",",") }}</th>
                    <th align="right">{{ number_format($grandTotalUangMuka,0,".",",") }}</th>
                    <th align="right">{{ number_format($grandTotalSisa,0,".",",") }}</th>
                    <th colspan="3">&nbsp;</th>
                </tr>
            </tfoot>
            </table>
        @endforeach
        <table class="table-custom2" width="100%" align="center" border="0">
        <thead>
            <tr>
                <th colspan="5" width="450">&nbsp;</th>
                <th colspan="2" width="200">Total Per Pelanggan</th>
                <th colspan="2" width="200">{{ $grandUnitAll }} Unit</th>
                <th align="right" width="100">{{ number_format($grandTotalAll,0,".",",") }}</th>
                <th align="right" width="100">{{ number_format($grandTotalORAll,0,".",",") }}</th>
                <th align="right" width="100">{{ number_format($grandTotalUangMuka,0,".",",") }}</th>
                <th align="right" width="100">{{ number_format($grandTotalAll,0,".",",") }}</th>
                <th colspan="3" width="280">&nbsp;</th>
            </tr>
        </thead>
        </table>
        </section>
    @endforeach
</body>
@endif


</html>