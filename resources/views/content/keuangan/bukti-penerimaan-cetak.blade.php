@extends('layouts/layoutMaster')

@section('title', $title)

<!-- Vendor Styles -->
@section('vendor-style')
<style>
 	body {
			/* background-color: #f4f4f4; */
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			font-size: 14px;
			color: #000;
	}
	.paper {
		background: white;
		width: 210mm;
		min-height: 297mm; /* A4 Height */
		margin: 20px auto;
		padding: 10mm 15mm;
		/* box-shadow: 0 0 10px rgba(0,0,0,0.1); */
		/* position: relative; */
		display: flex;
    flex-direction: column;
	}

	.header-section {
			display: flex;
			justify-content: space-between;
			align-items: center;
	}
	.header-title {
			text-align: center;
			font-size: 18px;
			font-weight: bold;
			margin-top: 10px;
	}
	.info-header {
			text-align: right;
			font-size: 12px;
	}
	.info-label {
			display: inline-block;
			width: 70px;
	}
	.info-value-header {
			display: inline-block;
			width: 150px;
			text-align: left;
	}
	.date-box {
			border: 1px solid #000;
			padding: 2px 10px;
			display: inline-block;
	}

	.divider {
			border-top: 2px solid #000;
			margin: 15px 0;
	}

	.divider-thick {
			border-top: 3px solid #000;
			margin: 15px 0;
	}

	.boxed-input {
			border: 1px solid #000;
			padding: 5px 10px;
			display: inline-block;
			width: 100%;
	}

	.voucher-label-group, .total-amount-box-group {
				display: flex;
				align-items: center;
				margin-bottom: 0;
	}

	.voucher-value, .amount-value {
			border: 1px solid #000;
			padding: 5px 10px;
			text-align: right;
			display: inline-block;
			font-weight: bold;
	}

	.terbilang-box {
			border: 1px solid #000;
			padding: 10px;
			min-height: 40px;
	}

	.signature-table th, .signature-table td {
			text-align: center;
			vertical-align: middle;
			border-color: #000;
	}

	.signature-table td {
			height: 60px; /* Jarak untuk area tanda tangan */
	}

	.text-bold {
			font-weight: bold;
	}

	.mt-4-custom {
			margin-top: 25px;
	}

	/* Penyesuaian jarak antar kolom agar rapat seperti digambar */
	.g-tight {
			--bs-gutter-x: 0.5rem;
			--bs-gutter-y: 0.5rem;
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
<div class="paper">
	<div class="top-section">
		<div class="row align-items-center">
			<div class="col-6">
					<p class="mb-0 text-bold">{{ $cabang->nama_cabang }}</p>
			</div>
			<div class="col-6 text-end">
					<div class="info-header">
							<span class="info-label text-bold">NOMOR</span> <span class="info-value-header text-bold">{{ $data->no_transaksi }}</span><br>
							<span class="info-label text-bold">Tanggal</span> <span class="date-box">{{ date("d-M-Y", strtotime($data->tanggal_transaksi)) }}</span>
					</div>
			</div>
		</div>

		<div class="header-title">
				Bukti Penerimaan {{ $data->kategori }}
		</div>

		<div class="divider"></div>

		<div class="row align-items-center g-tight mb-3">
				<div class="col-sm-2 text-bold">Di bayar kepada</div>
				<div class="col-sm-5">
						<span class="boxed-input text-bold">{{ $data->nama_pelanggan }}&nbsp;</span>
				</div>
				<div class="col-sm-2 text-end text-bold">Voucher</div>
				<div class="col-sm-3">
						<span class="boxed-input text-end text-bold">{{ $data->no_voucher }}</span>
				</div>
		</div>

		<div class="row g-tight mb-3">
				<div class="col-12">
						<span class="boxed-input">{{ $data->memo }}</span>
				</div>
		</div>

		<div class="row table-responsive">
				<table class="tableX table-borderless table-custom">
						<thead class="border-bottom border-dark border-2">
								<tr>
										<th class="ps-3" style="width: 70%;">Uraian</th>
										<th class="text-end pe-3" style="width: 30%;">Jumlah</th>
								</tr>
						</thead>
						<tbody>
							@foreach ($data_detail as $item)
							<tr>
								<td class="ps-3">{{ $item->uraian }}</td>
								<td class="text-end pe-3">{{  number_format($item->jumlah, 0, '.', ',') }}</td>
							</tr>
							@endforeach
						</tbody>
				</table>
		</div>
	</div>

	<div class="bottom-section mt-auto">
		<div class="divider-thick"></div>

		<div class="row align-items-center g-tight mb-3">
				<div class="col-sm-2 text-bold">Terbilang</div>
				<div class="col-sm-7">
						<span class="boxed-input text-bold">{{ $data->terbilang }}</span>
				</div>
				<div class="col-sm-3 text-end">
						<span class="boxed-input text-end text-bold">{{ number_format($data->total, 0, '.', ',') }}</span>
				</div>
		</div>

		<div class="divider"></div>

		<div class="row g-tight mb-3">
				<div class="col-sm-5">
						<div class="row g-tight align-items-center mb-1">
								<div class="col-sm-4 text-bold">TIPE</div>
								<div class="col-sm-8">
										<span class="boxed-input text-bold">{{ $data->nama_bank }}&nbsp;</span>
								</div>
						</div>
						<div class="row g-tight align-items-center mb-1">
								<div class="col-sm-4 text-bold">NO CH/BG</div>
								<div class="col-sm-8">
										<span class="boxed-input text-bold">{{ $data->no_ch_bg }}&nbsp;</span>
								</div>
						</div>
				</div>
				<div class="col-sm-7">
						<div class="row g-tight align-items-center mb-1">
								<div class="col-sm-4 text-end text-bold">Tanggal CH/BG</div>
								<div class="col-sm-8">
										<span class="boxed-input text-bold">{{ $data->tanggal_ch_bg }}&nbsp;</span>
								</div>
						</div>
						<div class="row g-tight align-items-center mb-1">
								<div class="col-sm-4 text-end text-bold">Cabang</div>
								<div class="col-sm-8">
										<span class="boxed-input text-bold">{{ $cabang->nama_singkat }}</span>
								</div>
						</div>
				</div>
		</div>

		<div class="row table-responsive mt-4-custom">
				<table class="tableX table-bordered signature-table">
						<thead class="border-dark border-1">
								<tr>
										<th style="width: 20%;">Disiapkan Oleh</th>
										<th style="width: 20%;">Diperiksa Oleh</th>
										<th style="width: 20%;">Disetujui Oleh</th>
										<th style="width: 20%;">Diterima Oleh</th>
										<th style="width: 20%;">Dibukukan Oleh</th>
								</tr>
						</thead>
						<tbody>
								<tr>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
								</tr>
						</tbody>
				</table>
		</div>
	</div>
</div>
@endsection
