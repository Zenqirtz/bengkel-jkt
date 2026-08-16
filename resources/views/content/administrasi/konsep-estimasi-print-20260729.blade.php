@extends('layouts/layoutMaster')

@section('title', $title)

<!-- Vendor Styles -->
@section('vendor-style')
<style>
   /* --- STYLE DASAR --- */
	body {
		background-color: #e9ecef;
		font-family: Arial, sans-serif;
		font-size: 12px; /* Sedikit lebih besar untuk keterbacaan */
		color: #000;
	}
	.sheet {
		background: white;
		width: 210mm;
		min-height: 297mm; /* A4 Height */
		margin: 20px auto;
		padding: 10mm 15mm;
		box-shadow: 0 0 10px rgba(0,0,0,0.1);
		position: relative;
	}

	/* --- HEADER SECTION --- */
	.header-title {
		font-size: 12px;
		font-weight: bold;
		margin-bottom: 25px;
	}
	.info-table td {
		padding: 2px 0;
		vertical-align: top;
	}
	.label-width { width: 120px; }
	.label-width2 { width: 150px; }
	.colon-width { width: 20px; }

	/* Border Styles */
	.border-top-thick {
		border-top: 3px solid #000 !important;
	}
	.border-bottom-thick {
		border-bottom: 3px solid #000 !important;
	}
	.border-dashed {
		border-bottom: 1px dashed #000 !important;
	}
	
	/* Table overrides */
	.table-custom th {
		/* text-transform: uppercase; */
		height: 30px;
		border: none;
		padding: 5px 0;
	}
	.table-custom td {
		border: none;
		padding: 8px 0;
	}
	.section-title {
		font-weight: bold;
		text-decoration: none;
		margin-top: 20px;
		display: block;
	}
	.total-row td {
		padding-top: 5px;
		font-weight: bold;
	}

	/* --- PRINT SETTINGS (Anti 2 Page) --- */
	@media print {
		@page {
			size: A4;
			margin: 0;
		}
		body { 
			margin: 0;
			padding: 0;
			background-color: white;
		}
		.sheet { 
      color: #000;
			width: 100%;
			margin: 0;
			box-shadow: none;
			padding: 10mm 15mm; 
			min-height: auto; 
			height: auto;
		}
		/* Sembunyikan elemen placeholder logo jika ada image asli */
		.logo-placeholder {
			border-color: #000; 
			-webkit-print-color-adjust: exact;
		}
	}
</style>
@endsection

<!-- Page Scripts -->
@section('page-script')
<script>
'use strict';
(function () {
  window.print();
})();
</script>
@endsection

