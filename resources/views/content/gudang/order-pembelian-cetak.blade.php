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
		/* --- HEADER SECTION --- */
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        /* Kiri: Info Perusahaan */
        .company-info {
            width: 60%;
        }
        .recipient-line {
            font-size: 13px;
            margin-bottom: 15px;
        }
        .company-name {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 2px;
        }
        .company-sub {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .company-address {
            font-size: 11px;
            line-height: 1.4;
        }

        /* Kanan: Judul & Tabel Kecil */
        .po-meta {
            width: 38%;
        }
        .doc-title {
            font-weight: bold;
            font-size: 20px;
            margin-bottom: 5px;
            text-align: left; /* Sesuai gambar rata kiri terhadap kolomnya */
        }
        
        /* Tabel Kecil di Header */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
        }
        .meta-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 11px;
        }
        .meta-label {
            background-color: #fff;
            width: 100px;
        }
        .meta-val {
            font-weight: bold;
        }

        /* --- MAIN ITEM TABLE --- */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border: 2px solid #000; /* Border luar tebal */
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: middle;
        }
        .main-table th {
            text-align: center;
            background-color: #fff; /* Putih polos */
            font-weight: normal; /* Font header tidak terlalu bold di gambar */
        }

        /* --- FOOTER & SIGNATURE --- */
        .footer-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 5px;
            margin-bottom: 30px;
            font-size: 12px;
        }
        .date-left {
            
        }
        .code-right {
            font-weight: bold;
            text-transform: uppercase;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            text-align: center;
            padding: 0 20px;
        }
        .sig-box {
            width: 180px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 120px; /* Tinggi ruang tanda tangan */
        }
        .sig-role {
            text-transform: uppercase;
            font-size: 12px;
        }
        .sig-name {
            text-decoration: underline; /* Garis bawah nama */
            font-weight: bold;
            text-transform: uppercase;
        }
        .sig-line-only {
            border-bottom: 2px solid #000; /* Garis saja tanpa nama */
            height: 20px;
            margin-top: auto;
        }

		/* @media print {
			@page {
				size: A5 landscape;
				/* Opsional: Anda juga bisa mengatur margin default di sini */
				/* margin: 10mm;  */
			}

			/* Opsional: Sembunyikan elemen yang tidak perlu di-print (seperti tombol/navbar) */
			/* .no-print {
				display: none !important;
			} */
		} */
	</style>
</head>

<body class="A5 landscape" onload="printData()">
	<section class="sheet padding-10mm">
		<div class="header-top">
            <div class="company-info">
                <div class="recipient-line">
                    Kepada Yth : <strong>{{ $datas->nama_pemasok }}</strong>
                </div>
                
                <div class="company-name">{{ $datas->nama_cabang }}</div>
                <div class="company-sub">BODY REPAIR & PAINT SPECIALIST</div>
                <div class="company-address">
                {{ $datas->alamat1 }}<br><br>
                    Telp.: {{ $datas->telepon }} Fax.: {{ $datas->fax }}
                </div>
            </div>

            <div class="po-meta">
                <div class="doc-title">PURCHASE ORDER</div>
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">NOMOR</td>
                        <td class="meta-val">{{ $datas->kode_order }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">NO. SPK</td>
                        <td class="meta-val">{{ $datas->kode_spk }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">NOMOR POLISI</td>
                        <td class="meta-val">@if (!blank($spk)) {{ $spk->no_polisi }} @endif</td>
                    </tr>
                    <tr>
                        <td class="meta-label">MEREK/TIPE</td>
                        <td class="meta-val">@if (!blank($spk)) {{ $spk->merek_tipe }} @endif</td>
                    </tr>
                    <tr>
                        <td class="meta-label">PEMBAYARAN</td>
                        <td class="meta-val">{{ $datas->tipe_bayar }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No.</th>
                    <th>Nama Barang</th>
                    <th style="width: 100px;">No. Parts</th>
                    <th style="width: 60px;">Qty</th>
                    <th style="width: 120px;">Harga Satuan</th>
                    <th style="width: 120px;">Total</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($details as $row)
            <tr>
                <td class="text-center">{{ $row->seq_no }}</td>
                <td>{{ $row->nama_bahan }}</td>
                <td>{{ $row->no_sparepart }}</td>
                <td class="text-center">{{ number_format($row->qty, 0, ".", ",") }}</td>
                <td class="text-end">{{ number_format($row->harga, 0, ".", ",") }}</td>
                <td class="text-end">{{ number_format($row->jumlah, 0, ".", ",") }}</td>
            </tr>   
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td colspan="5"><strong>Total</strong></td>
                <td class="text-end">{{ number_format($datas->total - $datas->ppn, 0, ".", ",") }}</td>
            </tr>
            <tr>
                <td colspan="5"><strong>PPn {{ $ppn_persen }}%</strong></td>
                <td class="text-end">{{ number_format($datas->ppn, 0, ".", ",") }}</td>
            </tr>
            <tr>
                <td colspan="5"><strong>Grand Total</strong></td>
                <td class="text-end">{{ number_format($datas->total, 0, ".", ",") }}</td>
            </tr>
            </tfoot>
        </table>

        <div class="footer-row">
            <div class="date-left">Jakarta, {{ $datas->tanggal }}</div>
            <div class="code-right">{{ $datas->tipe_barang }}</div>
        </div>

        <div class="signature-section">
            <div class="sig-box">
                <div class="sig-role">PEMOHON</div>
                <div class="sig-line-only"></div>
            </div>

            <div class="sig-box">
                <div class="sig-role">MENGETAHUI</div>
                <div class="sig-name">K.A. BENGKEL</div>
            </div>

            <div class="sig-box">
                <div class="sig-role">MENYETUJUI</div>
                <div class="sig-name">PIMPINAN</div>
            </div>
        </div>
	</section>
</body>

</html>