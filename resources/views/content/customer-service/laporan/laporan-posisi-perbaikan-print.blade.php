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
        <span class="app-brand-text fw-bold">Laporan Posisi Perbaikan di Lapangan</span>
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
          <th>No. SPK</th>
          <th>No. Polisi</th>
          <th>Tipe Kendaraan</th>
          <th>Tanggal Turun Lap.</th>
          <th>Rencana Selesai</th>
          <th>Bongkar</th>
          <th>Las</th>
          <th>Dempul</th>
          <th>Mixing</th>
          <th>Cat</th>
          <th>Poles</th>
          <th>Finishing</th>
          <th>Nama Asuransi</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($datas as $row)
        <tr>
          <td>{{ $no++ }}</td>
          <td>{{ $row->kode_spk }}</td>
          <td>{{ $row->no_polisi }}</td>
          <td>{{ $row->merek_tipe }}</td>
          <td>{{ blank($row->tgl_turun_lapangan) ? '' : date("d/m/Y", strtotime($row->tgl_turun_lapangan ))}}</td>
          <td>{{ blank($row->tgl_rencana_selesai) ? '' : date("d/m/Y", strtotime($row->tgl_rencana_selesai ))}}</td>
          <td>{{ blank($row->tgl_bongkar2) ? '' : date("d/m/Y", strtotime($row->tgl_bongkar2 ))}}</td>
          <td>{{ blank($row->tgl_las2) ? '' : date("d/m/Y", strtotime($row->tgl_las2 ))}}</td>
          <td>{{ blank($row->tgl_dempul2) ? '' : date("d/m/Y", strtotime($row->tgl_dempul2 ))}}</td>
          <td>{{ blank($row->tgl_mixing2) ? '' : date("d/m/Y", strtotime($row->tgl_mixing2 ))}}</td>
          <td>{{ blank($row->tgl_cat2) ? '' : date("d/m/Y", strtotime($row->tgl_cat2 ))}}</td>
          <td>{{ blank($row->tgl_poles2) ? '' : date("d/m/Y", strtotime($row->tgl_poles2 ))}}</td>
          <td>{{ blank($row->tgl_finishing2) ? '' : date("d/m/Y", strtotime($row->tgl_finishing2 ))}}</td>
          <td>{{ $row->nama_pelanggan }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  
</div>
@endsection
