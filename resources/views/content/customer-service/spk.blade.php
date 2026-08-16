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
  <script src="{{ asset('assets/js/spk.js') }}"></script>
@endsection

@section('content')
  <!-- Users List Table -->
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="card-title mb-0">
        <h5 class="m-0 me-2">{{ $title }}</h5>
      </div>
      <div class="demo-inline-spacing">
        <button type="button" class="btn btn-primary filter-spk" data-bs-toggle="modal" data-bs-target="#filterRoleModal">Cari SPK</button>
        <button type="button" class="btn btn-primary add-new" data-bs-toggle="modal" data-bs-target="#addRoleModal">SPK Baru</button>
        <button type="button" class="btn btn-primary edit-selected-spk">Ubah SPK</button>
      </div>
      {{-- <div class="btn-group">
        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown"
          aria-expanded="false">Detail</button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item filter-spk" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#filterRoleModal">Cari SPK</a></li>
          <li><a class="dropdown-item add-new" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#addRoleModal">SPK Baru</a></li>
          <li><a class="dropdown-item edit-selected-spk" href="javascript:void(0);">Ubah SPK</a></li>
          <li>
            <hr class="dropdown-divider" />
          </li>
          <li><a class="dropdown-item edit-selected-inv-or" href="javascript:void(0);">Invoice OR</a></li>
          <li><a class="dropdown-item edit-selected-spk-tutup" href="javascript:void(0);">SPK Tutup</a></li>
          <li><a class="dropdown-item edit-selected-spk-keluar" href="javascript:void(0);">Kendaraan Keluar</a></li>
          <li><a class="dropdown-item edit-selected-spk-batal" href="javascript:void(0);">SPK Batal</a></li>
        </ul>
      </div> --}}
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
            <th>Nama Pelanggan</th>
            <th>Tanggal Batal</th>
            <th>Tanggal Turun Lap.</th>
            <th>Tanggal Selesai</th>
            <th>Tanggal Keluar</th>
            <th>Status SPK</th>
            <th>No. Polis</th>
            <th>No. Klaim</th>
            <th>Aksi</th>
          </tr>
        </thead>
      </table>
    </div>
      <!-- Add SPK BARU Modal -->
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
                          <span class="bs-stepper-title">Data Kendaraan</span>
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
                          <span class="bs-stepper-title">Data SPK</span>
                          {{-- <span class="bs-stepper-subtitle">Property Type</span> --}}
                        </span>
                      </span>
                    </button>
                  </div>
                </div>
                <div class="bs-stepper-content">
                  <form id="wizard-property-listing-form" method="post" action="{{ url('spk-list') }}" onSubmit="return false">
                    @csrf
                    <input type="hidden" name="id" id="user_id">
                    <input type="hidden" name="kode_pelanggan2" id="kode_pelanggan">
                    <!-- Personal Details -->
                    <div id="personal-details" class="content">
                      <div class="row g-5">
                        <div class="col-sm-12 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <select id="add-nopolisi" name="no_polisi" class="select2 form-select is-invalid" data-allow-clear="true">
                              <option value="">Pilih Nomor Polisi</option>
                              @foreach($kendaraan as $row)
                                <option value="{{ $row->no_polisi }}">{{ $row->no_polisi }}</option>
                              @endforeach
                            </select>
                            <label for="add-nopolisi">Nomor Polisi</label>
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
                            <input type="text" id="add-nama-pemilik" name="nama_pemilik" class="form-control" disabled />
                            <label for="add-nama-pemilik">Nama Pemilik</label>
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
                        <div class="col-sm-6 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <input type="text" id="add-merek-kendaraan" name="merek_kendaraan" class="form-control" disabled />
                            <label for="add-merek-kendaraan">Merek Kendaraan</label>
                          </div>
                        </div>
                        <div class="col-sm-6 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <input type="text" id="add-jenis-kendaraan" name="jenis_kendaraan" class="form-control" disabled />
                            <label for="add-jenis-kendaraan">Jenis Kendaraan</label>
                          </div>
                        </div>
                        <div class="col-sm-6 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <input type="text" id="add-tipe-kendaraan" name="tipe_kendaraan" class="form-control" disabled />
                            <label for="add-tipe-kendaraan">Tipe Kendaraan</label>
                          </div>
                        </div>
                        <div class="col-sm-6 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <input type="text" id="add-warna" name="warna" class="form-control" disabled/>
                            <label for="add-warna">Warna</label>
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
                            <input type="text" id="add-nomor-spk" name="kode_spk" class="form-control" value="{{ $nomorspk }}" disabled />
                            <label for="add-nomor-spk">Nomor SPK</label>
                          </div>
                        </div>
                        <div class="col-sm-6 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <input type="text" id="add-tgl-transaksi" name="tgl_masuk" class="form-control" value="{{ $tglspk }}" disabled />
                            <label for="add-tgl-transaksi">Tanggal SPK</label>
                          </div>
                        </div>
                        <div class="col-sm-6 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <select id="add-status-spk" name="kode_status_spk" class="select2 form-select is-invalid" data-allow-clear="true">
                              <option value="">Pilih Status SPK</option>
                              @foreach($status_spk as $row)
                                <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                              @endforeach
                            </select>
                            <label for="add-status-spk">Status SPK</label>
                          </div>
                        </div>
                        <div class="col-sm-6 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <select id="add-jenis-asuransi" name="kode_jenis_pelanggan" class="select2 form-select is-invalid" data-allow-clear="true">
                              <option value="">Pilih Jenis Asuransi</option>
                              @foreach($jenis_asuransi as $row)
                                <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                              @endforeach
                            </select>
                            <label for="add-jenis-asuransi">Jenis Asuransi</label>
                          </div>
                        </div>
                        <div class="col-sm-6 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <select id="add-pelanggan" name="kode_pelanggan" class="select2 form-select" data-allow-clear="true">
                              <option value="">Pilih Nama Asuransi</option>
                              {{-- @foreach($asuransi as $row)
                                <option value="{{ $row->kode_pelanggan }}">{{ $row->nama_pelanggan }}</option>
                              @endforeach --}}
                            </select>
                            <label for="add-pelanggan">Nama Asuransi</label>
                          </div>
                        </div>
                        <div class="col-sm-6 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <select id="add-perantara" name="kode_perantara" class="select2 form-select" data-allow-clear="true">
                              <option value="">Pilih Nama Perantara</option>
                              @foreach($perantara as $row)
                                <option value="{{ $row->kode_perantara }}">{{ $row->nama_perantara }}</option>
                              @endforeach
                            </select>
                            <label for="add-perantara">Nama Perantara</label>
                          </div>
                        </div>
                        <div class="col-sm-6 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <select id="add-marketing" name="kode_marketing" class="select2 form-select" data-allow-clear="true">
                              <option value="">Pilih Marketing</option>
                              @foreach($marketing as $row)
                                <option value="{{ $row->kode_marketing }}">{{ $row->nama_marketing }}</option>
                              @endforeach
                            </select>
                            <label for="add-marketing">Marketing</label>
                          </div>
                        </div>
                        <div class="col-sm-6 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <select id="add-jenis-polis" name="kode_jenis_polis" class="select2 form-select" data-allow-clear="true">
                              <option value="">Pilih Jenis Polis</option>
                              @foreach($jenis_polis as $row)
                                <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                              @endforeach
                            </select>
                            <label for="add-jenis-polis">Jenis Polis</label>
                          </div>
                        </div>
                        <div class="col-sm-6 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <input type="text" id="add-no-polis" name="no_polis" class="form-control" maxlength="30" />
                            <label for="add-no-polis">Nomor Polis / Nomor Tiket</label>
                          </div>
                        </div>
                        <div class="col-sm-6 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <input type="text" id="add-no-klaim" name="kode_claim" class="form-control" maxlength="30" />
                            <label for="add-no-klaim">Nomor Klaim Asuransi</label>
                          </div>
                        </div>
                        <div class="col-sm-6 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <input type="text" id="add-tgl-polis" name="tgl_polis" class="form-control dt-date-range" />
                            <label for="add-tgl-polis">Polis Berlaku</label>
                          </div>
                        </div>
                        <div class="col-sm-6 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <input type="text" id="add-tertanggung" name="tertanggung" class="form-control" maxlength="50" />
                            <label for="add-tertanggung">Tertanggung</label>
                          </div>
                        </div>
                        <div class="col-lg-12 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <textarea id="add-catatan-khusus" name="catatan_khusus" class="form-control" style="height: 144px;"></textarea>
                            <label for="add-catatan-khusus">Jenis Perbaikan Atas Permintaan Pemilik</label>
                          </div>
                        </div>
                        <div class="col-lg-12 form-control-validation">
                          <div class="form-floating form-floating-outline">
                            <textarea id="add-jenis-perbaikan" name="jenis_perbaikan" class="form-control" style="height: 144px;"></textarea>
                            <label for="add-jenis-perbaikan">Jenis Perbaikan Sesuai SPK Asuransi</label>
                          </div>
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
            {{-- <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary">Save changes</button>
            </div> --}}
          </div>
        </div>
      </div>
      <!--/ Add SPK BARU Modal -->

      <!-- Add CARI SPK Modal -->
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
                    <div class="row">
                      <label class="col-sm-3 col-form-label text-sm-end" for="filter-nomor-spk">Nomor SPK</label>
                      <div class="col-sm-9">
                        <input type="text" id="filter-nomor-spk" class="form-control" name="kode_spk" />
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="row">
                      <label class="col-sm-3 col-form-label text-sm-end" for="filter-no-polisi">Nomor Polisi</label>
                      <div class="col-sm-9">
                        <input type="text" id="filter-no-polisi" class="form-control" name="no_polisi" />
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="row">
                      <label class="col-sm-3 col-form-label text-sm-end" for="filter-tgl-spk-awal">Tanggal SPK</label>
                      <div class="col-sm-9">
                        <input type="text" id="filter-tgl-spk-awal" class="form-control dt-date" name="tgl_masuk_awal" />
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="row">
                      <label class="col-sm-3 col-form-label text-sm-center" for="filter-tgl-spk-akhir">s/d</label>
                      <div class="col-sm-9">
                        <input type="text" id="filter-tgl-spk-akhir" class="form-control dt-date" name="tgl_masuk_akhir" />
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="row">
                      <label class="col-sm-3 col-form-label text-sm-end" for="filter-nama-pelanggan">Nama Pelanggan</label>
                      <div class="col-sm-9">
                        <input type="text" id="filter-nama-pelanggan" class="form-control" name="nama_pelanggan" />
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="row">
                      <label class="col-sm-3 col-form-label text-sm-end" for="filter-nama-pemilik">Nama Pemilik</label>
                      <div class="col-sm-9">
                        <input type="text" id="filter-nama-pemilik" class="form-control" name="nama_pemilik" />
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="row">
                      <label class="col-sm-3 col-form-label text-sm-end" for="filter-no-polis">Nomor Polis</label>
                      <div class="col-sm-9">
                        <input type="text" id="filter-no-polis" class="form-control" name="no_polis" />
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="row">
                      <label class="col-sm-3 col-form-label text-sm-end" for="filter-no-klaim">Nomor Klaim</label>
                      <div class="col-sm-9">
                        <input type="text" id="filter-no-klaim" class="form-control" name="kode_claim" />
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="row">
                      <label class="col-sm-3 col-form-label text-sm-end" for="filter-status-spk">Status SPK</label>
                      <div class="col-sm-9">
                        <select id="filter-status-spk" class="select2 form-select" data-allow-clear="true">
                          <option value="all">Pilih Semua</option>
                          @foreach($status_spk as $row)
                            <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="row">
                      <label class="col-sm-3 col-form-label text-sm-end" for="filter-status">Status</label>
                      <div class="col-sm-9">
                        <select id="filter-status" class="select2 form-select" data-allow-clear="true">
                          <option value="all">Pilih Semua</option>
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
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Batal</button>
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
      <!--/ Add CARI SPK Modal -->

  </div>

@endsection
