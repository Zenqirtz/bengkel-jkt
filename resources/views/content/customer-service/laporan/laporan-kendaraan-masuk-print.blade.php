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
        <span class="app-brand-text fw-bold">Laporan Mobil Masuk per Asuransi</span>
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
          <th>Nama Asuransi</th>
          @foreach (@$datas['fields'] as $key => $row)
          @if ($datafilter['jenis_laporan'] == "tahun")
          <th>{{ date("m", strtotime($row)) }}<br>({{ date("M", strtotime($row)) }})</th>
          @else
          <th>{{ date("d/m/Y", strtotime($row)) }}</th>  
          @endif
          @endforeach
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach (@$datas['data'] as $key => $row)
          @php $total = 0; @endphp
          <tr>
            <td>{{ $key }}</td>
            @foreach (@$datas['fields'] as $key2 => $row2)
            @if (isset($row[$key2]))
            @php $total += $row[$key2]; @endphp
            <td style="text-align: center;">{{ $row[$key2] }}</td>
            @else
            <td style="text-align: center;">0</td>
            @endif
            @endforeach
            <td style="text-align: center;">{{ $total }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td>Total</td>
          @php $total = 0; @endphp
          @foreach (@$datas['total'] as $key3 => $row3)
          @php $total += $row3; @endphp
          <td style="text-align: center;">{{ $row3 }}</td>
          @endforeach
          <td style="text-align: center;">{{ $total }}</td>
        </tr>
      </tfoot>
    </table>
  </div>
  
</div>
@endsection
