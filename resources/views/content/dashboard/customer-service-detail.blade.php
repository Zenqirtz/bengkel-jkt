@extends('layouts/layoutMaster')

@section('title', 'Dashboard')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite([
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')
<script src="{{ asset('assets/js/dashboard-customer-service.js') }}"></script>
@endsection


@section('content')

<div class="row g-6">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">{{ $title }}</h5>
        </div>
      </div>
      <div class="card-datatable text-nowrap">
        @if ($tipe == "1")
        <table class="datatables-detail1 table table-bordered table-responsive">
          <thead>
            <tr>
              <th>No</th>
              <th>No SPK</th>
              <th>Tanggal Masuk</th>
              <th>No Estimasi</th>
              <th>Tanggal Estimasi</th>
              <th>No Polisi</th>
              <th>Tipe Kendaraan</th>
              <th>Nama Pemilik</th>
            </tr>
          </thead>
        </table>
        @endif

        @if ($tipe == "2")
        <table class="datatables-detail2 table table-bordered table-responsive">
          <thead>
            <tr>
              <th>No</th>
              <th>No SPK</th>
              <th>Tanggal Masuk</th>
              <th>No Polisi</th>
              <th>Tipe Kendaraan</th>
              <th>Nama Pemilik</th>
              <th>Turun Lap.</th>
              <th>Rencana Selesai</th>
              <th>Sisa Waktu</th>
            </tr>
          </thead>
        </table>
        @endif

        @if ($tipe == "3")
        <table class="datatables-detail3 table table-bordered table-responsive">
          <thead>
            <tr>
              <th>No</th>
              <th>No SPK</th>
              <th>Tanggal Masuk</th>
              <th>No Polisi</th>
              <th>Tipe Kendaraan</th>
              <th>Nama Pemilik</th>
            </tr>
          </thead>
        </table>
        @endif

        @if ($tipe == "4")
        <table class="datatables-detail4 table table-bordered table-responsive">
          <thead>
            <tr>
              <th>No</th>
              <th>No Estimasi</th>
              <th>Tanggal Estimasi</th>
              <th>No SPK</th>
              <th>Tanggal Masuk</th>
              <th>No Polisi</th>
              <th>Tipe Kendaraan</th>
              <th>Nama Pemilik</th>
              <th>Nama Pelanggan</th>
            </tr>
          </thead>
        </table>
        @endif

      </div>
    </div>
  </div>
</div>

@endsection
