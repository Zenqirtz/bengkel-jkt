@extends('layouts/layoutMaster')

@section('title', $title)

<!-- Vendor Styles -->
@section('vendor-style')
<style>
html,
body {
  background: var(--bs-white);
}
body > :not(.invoice-print) {
  display: none !important;
}
.invoice-print {
  font-size: 15px;
  min-inline-size: 768px !important;
}
.invoice-print * {
  color: #676a7b !important;
}
.invoice-print .table thead tr th {
  background-color: var(--bs-table-header-bg-color);
}
.invoice-print .text-primary * {
  color: var(--bs-primary) !important;
}
[data-bs-theme="dark"] .invoice-print th {
  color: var(--bs-white) !important;
}
@media print {
  @page {
    size: landscape;
    /* Opsi lain: margin: 0; */
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
<div class="invoice-print p-12">
  <div class="d-flex justify-content-between flex-row">
    <div class="mb-6">
      <div class="d-flex svg-illustration mb-6 gap-2">
        <span class="app-brand-text fw-bold">Laporan SPK Batal</span>
      </div>
      <p class="mb-1">Cabang : {{ $namaCabang }}</p>
      <p class="mb-1">Periode : {{ $periodeStr }}</p>
    </div>
  </div>

  <hr class="mb-6" />

  <div class="border-bottom-0 border-top-0 rounded">
    <table class="table m-0">
      <thead>
        <tr>
          <th>No</th>
          <th>Tanggal Batal</th>
          <th>No. SPK</th>
          <th>Tipe Kendaraan</th>
          <th>No. Polisi</th>
          <th>Nama Asuransi</th>
          <th>Nama Pemilik</th>
          <th>Dibatalkan Oleh</th>
          <th>Alasan Pembatalan</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($datas as $row)
        <tr>
          <td>{{ $no++ }}</td>
          <td>{{ date("d/m/Y", strtotime($row->tgl_batal ))}}</td>
          <td>{{ $row->kode_spk }}</td>
          <td>{{ $row->merek_tipe }}</td>
          <td>{{ $row->no_polisi }}</td>
          <td>{{ $row->nama_pelanggan }}</td>
          <td>{{ $row->pemilik }}</td>
          <td>{{ $row->batal_by }}</td>
          <td>{{ $row->memo_batal }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  
</div>
@endsection
