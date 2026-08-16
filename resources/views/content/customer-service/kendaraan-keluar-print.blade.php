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

		<div class="double-divider"></div>

		<div class="doc-title">TANDA KELUAR KENDARAAN</div>

		<table class="table" width="100%" align="center" border="0">
		<tr>
			<td class="label-col">No. Keluar</td>
			<td class="sep-col">:</td>
			<td class="val-col"><strong>{{ @$data->kode_keluar }}</strong></td>
			<td class="val-col" align="right">Jam Cetak : {{ date("H:i:s") }}</td>
		</tr>
		<tr>
			<td class="label-col">Tanggal Keluar</td>
			<td class="sep-col">:</td>
			<td class="val-col" colspan="2">{{ @$data->tgl_keluar }}</td>
		</tr>
		<tr>
			<td class="label-col">No. SPK</td>
			<td class="sep-col">:</td>
			<td class="val-col" colspan="2">{{ @$data->kode_spk }}</td>
		</tr>
		<tr>
			<td class="label-col">No. Polisi</td>
			<td class="sep-col">:</td>
			<td class="val-col" colspan="2">{{ @$data->no_polisi }}</td>
		</tr>
		<tr>
			<td class="label-col">Tipe Kendaraan</td>
			<td class="sep-col">:</td>
			<td class="val-col" colspan="2">{{ @$data->merek_tipe }}</td>
		</tr>
		<tr>
			<td class="label-col">Jenis Asuransi</td>
			<td class="sep-col">:</td>
			<td class="val-col" colspan="2">{{ @$data->nama_pelanggan }}</td>
		</tr>
		</table>

		<table class="table mt-3" width="100%" align="center" border="0">
		<tr>
			<td width="35%">
				<div class="signature-box">
					<div class="signature-title">Dibuat oleh</div>
					<div class="signature-line"></div>
					<div class="mt-1">CS</div>
				</div>
			</td>
			<td width="35%">
				<div class="signature-box">
					<div class="signature-title">Disetujui</div>
					<div class="signature-line"></div>
					<div class="mt-1">KaBeng</div>
				</div>
			</td>
			<td width="30%">
				<div class="signature-box">
					<div class="signature-title">Security</div> 
					<div class="signature-line"></div>
					<div class="mt-1">&nbsp;</div>
				</div>
			</td>
		</tr>
		</table>

		<div class="doc-footer mt-2">
			Lembar ke-1 : (Akunting), Lembar ke-2 : CS, Lembar ke-3 : Security
		</div>

	</section>

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

		<div class="double-divider"></div>

		<div class="doc-title">TANDA TERIMA KENDARAAN</div>

		<table class="table" width="100%" align="center" border="0">
		<tr>
			<td colspan="3" align="center" style="font-size: 16px;">Yang bertanda tangan di bawah ini :</td>
		</tr>
		<tr>
			<td class="label-col">Nama</td>
			<td class="sep-col">:</td>
			<td class="val-col">{{ @$data->pemilik }}</td>
		</tr>
		<tr>
			<td class="label-col">Alamat</td>
			<td class="sep-col">:</td>
			<td class="val-col">{{ @$data->alamat }}</td>
		</tr>
		<tr>
			<td colspan="3" align="center" style="font-size: 16px;">Sebagai pemilik/kuasa kendaraan</td>
		</tr>
		<tr>
			<td class="label-col">Merek</td>
			<td class="sep-col">:</td>
			<td class="val-col">{{ @$data->merek_tipe }}</td>
		</tr>
		<tr>
			<td class="label-col">No. Polisi</td>
			<td class="sep-col">:</td>
			<td class="val-col" colspan="2">{{ @$data->no_polisi }}</td>
		</tr>
		<tr>
			<td class="label-col">Jenis Asuransi</td>
			<td class="sep-col">:</td>
			<td class="val-col" colspan="2">{{ @$data->nama_pelanggan }}</td>
		</tr>
		</table>

		<table class="table mt-1" width="100%" align="center" border="0">
		<tr>
			<td colspan="2">Telah menerima kembali kendaraan tersebut dalam keadaan baik dan memuaskan</td>
		</tr>
		<tr>
			<td colspan="2">Jakarta, {{ date("d F Y") }}</td>
		</tr>
		<tr>
			<td width="50%">
				<div class="signature-box">
					<div class="signature-title">Yang Menyerahkan mobil</div>
					<div class="signature-line"></div>
					<div class="mt-1">&nbsp;</div>
				</div>
			</td>
			<td width="50%">
				<div class="signature-box">
					<div class="signature-title">Yang Menerima</div>
					<div class="signature-line"></div>
					<div class="mt-1">&nbsp;</div>
				</div>
			</td>
		</tr>
		</table>
	</section>
</body>

</html>