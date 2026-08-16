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
  <script src="{{ asset('assets/js/foto-pekerjaan.js') }}"></script>
@endsection

@section('content')
  <!-- Users List Table -->
  <div class="card">
    {{-- <img src="{{ route('photo.render', $foto->id) }}" alt="{{ $foto->nama_panel }}" style="width: 150px; border-radius: 8px;"> --}}

    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="card-title mb-0">
        <h5 class="m-0 me-2">{{ $title }}</h5>
      </div>
      <div class="demo-inline-spacing">
        <button type="button" class="btn btn-primary filter-spk" data-bs-toggle="modal" data-bs-target="#filterRoleModal">Cari</button>
        <button type="button" class="btn btn-primary upload-foto">Upload</button>
        <button type="button" class="btn btn-primary lihat-foto">Lihat</button>
      </div>
    </div>
    <div class="card-datatable text-nowrap">
      <table class="datatables-spk table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
        <thead>
          <tr>
            <th></th>
            <th>No</th>
            <th>Tgl Input SPK</th>
            <th>No SPK</th>
            <th>Keterangan</th>
            <th class="text-nowrap">No. Polisi</th>
            <th>Tipe Kendaraan</th>
            <th>Nama Pemilik</th>
            <th>Nama Asuransi</th>
            <th>Foto</th>
            {{-- <th>Tanggal Batal</th>
            <th>Tanggal Turun Lap.</th>
            <th>Tanggal Selesai</th>
            <th>Tanggal Keluar</th>
            <th>Status SPK</th>
            <th>No. Polis</th>
            <th>No. Klaim</th>
            <th>Aksi</th> --}}
          </tr>
        </thead>
      </table>
    </div>
  </div>

  <!-- Upload Foto Modal -->
  <div class="modal" id="uploadRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel2">Upload Foto Pekerjaan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="uploadNewDataForm" method="post" action="{{ url('foto-pekerjaan-list') }}" onSubmit="return false" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="id" id="spk_id">
          <input type="hidden" name="kode_cabang" id="kode_cabang" value="{{ $user_cabang }}">

          <div class="row g-2">
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. SPK</label>
                <div class="col-sm-5">
                  <input type="text" id="add-nomor-spk" name="kode_spk" class="form-control form-control-sm text-primary fw-bold" disabled />
                </div>
                <div class="col-sm-4">
                  <input type="text" id="add-tanggal-spk" name="tgl_spk" class="form-control form-control-sm text-primary" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Jenis Kendaraan</label>
                <div class="col-sm-9">
                  <input type="text" id="add-jenis-kendaraan" name="jenis_kendaraan" class="form-control form-control-sm text-primary" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Nama Pemilik</label>
                <div class="col-sm-9">
                  <input type="text" id="add-nama-pemilik" name="nama_pemilik" class="form-control form-control-sm text-primary" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Tahun</label>
                <div class="col-sm-9">
                  <input type="text" id="add-tahun" name="tahun" class="form-control form-control-sm text-primary" disabled />
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. Polisi</label>
                <div class="col-sm-9">
                  <input type="text" id="add-nomor-polisi" name="no_polisi" class="form-control form-control-sm text-primary" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Tipe Kendaraan</label>
                <div class="col-sm-9">
                  <input type="text" id="add-tipe-kendaraan" name="tipe_kendaraan" class="form-control form-control-sm text-primary" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Nama Asuransi</label>
                <div class="col-sm-9">
                  <input type="text" id="add-nama-pelanggan" name="nama_pelanggan" class="form-control form-control-sm text-primary" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Warna</label>
                <div class="col-sm-9">
                  <input type="text" id="add-warna" name="warna" class="form-control form-control-sm text-primary" disabled />
                </div>
              </div>
            </div>
          </div>

          <hr class="container-m-nx mt-2" />

          <div class="row g-2 filter-file-photo">
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">File Photo</label>
                <div class="col-sm-9">
                  <input type="file" class="form-control" id="add-file-photo" name="photo[]" placeholder="File Foto" accept=".jpg,.jpeg,.png" required multiple />
                  <div class="form-text">Format file: jpg|png</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">&nbsp;</div>
          </div>

          <div class="row mt-2 filter-photo-container">
            <div class="col-xl-12">
              <div class="card">
                <div class="card-body">
                  <button type="button" class="btn btn-primary d-none" id="btn-download-all">Download Semua</button>
                  <div class="row g-4 mt-3" id="photo-container"></div>
                </div>
              </div>
            </div>
          </div>
          
          {{-- <div class="row g-2">
            <div class="col-md-12">
              <div class="mb-3 form-control-validation">
                <label>Pilih Foto</label>
                <input type="file" name="photo[]" class="form-control" accept="image/jpeg, image/png" required multiple>
              </div>
            </div>
          </div> --}}

          <hr class="container-m-nx mt-2" />

          <div class="row mt-3 g-5">
            <div class="col-12 d-flex justify-content-between">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
              <button type="submit" class="btn btn-primary btn-submit">Upload</button>
            </div>
          </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!--/ Upload Modal -->   

  <!-- Cari Foto Modal -->
  <div class="modal" id="filterRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="filterModalLabel">Cari {{ $title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="formFilterSpk" onsubmit="return false">
            <div class="row g-4">
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-4 col-form-label" for="filter-nomor-spk">Nomor SPK</label>
                  <div class="col-sm-8">
                    <input type="text" id="filter-nomor-spk" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-4 col-form-label" for="filter-tgl-spk-awal">Tanggal SPK</label>
                  <div class="col-sm-8">
                    <div class="input-group">
                      <input type="text" id="filter-tgl-spk-awal" class="form-control form-control-sm dt-date" />
                      <span class="input-group-text">s/d</span>
                      <input type="text" id="filter-tgl-spk-akhir" class="form-control form-control-sm dt-date" />
                    </div>
                    {{-- <input type="text" id="filter-tgl-spk-awal" class="form-control form-control-sm dt-date" /> --}}
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-4 col-form-label" for="filter-nama-pemilik">Nama Pemilik</label>
                  <div class="col-sm-8">
                    <input type="text" id="filter-nama-pemilik" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-4 col-form-label" for="filter-no-polisi">Nomor Polisi</label>
                  <div class="col-sm-8">
                    <input type="text" id="filter-no-polisi" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-4 col-form-label" for="filter-tipe-kendaraan">Tipe Kendaraan</label>
                  <div class="col-sm-8">
                    <input type="text" id="filter-tipe-kendaraan" class="form-control form-control-sm text-uppercase" />
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-4 col-form-label" for="filter-status">Status</label>
                  <div class="col-sm-8">
                    <select id="filter-status" class="select2 form-select" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($status as $row)
                        <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                {{-- <div class="row mb-1 align-items-center">
                  <label class="col-sm-4 col-form-label text-center" for="filter-tgl-spk-akhir">s/d</label>
                  <div class="col-sm-8">
                    <input type="text" id="filter-tgl-spk-akhir" class="form-control form-control-sm dt-date" />
                  </div>
                </div> --}}
              </div>
            </div>
            <hr class="container-m-nx mt-2" />
            <div class="row mt-3 g-5">
              <div class="col-12 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary me-sm-3 me-1">Cari</button>
              </div>
            </div>
            {{-- <div class="row mt-6">
              <div class="col-md-6">
                <div class="row justify-content-end">
                  <div class="col-sm-9">
                    <button type="submit" class="btn btn-primary me-sm-3 me-1">Cari</button>
                    <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="modal">Batal</button>
                  </div>
                </div>
              </div>
            </div> --}}
          </form>
        </div>
      </div>
    </div>
  </div>
  <!--/ Cari Foto Modal -->   
  
@endsection
