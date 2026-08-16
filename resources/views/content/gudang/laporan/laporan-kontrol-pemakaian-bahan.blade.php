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
    <script src="{{ asset('assets/js/laporan-kontrol-pemakaian-bahan.js') }}"></script>
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
                    <div class="alert alert-danger">{{ $error }}</div>
                @endforeach
            @endif
        </div>

        {{-- Filter Card --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Filter</h5>
                </div>
                <div class="card-body">
                    <form id="filterForm" class="form-control-validation" method="post"
                        action="{{ url('lap-kontrol-pemakaian-bahan-list') }}" autocomplete="off">
                        @csrf
                        <div class="row mb-4">
                            <label class="col-sm-2 col-form-label" for="filter-no-spk">Nomor SPK</label>
                            <div class="col-sm-10 form-control-validation">
                                <input type="text" id="filter-no-spk" name="no_spk" class="form-control"
                                    value="{{ @$datafilter['no_spk'] }}" placeholder="Masukan Nomor SPK" />
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

    {{-- Result Card --}}
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Formulir Kontrol Pemakaian Bahan</h5>
                    </div>
                    <div class="demo-inline-spacing">
                        <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
                        <button type="button" class="btn btn-primary btn-print">Print</button>
                    </div>
                </div>

                {{-- Meta info - tampilkan jika sudah ada filter --}}
                <div class="card-body pb-0" id="meta-info"
                    style="{{ !empty($datafilter['no_spk']) ? '' : 'display:none;' }}">
                    <div class="d-flex mb-2" style="gap: 80px;">
                        <table style="border:none; border-collapse:collapse; font-size:14px;">
                            <tr>
                                <td style="width:95px; padding:1px 0; font-weight:600;">No. SPK</td>
                                <td style="padding:1px 4px;">: <span
                                        id="meta-no-spk">{{ @$datafilter['no_spk'] ?: '-' }}</span></td>
                            </tr>
                            <tr>
                                <td style="padding:1px 0; font-weight:600;">Point Panel</td>
                                <td style="padding:1px 4px;">: <span id="meta-point-panel">-</span></td>
                            </tr>
                        </table>
                        <table style="border:none; border-collapse:collapse; font-size:14px;">
                            <tr>
                                <td style="width:95px; padding:1px 0; font-weight:600;">Pemilik</td>
                                <td style="padding:1px 4px;">: <span id="meta-pemilik">-</span></td>
                            </tr>
                            <tr>
                                <td style="padding:1px 0; font-weight:600;">Merek Tipe</td>
                                <td style="padding:1px 4px;">: <span id="meta-merek-tipe">-</span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card-datatable">
                    <table class="datatables-kontrol-pemakaian-bahan table table-bordered" data-title="{{ $title }}"
                        data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
                        <thead>
                            <tr>
                                <th rowspan="2" class="text-center align-middle">No</th>
                                <th rowspan="2" class="text-center align-middle">Bagian</th>
                                <th rowspan="2" class="text-center align-middle">Nama Bahan</th>
                                <th colspan="2" class="text-center">Standard Pemakaian</th>
                                <th colspan="3" class="text-center">Aktual Pemakaian</th>
                                <th colspan="2" class="text-center">Variance</th>
                            </tr>
                            <tr>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Harga</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Harga</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Harga</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-center fw-bold">Grand Total</th>
                                <th class="text-end fw-bold" id="foot-std-qty"></th>
                                <th class="text-end fw-bold" id="foot-std-harga"></th>
                                <th class="text-end fw-bold" id="foot-aktual-qty"></th>
                                <th class="text-end fw-bold" id="foot-aktual-harga"></th>
                                <th class="text-end fw-bold" id="foot-aktual-total"></th>
                                <th class="text-end fw-bold" id="foot-variance-qty"></th>
                                <th class="text-end fw-bold" id="foot-variance-harga"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
