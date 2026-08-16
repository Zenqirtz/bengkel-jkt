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
  <script src="{{ asset('assets/js/karyawan.js') }}"></script>
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
      <table class="datatables-karyawan table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
        <thead>
          <tr>
            <th></th>
            <th>No</th>
            <th>Kode</th>
            <th>Nama Karyawan</th>
            <th>Jabatan</th>
            <th>Posisi Pekerjaan</th>
            <th>NIK</th>
            <th>Telepon</th>
            <th>Alamat</th>
            <th>Status Pajak</th>
            <th>Jenis Karyawan</th>
            <th>Tanggal Masuk</th>
            <th>Tanggal Keluar</th>
            <th>File Foto</th>
            <th>File KTP</th>
            <th>File TTD</th>
            <th>Status Aktif</th>
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
                  <label class="col-sm-3 col-form-label" for="filter-nik">NIK</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-nik" class="form-control form-control-sm text-number" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-nama">Nama Karyawan</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-nama" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-posisi">Posisi Pekerjaan</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="filter-posisi" class="select2 form-select form-select-sm" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($data_posisi as $row)
                        <option value="{{ $row->kode_posisi }}">{{ $row->posisi_pekerjaan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-jabatan">Jabatan</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="filter-jabatan" class="select2 form-select form-select-sm" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($jabatan as $row)
                        <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-pajak">Status Pajak</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="filter-pajak" class="select2 form-select form-select-sm" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($status_pajak as $row)
                        <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-telepon">Nomor Telepon</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-telepon" class="form-control form-control-sm text-number" maxlength="13" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-alamat">Alamat</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-alamat" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-status-karyawan">Jenis Karyawan</label>
                  <div class="col-sm-9 form-control-validation">
                    <select id="filter-status-karyawan" class="select2 form-select form-select-sm" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($status_karyawan as $row)
                        <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
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
          <form id="addNewDataForm" method="post" action="{{ url('karyawan-list') }}" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="id" id="user_id">
          <input type="hidden" name="kode_cabang" id="kode_cabang" value="{{ $user_cabang }}">
          <input type="hidden" id="old_photo" name="old_photo" value="">
          <input type="hidden" id="old_photo_ktp" name="old_photo_ktp" value="">

          <div class="row g-2">
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-kode-karyawan">Kode Karyawan</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-primary fw-bold" id="add-kode-karyawan" name="kode_karyawan" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-nama">Nama Karyawan</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-uppercase" id="add-nama" name="nama" maxlength="60" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-pajak">Status Pajak</label>
                <div class="col-sm-9 form-control-validation">
                  <select id="add-pajak" name="status_pajak" class="select2 form-select form-select-sm" data-allow-clear="true">
                    <option value="">Pilih Status Pajak</option>
                    @foreach($status_pajak as $row)
                      <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-status-karyawan">Jenis Karyawan</label>
                <div class="col-sm-9 form-control-validation">
                  <select id="add-status-karyawan" name="status_karyawan" class="select2 form-select form-select-sm" data-allow-clear="true">
                    <option value="">Pilih Jenis Karyawan</option>
                    @foreach($status_karyawan as $row)
                      <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-tgl-masuk">Tanggal Masuk</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm dt-date" id="add-tgl-masuk" name="tgl_masuk" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-absen">No Absen</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-number" id="add-absen" name="no_absen" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-phone">Nomor Telepon</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-number" id="add-phone" name="no_hp" maxlength="13" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-alamat">Alamat</label>
                <div class="col-sm-9 form-control-validation">
                  <textarea class="form-control text-uppercase h-px-100 is-invalid" id="add-alamat" name="alamat"></textarea>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-nik">NIK</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-number" id="add-nik" name="nik" maxlength="16" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="filter-jabatan">Jabatan</label>
                <div class="col-sm-9 form-control-validation">
                  <select id="add-jabatan" name="kode_jabatan" class="select2 form-select form-select-sm" data-allow-clear="true">
                    <option value="">Pilih Jabatan</option>
                    @foreach($jabatan as $row)
                      <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-posisi">Posisi Pekerjaan</label>
                <div class="col-sm-9 form-control-validation">
                  <select id="add-posisi" name="kode_posisi" class="select2 form-select form-select-sm" data-allow-clear="true">
                    <option value="">Pilih Posisi Pekerjaan</option>
                    @foreach($data_posisi as $row)
                      <option value="{{ $row->kode_posisi }}">{{ $row->posisi_pekerjaan }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-status-aktif">Status Karyawan</label>
                <div class="col-sm-9 form-control-validation">
                  <select id="add-status-aktif" name="status_aktif" class="select2 form-select form-select-sm" data-allow-clear="true">
                    <option value="">Pilih Status Aktif</option>
                    @foreach($status_aktif as $row)
                      <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-tgl-keluar">Tanggal Keluar</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm dt-date" id="add-tgl-keluar" name="tgl_keluar" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-file-ktp">File Foto</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="file" class="form-control form-control-sm" id="add-file-photo" name="photo" placeholder="File Foto" accept=".jpg,.jpeg,.png" />
                  <div class="form-text">Format file: jpg|png</div>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-file-ktp">File KTP</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="file" class="form-control form-control-sm" id="add-file-ktp" name="photo_ktp" placeholder="File KTP" accept=".pdf,.jpg,.jpeg,.png" />
                  <div class="form-text">Format file: jpg|png|pdf</div>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-file-ttd">File TTD</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="file" class="form-control form-control-sm" id="add-file-ttd" name="photo_ttd" placeholder="File TTD" accept=".jpg,.jpeg,.png" />
                  <div class="form-text">Format file: jpg|png</div>
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

  <!-- View Foto Modal -->
  <div class="modal" id="viewFotoModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Lihat Foto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-2">
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Kode Karyawan</label>
                <div class="col-sm-8">
                  <input type="text" id="view-kode-karyawan" class="form-control form-control-sm text-primary" disabled />
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Nama Karyawan</label>
                <div class="col-sm-8">
                  <input type="text" id="view-nama-karyawan" class="form-control form-control-sm text-primary" disabled />
                </div>
              </div>
            </div>
          </div>

          <hr class="container-m-nx mt-2" />

          <div class="row mt-2">
            <div class="col-xl-12">
              <div class="card">
                <div class="card-body">
                  <div class="row g-4 mt-3" id="photo-container"></div>
                </div>
              </div>
            </div>
          </div>
          
          <hr class="container-m-nx mt-2" />

          <div class="row mt-3 g-5">
            <div class="col-12 d-flex justify-content-between">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ View Foto Modal -->

@endsection
