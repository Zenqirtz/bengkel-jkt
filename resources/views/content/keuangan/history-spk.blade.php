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

  <script src="{{ asset('assets/js/history-spk.js') }}"></script>
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
        {{-- <button type="button" class="btn btn-primary add-new" data-bs-toggle="modal" data-bs-target="#addRoleModal">SPK Baru</button> --}}
        <button type="button" class="btn btn-primary edit-selected-spk">Detail</button>
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
            <th>No. Polisi</th>
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

  <!-- Add SPK BARU Modal -->
  <div class="modal" id="addRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel1">{{ $title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="nav-align-left nav-tabs-shadow">
            <ul class="nav nav-tabs" role="tablist">
              <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-left-home" aria-controls="navs-left-home" aria-selected="true">Detail SPK</button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-left-dempul" aria-controls="navs-left-dempul" aria-selected="false">Dempul</button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-left-las" aria-controls="navs-left-las" aria-selected="false">Las</button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-left-cat" aria-controls="navs-left-cat" aria-selected="false">Cat</button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-left-poles" aria-controls="navs-left-poles" aria-selected="false">Poles</button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-left-finishing" aria-controls="navs-left-finishing" aria-selected="false">Finishing</button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-left-mixing" aria-controls="navs-left-mixing" aria-selected="false">Mixing</button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-left-borongan" aria-controls="navs-left-borongan" aria-selected="false">Borongan</button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-left-sparepart" aria-controls="navs-left-sparepart" aria-selected="false">Sparepart</button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-left-komisi" aria-controls="navs-left-komisi" aria-selected="false">Komisi</button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-left-orfree" aria-controls="navs-left-orfree" aria-selected="false">OR Free</button>
              </li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="navs-left-home">

                <div class="bg-label-primary p-4 rounded-4 my-3">
                  <div class="d-flex">
                    <h5 class="mb-0">SPK</h5>
                  </div>
                </div>

                <div class="row g-2">
                  <div class="col-md-6">
                    <div class="row mb-1 align-items-center">
                      <label class="col-sm-3 col-form-label" for="add-spk">No SPK</label>
                      <div class="col-sm-9">
                        <input type="text" id="add-nomor-spk" class="form-control form-control-sm" disabled />
                      </div>
                    </div>
                    <div class="row mb-1 align-items-center">
                      <label class="col-sm-3 col-form-label" for="add-nama">Nama Pemilik</label>
                      <div class="col-sm-9">
                        <input type="text" id="add-nama-pemilik" class="form-control form-control-sm" disabled />
                      </div>
                    </div>
                    <div class="row mb-1 align-items-center">
                      <label class="col-sm-3 col-form-label" for="add-no-polisi">No Polisi</label>
                      <div class="col-sm-9">
                        <input type="text" id="add-no-polisi" class="form-control form-control-sm" disabled />
                      </div>
                    </div>
                    <div class="row mb-1 align-items-center">
                      <label class="col-sm-3 col-form-label" for="add-tipe-kendaraan">Tipe Kendaraan</label>
                      <div class="col-sm-9">
                        <input type="text" id="add-tipe-kendaraan" class="form-control form-control-sm" disabled />
                      </div>
                    </div>
                    <div class="row mb-1 align-items-center">
                      <label class="col-sm-3 col-form-label" for="add-point-panel">Point Panel</label>
                      <div class="col-sm-9">
                        <input type="text" id="add-point-panel" class="form-control form-control-sm" disabled />
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="row mb-1 align-items-center">
                      <label class="col-sm-3 col-form-label" for="add-pelanggan">Nama Asuransi</label>
                      <div class="col-sm-9">
                        <input type="text" id="add-pelanggan" class="form-control form-control-sm" disabled />
                      </div>
                    </div>
                    <div class="row mb-1 align-items-center">
                      <label class="col-sm-3 col-form-label" for="add-nomor-estimasi">No Estimasi</label>
                      <div class="col-sm-9">
                        <input type="text" id="add-nomor-estimasi" class="form-control form-control-sm" disabled />
                      </div>
                    </div>
                    <div class="row mb-1 align-items-center">
                      <label class="col-sm-3 col-form-label" for="add-estimator">Estimator</label>
                      <div class="col-sm-9">
                        <input type="text" id="add-estimator" class="form-control form-control-sm" disabled />
                      </div>
                    </div>
                    <div class="row mb-1 align-items-center">
                      <label class="col-sm-3 col-form-label" for="add-no-polis">No Polis</label>
                      <div class="col-sm-9">
                        <input type="text" id="add-no-polis" class="form-control form-control-sm" disabled />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="bg-label-primary p-4 rounded-4 my-3">
                  <div class="d-flex">
                    <h5 class="mb-0">Invoice</h5>
                  </div>
                </div>
                <div class="row g-2">
                  <div class="col-md-6">
                    <div class="row mb-1 align-items-center">
                      <label class="col-sm-3 col-form-label" for="add-nomor-kwitansi">No Invoice</label>
                      <div class="col-sm-9">
                        <input type="text" id="add-nomor-kwitansi" class="form-control form-control-sm" disabled />
                      </div>
                    </div>
                    <div class="row mb-1 align-items-center">
                      <label class="col-sm-3 col-form-label" for="add-tgl-kwitansi">Tgl Invoice</label>
                      <div class="col-sm-9">
                        <input type="text" id="add-tgl-kwitansi" class="form-control form-control-sm" disabled />
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="row mb-1 align-items-center">
                      <label class="col-sm-3 col-form-label" for="add-tgl-kirim">Tgl Kirim</label>
                      <div class="col-sm-9">
                        <input type="text" id="add-tgl-kirim" class="form-control form-control-sm" disabled />
                      </div>
                    </div>
                    <div class="row mb-1 align-items-center">
                      <label class="col-sm-3 col-form-label" for="add-tgl-lunas">Tgl Lunas</label>
                      <div class="col-sm-9">
                        <input type="text" id="add-tgl-lunas" class="form-control form-control-sm" disabled />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row g-6">
                  <div class="col-xl-6">
                    <div class="bg-label-primary p-4 rounded-4 my-3">
                      <div class="d-flex">
                        <h5 class="mb-0">Pendapatan</h5>
                      </div>
                    </div>
                    <div class="row g-2">
                      <div class="col-md-12">
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-3 col-form-label" for="add-nilai-or">Nilai OR</label>
                          <div class="col-sm-9">
                            <input type="text" id="add-nilai-or" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" disabled />
                          </div>
                        </div>
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-3 col-form-label" for="add-nilai-perbaikan">Nilai Perbaikan</label>
                          <div class="col-sm-9">
                            <input type="text" id="add-nilai-perbaikan" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" disabled />
                          </div>
                        </div>
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-3 col-form-label" for="add-nilai-sparepart">Nilai Sparepart</label>
                          <div class="col-sm-9">
                            <input type="text" id="add-nilai-sparepart" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" disabled />
                          </div>
                        </div>
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-3 col-form-label" for="add-nilai-lain">Nilai Lain</label>
                          <div class="col-sm-9">
                            <input type="text" id="add-nilai-lain" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" disabled />
                          </div>
                        </div>
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-3 col-form-label"></label>
                          <div class="col-sm-9">
                            <div class="divider-black"><span class="operator-sign">+</span></div>
                          </div>
                        </div>
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-3 col-form-label" for="add-total-pendapatan">Total Nilai</label>
                          <div class="col-sm-9">
                            <input type="text" id="add-total-pendapatan" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" disabled />
                          </div>
                        </div>
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-3 col-form-label" for="add-total-all">Grand Total</label>
                          <div class="col-sm-9">
                            <input type="text" id="add-total-all" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" disabled />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-xl-6">
                    <div class="bg-label-primary p-4 rounded-4 my-3">
                      <div class="d-flex">
                        <h5 class="mb-0">Biaya - Biaya</h5>
                      </div>
                    </div>
                    <div class="row g-2">
                      <div class="col-md-12">
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-3 col-form-label" for="add-upah-borongan">Upah Borongan</label>
                          <div class="col-sm-9">
                            <input type="text" id="add-upah-borongan" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" disabled />
                          </div>
                        </div>
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-3 col-form-label" for="add-biaya-perbaikan">Biaya Perbaikan</label>
                          <div class="col-sm-9">
                            <input type="text" id="add-biaya-perbaikan" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" disabled />
                          </div>
                        </div>
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-3 col-form-label" for="add-biaya-sparepart">Biaya Sparepart</label>
                          <div class="col-sm-9">
                            <input type="text" id="add-biaya-sparepart" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" disabled />
                          </div>
                        </div>
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-3 col-form-label" for="add-biaya-lain">Biaya Lain</label>
                          <div class="col-sm-9">
                            <input type="text" id="add-biaya-lain" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" disabled />
                          </div>
                        </div>
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-3 col-form-label"></label>
                          <div class="col-sm-9">
                            <div class="divider-black"><span class="operator-sign">+</span></div>
                          </div>
                        </div>
                        <div class="row mb-1 align-items-center">
                          <label class="col-sm-3 col-form-label" for="add-total-biaya">Total Biaya</label>
                          <div class="col-sm-9">
                            <input type="text" id="add-total-biaya" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" disabled />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                
              </div>
              <div class="tab-pane fade" id="navs-left-dempul">
                <div class="custom-table-container">
                  <table class="datatables-dempul table table-bordered table-responsive">
                    <thead>
                      <tr>
                        <th width="5%">No</th>
                        <th>Nama Bahan</th>
                        <th width="5%">Satuan</th>
                        <th width="10%">Qty</th>
                        <th width="20%" class="text-center">Harga</th>
                        <th width="20%" class="text-center">Jumlah</th>
                        <th width="15%">No. BPB</th>
                      </tr>
                    </thead>
                    <tfoot>
                      <tr>
                        <th colspan="5" style="text-align:right">Total</th>
                        <th class="text-end"><span id="total-perbaikan">0.00</span></th> 
                        <th></th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
              <div class="tab-pane fade" id="navs-left-las">
                <div class="custom-table-container">
                  <table class="datatables-las table table-bordered table-responsive">
                    <thead>
                      <tr>
                        <th width="5%">No</th>
                        <th>Nama Bahan</th>
                        <th width="5%">Satuan</th>
                        <th width="10%">Qty</th>
                        <th width="20%" class="text-center">Harga</th>
                        <th width="20%" class="text-center">Jumlah</th>
                        <th width="15%">No. BPB</th>
                      </tr>
                    </thead>
                    <tfoot>
                      <tr>
                        <th colspan="5" style="text-align:right">Total</th>
                        <th class="text-end"><span id="total-perbaikan">0.00</span></th> 
                        <th></th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
              <div class="tab-pane fade" id="navs-left-cat">
                <div class="custom-table-container">
                  <table class="datatables-cat table table-bordered table-responsive">
                    <thead>
                      <tr>
                        <th width="5%">No</th>
                        <th>Nama Bahan</th>
                        <th width="5%">Satuan</th>
                        <th width="10%">Qty</th>
                        <th width="20%" class="text-center">Harga</th>
                        <th width="20%" class="text-center">Jumlah</th>
                        <th width="15%">No. BPB</th>
                      </tr>
                    </thead>
                    <tfoot>
                      <tr>
                        <th colspan="5" style="text-align:right">Total</th>
                        <th class="text-end"><span id="total-perbaikan">0.00</span></th> 
                        <th></th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
              <div class="tab-pane fade" id="navs-left-poles">
                <div class="custom-table-container">
                  <table class="datatables-poles table table-bordered table-responsive">
                    <thead>
                      <tr>
                        <th width="5%">No</th>
                        <th>Nama Bahan</th>
                        <th width="5%">Satuan</th>
                        <th width="10%">Qty</th>
                        <th width="20%" class="text-center">Harga</th>
                        <th width="20%" class="text-center">Jumlah</th>
                        <th width="15%">No. BPB</th>
                      </tr>
                    </thead>
                    <tfoot>
                      <tr>
                        <th colspan="5" style="text-align:right">Total</th>
                        <th class="text-end"><span id="total-perbaikan">0.00</span></th> 
                        <th></th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
              <div class="tab-pane fade" id="navs-left-finishing">
                <div class="custom-table-container">
                  <table class="datatables-finishing table table-bordered table-responsive">
                    <thead>
                      <tr>
                        <th width="5%">No</th>
                        <th>Nama Bahan</th>
                        <th width="5%">Satuan</th>
                        <th width="10%">Qty</th>
                        <th width="20%" class="text-center">Harga</th>
                        <th width="20%" class="text-center">Jumlah</th>
                        <th width="15%">No. BPB</th>
                      </tr>
                    </thead>
                    <tfoot>
                      <tr>
                        <th colspan="5" style="text-align:right">Total</th>
                        <th class="text-end"><span id="total-perbaikan">0.00</span></th> 
                        <th></th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
              <div class="tab-pane fade" id="navs-left-mixing">
                <div class="custom-table-container">
                  <table class="datatables-mixing table table-bordered table-responsive">
                    <thead>
                      <tr>
                        <th width="5%">No</th>
                        <th>Nama Bahan</th>
                        <th width="5%">Satuan</th>
                        <th width="10%">Qty</th>
                        <th width="20%" class="text-center">Harga</th>
                        <th width="20%" class="text-center">Jumlah</th>
                        <th width="15%">No. BPC</th>
                      </tr>
                    </thead>
                    <tfoot>
                      <tr>
                        <th colspan="5" style="text-align:right">Total</th>
                        <th class="text-end"><span id="total-perbaikan">0.00</span></th> 
                        <th></th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
              <div class="tab-pane fade" id="navs-left-borongan">
                <div class="custom-table-container">
                  <table class="datatables-borongan table table-bordered table-responsive">
                    <thead>
                      <tr>
                        <th width="5%">No</th>
                        <th>Pekerjaan</th>
                        <th>Nama Pekerja</th>
                        <th>Tgl Pembayaran</th>
                        <th>No Voucher</th>
                        <th>Pembayaran Ubah</th>
                      </tr>
                    </thead>
                    <tfoot>
                      <tr>
                        <th colspan="5" style="text-align:right">Total</th>
                        <th class="text-end">0.00</th> 
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
              <div class="tab-pane fade" id="navs-left-sparepart">
                <div class="custom-table-container">
                  <table class="datatables-sparepart table table-bordered table-responsive">
                    <thead>
                      <tr>
                        <th width="5%">No</th>
                        <th>Nama Bahan</th>
                        <th width="5%">Satuan</th>
                        <th width="10%">Qty</th>
                        <th width="20%" class="text-center">Harga</th>
                        <th width="20%" class="text-center">Jumlah</th>
                        <th width="15%">No. SPB</th>
                      </tr>
                    </thead>
                    <tfoot>
                      <tr>
                        <th colspan="5" style="text-align:right">Total</th>
                        <th class="text-end"><span id="total-perbaikan">0.00</span></th> 
                        <th></th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
              <div class="tab-pane fade" id="navs-left-komisi">
                <div class="custom-table-container">
                  <table class="datatables-komisi table table-bordered table-responsive">
                    <thead>
                      <tr>
                        <th width="5%">No</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>No Voucher</th>
                        <th>Jumlah</th>
                      </tr>
                    </thead>
                    <tfoot>
                      <tr>
                        <th colspan="4" style="text-align:right">Total</th>
                        <th class="text-end">0.00</th> 
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
              <div class="tab-pane fade" id="navs-left-orfree">
                <div class="custom-table-container">
                  <table class="datatables-orfree table table-bordered table-responsive">
                    <thead>
                      <tr>
                        <th width="5%">No</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>No Voucher</th>
                        <th>Jumlah</th>
                      </tr>
                    </thead>
                    <tfoot>
                      <tr>
                        <th colspan="4" style="text-align:right">Total</th>
                        <th class="text-end">0.00</th> 
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
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
          </form>
        </div>
      </div>
    </div>
  </div>
  <!--/ Add CARI SPK Modal -->   

@endsection
