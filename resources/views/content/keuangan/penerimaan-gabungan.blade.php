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
    <script src="{{ asset('assets/js/penerimaan-gabungan.js') }}"></script>
@endsection

@section('content')
    {{-- ===== CARD UTAMA ===== --}}
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
            </div>
        </div>

        <div class="card-datatable text-nowrap">
            <table class="datatables-pg table table-bordered table-responsive" data-title="{{ $title }}"
                data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
                <thead>
                    <tr>
                        <th></th>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nomor Transaksi</th>
                        <th>Jenis Pembayaran</th>
                        <th>Nama Customer</th>
                        <th>Masuk Kas/Bank</th>
                        <th>No. Rekening</th>
                        <th class="text-end">Total Nilai</th>
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
                                    <label class="col-sm-3 col-form-label" for="filter-nama">Nama Customer</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="filter-nama"
                                            class="form-control form-control-sm text-uppercase" />
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-3 col-form-label" for="filter-tanggal-awal">Tanggal</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="filter-tanggal-awal"
                                            class="form-control form-control-sm dt-date" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-3 col-form-label" for="filter-jenis">Jenis Pembayaran</label>
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
                                            class="form-control form-control-sm dt-date" />
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
                    <form class="pt-0" id="addNewDataForm" method="post"
                        action="{{ url('penerimaan-gabungan-list') }}">
                        @csrf
                        <input type="hidden" name="id" id="pg_id">
                        <input type="hidden" name="details" id="pg_details">

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
                                    <label class="col-sm-3 col-form-label" for="add-jenis">Jenis Pembayaran</label>
                                    <div class="col-sm-9 form-control-validation">
                                        <select id="add-jenis" name="jenis_pembayaran" class="select2 form-select"
                                            data-allow-clear="true">
                                            <option value="">Pilih Jenis</option>
                                            @foreach ($jenisPenerimaan as $j)
                                                <option value="{{ $j->keterangan }}">{{ $j->keterangan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                               <div class="row mb-1 align-items-center">
                                    <label class="col-sm-3 col-form-label" for="add-kode-pelanggan">Nama Customer</label>
                                    <div class="col-sm-9 form-control-validation">
                                        <input type="hidden" id="add-kode-pelanggan" name="kode_pelanggan" />
                                        <input type="hidden" id="add-nama-customer" name="nama_customer" />
                                        <select id="add-select-customer" class="form-select"></select>
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
                                    <label class="col-sm-3 col-form-label">Total Nilai</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="add-total-nilai"
                                            class="form-control form-control-sm text-end input-readonly fw-bold text-primary"
                                            readonly placeholder="0" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===== SECTION DETAIL SPK ===== --}}
                        <hr class="container-m-nx mt-2 mb-2" />
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">List No. SPK</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-pilih-spk">
                                <i class="ri-add-line"></i> Pilih SPK
                            </button>
                        </div>

                        <div class="table-responsive">
                            {{-- <table class="table table-sm table table-bordered" id="tbl-detail-spk">
                                <thead class="table table-bordered">
                                    <tr>
                                        <th style="width:40px">No</th>
                                        <th>No. SPK</th>
                                        <th>Nama Customer</th>
                                        <th style="width:180px" class="text-end">Nilai</th>
                                        <th style="width:60px" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-detail-spk">
                                    <tr id="row-no-data">
                                        <td colspan="5" class="text-center text-muted py-2">Belum ada SPK ditambahkan
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="table table-bordered">
                                        <td colspan="3" class="text-end">Total</td>
                                        <td class="text-end" id="tfoot-total">0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table> --}}
                            <table class="table table-sm table table-bordered" id="tbl-detail-spk">
                                <thead class="table table-bordered">
                                    <tr>
                                        <th style="width:40px">No</th>
                                        <th>No. SPK</th>
                                        <th>Nama Customer</th>
                                        <th style="width:200px" class="text-end">Nilai</th>
                                        <th style="width:200px" class="text-end">PPh</th>
                                        <th style="width:200px" class="text-end">Biaya Marimen</th>
                                        <th style="width:60px" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-detail-spk">
                                    <tr id="row-no-data">
                                        <td colspan="7" class="text-center text-muted py-2">Belum ada SPK ditambahkan</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="table table-bordered">
                                        <td colspan="3" class="text-end">Total</td>
                                        <td class="text-end" id="tfoot-total">0</td>
                                        <td class="text-end" id="tfoot-total-pph">0</td>
                                        <td class="text-end" id="tfoot-total-merimen">0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
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

    {{-- ===== MODAL POPUP PILIH SPK ===== --}}
    <div class="modal" id="modalPilihSpk" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih SPK</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-nowrap">
                        <table class="datatables-spk-pg table table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Tanggal Masuk</th>
                                    <th>No. SPK</th>
                                    <th>Nama Customer</th>
                                    <th>Nilai</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-tambah-spk-terpilih">Pilih</button>
                </div>
            </div>
        </div>
    </div>
    {{-- ===== END MODAL POPUP PILIH SPK ===== --}}

@endsection
