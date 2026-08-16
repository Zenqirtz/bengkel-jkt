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
  <script src="{{ asset('assets/js/menu.js') }}"></script>
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
      <table class="datatables-menu table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
        <thead>
          <tr>
            <th></th>
            <th>No</th>
            <th>Nama Menu</th>
            <th>Sub Menu</th>
            <th>URL</th>
            <th>Urutan</th>
            <th>Status</th>
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
                  <label class="col-sm-3 col-form-label" for="filter-sub-menu">Sub Menu</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="filter-sub-menu" class="select2 form-select form-select-sm" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      <option value="top">Menu Utama</option>
                      @foreach($data_menu as $row)
                        <option value="{{ $row->id }}">{{ $row->custom_title }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-status-aktif">Status Aktif</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="filter-status-aktif" class="select2 form-select form-select-sm" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($status_aktif as $row)
                        <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-menu">Nama Menu</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-menu" class="form-control form-control-sm" />
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
          <h5 class="modal-title" id="exampleModalLabel1">{{ $title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="addNewDataForm" method="post" action="{{ url('menu-list') }}">
          @csrf
          <input type="hidden" name="id" id="user_id">

          <div class="row g-2">
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-sub-menu">Sub Menu</label>
                <div class="col-sm-9 form-control-validation">
                  <select id="add-sub-menu" name="parent_id" class="select2 form-select form-select-sm" data-allow-clear="true">
                    <option value="">Pilih Sub Menu</option>
                    <option value="top">Menu Utama</option>
                    @foreach($data_menu as $row)
                      <option value="{{ $row->id }}">{{ $row->custom_title }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-menu">Nama Menu</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm" id="add-menu" name="custom_title" maxlength="64" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-url">URL</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm" id="add-url" name="url_menu" maxlength="128" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-status-aktif">Status Aktif</label>
                <div class="col-sm-9 form-control-validation">
                  <select id="add-status-aktif" name="active" class="select2 form-select form-select-sm" data-allow-clear="true">
                    <option value="">Pilih Status Aktif</option>
                    @foreach($status_aktif as $row)
                      <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-slug">Slug</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm" id="add-slug" name="slug" maxlength="128" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-path-icon">Path Icon</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm" id="add-path-icon" name="path_icon" maxlength="128" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-posisi">Posisi</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-number" id="add-posisi" name="tid" />
                </div>
              </div>
              
            </div>
          </div>

          <hr class="container-m-nx mt-2" />

          <div class="row mt-3 g-5">
            <div class="col-12 d-flex justify-content-between">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary btn-submit">Simpan</button>
            </div>
          </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!--/ Form Tambah Data Modal --> 

@endsection
