@extends('layouts/layoutMaster')

@section('title', $title)

<!-- Vendor Styles -->
@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
  @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss', 'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleave-zen/cleave-zen.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
  @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js', 'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js', 'resources/assets/vendor/libs/pickr/pickr.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
  <script src="{{ asset('assets/js/pemilik.js') }}"></script>
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
      <table class="datatables-pemilik table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
        <thead>
          <tr>
            <th></th>
            <th>No</th>
            <th>Nama Pemilik</th>
            <th>Jenis</th>
            <th>Alamat</th>
            <th>Kode Pos</th>
            <th>Telepon Kantor</th>
            <th>Telepon Selular</th>
            <th>NPWP</th>
            <th>KTP/SIM</th>
            <th>File NPWP</th>
            <th>File KTP/SIM</th>
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
                  <label class="col-sm-3 col-form-label" for="filter-nama-pemilik">Nama Pemilik</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-nama-pemilik" class="form-control form-control-sm" name="nama_pemilik" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-alamat">Alamat</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-alamat" class="form-control form-control-sm" name="alamat" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-handphone">Telepon Selular</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-handphone" class="form-control form-control-sm text-number" name="handphone" maxlength="12" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-ktp">Nomor KTP</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-ktp" class="form-control form-control-sm text-number" name="no_identitas" maxlength="16" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-jenis-pemilik">Jenis Pemilik</label>
                  <div class="col-sm-9">
                    <select id="filter-jenis-pemilik" class="select2 form-select form-select-sm" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($jenis_pemilik as $row)
                        <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-kodepos">Kode Pos</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-kodepos" class="form-control form-control-sm text-number" name="kode_pos" maxlength="5" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-nama-pemilik">Telepon Kantor</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-telepon" class="form-control form-control-sm text-number" name="telepon" maxlength="12" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-npwp">Nomor NPWP</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-npwp" class="form-control form-control-sm text-number" name="npwp" maxlength="16" />
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
          <form id="addNewDataForm" method="post" action="{{ url('pemilik-list') }}" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="id" id="user_id">
          <input type="hidden" name="kode_cabang" id="kode_cabang" value="{{ $user_cabang }}">
          <input type="hidden" id="old_file_ktp" name="old_file_ktp" value="">
          <input type="hidden" id="old_file_npwp" name="old_file_npwp" value="">

          <div class="row g-2">
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-nama">Nama Pemilik</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-uppercase is-invalid" id="add-nama" name="nama_pemilik" maxlength="50" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-ktp">Nomor KTP/SIM</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-number is-invalid" id="add-ktp" name="no_identitas" maxlength="16" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-file-ktp">File KTP/SIM</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="file" class="form-control form-control-sm is-invalid" id="add-file-ktp" name="file_ktp" placeholder="File KTP" accept=".pdf,.jpg,.jpeg,.png" />
                  <div class="form-text">Format file: jpg|png|pdf</div>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-handphone">Telepon Selular</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-number is-invalid" id="add-handphone" name="handphone" maxlength="13" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-fax">Fax</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-number" id="add-fax" name="fax" maxlength="13" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-alamat1">Alamat</label>
                <div class="col-sm-9 form-control-validation">
                  <textarea class="form-control text-uppercase h-px-100 is-invalid" id="add-alamat1" name="alamat1"></textarea>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-jenis">Jenis Pemilik</label>
                <div class="col-sm-9 form-control-validation">
                  <select id="add-jenis" name="kode_jenis_pemilik" class="select2 form-select form-select-sm is-invalid">
                    <option value="">Pilih Jenis Pemilik</option>
                    @foreach($jenis_pemilik as $row)
                      <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                    @endforeach
                </select>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-npwp">Nomor NPWP</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-number is-invalid" id="add-npwp" name="npwp" maxlength="20" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-file-npwp">File NPWP</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="file" class="form-control form-control-sm is-invalid" id="add-file-npwp" name="file_npwp" accept=".pdf,.jpg,.jpeg,.png" />
                  <div class="form-text">Format file: jpg|png|pdf</div>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-telepon">Telepon Kantor</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-number" id="add-telepon" name="telepon" maxlength="13" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-email">Email</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm" id="add-email" name="email" maxlength="50" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label" for="add-kodepos">Kode Pos</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" class="form-control form-control-sm text-number" id="add-kodepos" name="kode_pos" maxlength="5" />
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
