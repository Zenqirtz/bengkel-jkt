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

@section('page-script')
  <script src="{{ asset('assets/js/laporan-analisa-pemakaian-bahan.js') }}"></script>
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
      </div>
      <div class="card-body">
        {{-- <form id="filterForm" class="form-control-validation" method="post" action="{{ url('laporan-analisa-pemakaian-bahan-list') }}" autocomplete="off"> --}}
        <form id="filterForm" class="form-control-validation" method="post" action="{{ url('laporan-analisa-bahan-list') }}" autocomplete="off">
          @csrf
          <div class="row mb-4">
            <label class="col-sm-2 col-form-label" for="filter-jenis-report">Jenis Report</label>
            <div class="col-sm-10">
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="jenis_report" id="jenis-rinci" value="Rinci" {{ @$datafilter['jenis_report'] == 'Rinci' ? 'checked' : '' }}>
                <label class="form-check-label" for="jenis-rinci">Rinci</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="jenis_report" id="jenis-rekap" value="Rekap" {{ @$datafilter['jenis_report'] == 'Rekap' || empty(@$datafilter['jenis_report']) ? 'checked' : '' }}>
                <label class="form-check-label" for="jenis-rekap">Rekap</label>
              </div>
            </div>
          </div>
          <div class="row mb-4">
            <label class="col-sm-2 col-form-label" for="filter-tahun">Periode</label>
            <div class="col-sm-10">
              <div class="d-flex align-items-center gap-2">
                <div class="flex-grow-1">
                  <select id="filter-tahun" name="tahun" class="select2 form-select" data-allow-clear="true" style="width: 100%;">
                    @foreach ($years as $key => $row)
                      <option value="{{ $key }}" @if (old('tahun', @$datafilter['tahun']) == $key) selected @endif>{{ $row }}</option>
                    @endforeach
                  </select>
                </div>
                <span>Bulan</span>
                <div class="flex-grow-1">
                  <select id="filter-bulan" name="bulan" class="select2 form-select" data-allow-clear="true" style="width: 100%;">
                    <option value="">Pilih Bulan</option>
                    @foreach ($months as $key => $row)
                      <option value="{{ $key }}" @if (old('bulan', @$datafilter['bulan']) == $key) selected @endif>{{ $row }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
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

<div class="row mt-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Laporan Analisa Pemakaian Bahan</h5>
        </div>
        <div class="demo-inline-spacing">
          <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
          <button type="button" class="btn btn-primary btn-print">Print</button>
        </div>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="mb-1"><strong>Bulan / Tahun :</strong> <span id="display-periode">-</span></p>
            <p class="mb-0"><strong>Jumlah Panel :</strong> <span id="display-jumlah-panel">-</span></p>
          </div>
        </div>
      </div>
      <div class="card-datatable">
        <table class="datatables-analisa-bahan table table-bordered table-responsive" data-title="{{ $title }}"
            data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Bahan</th>
              <th>Qty</th>
              <th>Harga</th>
              <th>Jumlah</th>
              <th>Satuan</th>
              <th>Qty/ Point Panel</th>
              <th>Rupiah/ Point Panel</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection
