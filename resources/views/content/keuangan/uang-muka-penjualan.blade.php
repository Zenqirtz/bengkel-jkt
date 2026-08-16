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
    <style>
        .input-readonly {
            background-color: #dcdcdc !important;
            border: 1px solid #a0a0a0;
            box-shadow: inset 1px 1px 2px #c0c0c0;
        }
    </style>
    <script src="{{ asset('assets/js/uang-muka-penjualan.js') }}"></script>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div class="card-title mb-0">
                <h5 class="m-0 me-2">{{ $title }}</h5>
            </div>
            <div class="demo-inline-spacing">
                <button type="button" class="btn btn-primary cari-data" data-bs-toggle="modal"
                    data-bs-target="#filterRoleModal">Cari</button>
                <button type="button" class="btn btn-primary add-new" data-bs-toggle="modal"
                    data-bs-target="#addRoleModal">Tambah</button>
                <button type="button" class="btn btn-primary edit-record">Ubah</button>
                <button type="button" class="btn btn-primary delete-record">Hapus</button>
                <button type="button" class="btn btn-primary print-record">Cetak</button>
            </div>
        </div>

        <div class="card-datatable text-nowrap">
            <table class="datatables-ump table table-bordered table-responsive" data-title="{{ $title }}"
                data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
                <thead>
                    <tr>
                        <th></th>
                        <th>Tanggal</th>
                        <th>Nomor Transaksi</th>
                        <th>Jenis Penerimaan</th>
                        <th>Nama</th>
                        <th>Masuk Kas/Bank</th>
                        <th>No. Rekening</th>
                        <th class="text-end">Nilai</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- ===== MODAL CARI ===== --}}
    <div class="modal" id="filterRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cari {{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCariData" onsubmit="return false">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-3 col-form-label" for="filter-no-transaksi">No. Transaksi</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="filter-no-transaksi"
                                            class="form-control form-control-sm text-uppercase" />
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-3 col-form-label" for="filter-nama">Nama</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="filter-nama"
                                            class="form-control form-control-sm text-uppercase" />
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-3 col-form-label" for="filter-tanggal-awal">Tanggal</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="filter-tanggal-awal"
                                            class="form-control form-control-sm dt-date" value="{{ date('01/m/Y') }}" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-3 col-form-label" for="filter-jenis">Jenis Penerimaan</label>
                                    <div class="col-sm-9">
                                        <select id="filter-jenis" class="select2 form-select" data-allow-clear="true">
                                            <option value="all">Pilih Semua</option>
                                            @foreach ($jenisPenerimaan as $j)
                                                <option value="{{ $j->keterangan }}">{{ $j->keterangan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-3 col-form-label" for="filter-kode-bank">Masuk Kas/Bank</label>
                                    <div class="col-sm-9">
                                        <select id="filter-kode-bank" class="select2 form-select" data-allow-clear="true">
                                            <option value="all">Pilih Semua</option>
                                            @foreach ($bank as $b)
                                                <option value="{{ $b->kode_bank }}">{{ $b->nama_bank }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-3 col-form-label" for="filter-tanggal-akhir">s/d</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="filter-tanggal-akhir"
                                            class="form-control form-control-sm dt-date" value="{{ date('d/m/Y') }}" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="container-m-nx mt-2" />

                        <div class="row mt-3 g-5">
                            <div class="col-12 d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary me-sm-3 me-1">Cari</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- ===== END MODAL CARI ===== --}}

    {{-- ===== MODAL TAMBAH/UBAH ===== --}}
    <div class="modal" id="addRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Form {{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="add-new-user pt-0" id="addNewDataForm" method="post"
                        action="{{ url('uang-muka-penjualan-list') }}">
                        @csrf
                        <input type="hidden" name="id" id="user_id">

                        <div class="row g-2">
                            {{-- Kiri --}}
                            <div class="col-md-6">
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-3 col-form-label" for="add-no-transaksi">Nomor</label>
                                    <div class="col-sm-9 form-control-validation">
                                        <input type="text" id="add-no-transaksi" name="no_transaksi"
                                            class="form-control form-control-sm input-readonly text-primary fw-bold"
                                            readonly />
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-3 col-form-label" for="add-jenis">Jenis Penerimaan</label>
                                    <div class="col-sm-9 form-control-validation">
                                        <select id="add-jenis" name="jenis_penerimaan" class="select2 form-select"
                                            data-allow-clear="true">
                                            <option value="">Pilih Jenis</option>
                                            @foreach ($jenisPenerimaan as $j)
                                                <option value="{{ $j->keterangan }}">{{ $j->keterangan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-3 col-form-label" for="add-nama">Nama</label>
                                    <div class="col-sm-9 form-control-validation">
                                        <input type="text" id="add-nama" name="nama"
                                            class="form-control form-control-sm text-uppercase" maxlength="100" />
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-3 col-form-label" for="add-kode-bank">Masuk Kas/Bank</label>
                                    <div class="col-sm-9 form-control-validation">
                                        <select id="add-kode-bank" name="kode_bank" class="select2 form-select"
                                            data-allow-clear="true">
                                            <option value="">Pilih Bank</option>
                                            @foreach ($bank as $b)
                                                <option value="{{ $b->kode_bank }}">{{ $b->nama_bank }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Kanan --}}
                            <div class="col-md-6">
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-3 col-form-label" for="add-tanggal">Tanggal</label>
                                    <div class="col-sm-9 form-control-validation">
                                        <input type="text" id="add-tanggal" name="tanggal_transaksi"
                                            class="form-control form-control-sm dt-date" value="{{ date('d/m/Y') }}" />
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-3 col-form-label" for="add-no-rekening">No. Rekening</label>
                                    <div class="col-sm-9 form-control-validation">
                                        <select id="add-no-rekening" name="no_rekening" class="select2 form-select"
                                            data-allow-clear="true">
                                            <option value="">Pilih Bank terlebih dahulu</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-3 col-form-label" for="add-nilai">Nilai</label>
                                    <div class="col-sm-9 form-control-validation">
                                        {{-- DIUBAH: type="text" + class invoice-price agar bisa format ribuan --}}
                                        <input type="text" id="add-nilai" name="nilai"
                                            class="form-control form-control-sm text-end invoice-price" placeholder="0" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="container-m-nx mt-2" />

                        <div class="row mt-3 g-5">
                            <div class="col-12 d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- ===== END MODAL TAMBAH/UBAH ===== --}}

@endsection
