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
  <script src="{{ asset('assets/js/profile-perusahaan.js') }}"></script>
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
      <table class="datatables-profile table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
        <thead>
          <tr>
            <th></th>
            <th>No</th>
            <th>Perusahaan</th>
            <th>Alamat</th>
            <th>Kode Pos</th>
            <th>NPWP</th>
            <th>Telepon</th>
            <th>Fax</th>
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
                  <label class="col-sm-3 col-form-label" for="filter-nama-cabang">Nama Perusahaan</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-nama-cabang" class="form-control form-control-sm" name="nama_cabang" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-telepon">Nomor Telepon</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-telepon" class="form-control form-control-sm text-number" name="telepon" maxlength="13" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-npwp">Nomor NPWP</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-npwp" class="form-control form-control-sm text-number" name="npwp" maxlength="16" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-alamat">Alamat</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-alamat" class="form-control form-control-sm" name="alamat" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-fax">Nomor Fax</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-fax" class="form-control form-control-sm text-number" name="fax" maxlength="13" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-status-aktif">Status Aktif</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="filter-status-aktif" name="is_active" class="select2 form-select form-select-sm" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($status_aktif as $row)
                        <option value="{{ $row->kode }}" @selected($row->kode == 'Y')>{{ $row->keterangan }}</option>
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
                <label class="col-sm-3 col-form-label" for="add-nama-cabang">Nama Perusahaan</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-uppercase is-invalid" id="add-nama-cabang" name="nama_cabang" maxlength="60" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-npwp">Nomor NPWP</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-number is-invalid" id="add-npwp" name="npwp" maxlength="16" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-telepon">Nomor Telepon</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-number" id="add-telepon" name="telepon" maxlength="13" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-alamat1">Alamat</label>
                <div class="col-sm-9 form-control-validation">
                  <textarea class="form-control text-uppercase h-px-100 is-invalid" id="add-alamat1" name="alamat1"></textarea>
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
                <label class="col-sm-3 col-form-label" for="add-nama-singkat">Nama Singkat</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-uppercase is-invalid" id="add-nama-singkat" name="nama_singkat" maxlength="4" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-email">Email</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm" id="add-email" name="email" maxlength="50" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-fax">Nomor Fax</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-number" id="add-fax" name="fax" maxlength="13" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-kodepos">Kode Pos</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-number" id="add-kodepos" name="kode_pos" maxlength="5" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-status-aktif">Status Aktif</label>
                <div class="col-sm-9 form-control-validation">
                  <select id="add-status-aktif" name="is_active" class="select2 form-select form-select-sm" data-allow-clear="true">
                    <option value="">Pilih Status Aktif</option>
                    @foreach($status_aktif as $row)
                      <option value="{{ $row->kode }}" >{{ $row->keterangan }}</option>
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
