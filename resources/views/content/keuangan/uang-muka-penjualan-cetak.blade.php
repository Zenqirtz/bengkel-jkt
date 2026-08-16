@extends('layouts/layoutMaster')

@section('title', $title)

<!-- Vendor Styles -->
@section('vendor-style')
<style>
	body {
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
		display: flex;
		flex-direction: column;
	}

	.cabang-name {
		font-size: 16px;
		font-weight: bold;
		text-transform: uppercase;
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
		font-weight: bold;
	}

	.date-box {
		border: 1px solid #000;
		padding: 3px 12px;
		display: inline-block;
		font-weight: bold;
	}

	.header-title {
		text-align: center;
		font-size: 18px;
		font-weight: bold;
		margin: 18px 0 4px;
	}

	.divider {
		border-top: 2px solid #000;
		margin: 12px 0 20px;
	}

	.divider-thin {
		border-top: 1px solid #000;
		margin: 18px 0;
	}

	.field-label {
		font-weight: bold;
		font-size: 13px;
		margin-bottom: 4px;
		display: block;
	}

	.boxed-input {
		border: 1px solid #000;
		padding: 8px 12px;
		display: block;
		width: 100%;
		font-weight: bold;
		font-size: 14px;
		min-height: 38px;
	}

	.field-group {
		margin-bottom: 16px;
	}

	.nilai-section {
		border: 2px solid #000;
		padding: 14px 16px;
		margin: 6px 0 24px;
	}

	.nilai-label {
		font-size: 12px;
		font-weight: bold;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		color: #333;
	}

	.nilai-amount {
		font-size: 26px;
		font-weight: bold;
		text-align: right;
	}

	.terbilang-text {
		font-style: italic;
		font-size: 12px;
		margin-top: 6px;
		border-top: 1px dashed #999;
		padding-top: 6px;
	}

	.signature-table th,
	.signature-table td {
		text-align: center;
		vertical-align: middle;
		border-color: #000;
		font-size: 12px;
	}

	.signature-table th {
		font-weight: bold;
		padding: 6px 4px;
	}

	.signature-table td {
		height: 80px;
	}

	.footer-note {
		margin-top: auto;
		padding-top: 20px;
		font-size: 9px;
		color: #777;
		text-align: right;
	}
</style>
@endsection

<!-- Page Scripts -->
@section('page-script')
<script>
	'use strict';
	(function() {
		window.print();
	})();
</script>
@endsection

@section('content')
<div class="paper">
	<div class="top-section">
		<div class="row align-items-start">
			<div class="col-6">
				<p class="mb-0 cabang-name">{{ $cabang->nama_cabang }}</p>
			</div>
			<div class="col-6 text-end">
				<div class="info-header">
					<span class="info-label">NOMOR</span>
					<span class="info-value-header">{{ $data->no_transaksi }}</span><br>
					<span class="info-label">Tanggal</span>
					<span class="date-box">{{ date("d-M-Y", strtotime($data->tanggal_transaksi)) }}</span>
				</div>
			</div>
		</div>

		<div class="header-title">
			Bukti Uang Muka Penjualan
		</div>

		<div class="divider"></div>

		<div class="field-group">
			<span class="field-label">Diterima dari</span>
			<span class="boxed-input">{{ $data->nama }}</span>
		</div>

		<div class="row g-3">
			<div class="col-6 field-group">
				<span class="field-label">Jenis Penerimaan</span>
				<span class="boxed-input">{{ $data->jenis_penerimaan }}</span>
			</div>
			<div class="col-6 field-group">
				<span class="field-label">Masuk Kas/Bank</span>
				<span class="boxed-input">{{ $data->nama_bank }}</span>
			</div>
		</div>

		<div class="field-group">
			<span class="field-label">No. Rekening</span>
			<span class="boxed-input">{{ $data->no_rekening ?: '-' }}</span>
		</div>

		<div class="nilai-section">
			<div class="nilai-label">Jumlah Uang Muka</div>
			<div class="nilai-amount">Rp {{ $data->nilai_format }}</div>
			<div class="terbilang-text">
				Terbilang: {{ $data->terbilang ?? '-' }}
			</div>
		</div>
	</div>

	<div class="bottom-section mt-auto">
		<div class="divider-thin"></div>

		<div class="row table-responsive">
			<table class="tableX table-bordered signature-table">
				<thead>
					<tr>
						<th style="width: 33.33%;">Dibuat Oleh</th>
						<th style="width: 33.33%;">Disetujui Oleh</th>
						<th style="width: 33.33%;">Diterima Oleh</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>{{ $data->created_by }}</td>
						<td></td>
						<td></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="footer-note">
			Dicetak: {{ now()->format('d/m/Y H:i') }}
		</div>
	</div>
</div>
@endsection
