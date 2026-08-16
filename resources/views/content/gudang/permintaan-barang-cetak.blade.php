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

	/* HEADER STYLES */
	.company-title {
			font-size: 18px;
			font-weight: 900;
			margin-bottom: 5px;
	}
	.form-title {
			font-size: 15px;
			font-weight: bold;
			margin-bottom: 2px;
	}
	.address-text {
			font-size: 12px;
			margin-bottom: 25px;
	}
	.doc-number {
			font-weight: bold;
			font-size: 13px;
	}

	/* INFO TABLE */
	.info-table td {
			padding: 2px 0;
			vertical-align: top;
	}
	.info-label { width: 90px; }
	.info-colon { width: 20px; text-align: center; }

	/* MAIN TABLE STYLES */
	.table-custom {
			width: 100%;
			border-collapse: collapse;
	}
	.table-custom th {
			border-top: 3px double #000; /* Garis atas ganda */
			border-bottom: 1px solid #000; /* Garis bawah header tipis */
			padding: 8px 10px;
			font-weight: normal;
	}
	.table-custom td {
			padding: 6px 10px;
			vertical-align: top;
	}
	
	/* Garis Vertikal khusus kolom Harga */
	.col-harga {
			border-left: 1px solid #000;
			border-right: 1px solid #000;
			width: 150px;
	}

	/* Spacer Row untuk memperpanjang garis vertikal ke bawah */
	.spacer-row {
			height: 250px; /* Atur ketinggian ruang kosong di sini */
	}

	/* Garis tebal penutup tabel */
	.table-footer-line {
			border-top: 2px solid #000;
	}

	/* SIGNATURE SECTION */
	.signature-section {
			margin-top: 5px;
	}
	.sig-col {
			width: 25%;
			display: inline-block;
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
	<div class="company-title">{{ $cabang->nama_cabang }}</div>
	
	<div class="d-flex justify-content-between align-items-start">
		<div>
			<div class="form-title">FORM PERMINTAAN BARANG</div>
			<div class="address-text">{{ $cabang->alamat1 }}</div>
		</div>
		<div class="doc-number mt-1">
			No. Permintaan : {{ $data->kode_permintaan }}
		</div>
	</div>

	<div class="row mb-3">
		<div class="col-7">
			<table class="table-borderless info-table w-100">
				<tr>
					<td class="info-label">Tanggal</td>
					<td class="info-colon">:</td>
					<td>{{ date("d-M-Y", strtotime($data->tanggal_permintaan)) }}</td>
				</tr>
				<tr>
					<td>No. SPK</td>
					<td>:</td>
					<td>{{ $data->kode_spk }}</td>
				</tr>
				<tr>
					<td>Asuransi</td>
					<td>:</td>
					<td>{{ $data->nama_pelanggan }}</td>
				</tr>
			</table>
		</div>
		<div class="col-5">
			<table class="table-borderless info-table w-100">
				<tr>
					<td class="info-label">Merek/Tipe</td>
					<td class="info-colon">:</td>
					<td>{{ $data->merek_tipe }}</td>
				</tr>
				<tr>
					<td>No. Polisi</td>
					<td>:</td>
					<td>{{ $data->no_polisi }}</td>
				</tr>
			</table>
		</div>
	</div>

	<table class="table-custom">
		<thead>
			<tr>
				<th class="text-center">Nama Barang</th>
				@if ($data->kode_tipe_barang == "S")
				<th class="text-center" style="width: 20%;">No. Sparepart</th>
				@endif
				<th class="text-center" style="width: 15%;">Satuan</th>
				<th class="text-end" style="width: 20%;">Qty</th>
				{{-- <th class="text-center col-harga">Harga</th> --}}
			</tr>
		</thead>
		<tbody>
			@foreach ($data_detail as $item)
			<tr>
				<td>{{ $item->nama_bahan }}</td>
				@if ($data->kode_tipe_barang == "S")
				<td class="text-center">{{ $item->no_sparepart }}</td>
				@endif
				<td class="text-center">{{ $item->nama_satuan }}</td>
				<td class="text-end">{{  number_format($item->qty, 2, '.', ',') }}</td>
				{{-- <td class="text-end col-harga">{{  number_format($item->harga, 0, '.', ',') }}</td> --}}
			</tr>
			@endforeach
			<tr class="spacer-row">
				@if ($data->kode_tipe_barang == "S")
				<td colspan="4" ></td>
				@else
				<td colspan="3" ></td>
				@endif
			</tr>
			<tr>
				@if ($data->kode_tipe_barang == "S")
				<td colspan="4" class="p-0 table-footer-line"></td>
				@else
				<td colspan="3" class="p-0 table-footer-line"></td>
				@endif
			</tr>
		</tbody>
	</table>

	<div class="signature-section d-flex justify-content-between">
		<div class="sig-col text-start ps-4">Diterima</div>
		<div class="sig-col text-center">Dipenuhi</div>
		<div class="sig-col text-center">Disetujui Oleh</div>
		<div class="sig-col text-end pe-4">Pemohon</div>
	</div>
</div>
@endsection
