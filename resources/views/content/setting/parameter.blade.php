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
    'resources/assets/vendor/libs/notiflix/notiflix.js'
  ])
@endsection

<!-- Page Scripts -->
@section('page-script')
  <script src="{{ asset('assets/js/parameter.js') }}"></script>
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
      </div>
    </div>
    <div class="card-datatable text-nowrap">
      <table class="datatables-parameter table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
        <thead>
          <tr>
            <th></th>
            <th>No</th>
            <th>Kode</th>
            <th>Keterangan</th>
            <th>Nama Tabel</th>
            <th>Nomor Urut</th>
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
                  <label class="col-sm-3 col-form-label" for="filter-kode">Kode</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-kode" class="form-control form-control-sm text-uppercase" maxlength="16" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-keterangan">Keterangan</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-keterangan" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-nama-tabel">Nama Tabel</label>
                  <div class="col-sm-9">
                    <select id="filter-nama-tabel" class="select2 form-select form-select-sm" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($nama_tabel as $row)
                        <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
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
          <form class="add-new-user pt-0" id="addNewDataForm" method="post" action="{{ url('parameter-list') }}">
            @csrf
            <input type="hidden" name="id" id="user_id">
            <div class="row g-2">
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="add-kode">Kode</label>
                  <div class="col-sm-9 form-control-validation">
                    <input type="text" id="add-kode" class="form-control form-control-sm text-uppercase" name="kode" maxlength="16" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="add-keterangan">Keterangan</label>
                  <div class="col-sm-9 form-control-validation">
                    <input type="text" id="add-keterangan" class="form-control form-control-sm text-uppercase" name="keterangan" maxlength="255" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-4 col-form-label" for="add-nama-tabel">Nama Tabel</label>
                  <div class="col-sm-8 form-control-validation">
                    <select id="add-nama-tabel" name="nama_tabel" class="select2 form-select form-select-sm" data-allow-clear="true">
                      <option value="">Pilih Nama Tabel</option>
                      @foreach($nama_tabel as $row)
                        <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                      @endforeach
                      <option value="other">LAINNYA</option>
                    </select>
                  </div>
                </div>
                <div class="row mb-1 align-items-center" id="TableNameOther">
                  <label class="col-sm-4 col-form-label" for="add-nama-tabel-lain">Nama Tabel Lainnya</label>
                  <div class="col-sm-8 form-control-validation">
                    <input type="text" id="add-nama-tabel-lain" class="form-control form-control-sm text-uppercase" name="nama_tabel_lain" maxlength="32" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-4 col-form-label" for="add-nomor-urut">Nomor Urut</label>
                  <div class="col-sm-8 form-control-validation">
                    <input type="text" id="add-nomor-urut" class="form-control form-control-sm text-number" name="no_urut" maxlength="3" />
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

@endsection
