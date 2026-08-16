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
		/* .title { font-size: 18px; }
		.subtitle { font-size: 12px; } */

         /* Table overrides */
        .table-custom {
            border-collapse: collapse; /* Ini kunci agar border menyatu */
            width: 100%; /* Lebar bisa dipindah ke sini agar HTML lebih bersih */
        }
        
        .table-custom th {
            border: 1px solid #000 !important;
            padding: 4px;
            font-size: 12px;
        }
        .table-custom td {
            border: 1px solid #000 !important;
            padding: 4px;
            font-size: 12px;
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

        .doc-footer {
            font-size: 12px;
            font-style: italic; /* Sesuai gaya umum dokumen rangkap */
        }

		/* @media print {
			@page {
				size: A5 landscape;
				Opsional: Anda juga bisa mengatur margin default di sini
				margin: 10mm; 
			}

			Opsional: Sembunyikan elemen yang tidak perlu di-print (seperti tombol/navbar)
			.no-print {
				display: none !important;
			}
		} */
	</style>
</head>

<body class="A5 landscape" onload="printData()">
	<section class="sheet padding-10mm">
		<table width="100%" align="center" border="0">
		<tr>
			<td width="180" valign="top">
				@if (@$file_logo)
					<img src="{{ asset('assets/img/cabang/' . @$cabang->logo_cabang) }}" alt="" width="150">
				@else
					<div class="logo-oval">
						<h2>{{ @$cabang->nama_singkat }}</h2>
					</div>
				@endif
			</td>
			<td valign="top" class="title">
				<div class="mb-1">{{ @$cabang->nama_cabang }}</div>
                <div class="mb-1">CAR BODY REPAIR & PAINT SPECIALIST</div>
				<div class="mb-1">{{ @$cabang->alamat1 }}</div>
				<div class="mb-1">Telp : {{ @$cabang->telepon }} Fax : {{ @$cabang->fax }}</div>
			</td>
		</tr>
		</table>

        <table class="table" width="100%" align="center" border="0">
        <tr>
            <td align="left" class="subtitle">
                NO. INVOICE : {{ $data->no_invoice }}
            </td>
        </tr>
        </table>

        <table class="table-custom" width="100%" align="center" border="0">
        <tr>
            <td width="30%" height="50" align="left">
                Kepada Yth
            </td>
            <td align="left">
                <span class="fw-bold">Rp. {{ $data->pemilik }}</span> <br>
                {{ $data->alamat }}
            </td>
        </tr>
        <tr>
            <td height="50" align="left">
                Nilai Tagihan
            </td>
            <td align="left">
                <span class="fw-bold">Rp. {{ number_format($data->total_or, 0, ',', '.') }}</span> <br>
                # {{ $data->terbilang }} #
            </td>
        </tr>
        <tr>
            <td height="80" align="left">
                Untuk Tagihan
            </td>
            <td align="left" valign="middle">
                Biaya resiko perbaikan sendiri<br>
                {{ $data->nama_pelanggan }}<br>
                {{ $data->kode_spk }}<br>
                Merek Kendaraan : {{ $data->merek_tipe }} No. Polisi : {{ $data->no_polisi }}<br>
                {{ $data->no_polis }}
            </td>
        </tr>
        </table>

        <table class="table" width="100%" align="center" border="0">
            <tr>
                <td width="70%" align="left">
                    * Pembayaran dapat ditransfer ke rekening : <br>
                    @if (!blank($cabang->rekening1))
                        <span class="fw-bold">{{ $cabang->rekening1 }}</span> <br>
                    @endif
                    @if (!blank($cabang->rekening2))
                        <span class="fw-bold">{{ $cabang->rekening2 }}</span>
                    @endif
                </td>
                <td align="right" valign="top">
                    Jakarta, {{ date("d F Y", strtotime($data->tgl_invoice)) }}
                </td>
            </tr>
        </table>

	</section>
</body>

</html>