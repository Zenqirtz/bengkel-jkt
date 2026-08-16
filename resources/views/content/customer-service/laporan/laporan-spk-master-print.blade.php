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
        <span class="app-brand-text fw-bold">Laporan SPK Master</span>
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
          <th>No SPK</th>
          <th>Tgl Masuk</th>
          <th>No. Polisi</th>
          <th>Keterangan</th>
          <th>Status SPK</th>
          <th>Tipe Kendaraan</th>
          <th>Nama Pemilik</th>
          <th>No. Telepon</th>
          <th>Jenis Perbaikan</th>
          <th>Nama Asuransi</th>
          <th>Tgl. Estimasi</th>
          <th>No. Estimasi</th>
          <th>Nilai Estimasi</th>
          <th>Tgl. Kirim Estimasi</th>
          <th>Tgl. Turun Lap.</th>
          <th>Tgl. Rencana Selesai</th>
          <th>Tgl. Keluar</th>
          <th>Tgl. Inv. OR</th>
          <th>No. Inv. OR</th>
          <th>Nilai OR</th>
          <th>Tgl. Inv. Asuransi</th>
          <th>No. Inv. Asuransi</th>
          <th>Nilai Inv. Asuransi</th>
          <th>Tgl. Kwitansi</th>
          <th>No. Kwitansi</th>
          <th>Nilai Kwitansi</th>
          <th>Nama Surveyor</th>
          <th>Nama Marketing</th>
          <th>Nama Perantara</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($datas as $row)
        <tr>
          <td>{{ $no++ }}</td>
          <td>{{ $row->kode_spk }}</td>
          <td>{{ blank($row->tgl_masuk) ? '' : date("d/m/Y", strtotime($row->tgl_masuk)) }}</td>
          <td>{{ $row->no_polisi }}</td>
          <td>{{ $row->status }}</td>
          <td>{{ $row->status_spk }}</td>
          <td>{{ $row->merek_tipe }}</td>
          <td>{{ $row->pemilik }}</td>
          <td>{{ $row->telepon }}</td>
          <td>{{ $row->jenis_perbaikan }}</td>
          <td>{{ $row->nama_pelanggan }}</td>
          <td>{{ blank($row->tgl_estimasi) ? '' : date("d/m/Y", strtotime($row->tgl_estimasi)) }}</td>
          <td>{{ $row->kode_estimasi }}</td>
          <td>{{ $row->nilai_estimasi }}</td>
          <td>{{ blank($row->tgl_pengiriman) ? '' : date("d/m/Y", strtotime($row->tgl_pengiriman)) }}</td>
          <td>{{ blank($row->tgl_turun_lapangan) ? '' : date("d/m/Y", strtotime($row->tgl_turun_lapangan)) }}</td>
          <td>{{ blank($row->tgl_rencana_selesai) ? '' : date("d/m/Y", strtotime($row->tgl_rencana_selesai)) }}</td>
          <td>{{ blank($row->tgl_keluar) ? '' : date("d/m/Y", strtotime($row->tgl_keluar)) }}</td>
          <td>{{ blank($row->tanggal_or) ? '' : date("d/m/Y", strtotime($row->tanggal_or)) }}</td>
          <td>{{ $row->kode_or }}</td>
          <td>{{ $row->total_or }}</td>
          <td>{{ blank($row->tgl_invoice) ? '' : date("d/m/Y", strtotime($row->tgl_invoice)) }}</td>
          <td>{{ $row->no_invoice }}</td>
          <td>{{ $row->nilai_tawar }}</td>
          <td>{{ blank($row->tgl_kwitansi) ? '' : date("d/m/Y", strtotime($row->tgl_kwitansi)) }}</td>
          <td>{{ $row->kode_kwitansi }}</td>
          <td>{{ $row->nilai_kwitansi }}</td>
          <td>{{ $row->nama_surveyor }}</td>
          <td>{{ $row->nama_marketing }}</td>
          <td>{{ $row->nama_perantara }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  
</div>
@endsection
