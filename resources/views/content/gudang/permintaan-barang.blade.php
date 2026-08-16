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
    /* Garis Pemisah Hitam Tebal */
    .divider-black {
        border-top: 2px solid #000;
        margin: 5px 0 5px 0;
        position: relative;
    }
    .operator-sign {
        position: absolute;
        right: -15px;
        top: -12px;
        font-weight: bold;
        font-size: 16px;
    }
  </style>

  <script src="{{ asset('assets/js/permintaan-barang.js') }}"></script>
@endsection

@section('content')
  <div class="card mb-6">
    <div class="card-widget-separator-wrapper">
      <div class="card-body card-widget-separator">
        <div class="row gy-4 gy-sm-1">
          <div class="col-sm-6 col-lg-4">
            <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-4 pb-sm-0">
              <div>
                <h4 class="mb-0" id="total-spk">0</h4>
                <p class="mb-0">SPK baru</p>
                <p class="mb-0"><small class="text-body-secondary">{{ date('Y') }}</small></p>
              </div>
              <div class="avatar me-sm-6">
                <span class="avatar-initial rounded-3 text-heading view-spk">
                  <i class="icon-base ri ri-calendar-2-line icon-26px"></i>
                </span>
              </div>
            </div>
            <hr class="d-none d-sm-block d-lg-none me-6" />
          </div>
          <div class="col-sm-6 col-lg-4">
            <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-4 pb-sm-0">
              <div>
                <h4 class="mb-0" id="total-permintaan-bulan">0</h4>
                <p class="mb-0">Permintaan Barang</p>
                <p class="mb-0"><small class="text-body-secondary">{{ date('F Y') }}</small></p>
              </div>
              <div class="avatar me-lg-6">
                <span class="avatar-initial rounded-3 text-heading">
                  <i class="icon-base ri ri-check-double-line icon-26px"></i>
                </span>
              </div>
            </div>
            <hr class="d-none d-sm-block d-lg-none" />
          </div>
          <div class="col-sm-6 col-lg-4">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h4 class="mb-0" id="total-permintaan-tahun">0</h4>
                <p class="mb-0">Permintaan Barang</p>
                <p class="mb-0"><small class="text-body-secondary">{{ date('Y') }}</small></p>
              </div>
              <div class="avatar me-sm-6">
                <span class="avatar-initial rounded-3 text-heading">
                  <i class="icon-base ri ri-wallet-3-line icon-26px"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Users List Table -->
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="card-title mb-0">
        <h5 class="m-0 me-2">{{ $title }}</h5>
      </div>
      <div class="demo-inline-spacing">
        <button type="button" class="btn btn-primary cari-record" data-bs-toggle="modal" data-bs-target="#filterRoleModal">Cari</button>
        @if ($isList)
        <button type="button" class="btn btn-primary view-record">Lihat</button>
        @endif
        @if ($isAdd)
        <button type="button" class="btn btn-primary add-record" data-bs-toggle="modal" data-bs-target="#addRoleModal">Tambah</button>
        @endif
        @if ($isEdit)
        <button type="button" class="btn btn-primary edit-record">Ubah</button>
        @endif
        @if ($isDel)
        <button type="button" class="btn btn-primary delete-record">Hapus</button>
        @endif
        @if ($isAdd)
        <button type="button" class="btn btn-primary cetak-record">Cetak</button>
        @endif
      </div>
    </div>
    <div class="card-datatable text-nowrap">
      <table class="datatables-permintaan table table-bordered table-responsive" data-title="{{ $title }}" data-view="{{ $isList }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
        <thead>
          <tr>
            <th></th>
            <th>No</th>
            <th>Tanggal</th>
            <th>Nomor Permintaan</th>
            <th>Tipe Barang</th>
            <th>Bagian</th>
            <th>No SPK</th>
            <th>No Polisi</th>
            <th>Tipe Kendaraan</th>
            <th>Nama Pemilik</th>
            <th>Nama Asuransi</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>

   <!-- Add SPK Modal -->
   <div class="modal" id="addRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel1">{{ $title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="addNewDataForm" method="post" action="{{ url('permintaan-barang-list') }}" onSubmit="return false">
          @csrf
          <input type="hidden" name="id" id="user_id">
          <input type="hidden" name="kode_cabang" id="kode_cabang" value="{{ $user_cabang }}">
          <input type="hidden" name="kode_bagian2" id="kode_bagian2">

          <div class="row g-2">
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Nomor SPK</label>
                <div class="col-sm-8 form-control-validation">
                  <input type="text" id="add-nomor-spk" name="kode_spk" class="form-control form-control-sm text-primary fw-bold input-readonly" readonly />
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <button type="button" class="btn btn-primary btn-cek-spk mt-1">Cari</button>
            </div>

            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Nomor Polisi</label>
                <div class="col-sm-8 form-control-validation">
                  <input type="text" id="add-nomor-polisi" name="no_polisi" class="form-control form-control-sm text-primary" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Tipe Kendaraan</label>
                <div class="col-sm-8 form-control-validation">
                  <input type="text" id="add-tipe-kendaraan" name="merek_tipe" class="form-control form-control-sm text-primary" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Nama Pemilik</label>
                <div class="col-sm-8">
                  <input type="text" id="add-nama-pemilik" name="nama_pemilik" class="form-control form-control-sm text-primary" disabled />
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Nama Asuransi</label>
                <div class="col-sm-8">
                  <input type="text" id="add-nama-pelanggan" name="nama_pelanggan" class="form-control form-control-sm text-primary" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">No. Polis / No. Tiket</label>
                <div class="col-sm-8">
                  <input type="text" id="add-nomor-polis" name="no_polis" class="form-control form-control-sm text-primary" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">No. Klaim</label>
                <div class="col-sm-8">
                  <input type="text" id="add-nomor-klaim" name="kode_claim" class="form-control form-control-sm text-primary" disabled />
                </div>
              </div>
            </div>

            <hr class="container-m-nx mt-2" />

            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Nomor Permintaan</label>
                <div class="col-sm-8">
                  <input type="text" id="add-nomor-permintaan" name="kode_permintaan" class="form-control form-control-sm text-primary fw-bold" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Tanggal</label>
                <div class="col-sm-8 form-control-validation">
                  <input type="text" id="add-tanggal-permintaan" name="tanggal_permintaan" class="form-control form-control-sm dt-date" value="{{ date('d/m/Y') }}" />
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Tipe Barang</label>
                <div class="col-sm-8 form-control-validation">
                  <select id="add-tipe-barang" name="tipe_barang" class="select2 form-select form-select-sm" data-allow-clear="true">
                    <option value="">Pilih Tipe Barang</option>
                    @foreach($tipe_barang as $row)
                      <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Bagian</label>
                <div class="col-sm-8 form-control-validation">
                  <select id="add-kode-bagian" name="kode_bagian" class="select2 form-select form-select-sm" data-allow-clear="true">
                    <option value="">Pilih Bagian</option>
                    {{-- @foreach($bagian as $row)
                      <option value="{{ $row->kode_posisi }}">{{ $row->posisi_pekerjaan }}</option>
                    @endforeach --}}
                  </select>
                </div>
              </div>
            </div>

          </div>

          <hr class="container-m-nx mt-2" />

          <div class="row mt-2">
            <div class="col-xl-12">
              <div class="nav-align-top">
                <ul class="nav nav-pills mb-4" role="tablist">
                  <li class="nav-item">
                    <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                      data-bs-target="#navs-pills-top-barang" aria-controls="navs-pills-top-barang"
                      aria-selected="true">Detail Barang</button>
                  </li>
                </ul>
                <div class="tab-content">
                  <div class="tab-pane fade show active" id="navs-pills-top-barang" role="tabpanel">
                    <div class="bg-light border p-1" id="addDetBarang">
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
                            <th>Nama Barang</th>
                            <th width="20%">No Sparepart</th>
                            <th width="15%">Satuan</th>
                            <th width="10%">Qty</th>
                            <th width="5%">Tipe</th>
                            {{-- <th class="text-center">Harga</th> --}}
                            {{-- <th width="20"></th> --}}
                            <th width="5%">Aksi</th>
                          </tr>
                        </thead>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row mt-3 g-5">
            <div class="col-12 d-flex justify-content-between">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
              <button type="submit" class="btn btn-primary btn-submit">Simpan</button>
            </div>
          </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!--/ Add SPK Modal -->   

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
                  <label class="col-sm-3 col-form-label" for="filter-nomor-permintaan">Nomor Permintaan</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-nomor-permintaan" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-tanggal-awal">Tanggal</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-tanggal-awal" class="form-control form-control-sm dt-date" value="{{ date('01/01/Y') }}" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-bagian">Bagian</label>
                  <div class="col-sm-9">
                    <select id="filter-kode-bagian" class="select2 form-select" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($bagian as $row)
                        <option value="{{ $row->kode_posisi }}">{{ $row->posisi_pekerjaan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-nama-pemilik">Nama Pemilik</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-nama-pemilik" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-nomor-polisi">Nomor Polisi</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-nomor-polisi" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-nomor-spk">Nomor SPK</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-nomor-spk" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label text-sm-center" for="filter-tanggal-akhir">s/d</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-tanggal-akhir" class="form-control form-control-sm dt-date" value="{{ date('d/m/Y') }}" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-tipe-barang">Tipe Barang</label>
                  <div class="col-sm-9">
                    <select id="filter-tipe-barang" class="select2 form-select" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($tipe_barang as $row)
                        <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-nama-pelanggan">Nama Pelanggan</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-nama-pelanggan" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-tipe-kendaraan">Tipe Kendaraan</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-tipe-kendaraan" class="form-control form-control-sm text-uppercase" />
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

  <!-- Form Data SPK Modal -->
  <div class="modal" id="viewSpkModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="spkModalLabel">Cari SPK</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-nowrap">
            <table class="datatables-spk table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
              <thead>
                <tr>
                  <th></th>
                  <th>No</th>
                  <th>Tgl Input SPK</th>
                  <th>No SPK</th>
                  <th>No. Polisi</th>
                  <th>Tipe Kendaraan</th>
                  <th>Nama Pemilik</th>
                  <th>Nama Pelanggan</th>
                  <th>No. Polis</th>
                  <th>No. Klaim</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
            </table>
          </div>

          <hr class="container-m-nx mt-2" />

          <div class="row mt-3 g-5">
            <div class="col-12 d-flex justify-content-between">
              <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Batal</button>
              <button type="button" class="btn btn-primary btn-pilih-spk">Pilih</button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
  <!--/ Form Data SPK Modal -->

  <!-- Offcanvas to add detail detail -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddDet1" aria-labelledby="offcanvasAddLabelDet1">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddLabelDet1" class="offcanvas-title">Data Detail</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="add-new-user pt-0" id="addNewDataFormDet1">
        <input type="hidden" name="id" id="est_dtl1_id">
        {{-- <input type="hidden" name="kode_bahan2" id="kode_bahan2"> --}}
        
        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <select id="add-bahan" name="kode_bahan" class="select2 form-select is-invalid">
            <option value="">Pilih Nama Barang</option>
            {{-- @foreach($bahan as $row)
            <option value="{{ $row->kode_bahan }}">{{ $row->nama_bahan }}</option>
            @endforeach --}}
          </select>
          <label for="add-bahan">Nama Barang</label>
        </div>
        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <select id="add-satuan" name="kode_satuan" class="select2 form-select is-invalid">
            <option value="">Pilih Satuan</option>
            @foreach($satuan as $row)
            <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
            @endforeach
          </select>
          <label for="add-satuan">Satuan</label>
        </div>
        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <input type="text" class="form-control invoice-price is-invalid" id="add-qty-bahan" name="qty" placeholder="Qty" />
          <label for="add-qty-bahan">Qty</label>
        </div>

        <button type="submit" class="btn btn-primary me-sm-3 me-1">Simpan</button>
        <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="offcanvas">Batal</button>
      </form>
    </div>
  </div>
  
@endsection
