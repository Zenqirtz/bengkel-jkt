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
    <script src="{{ asset('assets/js/laporan-estimasi-per-tahun.js') }}"></script>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            @if (session()->has('success'))
                <div class="alert alert-solid-success alert-dismissible d-flex align-items-center flex-wrap row-gap-2"
                    role="alert">
                    <span class="alert-icon rounded">
                        <i class="icon-base ri ri-checkbox-circle-line icon-md"></i>
                    </span>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-solid-danger alert-dismissible d-flex align-items-center flex-wrap row-gap-2"
                    role="alert">
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
                        {{ $error }}
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
                    <form id="filterForm" class="form-control-validation" method="post"
                        action="{{ url('laporan-estimasi-tahun-list') }}" autocomplete="off">
                        @csrf
                        <div class="row mb-4">
                            <label class="col-sm-2 col-form-label" for="filter-jenis-laporan">Jenis Report</label>
                            <div class="col-sm-10">
                                <select id="filter-jenis-laporan" name="jenis_laporan" class="select2 form-select"
                                    data-allow-clear="true">
                                    @foreach ($jenis_laporan as $key => $row)
                                        <option value="{{ $key }}"
                                            @if (old('jenis_laporan', @$datafilter['jenis_laporan']) == $key) selected @endif>{{ $row }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-sm-2 col-form-label" for="filter-tahun">Tahun</label>
                            <div class="col-sm-10">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1">
                                        <select id="filter-tahun" name="tahun" class="select2 form-select"
                                            data-allow-clear="true" style="width: 100%;">
                                            @foreach ($years as $key => $row)
                                                <option value="{{ $key }}"
                                                    @if (old('tahun', @$datafilter['tahun']) == $key) selected @endif>{{ $row }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <span>Bulan</span>
                                    <div class="flex-grow-1">
                                        <select id="filter-bulan" name="bulan" class="select2 form-select"
                                            data-allow-clear="true" style="width: 100%;">
                                            <option value="">Pilih Bulan</option>
                                            @foreach ($months as $key => $row)
                                                <option value="{{ $key }}"
                                                    @if (old('bulan', @$datafilter['bulan']) == $key) selected @endif>{{ $row }}
                                                </option>
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
                        <h5 class="m-0 me-2">Laporan Rekap Estimasi Per Tipe</h5>
                    </div>
                    <div class="demo-inline-spacing">
                        <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
                        <button type="button" class="btn btn-primary btn-print">Print</button>
                    </div>
                </div>
                <div class="card-datatable">
                    <table class="datatables-estimasi-tahun table table-bordered" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
                      <thead>
                        <tr>
                          <th rowspan="2">No</th>
                          <th rowspan="2">Nama Asuransi</th>
                          <th rowspan="2">Unit</th>
                          <th colspan="4" class="text-center">Perbaikan</th>
                          <th colspan="4" class="text-center">Sparepart</th>
                          <th colspan="4" class="text-center">Lain-lain</th>
                          <th rowspan="2">Total R</th>
                          <th rowspan="2">Total S</th>
                          <th rowspan="2">Total T</th>
                          <th rowspan="2">PPN</th>
                          <th rowspan="2">Total</th>
                        </tr>
                        <tr>
                          <th>R</th>
                          <th>S</th>
                          <th>T</th>
                          <th>Total</th>
                          <th>R</th>
                          <th>S</th>
                          <th>T</th>
                          <th>Total</th>
                          <th>R</th>
                          <th>S</th>
                          <th>T</th>
                          <th>Total</th>
                        </tr>
                      </thead>
                      <tfoot>
                        <tr style="font-weight: bold;">
                          <th colspan="2"></th>
                          <th class="text-center">0</th>
                          <th class="text-end">0</th>
                          <th class="text-end">0</th>
                          <th class="text-end">0</th>
                          <th class="text-end">0</th>
                          <th class="text-end">0</th>
                          <th class="text-end">0</th>
                          <th class="text-end">0</th>
                          <th class="text-end">0</th>
                          <th class="text-end">0</th>
                          <th class="text-end">0</th>
                          <th class="text-end">0</th>
                          <th class="text-end">0</th>
                          <th class="text-end">0</th>
                          <th class="text-end">0</th>
                          <th class="text-end">0</th>
                          <th class="text-end">0</th>
                          <th class="text-end">0</th>
                        </tr>
                      </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
