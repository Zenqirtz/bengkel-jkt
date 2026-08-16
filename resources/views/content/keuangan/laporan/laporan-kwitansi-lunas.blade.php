@extends('layouts/layoutMaster')

@section('title', $title)

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss',
'resources/assets/vendor/libs/animate-css/animate.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.scss',
'resources/assets/vendor/libs/pickr/pickr-themes.scss',
'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
'resources/assets/vendor/libs/tagify/tagify.scss'
])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite([
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js',
'resources/assets/vendor/libs/cleave-zen/cleave-zen.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js',
'resources/assets/vendor/libs/pickr/pickr.js',
'resources/assets/vendor/libs/bs-stepper/bs-stepper.js',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
'resources/assets/vendor/libs/tagify/tagify.js'
])
@endsection

<!-- Page Scripts -->
@section('page-script')
  <script src="{{ asset('assets/js/laporan-kwitansi-lunas.js') }}"></script>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    @if(session()->has('success'))
    <div class="alert alert-solid-success alert-dismissible d-flex align-items-center flex-wrap row-gap-2" role="alert">
      <span class="alert-icon rounded">
        <i class="icon-base ri ri-checkbox-circle-line icon-md"></i>
      </span>
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session()->has('error'))
    <div class="alert alert-solid-danger alert-dismissible d-flex align-items-center flex-wrap row-gap-2" role="alert">
      <span class="alert-icon rounded">
        <i class="icon-base ri ri-error-warning-line icon-md"></i>
      </span>
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if ($errors->any())
      @foreach ($errors->all() as $error)
          <div class="alert alert-danger">
          {{$error}}
          </div>
      @endforeach
    @endif
  </div>
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Filter</h5>
        {{-- <h5 class="mb-0">Laporan {{ $title }}</h5> --}}
      </div>
      <div class="card-body">
        <form id="filterForm" class="form-control-validation" method="post" action="{{ url('laporan-kwitansi-lunas-list') }}" autocomplete="off">
          @csrf
          <div class="row mb-4">
            <label class="col-sm-2 col-form-label" for=filter-jenis-laporan">Jenis Laporan</label>
            <div class="col-sm-10">
              <select id="filter-jenis-laporan" name="jenis_laporan" class="select2 form-select" data-allow-clear="true">
                @foreach($jenis_laporan as $key => $row)
                  <option value="{{ $key }}" @if(old('jenis_laporan', @$datafilter['jenis_laporan']) == $key) selected @endif>{{ $row }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row mb-4" id="jns-lap-periode">
            <label class="col-sm-2 col-form-label" for="filter-tgl-awal">Tanggal</label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="text" id="filter-tgl-awal" name="tgl_awal" class="form-control dt-date" value="{{ @$datafilter['tgl_awal'] }}" placeholder="Tanggal Awal" />
                <span class="input-group-text">s/d</span>
                <input type="text" id="filter-tgl-akhir" name="tgl_akhir" class="form-control dt-date" value="{{ @$datafilter['tgl_akhir'] }}" placeholder="Tanggal Akhir" />
              </div>
            </div>
          </div>
          <div class="row mb-4" id="no-voucher">
            <label class="col-sm-2 col-form-label" for="filter-no-voucher">No. Voucher</label>
            <div class="col-sm-10">
              <input type="text" id="filter-no-voucher" name="no_voucher" class="form-control form-control-sm" value="{{ @$datafilter['no_voucher'] }}" placeholder="Nomor Voucher" >
            </div>
          </div>
          <div class="row justify-content-end">
            <div class="col-sm-10">
              <button type="submit" class="btn btn-primary">Tampilkan</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@if (@$datafilter['jenis_laporan'] == "voucher")
  @include('content/keuangan/laporan/laporan-kwitansi-lunas-voucher')
@elseif (@$datafilter['jenis_laporan'] == "rekap")
  @include('content/keuangan/laporan/laporan-kwitansi-lunas-rekap')
@elseif (@$datafilter['jenis_laporan'] == "rinci")
  @include('content/keuangan/laporan/laporan-kwitansi-lunas-rinci')
@endif

@endsection
