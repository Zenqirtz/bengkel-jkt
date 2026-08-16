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
    <script src="{{ asset('assets/js/laporan-unit-rawat-jalan.js') }}"></script>
@endsection

@section('content')
    {{-- Filter Card --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Filter</h5>
                </div>
                <div class="card-body">
                    <form id="filterForm" method="post" action="{{ url('administrasi/laporan-unit-rawat-jalan-list') }}"
                        autocomplete="off">
                        @csrf
                        <div class="row mb-4">
                            <label class="col-sm-2 col-form-label" for="filter-tanggal">Tanggal</label>
                            <div class="col-sm-10">
                                <input type="text" id="filter-tanggal" name="tanggal" class="form-control dt-date"
                                    value="{{ @$datafilter['tanggal'] }}" placeholder="dd/mm/yyyy" />
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

    {{-- Table Card --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Laporan Unit Rawat Jalan</h5>
                    </div>
                    <div class="demo-inline-spacing">
                        <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
                        <button type="button" class="btn btn-primary btn-print">Print</button>
                    </div>
                </div>
                <div class="card-datatable">
                    <table class="datatables-laporan-unit-rawat-jalan table table-bordered table-responsive"
                        data-title="{{ $title }}">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No. SPK</th>
                                <th>Tanggal Masuk</th>
                                <th>Tanggal Rawat Jalan</th>
                                <th>Tanggal Selesai</th>
                                <th>No. Polisi</th>
                                <th>Merek / Tipe</th>
                                <th>Pemilik</th>
                                <th>Nama Asuransi</th>
                                <th>No. Polis</th>
                                <th>No. Klaim</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
