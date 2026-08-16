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

@section('page-style')
<style>
  /* Input Readonly (Background Abu-abu) */
  .input-readonly {
      background-color: #dcdcdc !important;
      border: 1px solid #a0a0a0;
      box-shadow: inset 1px 1px 2px #c0c0c0;
  }
  /* Garis Pemisah Hitam Tebal */
  .divider-black {
      border-top: 2px solid #000;
      margin: 5px 0 5px 0;
      position: relative;
  }
  .operator-sign {
      position: absolute;
      right: -15px;
      top: -12px;
      font-weight: bold;
      font-size: 16px;
  }
</style>
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
  <script>
    const authUsername = "{{ $username }}";
  </script>

  <script src="{{ asset('assets/js/estimasi.js') }}"></script>
@endsection

@section('content')
  <!-- Users List Table -->
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="card-title mb-0">
        <h5 class="m-0 me-2">{{ $title }}</h5>
      </div>
      <div class="demo-inline-spacing">
        <button type="button" class="btn btn-primary filter-spk" data-bs-toggle="modal" data-bs-target="#filterRoleModal">Cari</button>
        @if ($isList)
        <button type="button" class="btn btn-primary view-selected-spk">Lihat</button>
        @endif
        @if ($isAdd || $isEdit)
        <button type="button" class="btn btn-primary edit-selected-spk">Ubah</button>
        <button type="button" class="btn btn-primary cetak-estimasi">Cetak</button>
        <button type="button" class="btn btn-primary export-estimasi">Export Excel</button>
        @endif
      </div>
    </div>
    <div class="card-datatable text-nowrap">
      <table class="datatables-spk table table-bordered table-responsive" data-title="{{ $title }}" data-view="{{ $isList }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
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
          <form id="addNewDataForm" method="post" action="{{ url('estimasi-list') }}" onSubmit="return false">
          @csrf
          <input type="hidden" name="id" id="user_id">
          {{-- <input type="hidden" name="kode_konsep_estimasi" id="add-nomor-konsep"> --}}
          {{-- <input type="hidden" name="kode_estimator" id="add-estimator"> --}}
          {{-- <input type="hidden" name="kode_spk" id="add-nomor-spk"> --}}
          {{-- <input type="hidden" name="tahun" id="add-tahun"> --}}
          <input type="hidden" name="ppn_persen" id="add-ppn-persen" value="{{ $ppn_persen }}">

          <div class="row g-2">
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. Estimasi</label>
                <div class="col-sm-5">
                  <input type="text" id="add-nomor-estimasi" name="kode_estimasi" class="form-control form-control-sm text-primary fw-bold input-readonly" readonly />
                </div>
                <div class="col-sm-4">
                  <input type="text" id="add-tanggal-estimasi" name="tgl_estimasi" class="form-control form-control-sm input-readonly" readonly />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. Konsep</label>
                <div class="col-sm-5">
                  <input type="text" id="add-nomor-konsep" name="kode_konsep_estimasi" class="form-control form-control-sm text-primary fw-bold input-readonly" readonly />
                </div>
                <div class="col-sm-4">
                  <input type="text" id="add-tanggal-konsep" name="tgl_konsep" class="form-control form-control-sm input-readonly" readonly />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. SPK</label>
                <div class="col-sm-4">
                  <input type="text" id="add-nomor-spk" name="kode_spk" class="form-control form-control-sm input-readonly" readonly />
                </div>
                <div class="col-sm-5">
                  <input type="text" id="add-nomor-polisi" name="no_polisi" class="form-control form-control-sm bg-primary-subtle input-readonly" readonly />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Nama Pemilik</label>
                <div class="col-sm-9">
                  <input type="text" id="add-nama-pemilik" name="nama_pemilik" class="form-control form-control-sm text-primary input-readonly" readonly />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Tipe Kendaraan</label>
                <div class="col-sm-9">
                  <input type="text" id="add-tipe-kendaraan" name="tipe_kendaraan" class="form-control form-control-sm input-readonly" readonly />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. Polis / No. Tiket</label>
                <div class="col-sm-9">
                  <input type="text" id="add-nomor-polis" name="no_polis" class="form-control form-control-sm" maxlength="30" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. Klaim</label>
                <div class="col-sm-9">
                  <input type="text" id="add-nomor-klaim" name="kode_claim" class="form-control form-control-sm" maxlength="30" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Tahun</label>
                <div class="col-sm-4">
                  <input type="text" id="add-tahun" name="tahun" class="form-control form-control-sm input-readonly" readonly />
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                  <label class="col-sm-4 col-form-label">Nama Estimator</label>
                  <div class="col-sm-8 form-control-validation">
                    <select id="add-estimator" name="kode_estimator" class="select2 form-select form-select-sm input-wajib" data-allow-clear="true">
                      <option value="">Pilih Nama Estimator</option>
                      @foreach($estimator as $row)
                        <option value="{{ $row->kode_estimator }}">{{ $row->nama_estimator }}</option>
                      @endforeach
                    </select>
                  </div>
              </div>
              <div class="row mb-1 align-items-center">
                  <label class="col-sm-4 col-form-label">Nama Asuransi</label>
                  <div class="col-sm-8 form-control-validation">
                    <select id="add-pelanggan" name="kode_pelanggan" class="select2 form-select form-select-sm input-wajib" data-allow-clear="true">
                      <option value="">Pilih Nama Asuransi</option>
                      @foreach($asuransi as $row)
                        <option value="{{ $row->kode_pelanggan }}">{{ $row->nama_pelanggan }}</option>
                      @endforeach
                    </select>
                  </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Surveyor</label>
                <div class="col-sm-8 form-control-validation">
                  <input type="text" id="add-surveyor" name="nama_surveyor" class="form-control form-control-sm text-uppercase" maxlength="50" />
                  {{-- <select id="add-surveyor" name="kode_surveyor" class="select2 form-select form-select-sm input-wajib" data-allow-clear="true">
                    <option value="">Pilih Nama Surveyor</option>
                    @foreach($surveyor as $row)
                      <option value="{{ $row->kode_surveyor }}">{{ $row->nama_surveyor }}</option>
                    @endforeach
                  </select> --}}
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Tanggal Survey</label>
                <div class="col-sm-8 form-control-validation">
                  <input type="text" id="add-tanggal-survey" name="tgl_survey" class="form-control form-control-sm dt-date" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                  <label class="col-sm-4 col-form-label">Lama Pekerjaan</label>
                  <div class="col-sm-4 form-control-validation">
                      <div class="input-group input-group-sm">
                        <input type="number" id="add-lama-pekerjaan" name="lama_pekerjaan" class="form-control" value="0" />
                        <span class="input-group-text">hari</span>
                      </div>
                  </div>
              </div>
              <div class="row mb-1">
                  <label class="col-sm-4 col-form-label">Keterangan</label>
                  <div class="col-sm-8 form-control-validation">
                    <textarea id="add-memo" name="memo" class="form-control form-control-sm" style="height: 120px;"></textarea>
                  </div>
              </div>
              <div class="row mb-1">
                <label class="col-sm-4 col-form-label">Perbaikan Kena PPN</label>
                <div class="col-sm-8 form-control-validation">
                  <div class="form-check form-check-inline mt-3">
                    <input class="form-check-input add-ppn" type="radio" name="sifat_ppn" id="add-ppn-sifat1" data-tipe="perbaikan" value="0" checked />
                    <label class="form-check-label" for="add-ppn-sifat1">Tidak</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input add-ppn" type="radio" name="sifat_ppn" id="add-ppn-sifat2" data-tipe="perbaikan" value="1" />
                    <label class="form-check-label" for="add-ppn-sifat2">Ya</label>
                  </div>
                </div>
              </div>
              <div class="row mb-1">
                <label class="col-sm-4 col-form-label">Sparepart Kena PPN</label>
                <div class="col-sm-8 form-control-validation">
                  <div class="form-check form-check-inline mt-3">
                    <input class="form-check-input add-ppn" type="radio" name="sparepart_ppn" id="add-ppn-sparepart1" data-tipe="sparepart" value="0" checked />
                    <label class="form-check-label" for="add-ppn-sparepart1">Tidak</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input add-ppn" type="radio" name="sparepart_ppn" id="add-ppn-sparepart2" data-tipe="sparepart" value="1" />
                    <label class="form-check-label" for="add-ppn-sparepart2">Ya</label>
                  </div>
                </div>
              </div>
              <div class="row mb-1">
                <label class="col-sm-4 col-form-label">Lain-lain Kena PPN</label>
                <div class="col-sm-8 form-control-validation">
                  <div class="form-check form-check-inline mt-3">
                    <input class="form-check-input add-ppn" type="radio" name="lain_ppn" id="add-ppn-lain1" data-tipe="lain" value="0" checked />
                    <label class="form-check-label" for="add-ppn-lain1">Tidak</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input add-ppn" type="radio" name="lain_ppn" id="add-ppn-lain2" data-tipe="lain" value="1" />
                    <label class="form-check-label" for="add-ppn-lain2">Ya</label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <hr class="container-m-nx mt-2" />

          <div class="row mt-2">
            <div class="col-xl-12">
              <div class="nav-align-top">
                <ul class="nav nav-pills mb-4" role="tablist">
                  <li class="nav-item">
                    <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                      data-bs-target="#navs-pills-top-perbaikan" aria-controls="navs-pills-top-perbaikan"
                      aria-selected="true">Perbaikan</button>
                  </li>
                  <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                      data-bs-target="#navs-pills-top-sparepart" aria-controls="navs-pills-top-sparepart"
                      aria-selected="false">Spare Part</button>
                  </li>
                  <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                      data-bs-target="#navs-pills-top-lain" aria-controls="navs-pills-top-lain"
                      aria-selected="false">Lain-lain</button>
                  </li>
                </ul>
                <div class="tab-content">
                  <div class="tab-pane fade show active" id="navs-pills-top-perbaikan" role="tabpanel">
                    <div class="bg-light border p-1">
                      <button type="button" class="btn btn-sm btn-primary add-detail" data-tipe="perbaikan" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDet1">
                        <i class="icon-base ri ri-add-line icon-sm me-0 me-sm-2"></i>
                        <span class="d-none d-sm-inline-block">Tambah</span>
                      </button>
                    </div>
                    <div class="custom-table-container">
                      <table class="datatables-perbaikan table table-bordered table-responsive">
                        <thead>
                          <tr>
                            <th width="5%">No</th>
                            <th>Jenis Pekerjaan</th>
                            <th>Panel Pekerjaan</th>
                            <th width="20%" class="text-center">Harga</th>
                            <th width="10%">Tipe</th>
                            <th width="3%">Aksi</th>
                          </tr>
                        </thead>
                        <tfoot>
                          <tr>
                            <th colspan="3" style="text-align:right">Total Perbaikan</th>
                            <th><span id="total-perbaikan">0.00</span><input type="hidden" name="total_perbaikan" id="add-total-perbaikan"></th>
                            <th></th>
                            <th></th>
                          </tr>
                          <tr>
                            <th colspan="3" style="text-align:right">PPN ({{ $ppn_persen }}%)</th>
                            <th><span id="ppn-perbaikan">0.00</span></th>
                            <th></th>
                            <th></th>
                          </tr>
                          <tr>
                            <th colspan="3" style="text-align:right">Diskon</th>
                            <th><input type="text" id="add-disc-perbaikan" name="disc_perbaikan" class="form-control form-control-sm text-end invoice-diskon" placeholder="Diskon" /></th>
                            <th></th>
                            <th></th>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="navs-pills-top-sparepart" role="tabpanel">
                    <div class="bg-light border p-1">
                      <button type="button" class="btn btn-sm btn-primary add-detail" data-tipe="sparepart" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDet2">
                        <i class="icon-base ri ri-add-line icon-sm me-0 me-sm-2"></i>
                        <span class="d-none d-sm-inline-block">Tambah</span>
                      </button>
                    </div>
                    <div class="custom-table-container">
                      <table class="datatables-sparepart table table-bordered table-responsive">
                        <thead>
                          <tr>
                            <th width="5%">No</th>
                            <th>Nama Spare Part</th>
                            <th>No. Spare Part</th>
                            <th width="10%">Qty</th>
                            <th width="15%" class="text-center">Harga</th>
                            <th width="15%" class="text-center">Jumlah</th>
                            <th width="10%">Tipe</th>
                            <th width="5%">Aksi</th>
                          </tr>
                        </thead>
                        <tfoot>
                          <tr>
                            <th colspan="5" style="text-align:right">Total Spare Part</th>
                            <th><span id="total-sparepart">0.00</span><input type="hidden" name="total_sparepart" id="add-total-sparepart"></th>
                            <th></th>
                            <th></th>
                          </tr>
                          <tr>
                            <th colspan="5" style="text-align:right">PPN ({{ $ppn_persen }}%)</th>
                            <th><span id="ppn-sparepart">0.00</span></th>
                            <th></th>
                            <th></th>
                          </tr>
                          <tr>
                            <th colspan="5" style="text-align:right">Diskon</th>
                            <th><input type="text" id="add-disc-sparepart" name="disc_sparepart" class="form-control form-control-sm text-end invoice-diskon" placeholder="Diskon" /></th>
                            <th></th>
                            <th></th>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="navs-pills-top-lain" role="tabpanel">
                    <div class="bg-light border p-1">
                      <button type="button" class="btn btn-sm btn-primary add-detail" data-tipe="lain" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDet3">
                        <i class="icon-base ri ri-add-line icon-sm me-0 me-sm-2"></i>
                        <span class="d-none d-sm-inline-block">Tambah</span>
                      </button>
                    </div>
                    <div class="custom-table-container">
                      <table class="datatables-lain table table-bordered table-responsive">
                        <thead>
                          <tr>
                            <th width="5%">No</th>
                            <th>Pekerjaan Lain-lain</th>
                            <th width="20%" class="text-center">Harga</th>
                            <th width="10%">Tipe</th>
                            <th width="5%">Aksi</th>
                          </tr>
                        </thead>
                        <tfoot>
                          <tr>
                            <th colspan="2" style="text-align:right">Total Lain-lain</th>
                            <th><span id="total-lain">0.00</span><input type="hidden" name="total_lain" id="add-total-lain"></th>
                            <th></th>
                            <th></th>
                          </tr>
                          <tr>
                            <th colspan="2" style="text-align:right">PPN ({{ $ppn_persen }}%)</th>
                            <th><span id="ppn-lain">0.00</span></th>
                            <th></th>
                            <th></th>
                          </tr>
                          <tr>
                            <th colspan="2" style="text-align:right">Diskon</th>
                            <th><input type="text" id="add-disc-lain" name="disc_lain" class="form-control form-control-sm text-end invoice-diskon" placeholder="Diskon" /></th>
                            <th></th>
                            <th></th>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="table-responsive mt-3 fw-bold">
            <table class="table m-0 table-borderless">
              <tbody>
                <tr>
                  <td class="align-top px-0 py-6">
                    &nbsp;
                  </td>
                  <td class="pe-0 py-6 w-px-150">
                    <p class="mb-1">Total Estimasi:</p>
                    <p class="mb-1">Total Diskon:</p>
                    <p class="mb-1 border-bottom pb-2">Total PPN:</p>
                    <p class="mb-0 pt-2">Grand Total:</p>
                  </td>
                  <td class="text-end px-0 py-6 w-px-200">
                    <p class="fw-medium mb-1"><span id="disp-subtotal-keseluruhan">0.00</span> <input type="hidden" name="subtotal" id="add-subtotal-keseluruhan"></p>
                    <p class="fw-medium mb-1"><span id="disp-total-diskon">0.00</span> <input type="hidden" name="disc_total" id="add-total-diskon"></p>
                    <p class="fw-medium mb-1 border-bottom pb-2"><span id="disp-total-ppn">0.00</span> <input type="hidden" name="ppn" id="add-total-ppn"></p>
                    <p class="fw-medium mb-0 pt-2"><span id="disp-total-keseluruhan">0.00</span> <input type="hidden" name="total" id="add-total-keseluruhan"></p>
                  </td>
                </tr>
              </tbody>
            </table>
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

  <!-- Offcanvas to add detail perbaikan -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddDet1" aria-labelledby="offcanvasAddLabelDet1">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddLabelDet1" class="offcanvas-title">Data Perbaikan</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="add-new-user pt-0" id="addNewDataFormDet1">
        <input type="hidden" name="id" id="est_dtl1_id">
        <input type="hidden" name="jenis_pekerjaan" id="jenis_pekerjaan">
        <input type="hidden" name="panel_pekerjaan" id="panel_pekerjaan">
        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <select id="add-jenis-pekerjaan" name="kode_jenis_pekerjaan" class="select2 form-select input-wajib">
            <option value="">Pilih Jenis Pekerjaan</option>
            @foreach($jenis_pekerjaan as $row)
            <option value="{{ $row->kode_jenis_pekerjaan }}">{{ $row->jenis_pekerjaan }}</option>
            @endforeach
          </select>
          <label for="add-jenis-pekerjaan">Jenis Pekerjaan</label>
        </div>
        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <select id="add-panel-pekerjaan" name="kode_panel_pekerjaan" class="select2 form-select input-wajib">
            <option value="">Pilih Panel Pekerjaan</option>
            @foreach($panel_pekerjaan as $row)
            <option value="{{ $row->kode_panel_pekerjaan }}">{{ $row->panel_pekerjaan }}</option>
            @endforeach
          </select>
          <label for="add-panel-pekerjaan">Panel Pekerjaan</label>
        </div>
        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <input type="text" class="form-control invoice-price input-wajib" id="add-harga" name="harga" placeholder="Harga" />
          <label for="add-harga">Harga</label>
        </div>
        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <select id="add-tipe-pekerjaan" name="tipe" class="select2 form-select input-wajib">
            <option value="">Pilih Tipe</option>
            @foreach($tipe_pekerjaan as $row)
            <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
            @endforeach
          </select>
          <label for="add-tipe-pekerjaan">Tipe</label>
        </div>
        <button type="submit" class="btn btn-primary me-sm-3 me-1">Simpan</button>
        <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="offcanvas">Batal</button>
      </form>
    </div>
  </div>

  <!-- Offcanvas to add detail sparepart -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddDet2" aria-labelledby="offcanvasAddLabelDet2">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddLabelDet2" class="offcanvas-title">Data Sparepart</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="add-new-user pt-0" id="addNewDataFormDet2">
        <input type="hidden" name="id" id="est_dtl2_id">
        <input type="hidden" name="nama_sparepart" id="nama_sparepart">
        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <select id="add-kode-sparepart" name="kode_sparepart" class="select2 form-select input-wajib">
            <option value="">Pilih Nama Sparepart</option>
            @foreach($sparepart as $row)
            <option value="{{ $row->kode_sparepart }}">{{ $row->nama_sparepart }}</option>
            @endforeach
          </select>
          <label for="add-kode-sparepart">Nama Sparepart</label>
        </div>
        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <input type="text" class="form-control input-wajib" id="add-nomor-sparepart" name="no_sparepart" placeholder="Nomor Sparepart" maxlength="30" value="SUPPLY" />
          <label for="add-nomor-sparepart">Nomor Sparepart</label>
        </div>
        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <input type="text" class="form-control invoice-price-sparepart input-wajib" id="add-qty-sparepart" name="qty" placeholder="Qty" />
          <label for="add-qty-sparepart">Qty</label>
        </div>
        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <input type="text" class="form-control invoice-price-sparepart input-wajib" id="add-harga-sparepart" name="harga" placeholder="Harga" />
          <label for="add-harga-sparepart">Harga</label>
        </div>
        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <input type="text" class="form-control invoice-price-sparepart input-wajib" id="add-jumlah-sparepart" name="jumlah" placeholder="Jumlah" readonly />
          <label for="add-jumlah-sparepart">Jumlah</label>
        </div>
        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <select id="add-tipe-sparepart" name="tipe" class="select2 form-select input-wajib">
            <option value="">Pilih Tipe</option>
            @foreach($tipe_pekerjaan as $row)
            <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
            @endforeach
          </select>
          <label for="add-tipe-sparepart">Tipe</label>
        </div>
        <button type="submit" class="btn btn-primary me-sm-3 me-1">Simpan</button>
        <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="offcanvas">Batal</button>
      </form>
    </div>
  </div>

  <!-- Offcanvas to add detail lain-lain -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddDet3" aria-labelledby="offcanvasAddLabelDet3">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddLabelDet3" class="offcanvas-title">Data Lain-lain</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="add-new-user pt-0" id="addNewDataFormDet3">
        <input type="hidden" name="id" id="est_dtl3_id">
        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <input type="text" class="form-control input-wajib" id="add-memo-lain" name="memo" placeholder="Pekerjaan Lain-lain" maxlength="100" />
          <label for="add-memo-lain">Pekerjaan Lain-lain</label>
        </div>
        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <input type="text" class="form-control invoice-price input-wajib" id="add-harga-lain" name="harga" placeholder="Harga" />
          <label for="add-harga-lain">Harga</label>
        </div>
        <div class="form-floating form-floating-outline mb-5 form-control-validation">
          <select id="add-tipe-lain" name="tipe" class="select2 form-select input-wajib">
            <option value="">Pilih Tipe</option>
            @foreach($tipe_pekerjaan as $row)
            <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
            @endforeach
          </select>
          <label for="add-tipe-lain">Tipe</label>
        </div>
        <button type="submit" class="btn btn-primary me-sm-3 me-1">Simpan</button>
        <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="offcanvas">Batal</button>
      </form>
    </div>
  </div>

@endsection
