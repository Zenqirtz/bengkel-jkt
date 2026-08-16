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

@section('page-script')
    <style>
        .input-readonly {
            background-color: #dcdcdc !important;
            border: 1px solid #a0a0a0;
        }
    </style>
    <script src="{{ asset('assets/js/input-memorial.js') }}"></script>
@endsection

@section('content')

    {{-- TABEL UTAMA --}}
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="m-0">{{ $title }}</h5>
            <div class="demo-inline-spacing">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterRoleModal">Cari</button>
                <button class="btn btn-primary add-new" data-bs-toggle="modal" data-bs-target="#addRoleModal">Tambah</button>
                <button class="btn btn-primary edit-record">Ubah</button>
                <button class="btn btn-primary delete-record">Hapus</button>
            </div>
        </div>
        <div class="card-datatable text-nowrap">
            <table class="datatables-im table table-bordered" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}"
                data-delete="{{ $isDel }}">
                <thead>
                    <tr>
                        <th></th>
                        <th>Tanggal</th>
                        <th>No. Voucher</th>
                        <th>Jenis</th>
                        <th>Transaksi</th>
                        <th>No. SPK</th>
                        <th>Account / COA</th>
                        <th class="text-end">Jumlah Dibayar</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- MODAL CARI --}}
    <div class="modal" id="filterRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cari {{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCariData">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">No. Voucher</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="filter-no-voucher" class="form-control form-control-sm">
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Jenis</label>
                                    <div class="col-sm-8">
                                        <select id="filter-jenis" class="select2 form-select">
                                            <option value="all">Pilih Semua</option>
                                            @foreach ($jenis as $j)
                                                <option value="{{ $j->kode }}">{{ $j->keterangan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Transaksi</label>
                                    <div class="col-sm-8">
                                        <select id="filter-transaksi" class="select2 form-select">
                                            <option value="all">Pilih Semua</option>
                                            @foreach ($jenisTransaksi as $j)
                                                <option value="{{ $j->kode }}">{{ $j->keterangan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Tgl. Awal</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="filter-tanggal-awal"
                                            class="form-control form-control-sm dt-date" value="{{ date('01/m/Y') }}">
                                    </div>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Tgl. Akhir</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="filter-tanggal-akhir"
                                            class="form-control form-control-sm dt-date" value="{{ date('d/m/Y') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="mt-2">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL FORM TAMBAH / UBAH --}}
    <div class="modal" id="addRoleModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Form {{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addNewDataForm" method="post" action="{{ url('input-memorial-list') }}">
                        @csrf
                        <input type="hidden" name="id" id="im_id">

                        <div class="row g-2">

                            {{-- Kolom Kiri --}}
                            <div class="col-md-6">

                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">No. Voucher</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="add-no-voucher" name="no_voucher"
                                            class="form-control form-control-sm input-readonly text-primary fw-bold"
                                            readonly>
                                    </div>
                                </div>

                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Jenis</label>
                                    <div class="col-sm-8 form-control-validation">
                                        <select id="add-jenis" name="jenis" class="select2 form-select">
                                            <option value="">Pilih Jenis</option>
                                            @foreach ($jenis as $j)
                                                <option value="{{ $j->kode }}">{{ $j->keterangan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Transaksi</label>
                                    <div class="col-sm-8 form-control-validation">
                                        <select id="add-transaksi" name="transaksi" class="select2 form-select">
                                            <option value="">Pilih Transaksi</option>
                                            @foreach ($jenisTransaksi as $j)
                                                <option value="{{ $j->kode }}">{{ $j->keterangan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Tipe</label>
                                    <div class="col-sm-8 form-control-validation">
                                        <select id="add-tipe" name="tipe" class="select2 form-select">
                                            <option value="">Pilih Tipe</option>
                                            @foreach ($tipeMemorial as $t)
                                                <option value="{{ $t->kode }}">{{ $t->keterangan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- No. SPK (muncul jika tipe = SPK) --}}
                                <div id="section-spk">
                                    <div class="row mb-1 align-items-center">
                                        <label class="col-sm-4 col-form-label">No. SPK</label>
                                        <div class="col-sm-8 d-flex gap-2">
                                            <input type="text" id="add-no-spk" name="no_spk"
                                                class="form-control form-control-sm input-readonly text-primary fw-bold"
                                                readonly>
                                            <button type="button" class="btn btn-sm btn-primary"
                                                id="btn-pilih-spk">Cari</button>
                                        </div>
                                    </div>
                                    <div class="row mb-1 align-items-center">
                                        <label class="col-sm-4 col-form-label">Nama Pemilik</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="add-nama-pemilik"
                                                class="form-control form-control-sm input-readonly" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-1 align-items-center">
                                        <label class="col-sm-4 col-form-label">No. Polisi</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="add-no-polisi"
                                                class="form-control form-control-sm input-readonly" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-1 align-items-center">
                                        <label class="col-sm-4 col-form-label">No. Invoice</label>
                                        <div class="col-sm-8 d-flex gap-2">
                                            <select id="add-no-invoice" name="no_invoice" class="select2 form-select">
                                                <option value="">Pilih SPK dahulu</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- IG & Bon Toko (muncul jika tipe = Umum) --}}
                                <div id="section-ig" style="display:none">
                                    <div class="row mb-1 align-items-center">
                                        <label class="col-sm-4 col-form-label">Nama Supplier</label>
                                        <div class="col-sm-8 d-flex gap-2">
                                            <input type="text" id="add-nama-supplier" name="nama_supplier"
                                                class="form-control form-control-sm input-readonly text-primary fw-bold"
                                                readonly>
                                            <button type="button" class="btn btn-sm btn-primary"
                                                id="btn-pilih-ig">Cari</button>
                                        </div>
                                    </div>
                                    <div class="row mb-1 align-items-center">
                                        <label class="col-sm-4 col-form-label">No. IG</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="add-no-ig" name="no_ig"
                                                class="form-control form-control-sm input-readonly" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-1 align-items-center">
                                        <label class="col-sm-4 col-form-label">No. Bon Toko</label>
                                        <div class="col-sm-8">
                                            <select id="add-no-bon-toko" name="no_bon_toko" class="select2 form-select">
                                                <option value="">Pilih IG dahulu</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>{{-- /col-kiri --}}

                            {{-- Kolom Kanan --}}
                            <div class="col-md-6">

                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Tanggal</label>
                                    <div class="col-sm-8 form-control-validation">
                                        <input type="text" id="add-tanggal" name="tanggal"
                                            class="form-control form-control-sm dt-date" value="{{ date('d/m/Y') }}">
                                    </div>
                                </div>

                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Account / COA</label>
                                    <div class="col-sm-8 form-control-validation">
                                        <select id="add-account-coa" name="account_coa" class="select2 form-select">
                                            <option value="">Pilih COA</option>
                                            {{-- @foreach ($coa as $c)
                                                <option value="{{ $c->keterangan }}">{{ $c->keterangan }}</option>
                                            @endforeach --}}
                                            @foreach ($coa as $c)
                                                <option value="{{ $c->acct_cd }}">{{ $c->descs }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Nilai</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="add-nilai" name="nilai"
                                            class="form-control form-control-sm text-end input-readonly" readonly>
                                    </div>
                                </div>

                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Jumlah</label>
                                    <div class="col-sm-8 form-control-validation">
                                        <input type="text" id="add-jml-dibayar" name="jml_dibayar"
                                            class="form-control form-control-sm text-end hitung-sisa" placeholder="0">
                                    </div>
                                </div>

                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Sisa</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="add-sisa" name="sisa"
                                            class="form-control form-control-sm text-end input-readonly fw-bold text-primary"
                                            readonly placeholder="0">
                                    </div>
                                </div>

                                <div class="row mb-1">
                                    <label class="col-sm-4 col-form-label">Keterangan</label>
                                    <div class="col-sm-8">
                                        <textarea id="add-keterangan" name="keterangan" class="form-control form-control-sm" rows="4"></textarea>
                                    </div>
                                </div>

                            </div>{{-- /col-kanan --}}
                        </div>

                        <hr class="mt-2">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PILIH SPK --}}
    <div class="modal" id="modalPilihSpk" data-bs-backdrop="static" tabindex="-1">
         <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih SPK</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-nowrap">
                        <table class="datatables-spk-popup table table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Tanggal</th>
                                    <th>No. SPK</th>
                                    <th>Nama Pemilik</th>
                                    <th>No. Polisi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-pilih-spk-terpilih">Pilih</button>
                </div>
            </div>
        </div>
    </div>

   {{-- MODAL PILIH IG --}}
    <div class="modal" id="modalPilihIg" data-bs-backdrop="static" tabindex="-1">
         <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Input Gudang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-nowrap">
                        <table class="datatables-ig-popup table table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Tanggal</th>
                                    <th>No. IG</th>
                                    <th>No. Bon Toko</th>
                                    <th>Nama Supplier</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-pilih-ig-terpilih">Pilih</button>
                </div>
            </div>
        </div>
    </div>

@endsection
