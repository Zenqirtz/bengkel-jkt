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
    'resources/assets/vendor/libs/pickr/pickr-themes.scss'
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
    'resources/assets/vendor/libs/pickr/pickr.js'
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

  <script src="{{ asset('assets/js/pemakaian-bahan.js') }}"></script>
@endsection

@section('content')
  <!-- Data Sparepart List Table -->
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="card-title mb-0">
        <h5 class="m-0 me-2">{{ $title }}</h5>
      </div>
      <div class="demo-inline-spacing">
        <button type="button" class="btn btn-primary cari-data" data-bs-toggle="modal" data-bs-target="#filterRoleModal">Cari</button>
        @if ($isList)
        <button type="button" class="btn btn-primary detail-record">Lihat</button>
        @endif
        @if ($isAdd)
        <button type="button" class="btn btn-primary add-new" data-bs-toggle="modal" data-bs-target="#addRoleModal">Tambah</button>
        @endif
        @if ($isEdit)
        <button type="button" class="btn btn-primary edit-record">Ubah</button>
        @endif
        @if ($isDel)
        <button type="button" class="btn btn-primary delete-record">Hapus</button>
        @endif
      </div>
    </div>
    <div class="card-datatable text-nowrap">
      <table class="datatables-pemakaian-bahan table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
        <thead>
          <tr>
            <th></th>
            <th>No</th>
            <th>Pekerjaan</th>
            <th>Nama Bahan</th>
            <th>Point Panel</th>
            <th>Qty</th>
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
                  <label class="col-sm-3 col-form-label" for="filter-pekerjaan">Pekerjaan</label>
                  <div class="col-sm-9">
                    <select id="filter-pekerjaan" class="select2 form-select" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($pekerjaan as $row)
                        <option value="{{ $row->kode_posisi }}">{{ $row->posisi_pekerjaan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-bahan">Nama Bahan</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-bahan" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-point-panel">Point Panel</label>
                  <div class="col-sm-9">
                    <select id="filter-point-panel" class="select2 form-select" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($point_panel as $row)
                        <option value="{{ $row }}">{{ $row }}</option>
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
          <form class="add-new-user pt-0" id="addNewDataForm" method="post" action="{{ url('pemakaian-bahan-list') }}">
            @csrf
            <input type="hidden" name="id" id="user_id">
            <input type="hidden" name="kode_bahan2" id="kode_bahan2">
            <input type="hidden" name="kode_cabang" id="kode_cabang" value="{{ $user_cabang }}">
            <div class="row g-2">
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="add-pekerjaan">Pekerjaan</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="add-pekerjaan" name="kode_posisi" class="select2 form-select">
                      <option value="">Pilih Pekerjaan</option>
                      @foreach($pekerjaan as $row)
                        <option value="{{ $row->kode_posisi }}">{{ $row->posisi_pekerjaan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="add-bahan">Nama Bahan</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="add-bahan" name="kode_bahan" class="select2 form-select">
                      <option value="">Pilih Nama Bahan</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="add-point-panel">Point Panel</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="add-point-panel" name="point_panel" class="select2 form-select is-invalid">
                      <option value="">Pilih Point Panel</option>
                      @foreach($point_panel as $row)
                        <option value="{{ $row }}">{{ $row }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="add-qty">Qty</label>
                  <div class="col-sm-9 form-control-validation">
                    <input type="text" id="add-qty" class="form-control form-control-sm text-decimal" name="qty" />
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


  <!-- Modal Detail Pemakaian Bahan -->
<div class="modal" id="viewRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail {{ $title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        {{-- Info Header --}}
        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <div class="row mb-1 align-items-center">
              <label class="col-sm-3 col-form-label">Pekerjaan</label>
              <div class="col-sm-9">
                <input type="text" id="view-pekerjaan" class="form-control form-control-sm input-readonly" readonly />
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="row mb-1 align-items-center">
              <label class="col-sm-3 col-form-label">Point Panel</label>
              <div class="col-sm-9">
                <input type="text" id="view-point-panel" class="form-control form-control-sm input-readonly" readonly />
              </div>
            </div>
          </div>
        </div>

        <hr class="mt-1 mb-3" />

        {{-- Tabel Detail --}}
        <div class="table-responsive">
         <table class="datatables-pemakaian-detail table table-bordered">
            <thead>
              <tr>
                <th style="width:5%">No</th>
                <th>Nama Bahan</th>
                <th style="width:15%; text-align:right">QTY</th>
              </tr>
            </thead>
            <tbody id="detail-tbody">
            </tbody>
          </table>
        </div>

        <div class="row mt-3">
          <div class="col-12 d-flex justify-content-end">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
<!--/ Modal Detail Pemakaian Bahan -->

@endsection