@section('content')
<div class="sheet">
	<div class="header-title">{{ $cabang->nama_cabang }}</div>

	<div class="row mb-4">
		<div class="col-12">
			<table class="info-table">
				<tr>
					<td class="fw-bold label-width2">Kode Konsep Estimasi</td>
					<td class="colon-width">:</td>
					<td class="fw-bold">{{ $data->kode_konsep_estimasi }}</td>
				</tr>
			</table>
		</div>
	</div>

	<div class="row">
		<div class="col-6">
			<table class="info-table w-100">
					<tr>
							<td class="label-width">Tanggal</td>
							<td class="colon-width">:</td>
							<td>{{ $data->tgl_konsep }}</td>
					</tr>
					<tr>
							<td>Tahun</td>
							<td>:</td>
							<td>{{ $data->tahun }}</td>
					</tr>
					<tr>
							<td>Merek Tipe</td>
							<td>:</td>
							<td>{{ $data->merek_tipe }}</td>
					</tr>
					<tr>
							<td>No. Polisi</td>
							<td>:</td>
							<td>{{ $data->no_polisi }}</td>
					</tr>
			</table>
		</div>
		<div class="col-6">
				<table class="info-table w-100">
						<tr>
								<td class="label-width">No. SPK</td>
								<td class="colon-width">:</td>
								<td>{{ $data->kode_spk }}</td>
						</tr>
						<tr>
								<td>Tertanggung</td>
								<td>:</td>
								<td>{{ $data->tertanggung }}</td>
						</tr>
						<tr>
								<td>No. Polis</td>
								<td>:</td>
								<td>{{ $data->no_polis }}</td>
						</tr>
						<tr>
								<td>Pelanggan</td>
								<td>:</td>
								<td>{{ $data->nama_pelanggan }}</td>
						</tr>
				</table>
		</div>
	</div>

	@if (count($data_perbaikan))
	<div class="mt-4 border-top-thick">
		<span class="section-title">1 PERBAIKAN</span>
		<table class="table-custom w-100 mb-0">
			<thead>
				<tr class="border-bottom-thick">
					<th style="width: 50px;">No.</th>
					<th>Keterangan</th>
					<th class="text-end" style="width: 100px;">Jumlah</th>
				</tr>
			</thead>
			<tbody>
					@foreach ($data_perbaikan as $row)
					<tr class="border-dashed">
						<td>{{ $row->idx }}</td>
						<td>{{ sprintf("%s %s", $row->jenis_pekerjaan, $row->panel_pekerjaan) }}</td>
						<td class="text-end">{{ number_format($row->harga, 0, ".", ",") }}</td>
					</tr>
					@endforeach
					<tr class="total-row">
							<td colspan="2" class="text-end">Total</td>
							<td class="text-end">{{ $data->total_perbaikan }}</td>
					</tr>
			</tbody>
		</table>
	</div>
	@endif

	@if (count($data_sparepart))
	<div class="mt-4">
		<span class="section-title">2 SPAREPART</span>
		<table class="table-custom w-100 mb-0">
			<thead>
				<tr class="border-bottom-thick">
					<th style="width: 50px;">No.</th>
					<th style="width: 250px;">Keterangan</th>
					<th></th>
					<th style="width: 50px;" class="text-center">Qty</th>
					<th style="width: 100px;" class="text-end">Harga</th>
					<th style="width: 100px;" class="text-end">Jumlah</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($data_sparepart as $row)
				<tr class="border-dashed">
					<td>{{ $row->idx }}</td>
					<td>{{ $row->nama_sparepart }}</td>
					<td>{{ $row->no_sparepart }}</td>
					<td>{{ $row->qty }}</td>
					<td class="text-end">{{ number_format($row->harga, 0, ".", ",") }}</td>
					<td class="text-end">{{ number_format($row->jumlah, 0, ".", ",") }}</td>
				</tr>
				@endforeach
				<tr class="total-row">
					<td colspan="5" class="text-end">Total</td>
					<td class="text-end">{{ $data->total_sparepart }}</td>
				</tr>
			</tbody>
		</table>
	</div>
	@endif

	@if (count($data_lain))
	<div class="mt-4">
		<span class="section-title">3 LAIN-LAIN</span>
		<table class="table-custom w-100 mb-0">
			<thead>
				<tr class="border-bottom-thick">
					<th style="width: 50px;">No.</th>
					<th>Keterangan</th>
					<th class="text-end" style="width: 100px;">Jumlah</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($data_lain as $row)
				<tr class="border-dashed">
					<td>{{ $row->idx }}</td>
					<td>{{ $row->memo }}</td>
					<td class="text-end">{{ number_format($row->harga, 0, ".", ",") }}</td>
				</tr>
				@endforeach
				<tr class="total-row">
					<td colspan="2" class="text-end">Total</td>
					<td class="text-end">{{ $data->total_lain }}</td>
				</tr>
			</tbody>
		</table>
	</div>
	@endif

	<div class="row mt-2">
		<div class="col-12">
			<table class="w-100 fw-bold">
				<tr class="border-top-thick">
					<td class="text-end">Grand Total</td>
					<td class="text-end" style="width: 100px;">{{ $data->total }}</td>
				</tr>
			</table>
		</div>
	</div>

	<div class="mt-3" style="margin-top: 60px !important;">
		<p>Dibuat Oleh,</p>
		<br><br><br>
		<p class="mb-0"><u><b>{{ $data->nama_estimator }}</b></u></p>
		<p><b>Estimator</b></p>
	</div>
</div>
@endsection
