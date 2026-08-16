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
			font-size: 11px;
		}

		.table-custom2 td {
			border: 0px solid #000 !important;
			padding: 2px;
			font-size: 11px;
		}

		.total-row {
			/* padding-top: 5px; */
			font-weight: bold;
		}

		/* Border Styles */
		.border-top-thick {
			border-top: 2px solid #000 !important;
		}
		.border-bottom-thick {
			border-bottom: 2px solid #000 !important;
		}
		.border-top-dashed {
			border-top: 1px dashed #000 !important;
		}
		.border-dashed {
			border-bottom: 1px dashed #000 !important;
		}

		@media print {
			@page {
				/* size: F4; */
				/* Opsional: Anda juga bisa mengatur margin default di sini */
				margin-top: 10mm; 
        		margin-bottom: 10mm;
			}

			/* Opsional: Sembunyikan elemen yang tidak perlu di-print (seperti tombol/navbar) */
			.no-print {
				display: none !important;
			}

			body, .sheet {
				height: auto !important;
				min-height: 100% !important;
				overflow: visible !important;
				padding-top: 0 !important; 
        		padding-bottom: 0 !important;
			}
		}
	</style>
</head>

<body class="F4" onload="printData()">
	<section class="sheet padding-10mm">
		<table class="table" width="100%" align="center" border="0">
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
			<td valign="top">
				<div class="mb-1 title">{{ @$cabang->nama_cabang }}</div>
                <div class="mb-1 subtitle">CAR BODY REPAIR & PAINT SPECIALIST</div>
				<div class="mb-1 subtitle">{{ @$cabang->alamat1 }}</div>
				<div class="mb-1 subtitle">Telp : {{ @$cabang->telepon }} Fax : {{ @$cabang->fax }}</div>
			</td>
		</tr>
		</table>
		<table class="table-custom2" width="100%" border="0">
		<tr>
			<td colspan="2"><div class="border-bottom-thick"></div></td>
		</tr>
		<tr>
			<td width="60%">
				<table class="table-custom2" width="100%" align="center" border="0">
				<tr>
					<td width="100">Nomor</td>
					<td width="15" align="center">:</td>
					<td><strong>{{ $data->kode_estimasi }} </strong></td>
				</tr>
				<tr>
					<td>Hal.</td>
					<td align="center">:</td>
					<td>Taksasi perbaikan kendaraan</td>
				</tr>
				</table>
			</td>
			<td valign="top">
				<table class="table-custom2" width="100%" align="center" border="0">
				<tr>
					<td>{{ $data->nama_pelanggan }}</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2"><div class="border-bottom-thick"></div></td>
		</tr>
		<tr>
			<td width="60%">
				<table class="table-custom2" width="100%" align="center" border="0">
				<tr>
					<td width="100">Tertanggung</td>
					<td width="15" align="center">:</td>
					<td>{{ $data->tertanggung }}</td>
				</tr>
				<tr>
					<td>No. Polis / No. Tiket</td>
					<td align="center">:</td>
					<td>{{ $data->no_polis }}</td>
				</tr>
				<tr>
					<td>Surveyor</td>
					<td align="center">:</td>
					<td>{{ $data->nama_surveyor }}</td>
				</tr>
				<tr>
					<td>Klaim / SPK Ass</td>
					<td align="center">:</td>
					<td>{{ $data->kode_claim }}</td>
				</tr>
				</table>
			</td>
			<td valign="top">
				<table class="table-custom2" width="100%" align="center" border="0">
				<tr>
					<td width="80">Merek Tipe</td>
					<td width="15" align="center">:</td>
					<td>{{ $data->merek_tipe }}</td>
				</tr>
				<tr>
					<td>No. Polisi</td>
					<td align="center">:</td>
					<td>{{ $data->no_polisi }}</td>
				</tr>
				<tr>
					<td>Tahun</td>
					<td align="center">:</td>
					<td>{{ $data->tahun }}</td>
				</tr>
				<tr>
					<td>SPK</td>
					<td align="center">:</td>
					<td>{{ $data->kode_spk }}</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2"><div class="border-bottom-thick"></div></td>
		</tr>
		<tr>
			<td colspan="2">
				Dengan hormat, <br>
				Bersamaan ini kami sampaikan taksasi perbaikan kendaraan dengan perincian sebagai berikut:
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		@if (count($data_perbaikan))
		<tr>
			<td colspan="2"><strong>PERBAIKAN</strong></td>
		</tr>
		<tr>
			<td colspan="2">
				<table class="table-custom">
				<thead>
					<tr class="border-bottom-thick">
						<th width="35" align="center">No.</th>
						<th align="left">Keterangan</th>
						<th align="right" width="150">Jumlah</th>
					</tr>
				</thead>
				<tbody>
						@foreach ($data_perbaikan as $row)
						<tr>
							<td align="center">{{ $row->idx }}</td>
							<td>{{ sprintf("%s %s", $row->jenis_pekerjaan, $row->panel_pekerjaan) }}</td>
							<td align="right">{{ number_format($row->harga, 0, ".", ",") }}</td>
						</tr>
						@endforeach
				</tbody>
			</table>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<table width="100%" class="table-custom">
					<tr class="border-top-dashed">
						<td>Subtotal</td>
						<td align="right" width="150">{{ number_format($data->total_perbaikan, 0, ".", ",") }}</td>
					</tr>
					<tr>
						<td>Discount</td>
						<td align="right">{{ number_format($data->disc_perbaikan, 0, ".", ",") }}</td>
					</tr>
					<tr class="total-row border-top-thick">
						<td>Total</td>
						<td align="right">{{ number_format($data->total_perbaikan - $data->disc_perbaikan, 0, ".", ",") }}</td>
					</tr>
				</table>
			</td>
		</tr>
		@endif

		@if (count($data_sparepart))
		<tr>
			<td colspan="2"><strong>SPAREPART</strong></td>
		</tr>
		<tr>
			<td colspan="2">
				<table class="table-custom">
				<thead>
					<tr class="border-bottom-thick">
						<th width="35" align="center">No.</th>
						<th width="250" align="left">Keterangan</th>
						<th>&nbsp;</th>
						<th width="80" align="center">Qty</th>
						<th width="150" align="right">Harga</th>
						<th width="150" align="right">Jumlah</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($data_sparepart as $row)
					<tr>
						<td align="center">{{ $row->idx }}</td>
						<td>{{ $row->nama_sparepart }}</td>
						<td>{{ $row->no_sparepart }}</td>
						<td align="center">{{ number_format($row->qty, 0, ".", ",") }}</td>
						<td align="right">{{ number_format($row->harga, 0, ".", ",") }}</td>
						<td align="right">{{ number_format($row->jumlah, 0, ".", ",") }}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<table width="100%" class="table-custom">
					<tr class="border-top-dashed">
						<td>Subtotal</td>
						<td align="right" width="150">{{ number_format($data->total_sparepart, 0, ".", ",") }}</td>
					</tr>
					<tr>
						<td>Discount</td>
						<td align="right">{{ number_format($data->disc_sparepart, 0, ".", ",") }}</td>
					</tr>
					<tr class="total-row border-top-thick">
						<td>Total</td>
						<td align="right">{{ number_format($data->total_sparepart - $data->disc_sparepart, 0, ".", ",") }}</td>
					</tr>
				</table>
			</td>
		</tr>
		@endif

		@if (count($data_lain))
		<tr>
			<td colspan="2"><strong>LAIN-LAIN</strong></td>
		</tr>
		<tr>
			<td colspan="2">
				<table class="table-custom">
				<thead>
					<tr class="border-bottom-thick">
						<th width="35" align="center">No.</th>
						<th align="left">Keterangan</th>
						<th align="right" width="150">Jumlah</th>
					</tr>
				</thead>
				<tbody>
						@foreach ($data_lain as $row)
						<tr>
							<td align="center">{{ $row->idx }}</td>
							<td>{{ $row->memo }}</td>
							<td align="right">{{ number_format($row->harga, 0, ".", ",") }}</td>
						</tr>
						@endforeach
				</tbody>
			</table>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<table width="100%" class="table-custom">
					<tr class="border-top-dashed">
						<td>Subtotal</td>
						<td align="right" width="150">{{ number_format($data->total_lain, 0, ".", ",") }}</td>
					</tr>
					<tr>
						<td>Discount</td>
						<td align="right">{{ number_format($data->disc_lain, 0, ".", ",") }}</td>
					</tr>
					<tr class="total-row border-top-thick">
						<td>Total</td>
						<td align="right">{{ number_format($data->total_lain - $data->disc_lain, 0, ".", ",") }}</td>
					</tr>
				</table>
			</td>
		</tr>
		@endif

		<tr class="border-top-thick">
			<td>&nbsp;</td>
			<td>
				<table width="100%" class="table-custom">
					<tr>
						<td>Grand Total</td>
						<td align="right" width="150">{{ number_format($data->total - $data->ppn, 0, ".", ",") }}</td>
					</tr>
					<tr>
						<td>PPN</td>
						<td align="right">{{ number_format($data->ppn, 0, ".", ",") }}</td>
					</tr>
					<tr class="total-row border-top-thick">
						<td>Total Estimasi</td>
						<td align="right">{{ number_format($data->total, 0, ".", ",") }}</td>
					</tr>
				</table>
			</td>
		</tr>

		<tr>
			<td colspan="2">
				<p><strong>Terbilang : <br> {{ $data->terbilang }}</strong></p>
				<p>Demikian taksasi perbaikan kendaraan tersebut di atas kami sampaikan, atas perhatian dan persetujuan kami ucapkan terima kasih.</p>
				<p>Jakarta, {{ $data->tgl_estimasi }}</p>
				<p>Hormat kami,</p>
				<br><br><br>
				<p class="mb-0"><u><b>AGUS RAHMAT</b></u></p>
				{{-- <p class="mb-0">...........................................</p> --}}
				<p><b>Workshop Manager</b></p>
			</td>
		</tr>
		{{-- <tr>
			<td colspan="2">
				Demikian taksasi perbaikan kendaraan tersebut di atas kami sampaikan, atas perhatian dan persetujuan kami ucapkan terima kasih.
			</td>
		</tr>
		<tr>
			<td colspan="2">
				Jakarta, {{ $data->tgl_estimasi }}
			</td>
		</tr>
		<tr>
			<td colspan="2">
				Hormat kami,
			</td>
		</tr> --}}
        </table>
		
	</section>
</body>

</html>