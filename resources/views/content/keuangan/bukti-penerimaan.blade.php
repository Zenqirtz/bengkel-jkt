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
  /* Input Readonly (Background Abu-abu) */
  .input-readonly {
      background-color: #dcdcdc !important;
      border: 1px solid #a0a0a0;
      box-shadow: inset 1px 1px 2px #c0c0c0;
  }
</style>

  <script src="{{ asset('assets/js/bukti-penerimaan.js') }}"></script>
@endsection

@section('content')
  <!-- Users List Table -->
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="card-title mb-0">
        <h5 class="m-0 me-2">{{ $title }}</h5>
      </div>
      <div class="demo-inline-spacing">
        <button type="button" class="btn btn-primary cari-data" data-bs-toggle="modal" data-bs-target="#filterRoleModal">Cari</button>
        <button type="button" class="btn btn-primary add-new" data-bs-toggle="modal" data-bs-target="#addRoleModal">Tambah</button>
        <button type="button" class="btn btn-primary edit-record">Ubah</button>
        <button type="button" class="btn btn-primary delete-record">Hapus</button>
        <button type="button" class="btn btn-primary cetak-record">Cetak</button>
      </div>
    </div>
    <div class="card-datatable text-nowrap">
      <table class="datatables-bukti-penerimaan table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
        <thead>
          <tr>
            <th></th>
            <th>No</th>
            <th>Tanggal</th>
            <th>Nomor Transaksi</th>
            <th>Kategori</th>
            <th>Nomor Voucher</th>
            <th>Pelanggan</th>
            <th>Memo</th>
            <th>Cabang</th>
            <th>Bank</th>
            <th>Tanggal CH BG</th>
            <th>Nomor CH BG</th>
            <th>Tanggal Kliring</th>
            <th>Nomor Voucher Cabang</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>

  <!-- Form Cari Data Modal -->
  <div class="modal" id="filterRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="filterModalLabel">Cari {{ $title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="formCariData" onsubmit="return false">
            <div class="row g-2">
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-nomor-transaksi">Nomor Transaksi</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-nomor-transaksi" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-tanggal-awal">Tanggal</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-tanggal-awal" class="form-control form-control-sm dt-date" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-nomor-voucher">Nomor Voucher</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-nomor-voucher" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-kode-kategori">Kategori</label>
                  <div class="col-sm-9">
                    <select id="filter-kode-kategori" class="select2 form-select" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($kategori as $row)
                        <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-memo">Memo</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-memo" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label text-sm-center" for="filter-tanggal-akhir">s/d</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-tanggal-akhir" class="form-control form-control-sm dt-date" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-nomor-chbg">Nomor CH BG</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-nomor-chbg" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-kode-bank">Bank</label>
                  <div class="col-sm-9">
                    <select id="filter-kode-bank" class="select2 form-select" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($bank as $row)
                        <option value="{{ $row->kode_bank }}">{{ $row->nama_bank }}</option>
                      @endforeach
                    </select>
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
  <!--/ Form Cari Data Modal -->

  <!-- Form Tambah Data Modal -->
  <div class="modal" id="addRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addModalLabel">Form {{ $title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form class="add-new-user pt-0" id="addNewDataForm" method="post" action="{{ url('bukti-penerimaan-list') }}">
            @csrf
            <input type="hidden" name="id" id="user_id">
            <div class="row g-2">
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="add-nomor-transaksi">Nomor Transaksi</label>
                  <div class="col-sm-9 form-control-validation">
                    <input type="text" id="add-nomor-transaksi" name="no_transaksi" class="form-control form-control-sm text-primary fw-bold input-readonly" readonly />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="add-nomor-voucher">Nomor Voucher</label>
                  <div class="col-sm-9 form-control-validation">
                    <input type="text" id="add-nomor-voucher" name="no_voucher" class="form-control form-control-sm text-primary fw-bold input-readonly" readonly />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="add-tanggal">Tanggal</label>
                  <div class="col-sm-9 form-control-validation">
                    <input type="text" id="add-tanggal" name="tanggal_transaksi" class="form-control form-control-sm dt-date" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="add-kode-kategori">Kategori</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="add-kode-kategori" name="kode_kategori" class="select2 form-select" data-allow-clear="true">
                      <option value="">Pilih Kategori</option>
                      @foreach($kategori as $row)
                        <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="add-kode-pelanggan">Pelanggan</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="add-kode-pelanggan" name="kode_pelanggan" class="select2 form-select" data-allow-clear="true">
                      <option value="">Pilih Pelanggan</option>
                      @foreach($pelanggan as $row)
                        <option value="{{ $row->kode_pelanggan }}">{{ $row->nama_pelanggan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="add-bank-tujuan">Bank Tujuan</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="add-bank-tujuan" name="kode_bank" class="select2 form-select" data-allow-clear="true">
                      <option value="">Pilih Bank Tujuan</option>
                      @foreach($bank as $row)
                        <option value="{{ $row->kode_bank }}">{{ $row->nama_bank }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="add-bank-asal">Bank Asal</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="add-bank-asal" name="kode_bank_asal" class="select2 form-select" data-allow-clear="true">
                      <option value="">Pilih Bank Asal</option>
                      @foreach($bank as $row)
                        <option value="{{ $row->kode_bank }}">{{ $row->nama_bank }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="add-nomor-chbg">Nomor CH BG</label>
                  <div class="col-sm-9 form-control-validation">
                    <input type="text" id="add-nomor-chbg" name="no_ch_bg" class="form-control form-control-sm text-uppercase" maxlength="20" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="add-tanggal-chbg">Tanggal CH BG</label>
                  <div class="col-sm-9 form-control-validation">
                    <input type="text" id="add-tanggal-chbg" name="tanggal_ch_bg" class="form-control form-control-sm dt-date" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="add-memo">Memo</label>
                  <div class="col-sm-9 form-control-validation">
                    <textarea id="add-memo" name="memo" class="form-control form-control-sm text-uppercase" style="height: 100px;"></textarea>
                  </div>
                </div>
              </div>
            </div>

            <div class="row mt-2">
              <div class="col-xl-12">
                <div class="nav-align-top">
                  <ul class="nav nav-pills mb-4" role="tablist">
                    <li class="nav-item">
                      <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                        data-bs-target="#navs-pills-top-barang" aria-controls="navs-pills-top-barang"
                        aria-selected="true">Detail</button>
                    </li>
                  </ul>
                  <div class="tab-content">
                    <div class="tab-pane fade show active" id="navs-pills-top-barang" role="tabpanel">
                      <div class="bg-light border p-1">
                        <button type="button" class="btn btn-sm btn-primary add-detail" data-tipe="detail" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDet1">
                          <i class="icon-base ri ri-add-line icon-sm me-0 me-sm-2"></i>
                          <span class="d-none d-sm-inline-block">Tambah</span>
                        </button>
                      </div>
                      <div class="custom-table-container">
                        <table class="datatables-detail table table-bordered table-responsive">
                          <thead>
                            <tr>
                              <th width="5%">No</th>
                              <th>Uraian</th>
                              <th width="20%" class="text-center">Jumlah</th>
                              <th width="5%">Aksi</th>
                            </tr>
                          </thead>
                          <tfoot>
                            <tr>
                                <th colspan="2" style="text-align:right">Total</th>
                                <th style="text-align:right"><span id="total-uraian">0.00</span><input type="hidden" name="total" id="add-total-uraian"></th>
                                <th></th>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <hr class="container-m-nx mt-2" />

            <div class="row mt-3 g-5">
              <div class="col-12 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Simpan</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!--/ Form Tambah Data Modal -->

  <!-- Offcanvas to add detail detail -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddDet1" aria-labelledby="offcanvasAddLabelDet1">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddLabelDet1" class="offcanvas-title">Data Detail</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="add-new-user pt-0" id="addNewDataFormDet1">
        <input type="hidden" name="id" id="est_dtl1_id">

        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <input type="text" class="form-control text-uppercase is-invalid" id="add-uraian" name="uraian" placeholder="Uraian" />
          <label for="add-uraian">Uraian</label>
        </div>
        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <input type="text" class="form-control invoice-price is-invalid" id="add-jumlah" name="jumlah" placeholder="Jumlah" />
          <label for="add-jumlah">Jumlah</label>
        </div>

        <button type="submit" class="btn btn-primary me-sm-3 me-1">Simpan</button>
        <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="offcanvas">Batal</button>
      </form>
    </div>
  </div>

@endsection
