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
'resources/assets/vendor/libs/tagify/tagify.scss'
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
'resources/assets/vendor/libs/tagify/tagify.js'
])
@endsection

<!-- Page Scripts -->
@section('page-script')
  <script src="{{ asset('assets/js/kendaraan.js') }}"></script>
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
      <table class="datatables-kendaraan table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
        <thead>
          <tr>
            <th></th>
            <th>No</th>
            <th class="text-nowrap">No. Polisi</th>
            <th>Nama Pemilik</th>
            <th>Nama STNK</th>
            <th>Nama Merek</th>
            <th>Nama Tipe</th>
            <th>Nama Jenis</th>
            <th>No. Rangka</th>
            <th>No. Mesin</th>
            <th>No. Model</th>
            <th>Tahun</th>
            <th>CC</th>
            <th>Jenis Perseneling</th>
            <th>Warna</th>
            <th>Bahan Bakar</th>
            <th>Tgl STNK Berakhir</th>
            {{-- <th>Aksi</th> --}}
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
                  <label class="col-sm-3 col-form-label" for="filter-no-polisi">Nomor Polisi</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-no-polisi" class="form-control form-control-sm text-uppercase" name="no_polisi" maxlength="15" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-rangka">Nomor Rangka</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-rangka" class="form-control form-control-sm text-uppercase" name="no_rangka" maxlength="50" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-model">Nomor Model/SN/<br>Body</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-model" class="form-control form-control-sm text-uppercase" name="no_model" maxlength="50" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-nama-distnk">Nama STNK</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-nama-distnk" class="form-control form-control-sm text-uppercase" name="nama_distnk" maxlength="50" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-mesin">Nomor Mesin</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-mesin" class="form-control form-control-sm text-uppercase" name="no_mesin" maxlength="50" />
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

  <!-- Add Role Modal -->
  <div class="modal" id="addRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel1">{{ $title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="wizard-property-listing" class="bs-stepper vertical mt-2">
            <div class="bs-stepper-header gap-lg-2 border-end">
              <div class="step" data-target="#personal-details">
                <button type="button" class="step-trigger">
                  <span class="bs-stepper-circle"><i class="icon-base ri ri-check-line"></i></span>
                  <span class="bs-stepper-label">
                    {{-- <span class="bs-stepper-number">01</span> --}}
                    <span class="d-flex flex-column ms-2">
                      <span class="bs-stepper-title">Data Pemilik</span>
                      {{-- <span class="bs-stepper-subtitle">Your Name/Email</span> --}}
                    </span>
                  </span>
                </button>
              </div>
              <div class="line"></div>
              <div class="step" data-target="#property-details">
                <button type="button" class="step-trigger">
                  <span class="bs-stepper-circle"><i class="icon-base ri ri-check-line"></i></span>
                  <span class="bs-stepper-label">
                    {{-- <span class="bs-stepper-number">02</span> --}}
                    <span class="d-flex flex-column ms-2">
                      <span class="bs-stepper-title">Data Kendaraan</span>
                      {{-- <span class="bs-stepper-subtitle">Property Type</span> --}}
                    </span>
                  </span>
                </button>
              </div>
            </div>
            <div class="bs-stepper-content">
              <form id="wizard-property-listing-form" method="post" action="{{ url('kendaraan-list') }}" onSubmit="return false" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="user_id">
                <input type="hidden" name="kode_tipe" id="kode_tipe">
                <input type="hidden" name="kode_cabang" id="kode_cabang" value="{{ $user_cabang }}">
                <input type="hidden" name="old_file_stnk" id="old_file_stnk" value="">

                <!-- Personal Details -->
                <div id="personal-details" class="content">
                  <div class="row g-5">
                    <div class="col-sm-12 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <select id="add-nama-pemilik" name="kode_pemilik" class="select2 form-select is-invalid" data-allow-clear="true">
                          <option value="">Pilih Nama Pemilik</option>
                          @foreach($pemilik as $row)
                            <option value="{{ $row->kode_pemilik }}">{{ $row->nama_pemilik }}</option>
                          @endforeach
                        </select>
                        <label for="add-nama-pemilik">Nama Pemilik</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="add-ktp-pemilik" name="nomor_identitas" class="form-control" disabled />
                        <label for="add-ktp-pemilik">Nomor Identitas</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        {{-- <input type="text" id="add-jenis-pemilik" name="jenis_pemilik" class="form-control" disabled />
                        <label for="add-jenis-pemilik">Jenis Pemilik</label> --}}
                        <select id="add-jenis-pemilik" name="jenis_pemilik" class="select2 form-select" data-allow-clear="true" disabled>
                          <option value="">-</option>
                          @foreach($jenis_pemilik as $row)
                            <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                          @endforeach
                        </select>
                        <label for="add-jenis-pemilik">Jenis Pemilik</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="add-handphone-pemilik" name="handphone" class="form-control" disabled />
                        <label for="add-handphone-pemilik">Telepon Selular</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="add-telepon-pemilik" name="telepon" class="form-control" disabled />
                        <label for="add-telepon-pemilik">Telepon Rumah</label>
                      </div>
                    </div>
                    <div class="col-lg-12 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <textarea id="add-alamat-pemilik" name="alamat_pemilik" class="form-control" style="height: 144px;" disabled></textarea>
                        <label for="add-alamat-pemilik">Alamat</label>
                      </div>
                    </div>
                    <div class="col-12 d-flex justify-content-between mt-6">
                      <button class="btn btn-outline-secondary btn-prev" disabled>
                        <i class="icon-base ri ri-arrow-left-line icon-16px me-sm-1 me-0"></i>
                        <span class="align-middle d-sm-inline-block d-none">Sebelumnya</span>
                      </button>
                      <button class="btn btn-primary btn-next"><span
                          class="align-middle d-sm-inline-block d-none me-sm-1">Berikutnya</span> <i
                          class="icon-base ri ri-arrow-right-line icon-16px"></i></button>
                    </div>
                  </div>
                </div>
        
                <!-- Property Details -->
                <div id="property-details" class="content">
                  <div class="row g-5">
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="add-no-polisi" name="no_polisi" class="form-control text-uppercase is-invalid" maxlength="15" />
                        <label for="add-no-polisi">Nomor Polisi</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="add-nama" name="nama_distnk" class="form-control text-uppercase is-invalid" maxlength="50" />
                        <label for="add-nama">Nama STNK</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <select id="add-merek" name="kode_merek" class="select2 form-select is-invalid" data-allow-clear="true">
                          <option value="">Pilih Merek Kendaraan</option>
                          @foreach($merek_kendaraan as $row)
                            <option value="{{ $row->kode_merek }}">{{ $row->nama_merek }}</option>
                          @endforeach
                        </select>
                        <label for="add-merek">Merek Kendaraan</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <select id="add-jenis-kendaraan" name="jenis_kendaraan" class="select2 form-select is-invalid" data-allow-clear="true">
                          <option value="">Pilih Jenis Kendaraan</option>
                          @foreach($jenis_kendaraan as $row)
                            <option value="{{ $row->kode_jenis }}">{{ $row->nama_jenis }}</option>
                          @endforeach
                        </select>
                        <label for="add-jenis-kendaraan">Jenis Kendaraan</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <select id="add-tipe" name="kode_tipe" class="select2 form-select is-invalid" data-allow-clear="true">
                          <option value="">Pilih Tipe Kendaraan</option>
                        </select>
                        <label for="add-tipe">Tipe Kendaraan</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="add-warna" name="warna" class="form-control text-uppercase" maxlength="50" />
                        <label for="add-warna">Warna</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="add-cc" name="ukuran_cc" class="form-control tahun-mask" maxlength="4" />
                        <label for="add-cc">Ukuran CC</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="add-tahun" name="tahun" class="form-control tahun-mask" maxlength="4" />
                        <label for="add-tahun">Tahun</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <select id="add-perseneling" name="kode_jenis_perseneling" class="select2 form-select is-invalid" data-allow-clear="true">
                          <option value="">Pilih Perseneling</option>
                          @foreach($perseneling as $row)
                            <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                          @endforeach
                        </select>
                        <label for="add-perseneling">Perseneling</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <select id="add-bahan-bakar" name="kode_bahan_bakar" class="select2 form-select is-invalid" data-allow-clear="true">
                          <option value="">Pilih Bahan Bakar</option>
                          @foreach($bahan_bakar as $row)
                            <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                          @endforeach
                        </select>
                        <label for="add-bahan-bakar">Bahan Bakar</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="add-rangka" name="no_rangka" class="form-control text-uppercase is-invalid" maxlength="50" />
                        <label for="add-rangka">Nomor Rangka</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="add-mesin" name="no_mesin" class="form-control text-uppercase is-invalid" maxlength="50" />
                        <label for="add-mesin">Nomor Mesin</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="add-tgl-stnk" name="tgl_stnk_berakhir" class="form-control dt-date" />
                        <label for="add-tgl-stnk">Tanggal STNK Berakhir</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="add-model" name="no_model" class="form-control text-uppercase" maxlength="50" />
                        <label for="add-model">Nomor Model/SN/Body</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="form-floating form-floating-outline">
                        <input type="file" class="form-control form-control-sm" id="add-file-stnk" name="file_stnk" accept=".jpg,.jpeg,.png,.pdf" />
                        <div class="form-text">Format file: jpg|png|pdf</div>
                        <label for="add-tgl-stnk">File STNK</label>
                      </div>
                    </div>
                    <div class="col-sm-6 form-control-validation">
                      <div class="row g-4 mt-3" id="stnk-container"></div>
                    </div>
                    <div class="col-12 d-flex justify-content-between">
                      <button class="btn btn-outline-secondary btn-prev"><i
                          class="icon-base ri ri-arrow-left-line icon-16px me-sm-1 me-0"></i> <span
                          class="align-middle d-sm-inline-block d-none">Sebelumnya</span></button>
                      <button name="btnSubmit" type="submit" class="btn btn-primary btn-submit btn-next"><span
                          class="align-middle d-sm-inline-block d-none">Simpan</span> <i
                          class="icon-base ri ri-check-line icon-16px ms-0 ms-sm-2 rotate-0"></i></button>
                    </div>
                  </div>
                </div>

              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Add Role Modal --> 

@endsection
