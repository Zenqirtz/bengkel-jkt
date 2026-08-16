@extends('layouts/layoutMaster')

@section('title', 'Registrasi User')

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
  <script src="{{ asset('assets/js/user-management.js') }}"></script>
@endsection

@section('content')
  <div class="col-12">
    @if ($errors->any())
      @foreach ($errors->all() as $error)
          <div class="alert alert-danger">
          {{$error}}
          </div>
      @endforeach
    @endif
  </div>
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
        <table class="datatables-users table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
        <thead>
          <tr>
            <th></th>
            <th>No</th>
            <th>Nama User</th>
            <th>Nama Lengkap</th>
            <th>Email</th>
            <th>Group User</th>
            <th>Cabang</th>
            <th>Level</th>
            <th>File Photo</th>
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
                  <label class="col-sm-3 col-form-label" for="filter-username">Nama User</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-username" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-fullname">Nama Lengkap</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-fullname" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-level">User Level</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="filter-level" class="select2 form-select form-select-sm" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($userLevels as $row)
                        <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-status">Status</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="filter-status" class="select2 form-select form-select-sm" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($status as $row)
                        <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-email">Email</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-email" class="form-control form-control-sm" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-grup">Grup User</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="filter-grup" class="select2 form-select form-select-sm" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($groups as $row)
                        <option value="{{ $row->id }}">{{ $row->nama }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-cabang">Cabang</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="filter-cabang" class="select2 form-select form-select-sm" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($cabangs as $row)
                        <option value="{{ $row->id }}">{{ $row->nama_cabang }}</option>
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
          <h5 class="modal-title" id="exampleModalLabel1">{{ $title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="addNewDataForm" method="post" action="{{ url('profile-list') }}" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="id" id="user_id">
          <input type="hidden" id="old_photo" name="old_photo" value="">

          <div class="row g-2">
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-username">Nama User</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-uppercase" id="add-username" name="username" maxlength="32" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-fullname">Nama Lengkap</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-uppercase" id="add-fullname" name="fullname" maxlength="60" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-email">Email</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" id="add-email" class="form-control form-control-sm" name="email" maxlength="60" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-level">User Level</label>
                <div class="col-sm-9 form-control-validation">
                  <select id="add-level" name="user_level" class="select2 form-select form-select-sm" data-allow-clear="true">
                    <option value="">Pilih User Level</option>
                    @foreach($userLevels as $row)
                      <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-file-ktp">File Foto</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="file" class="form-control form-control-sm" id="add-file-photo" name="photo" placeholder="File Foto" accept=".jpg,.jpeg,.png" />
                  <div class="form-text">Format file: jpg|png</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-password">Kata Sandi</label>
                <div class="col-sm-9 form-control-validation">
                  <div class="form-password-toggle input-group input-group-merge">
                    <input type="password" id="add-password" class="form-control form-control-sm" name="password" />
                    <span class="input-group-text cursor-pointer"><i class="icon-base ri ri-eye-off-line icon-20px"></i></span>
                  </div>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-grup">Grup User</label>
                <div class="col-sm-9 form-control-validation">
                  <select id="add-grup" name="user_group" class="select2 form-select form-select-sm" data-allow-clear="true">
                    <option value="">Pilih Grup User</option>
                    @foreach($groups as $row)
                      <option value="{{ $row->id }}">{{ $row->nama }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-cabang">Cabang</label>
                <div class="col-sm-9 form-control-validation">
                  <select id="add-cabang" name="user_cabang" class="select2 form-select form-select-sm" data-allow-clear="true">
                    <option value="">Pilih Cabang</option>
                    @foreach($cabangs as $row)
                      <option value="{{ $row->id }}">{{ $row->nama_cabang }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-status">Status</label>
                <div class="col-sm-9 form-control-validation">
                  <select id="add-status" name="status" class="select2 form-select form-select-sm" data-allow-clear="true">
                    <option value="">Pilih Status</option>
                    @foreach($status as $row)
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
