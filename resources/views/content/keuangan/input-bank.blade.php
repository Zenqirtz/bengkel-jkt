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
    <script src="{{ asset('assets/js/input-bank.js') }}"></script>
@endsection

@section('content')

    {{-- ================================================================
         TABEL UTAMA
    ================================================================ --}}
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="m-0">{{ $title }}</h5>
            <div class="demo-inline-spacing">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterRoleModal">Cari</button>
                <button class="btn btn-primary add-new" data-bs-toggle="modal" data-bs-target="#addRoleModal">Tambah</button>
                <button class="btn btn-primary edit-record">Ubah</button>
                <button class="btn btn-primary delete-record">Hapus</button>
                <button class="btn btn-primary detail-record">Lihat</button>
                <button class="btn btn-primary cetak-record">Cetak</button>
            </div>
        </div>
        <div class="card-datatable text-nowrap">
            <table class="datatables-ib table table-bordered" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}"
                data-delete="{{ $isDel }}">
                <thead>
                    <tr>
                        {{-- <th></th> --}}
                        <th><input type="checkbox" id="selectAllIB" class="form-check-input"></th>
                        <th>Tanggal</th>
                        <th>No. Voucher</th>
                        <th>Jenis</th>
                        <th>Transaksi</th>
                        <th>No. Inv. Gabung</th>
                        <th>No. SPK</th>
                        <th>Bank</th>
                        <th>Account/COA</th>
                        <th class="text-end">Jumlah Dibayar</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- ================================================================
         MODAL CARI
    ================================================================ --}}
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
                                            @foreach ($jenisMasuk as $j)
                                                <option value="{{ $j->keterangan }}" data-jenis="PENERIMAAN">
                                                    {{ $j->keterangan }}
                                                </option>
                                            @endforeach
                                            @foreach ($jenisKeluar as $j)
                                                <option value="{{ $j->keterangan }}" data-jenis="PENGELUARAN">
                                                    {{ $j->keterangan }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Bank</label>
                                    <div class="col-sm-8">
                                        <select id="filter-kode-bank" class="select2 form-select">
                                            <option value="all">Pilih Semua</option>
                                            @foreach ($bank as $b)
                                                <option value="{{ $b->kode_bank }}">{{ $b->nama_bank }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
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

    {{-- ================================================================
         MODAL FORM TAMBAH / UBAH
    ================================================================ --}}
    <div class="modal" id="addRoleModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Form {{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addNewDataForm" method="post" action="{{ url('input-bank-list') }}">
                        @csrf
                        <input type="hidden" name="id" id="ib_id">

                        <div class="row g-2">

                            {{-- ============ Kolom Kiri ============ --}}
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
                                            <option value="">Pilih Jenis dahulu</option>
                                            @foreach ($jenisMasuk as $j)
                                                <option value="{{ $j->keterangan }}" data-jenis="PENERIMAAN">
                                                    {{ $j->keterangan }}
                                                </option>
                                            @endforeach
                                            @foreach ($jenisKeluar as $j)
                                                <option value="{{ $j->keterangan }}" data-jenis="PENGELUARAN">
                                                    {{ $j->keterangan }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label"></label>
                                    <div class="col-sm-8">
                                        <div class="form-check mt-1">
                                            <input type="checkbox" class="form-check-input" id="chk-pakai-invoice">
                                            <label class="form-check-label" for="chk-pakai-invoice">
                                                Berdasarkan Invoice Gabungan
                                            </label>
                                        </div>
                                    </div>
                                </div>


                                {{-- <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">No. Inv. Single</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="add-no-inv-single" name="no_inv_single"
                                            class="form-control form-control-sm text-uppercase">
                                    </div>
                                </div> --}}

                                {{-- <div class="row mb-1 align-items-center"> --}}
                                <div class="row mb-1 align-items-center" id="row-inv-gabung" style="display:none">
                                    <label class="col-sm-4 col-form-label" id="label-inv-gabung">
                                        No. Inv. Gabung (LCR)
                                    </label>
                                    <div class="col-sm-8 d-flex gap-2">
                                        <input type="text" id="add-no-inv-gabung" name="no_inv_gabung"
                                            class="form-control form-control-sm input-readonly text-primary fw-bold"
                                            readonly>
                                        <button type="button" class="btn btn-sm btn-primary" id="btn-pilih-inv"
                                            data-tipe="lcr">
                                            Cari
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary d-none" id="btn-pilih-hrl">
                                            Cari
                                        </button>
                                        <input type="hidden" id="add-id-hrl" name="id_hrl">
                                    </div>
                                </div>

                            </div>{{-- /col-kiri --}}

                            {{-- ============ Kolom Kanan ============ --}}
                            <div class="col-md-6">

                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Tanggal</label>
                                    <div class="col-sm-8 form-control-validation">
                                        <input type="text" id="add-tanggal" name="tanggal"
                                            class="form-control form-control-sm dt-date" value="{{ date('d/m/Y') }}">
                                    </div>
                                </div>

                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Bank</label>
                                    <div class="col-sm-8 form-control-validation">
                                        <select id="add-kode-bank" name="kode_bank" class="select2 form-select">
                                            <option value="">Pilih Bank</option>
                                            @foreach ($bank as $b)
                                                <option value="{{ $b->kode_bank }}">{{ $b->nama_bank }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">No. Rekening</label>
                                    <div class="col-sm-8 form-control-validation">
                                        <select id="add-no-rekening" name="no_rekening" class="select2 form-select">
                                            <option value="">Pilih Bank dahulu</option>
                                        </select>
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
                                    <label class="col-sm-4 col-form-label">No. SPK</label>
                                    <div class="col-sm-8">
                                        <select id="add-no-spk" name="no_spk" class="select2 form-select">
                                            <option value="">Pilih No. SPK</option>
                                            @foreach ($spkList as $s)
                                                <option value="{{ $s->kode_spk }}">{{ $s->kode_spk }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>{{-- /col-kanan --}}
                        </div>{{-- /row atas --}}

                        <hr class="mt-2 mb-2">

                        {{-- ============ Baris Nilai ============ --}}
                        <div class="row g-2">
                            <div class="col-md-6">

                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Nilai</label>
                                    <div class="col-sm-8">
                                       <input type="text" id="add-nilai" name="nilai"
                                       {{-- class="form-control form-control-sm text-end input-readonly" readonly> --}}
                                          class="form-control form-control-sm text-end hitung-sisa" placeholder="0">
                                    </div>
                                </div>
{{--
                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Jumlah Dibayar</label>
                                    <div class="col-sm-8 form-control-validation">
                                        <input type="text" id="add-jml-dibayar" name="jml_dibayar"
                                            class="form-control form-control-sm text-end hitung-sisa" placeholder="0">
                                    </div>
                                </div> --}}

                                <div id="section-uang-muka" style="display:none">
                                    <div class="row mb-1 align-items-center">
                                        <label class="col-sm-4 col-form-label">No. Uang Muka</label>
                                        <div class="col-sm-8 d-flex gap-2">
                                            <input type="text" id="add-no-uang-muka" name="no_uang_muka"
                                                class="form-control form-control-sm input-readonly text-primary fw-bold">
                                            <button type="button" class="btn btn-sm btn-primary" id="btn-pilih-umj"
                                                title="Pilih dari Uang Muka Penjualan">
                                                Cari
                                            </button>
                                        </div>
                                    </div>
                                    <input type="hidden" id="add-nama-uang-muka">
                                    <div class="row mb-1 align-items-center">
                                        <label class="col-sm-4 col-form-label">DP</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="add-dp" name="dp"
                                                class="form-control form-control-sm text-end input-readonly" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">PPH</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="add-pph" name="pph"
                                            class="form-control form-control-sm text-end hitung-sisa" placeholder="0">
                                    </div>
                                </div>

                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Biaya Merimen</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="add-biaya-merimen" name="biaya_merimen"
                                            class="form-control form-control-sm text-end hitung-sisa" placeholder="0">
                                    </div>
                                </div>

                                <div class="row mb-1 align-items-center">
                                    <label class="col-sm-4 col-form-label">Biaya Admin</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="add-biaya-admin" name="biaya_admin"
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

                            </div>{{-- /col nilai --}}

                            <div class="col-md-6">
                                <div class="row mb-1">
                                    <label class="col-sm-4 col-form-label">Keterangan</label>
                                    <div class="col-sm-8">
                                        <textarea id="add-keterangan" name="keterangan" class="form-control form-control-sm" rows="7"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>{{-- /row nilai --}}

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

    {{-- ================================================================
         MODAL PILIH INVOICE GABUNGAN (LCR / LSR)
    ================================================================ --}}
    <div class="modal" id="modalPilihInv" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="title-modal-inv">Pilih Invoice Gabungan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-nowrap">
                        <table class="datatables-inv-popup table table-bordered table-responsive">
                            <thead>
                                <tr>
                                    {{-- <th></th> --}}
                                    <th><input type="checkbox" id="selectAllInvPopup" class="form-check-input"></th>
                                    <th>No. Transaksi</th>
                                    <th id="th-nama-inv">Nama</th>
                                    <th>Total Nilai</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-pilih-inv-terpilih">Pilih</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================
         MODAL PILIH UANG MUKA PENJUALAN (UMJ)
    ================================================================ --}}
    <div class="modal" id="modalPilihUmj" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Uang Muka Penjualan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-nowrap">
                        <table class="datatables-umj-popup table table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>No. Transaksi</th>
                                    <th>Nama</th>
                                    <th>Jenis Penerimaan</th>
                                    <th>Nilai</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-pilih-umj-terpilih">Pilih</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================
         MODAL PILIH PEMBAYARAN UPAH HARIAN LEPAS (HRL)
    ================================================================ --}}
    <div class="modal" id="modalPilihHrl" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Pembayaran Upah Harian Lepas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-nowrap">
                    <table class="datatables-hrl-popup table table-bordered table-responsive">
                        <thead>
                            <tr>
                                <th></th>
                                <th>No. Transaksi</th>
                                <th>Tanggal</th>
                                <th>Nama Pekerja</th>
                                <th>Jenis Pekerjaan</th>
                                <th class="text-end">Total Nilai</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger" id="btn-batal-hrl">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-pilih-hrl-terpilih">Pilih</button>
            </div>
        </div>
    </div>
    </div>

    {{-- ================================================================
     MODAL DETAIL INPUT BANK (READ-ONLY)
      ================================================================ --}}
      <div class="modal" id="viewRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-xl" role="document">
              <div class="modal-content">
                  <div class="modal-header">
                      <h5 class="modal-title">Detail {{ $title }}</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                      <div class="row g-2">
                          <div class="col-md-6">
                              <div class="row mb-1 align-items-center">
                                  <label class="col-sm-4 col-form-label">No. Voucher</label>
                                  <div class="col-sm-8">
                                      <input type="text" id="view-no-voucher" class="form-control form-control-sm input-readonly text-primary fw-bold" readonly>
                                  </div>
                              </div>
                              <div class="row mb-1 align-items-center">
                                  <label class="col-sm-4 col-form-label">Tanggal</label>
                                  <div class="col-sm-8">
                                      <input type="text" id="view-tanggal" class="form-control form-control-sm input-readonly" readonly>
                                  </div>
                              </div>
                              <div class="row mb-1 align-items-center">
                                  <label class="col-sm-4 col-form-label">Jenis</label>
                                  <div class="col-sm-8">
                                      <input type="text" id="view-jenis" class="form-control form-control-sm input-readonly" readonly>
                                  </div>
                              </div>
                              <div class="row mb-1 align-items-center">
                                  <label class="col-sm-4 col-form-label">Transaksi</label>
                                  <div class="col-sm-8">
                                      <input type="text" id="view-transaksi" class="form-control form-control-sm input-readonly" readonly>
                                  </div>
                              </div>
                          </div>
                          <div class="col-md-6">
                              <div class="row mb-1 align-items-center">
                                  <label class="col-sm-4 col-form-label">Bank</label>
                                  <div class="col-sm-8">
                                      <input type="text" id="view-bank" class="form-control form-control-sm input-readonly" readonly>
                                  </div>
                              </div>
                              <div class="row mb-1 align-items-center">
                                  <label class="col-sm-4 col-form-label">Account/COA</label>
                                  <div class="col-sm-8">
                                      <input type="text" id="view-coa" class="form-control form-control-sm input-readonly" readonly>
                                  </div>
                              </div>
                              <div class="row mb-1 align-items-center">
                                  <label class="col-sm-4 col-form-label">No. SPK</label>
                                  <div class="col-sm-8">
                                      <input type="text" id="view-spk" class="form-control form-control-sm input-readonly" readonly>
                                  </div>
                              </div>
                              <div class="row mb-1 align-items-center">
                                  <label class="col-sm-4 col-form-label">Nilai</label>
                                  <div class="col-sm-8">
                                      <input type="text" id="view-nilai" class="form-control form-control-sm text-end input-readonly fw-bold text-primary" readonly>
                                  </div>
                              </div>
                          </div>
                      </div>

                      <hr class="mt-2 mb-2">

                     <h6 class="mb-2">Rincian No. Inv. Gabung</h6>
                        <div class="custom-table-container">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>No. Transaksi</th>
                                        <th class="text-end">Nilai</th>
                                        <th class="text-end">PPH</th>
                                    </tr>
                                </thead>
                                <tbody id="body-invgabung-view"></tbody>
                            </table>
                        </div>

                      <div class="row mt-3 g-5">
                          <div class="col-12 d-flex justify-content-end">
                              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
@endsection
