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
'resources/assets/vendor/libs/tagify/tagify.scss',
'resources/assets/vendor/libs/spinkit/spinkit.scss',
'resources/assets/vendor/libs/notiflix/notiflix.scss'
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
'resources/assets/vendor/libs/tagify/tagify.js',
'resources/assets/vendor/libs/notiflix/notiflix.js'
])
@endsection


<!-- Page Scripts -->
@section('page-script')
    <script src="{{ asset('assets/js/foto-bon.js') }}"></script>
@endsection

@section('content')
    <!-- Tabel Utama -->
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div class="card-title mb-0">
                <h5 class="m-0 me-2">{{ $title }}</h5>
            </div>
            <div class="demo-inline-spacing">
                <button type="button" class="btn btn-primary filter-ig" data-bs-toggle="modal"
                    data-bs-target="#filterRoleModal">Cari</button>
                <button type="button" class="btn btn-primary upload-foto">Upload</button>
                <button type="button" class="btn btn-primary lihat-foto">Lihat</button>
            </div>
        </div>
        <div class="card-datatable text-nowrap">
            <table class="datatables-ig table table-bordered table-responsive" data-title="{{ $title }}"
                data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
                <thead>
                    <tr>
                        <th></th>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>No. Input Gudang</th>
                        <th>Tipe Barang</th>
                        <th>No. PO</th>
                        <th>No. SPK</th>
                        <th>Supplier</th>
                        <th>Total</th>
                        <th>Foto Bon</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- Modal Upload / Lihat Foto Bon                           -->
    <!-- ======================================================= -->
    <div class="modal" id="uploadBonModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Foto Bon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadBonForm" method="post" action="{{ url('foto-bon-list') }}" onSubmit="return false"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" id="ig_id">
                        <input type="hidden" name="kode_cabang" id="kode_cabang" value="{{ $user_cabang }}">

                        {{-- Info Header --}}
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">No. Input Gudang</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="add-kode-input"
                                            class="form-control form-control-sm text-primary fw-bold" disabled />
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Tanggal</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="add-tanggal"
                                            class="form-control form-control-sm text-primary" disabled />
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Tipe Barang</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="add-tipe-barang"
                                            class="form-control form-control-sm text-primary" disabled />
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">No. Bon</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="add-no-bon"
                                            class="form-control form-control-sm text-primary" disabled />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">No. PO</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="add-kode-order"
                                            class="form-control form-control-sm text-primary" disabled />
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">No. SPK</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="add-kode-spk"
                                            class="form-control form-control-sm text-primary" disabled />
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Supplier</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="add-nama-pemasok"
                                            class="form-control form-control-sm text-primary" disabled />
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Total</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="add-total"
                                            class="form-control form-control-sm text-primary text-end" disabled />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="container-m-nx mt-2" />

                        {{-- Input File --}}
                        <div class="row g-2 filter-file-photo">
                            <div class="col-md-6">
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">File Foto Bon</label>
                                    <div class="col-sm-8">
                                        <input type="file" class="form-control" id="add-file-photo" name="photo[]"
                                            accept=".jpg,.jpeg,.png" required multiple />
                                        <div class="form-text">Format file: jpg | png</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Galeri Foto --}}
                        <div class="row mt-2 filter-photo-container">
                            <div class="col-xl-12">
                                <div class="card border-0 shadow-none">
                                    <div class="card-body p-0">
                                        <button type="button" class="btn btn-primary d-none" id="btn-download-all">
                                            <i class="icon-base ri ri-download-2-line me-1"></i>Download Semua
                                        </button>
                                        <div class="row g-4 mt-2" id="photo-container"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="container-m-nx mt-2" />

                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-primary btn-submit">Upload</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Modal Upload -->

    <!-- ======================================================= -->
    <!-- Modal Cari / Filter Input Gudang                        -->
    <!-- ======================================================= -->
    <div class="modal" id="filterRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cari {{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formFilterIG" onsubmit="return false">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label" for="filter-kode-input">No. Input
                                        Gudang</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="filter-kode-input"
                                            class="form-control form-control-sm text-uppercase" />
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label" for="filter-kode-order">No. PO</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="filter-kode-order"
                                            class="form-control form-control-sm text-uppercase" />
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label" for="filter-tanggal-awal">Tanggal</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="filter-tanggal-awal"
                                            class="form-control form-control-sm dt-date" value="{{ date('01/01/Y') }}" />
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label" for="filter-tipe-barang">Tipe Barang</label>
                                    <div class="col-sm-8">
                                        <select id="filter-tipe-barang" class="select2 form-select"
                                            data-allow-clear="true">
                                            <option value="all">Pilih Semua</option>
                                            @foreach ($tipe_barang as $row)
                                                <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label" for="filter-kode-spk">No. SPK</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="filter-kode-spk"
                                            class="form-control form-control-sm text-uppercase" />
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label" for="filter-nama-pemasok">Supplier</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="filter-nama-pemasok"
                                            class="form-control form-control-sm text-uppercase" />
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label text-sm-center">s/d</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="filter-tanggal-akhir"
                                            class="form-control form-control-sm dt-date" value="{{ date('d/m/Y') }}" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="container-m-nx mt-2" />

                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-primary">Cari</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Modal Filter -->

@endsection
