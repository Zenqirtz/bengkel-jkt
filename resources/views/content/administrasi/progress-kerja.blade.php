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
  <script src="{{ asset('assets/js/progress-kerja.js') }}"></script>
@endsection

@section('content')
  <!-- Users List Table -->
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="card-title mb-0">
        <h5 class="m-0 me-2">{{ $title }}</h5>
      </div>
      <div class="demo-inline-spacing">
        <button type="button" class="btn btn-primary filter-spk" data-bs-toggle="modal" data-bs-target="#filterRoleModal">Cari Data</button>
        <button type="button" class="btn btn-primary edit-selected-spk">Ubah Data</button>
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
  </div>

   <!-- Add SPK Modal -->
   <div class="modal" id="addRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel1">{{ $title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="addNewDataForm" method="post" action="{{ url('progress-kerja-list') }}" onSubmit="return false">
          @csrf
          <input type="hidden" name="id" id="user_id">
          <input type="hidden" name="kode_spk" id="kode_spk">
          <input type="hidden" name="kode_turun_lapangan" id="kode_turun_lapangan">
          <input type="hidden" name="step" id="step">

          <div class="row g-2">
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. Estimasi</label>
                <div class="col-sm-5">
                  <input type="text" id="add-nomor-estimasi" name="kode_estimasi" class="form-control form-control-sm text-primary fw-bold" disabled />
                </div>
                <div class="col-sm-4">
                  <input type="text" id="add-tanggal-estimasi" name="tgl_estimasi" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. SPK</label>
                <div class="col-sm-5">
                  <input type="text" id="add-nomor-spk" name="kode_spk2" class="form-control form-control-sm" disabled />
                </div>
                <div class="col-sm-4">
                  <input type="text" id="add-nomor-polisi" name="no_polisi" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Tipe Kendaraan</label>
                <div class="col-sm-9">
                  <input type="text" id="add-tipe-kendaraan" name="tipe_kendaraan" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Nama Pemilik</label>
                <div class="col-sm-9">
                  <input type="text" id="add-nama-pemilik" name="nama_pemilik" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Nama Asuransi</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" id="add-nama-pelanggan" name="nama_pelanggan" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. Polis</label>
                <div class="col-sm-9">
                  <input type="text" id="add-nomor-polis" name="no_polis" class="form-control form-control-sm" disabled />
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">No. Turun Lap.</label>
                <div class="col-sm-4">
                  <input type="text" id="add-kode-turun-lapangan" name="kode_turun_lapangan" class="form-control form-control-sm text-primary fw-bold" disabled />
                </div>
                <div class="col-sm-4">
                  <input type="text" id="add-tanggal-turun-lapangan" name="tgl_turun_lapangan" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Tgl Rencana Selesai</label>
                <div class="col-sm-8 form-control-validation">
                  <input type="text" id="add-tanggal-rencana-selesai" name="tgl_rencana_selesai" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1">
                <label class="col-sm-4 col-form-label">Yang Menyerahkan</label>
                <div class="col-sm-8 form-control-validation">
                  <input type="text" id="add-yang-menyerahkan" name="yang_menyerahkan" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1">
                <label class="col-sm-4 col-form-label">Yang Menerima</label>
                <div class="col-sm-8 form-control-validation">
                  <input type="text" id="add-yang-menerima" name="yang_menerima" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Tgl Terima</label>
                <div class="col-sm-8 form-control-validation">
                  <input type="text" id="add-tanggal-terima" name="tgl_terima" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Keterangan</label>
                <div class="col-sm-8 form-control-validation">
                  <input type="text" id="add-keterangan" name="keterangan" class="form-control form-control-sm" disabled />
                </div>
              </div>
            </div>
          </div>

          <hr class="container-m-nx mt-2" />

          <div class="row mt-2">
            <div class="col-xl-12">
              <div class="progress bg-label-primary" style="height: 15px;">
                <div class="progress-bar" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">100%</div>
              </div>
            </div>
          </div>

          <div class="row mt-2">
            <div class="col-xl-12">
              <div class="nav-align-top">
                <ul class="nav nav-pills mb-4" role="tablist">
                  <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                      data-bs-target="#navs-pills-top-bongkar" data-step="bongkar" aria-controls="navs-pills-top-bongkar"
                      aria-selected="true">Bongkar</button>
                  </li>
                  <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                      data-bs-target="#navs-pills-top-las" data-step="las" aria-controls="navs-pills-top-las"
                      aria-selected="false">Ketok & Las</button>
                  </li>
                  <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                      data-bs-target="#navs-pills-top-dempul" data-step="dempul" aria-controls="navs-pills-top-dempul"
                      aria-selected="false">Dempul</button>
                  </li>
                  <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                      data-bs-target="#navs-pills-top-mixing" data-step="mixing" aria-controls="navs-pills-top-mixing"
                      aria-selected="false">Mixing</button>
                  </li>
                  <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                      data-bs-target="#navs-pills-top-cat" data-step="cat" aria-controls="navs-pills-top-cat"
                      aria-selected="false">Cat</button>
                  </li>
                  <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                      data-bs-target="#navs-pills-top-poles" data-step="poles" aria-controls="navs-pills-top-poles"
                      aria-selected="false">Poles & Pasang</button>
                  </li>
                  {{-- RAWAT JALAN --}}
                  <li class="nav-item" id="tab-item-rawat-jalan" style="display:none;">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                      data-bs-target="#navs-pills-top-rawat-jalan" data-step="rawat_jalan"
                      aria-controls="navs-pills-top-rawat-jalan"
                      aria-selected="false">Rawat Jalan</button>
                  </li>
                  {{-- END RAWAT JALAN --}}
                  <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                      data-bs-target="#navs-pills-top-finishing" data-step="finishing" aria-controls="navs-pills-top-finishing"
                      aria-selected="false">Finishing</button>
                  </li>
                </ul>
                <div class="tab-content">
                  <div class="tab-pane fade" id="navs-pills-top-bongkar" role="tabpanel">
                    <div class="row g-2">
                      <div class="col-md-6">
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Tanggal Mulai</label>
                          <div class="col-sm-8 form-control-validation">
                            <input type="text" id="add-tanggal-bongkar1" name="tgl_bongkar1" class="form-control form-control-sm dt-date" value="{{ date('d/m/Y') }}" />
                          </div>
                        </div>
                        {{-- <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Tanggal Selesai</label>
                          <div class="col-sm-8 form-control-validation">
                            <input type="text" id="add-tanggal-bongkar2" name="tgl_bongkar2" class="form-control form-control-sm dt-date" />
                          </div>
                        </div> --}}
                      </div>
                      <div class="col-md-6">
                        &nbsp;
                      </div>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="navs-pills-top-las" role="tabpanel">
                    <div class="row g-2">
                      <div class="col-md-6">
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Tanggal Mulai</label>
                          <div class="col-sm-8 form-control-validation">
                            <input type="text" id="add-tanggal-las1" name="tgl_las1" class="form-control form-control-sm dt-date" value="{{ date('d/m/Y') }}" />
                          </div>
                        </div>
                        {{-- <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Tanggal Selesai</label>
                          <div class="col-sm-8 form-control-validation">
                            <input type="text" id="add-tanggal-las2" name="tgl_las2" class="form-control form-control-sm dt-date" />
                          </div>
                        </div> --}}
                      </div>
                       <div class="col-md-6">
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Nama Pekerja</label>
                          <div class="col-sm-8 form-control-validation">
                            <select id="add-pekerja-las" name="pekerja_las" class="select2 form-select form-select-sm" data-allow-clear="true">
                              <option value="">Pilih Nama Pekerja</option>
                              @foreach($pekerja_las as $row)
                                <option value="{{ $row->kode_karyawan }}">{{ $row->nama }}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Upah Las</label>
                          <div class="col-sm-8 form-control-validation">
                            <input type="text" id="add-upah-las" name="upah_las"
                              class="form-control form-control-sm text-end" placeholder="0" />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="navs-pills-top-dempul" role="tabpanel">
                    <div class="row g-2">
                      <div class="col-md-6">
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Tanggal Mulai</label>
                          <div class="col-sm-8 form-control-validation">
                            <input type="text" id="add-tanggal-dempul1" name="tgl_dempul1" class="form-control form-control-sm dt-date" value="{{ date('d/m/Y') }}" />
                          </div>
                        </div>
                        {{-- <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Tanggal Selesai</label>
                          <div class="col-sm-8 form-control-validation">
                            <input type="text" id="add-tanggal-dempul2" name="tgl_dempul2" class="form-control form-control-sm dt-date" />
                          </div>
                        </div> --}}
                      </div>
                       <div class="col-md-6">
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Nama Pekerja</label>
                          <div class="col-sm-8 form-control-validation">
                            <select id="add-pekerja-dempul" name="pekerja_dempul" class="select2 form-select form-select-sm" data-allow-clear="true">
                              <option value="">Pilih Nama Pekerja</option>
                              @foreach($pekerja_dempul as $row)
                                <option value="{{ $row->kode_karyawan }}">{{ $row->nama }}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Upah Dempul</label>
                          <div class="col-sm-8 form-control-validation">
                            <input type="text" id="add-upah-dempul" name="upah_dempul"
                              class="form-control form-control-sm text-end" placeholder="0" />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="navs-pills-top-mixing" role="tabpanel">
                    <div class="row g-2">
                      <div class="col-md-6">
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Tanggal Mulai</label>
                          <div class="col-sm-8 form-control-validation">
                            <input type="text" id="add-tanggal-mixing1" name="tgl_mixing1" class="form-control form-control-sm dt-date" value="{{ date('d/m/Y') }}" />
                          </div>
                        </div>
                        {{-- <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Tanggal Selesai</label>
                          <div class="col-sm-8 form-control-validation">
                            <input type="text" id="add-tanggal-mixing2" name="tgl_mixing2" class="form-control form-control-sm dt-date" />
                          </div>
                        </div> --}}
                      </div>
                      <div class="col-md-6">
                        &nbsp;
                      </div>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="navs-pills-top-cat" role="tabpanel">
                    <div class="row g-2">
                      <div class="col-md-6">
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Tanggal Mulai</label>
                          <div class="col-sm-8 form-control-validation">
                            <input type="text" id="add-tanggal-cat1" name="tgl_cat1" class="form-control form-control-sm dt-date" value="{{ date('d/m/Y') }}" />
                          </div>
                        </div>
                        {{-- <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Tanggal Selesai</label>
                          <div class="col-sm-8 form-control-validation">
                            <input type="text" id="add-tanggal-cat2" name="tgl_cat2" class="form-control form-control-sm dt-date" />
                          </div>
                        </div> --}}
                      </div>
                      <div class="col-md-6">
                        &nbsp;
                      </div>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="navs-pills-top-poles" role="tabpanel">
                    <div class="row g-2">
                      <div class="col-md-6">
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Tanggal Mulai</label>
                          <div class="col-sm-8 form-control-validation">
                            <input type="text" id="add-tanggal-poles1" name="tgl_poles1" class="form-control form-control-sm dt-date" value="{{ date('d/m/Y') }}" />
                          </div>
                        </div>
                        {{-- <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Tanggal Selesai</label>
                          <div class="col-sm-8 form-control-validation">
                            <input type="text" id="add-tanggal-poles2" name="tgl_poles2" class="form-control form-control-sm dt-date" />
                          </div>
                        </div> --}}
                      </div>
                      <div class="col-md-6">
                        &nbsp;
                      </div>
                    </div>
                  </div>
                  {{-- RAWAT JALAN --}}
                  <div class="tab-pane fade" id="navs-pills-top-rawat-jalan" role="tabpanel">
                    <div class="row g-2">
                      <div class="col-md-6">
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Tanggal Mulai</label>
                          <div class="col-sm-8 form-control-validation">
                            <input type="text" id="add-tanggal-rawat-jalan1" name="tgl_rawat_jalan1"
                                  class="form-control form-control-sm dt-date" value="{{ date('d/m/Y') }}" />
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6">&nbsp;</div>
                    </div>
                  </div>
                  {{-- END RAWAT JALAN --}}
                  <div class="tab-pane fade" id="navs-pills-top-finishing" role="tabpanel">
                    <div class="row g-2">
                      <div class="col-md-6">
                        {{-- <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Tanggal Mulai</label>
                          <div class="col-sm-8 form-control-validation">
                            <input type="text" id="add-tanggal-finishing1" name="tgl_finishing1" class="form-control form-control-sm dt-date" />
                          </div>
                        </div> --}}
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-4 col-form-label">Tanggal Selesai</label>
                          <div class="col-sm-8 form-control-validation">
                            <input type="text" id="add-tanggal-finishing2" name="tgl_finishing2" class="form-control form-control-sm dt-date" value="{{ date('d/m/Y') }}" />
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        &nbsp;
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row mt-3 g-5">
            <div class="col-12 d-flex justify-content-between">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
              <button type="submit" class="btn btn-primary btn-submit">Simpan</button>
            </div>
          </div>
          </form>
        </div>
        {{-- <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary">Simpan</button>
        </div> --}}
      </div>
    </div>
  </div>
  <!--/ Add SPK Modal -->

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
  <!--/ Add CARI SPK Modal -->

@endsection
