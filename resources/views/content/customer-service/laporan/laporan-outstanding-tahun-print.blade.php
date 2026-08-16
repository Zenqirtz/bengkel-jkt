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
        <span class="app-brand-text fw-bold">Laporan Rekap Outstanding OR per Tahun</span>
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
          <th rowspan="2">No</th>
          <th rowspan="2">Nama Asuransi</th>
          <th colspan="12" style="text-align: center;">Tahun {{ @$datafilter['tahun'] }}</th>
          <th rowspan="2">Total</th>
        </tr>
        <tr>
          <th>JAN</th>
          <th>FEB</th>
          <th>MAR</th>
          <th>APR</th>
          <th>MEI</th>
          <th>JUN</th>
          <th>JUL</th>
          <th>AGS</th>
          <th>SEP</th>
          <th>OKT</th>
          <th>NOV</th>
          <th>DES</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($datas as $row)
        <tr>
          <td>{{ $no++ }}</td>
          <td>{{ $row->nama_pelanggan }}</td>
          <td>{{ number_format($row->JAN,0,".",",") }}</td>
          <td>{{ number_format($row->FEB,0,".",",") }}</td>
          <td>{{ number_format($row->MAR,0,".",",") }}</td>
          <td>{{ number_format($row->APR,0,".",",") }}</td>
          <td>{{ number_format($row->MEI,0,".",",") }}</td>
          <td>{{ number_format($row->JUN,0,".",",") }}</td>
          <td>{{ number_format($row->JUL,0,".",",") }}</td>
          <td>{{ number_format($row->AGS,0,".",",") }}</td>
          <td>{{ number_format($row->SEP,0,".",",") }}</td>
          <td>{{ number_format($row->OKT,0,".",",") }}</td>
          <td>{{ number_format($row->NOV,0,".",",") }}</td>
          <td>{{ number_format($row->DES,0,".",",") }}</td>
          <td>{{ number_format($row->Total,0,".",",") }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  
</div>
@endsection
