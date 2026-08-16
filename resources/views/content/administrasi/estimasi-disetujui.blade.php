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
  <script src="{{ asset('assets/js/estimasi-disetujui.js') }}"></script>
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
        @if ($isAdd || $isEdit)
        <button type="button" class="btn btn-primary edit-selected-spk">Ubah</button>
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
          <form id="addNewDataForm" method="post" action="{{ url('estimasi-disetujui-list') }}" onSubmit="return false">
          @csrf
          <input type="hidden" name="id" id="user_id">
          <input type="hidden" name="kode_estimasi" id="kode_estimasi">
          <input type="hidden" name="kode_spk" id="kode_spk">
          <input type="hidden" name="kode_persetujuan" id="kode_persetujuan">
          <input type="hidden" name="ppn_persen" id="add-ppn-persen" value="{{ $ppn_persen }}">
          <input type="hidden" name="salvage" id="salvage">
          <input type="hidden" name="disc_tipe_perbaikan" id="add-disc-tipe-perbaikan">
          <input type="hidden" name="disc_tipe_sparepart" id="add-disc-tipe-sparepart">
          <input type="hidden" name="disc_tipe_lain" id="add-disc-tipe-lain">
          <input type="hidden" name="disc_perbaikan" id="add-disc-perbaikan">
          <input type="hidden" name="disc_sparepart" id="add-disc-sparepart">
          <input type="hidden" name="disc_lain" id="add-disc-lain">

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
                  <input type="text" id="add-nomor-spk2" name="kode_spk2" class="form-control form-control-sm" disabled />
                </div>
                <div class="col-sm-4">
                  <input type="text" id="add-nomor-polisi" name="no_polisi" class="form-control form-control-sm bg-primary-subtle" disabled />
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
                <div class="col-sm-9">
                  <input type="text" id="add-nama-pelanggan" name="nama_pelanggan" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. Klaim</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" id="add-nomor-claim" name="kode_claim" class="form-control form-control-sm input-wajib" />
                </div>
              </div>
              <div class="row mb-1">
                <label class="col-sm-3 col-form-label">Keterangan</label>
                <div class="col-sm-9 form-control-validation">
                  <textarea id="add-memo" name="memo" class="form-control form-control-sm" style="height: 100px;"></textarea>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Persetujuan</label>
                <div class="col-sm-5">
                  <input type="text" id="add-nomor-persetujuan2" name="kode_persetujuan2" class="form-control form-control-sm text-primary fw-bold" disabled />
                </div>
                <div class="col-sm-3 form-control-validation">
                  <input type="text" id="add-tanggal-persetujuan" name="tgl_persetujuan" class="form-control form-control-sm dt-date input-wajib" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Disetujui Oleh</label>
                <div class="col-sm-8 form-control-validation">
                  <input type="text" id="add-disetujui-oleh" name="disetujui_oleh" class="form-control form-control-sm input-wajib" maxlength="50" />
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
              {{-- <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Split Perbaikan</label>
                <div class="col-sm-4 form-control-validation">
                  <div class="input-group">
                    <div class="form-floating form-floating-outline">
                      <input type="text" id="add-persen-bahan" name="persen_bahan" class="form-control form-control-sm invoice-price" placeholder="Bahan" />
                      <label for="add-persen-bahan">Bahan</label>
                    </div>
                    <span class="input-group-text cursor-pointer">%</span>
                  </div>
                </div>
                <div class="col-sm-4 form-control-validation">
                  <div class="input-group">
                    <div class="form-floating form-floating-outline">
                      <input type="text" id="add-persen-jasa" name="persen_jasa" class="form-control form-control-sm invoice-price" placeholder="Jasa" />
                      <label for="add-persen-jasa">Jasa</label>
                    </div>
                    <span class="input-group-text cursor-pointer">%</span>
                  </div>
                </div>
              </div> --}}
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
                    <div class="custom-table-container">
                      <table class="datatables-perbaikan table table-bordered table-responsive">
                        <thead>
                          <tr>
                            <th width="5%">No</th>
                            <th>Jenis Pekerjaan</th>
                            <th>Panel Pekerjaan</th>
                            <th width="5%">Tipe</th>
                            <th width="20%" class="text-center">Harga</th>
                            <th width="20%" class="text-center">Harga Penawaran</th>
                            <th width="5%"></th>
                          </tr>
                        </thead>
                        {{-- <tfoot>
                          <tr>
                            <th colspan="5" style="text-align:right">Total Perbaikan</th>
                            <th><span id="total-perbaikan">0.00</span></th> 
                            <th></th>
                          </tr>
                          <tr>
                            <th colspan="5" style="text-align:right">Diskon</th>
                            <th><span id="disc-perbaikan">0.00</span></th>
                            <th></th>
                          </tr>
                        </tfoot> --}}
                      </table>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="navs-pills-top-sparepart" role="tabpanel">
                    <div class="custom-table-container">
                      <table class="datatables-sparepart table table-bordered table-responsive">
                        <thead>
                          <tr>
                            <th width="5%">No</th>
                            <th>Nama Spare Part</th>
                            <th width="5%">Tipe</th>
                            <th width="20%" class="text-center">Harga</th>
                            <th width="20%" class="text-center">Harga Penawaran</th>
                            <th width="5%"></th>
                          </tr>
                        </thead>
                        {{-- <tfoot>
                          <tr>
                              <th colspan="4" style="text-align:right">Total Spare Part</th>
                              <th><span id="total-sparepart">0.00</span></th>
                              <th></th>
                          </tr>
                          <tr>
                            <th colspan="4" style="text-align:right">Diskon</th>
                            <th><span id="disc-sparepart">0.00</span></th>
                            <th></th>
                          </tr>
                        </tfoot> --}}
                      </table>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="navs-pills-top-lain" role="tabpanel">
                    <div class="custom-table-container">
                      <table class="datatables-lain table table-bordered table-responsive">
                        <thead>
                          <tr>
                            <th width="5%">No</th>
                            <th>Pekerjaan Lain-lain</th>
                            <th width="5%">Tipe</th>
                            <th width="20%" class="text-center">Harga</th>
                            <th width="20%" class="text-center">Harga Penawaran</th>
                            <th width="5%"></th>
                          </tr>
                        </thead>
                        {{-- <tfoot>
                          <tr>
                              <th colspan="4" style="text-align:right">Total Lain-lain</th>
                              <th><span id="total-lain">0.00</span></th> 
                              <th></th>
                          </tr>
                          <tr>
                            <th colspan="4" style="text-align:right">Diskon</th>
                            <th><span id="disc-lain">0.00</span></th> 
                            <th></th>
                          </tr>
                        </tfoot> --}}
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row g-4 mt-2">
            <div class="col-md-5">
              <h6 class="section-title">Pengajuan Klaim</h6>
              
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total Perbaikan</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-perbaikan" name="total_perbaikan" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total Sparepart</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-sparepart" name="total_sparepart" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total Lain-lain</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-lain" name="total_lain" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
                </div>
              </div>
              {{-- <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total OR</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-or" name="total_or" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
                </div>
              </div> --}}
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label"></label>
                <div class="col-sm-8">
                  <div class="divider-black"><span class="operator-sign">+</span></div>
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-keseluruhan" name="total" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
                </div>
              </div>

            </div>

            <div class="col-md-7">
              <h6 class="section-title">Jumlah Yang Disetujui</h6>

              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total Perbaikan</label>
                <div class="col-sm-8">
                  <div class="input-group">
                    <input type="text" id="add-total-perbaikan-s" name="total_perbaikan_s" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" readonly />
                    <button class="btn btn-outline-primary add-detail" type="button" data-tipe="perbaikan" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDet1">Diskon</button>
                  </div>
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total Sparepart</label>
                <div class="col-sm-8">
                  <div class="input-group">
                    <input type="text" id="add-total-sparepart-s" name="total_sparepart_s" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" readonly />
                    <button class="btn btn-outline-primary add-detail" type="button" data-tipe="sparepart" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDet1">Diskon</button>
                  </div>
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total Lain-lain</label>
                <div class="col-sm-8">
                  <div class="input-group">
                    <input type="text" id="add-total-lain-s" name="total_lain_s" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" readonly />
                    <button class="btn btn-outline-primary add-detail" type="button" data-tipe="lain" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDet1">Diskon</button>
                  </div>
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label"></label>
                <div class="col-sm-8">
                  <div class="divider-black"><span class="operator-sign">+</span></div>
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total Kwitansi</label>
                <div class="col-sm-8">
                  <div class="input-group">
                    <input type="text" id="add-total-kwitansi" name="total_s" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" readonly />
                    <button class="btn btn-outline-primary add-detail" type="button" data-tipe="salvage" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDet2">Salvage?</button>
                  </div>
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total PPN</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-ppn" name="ppn_s" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" readonly />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total OR</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-or" name="total_or" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" readonly />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label"></label>
                <div class="col-sm-8">
                  <div class="divider-black"><span class="operator-sign">-</span></div>
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total Tagihan</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-tagihan" name="total_s" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" readonly />
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
  <div class="offcanvas offcanvas-bottom" tabindex="-1" id="offcanvasAddDet1" aria-labelledby="offcanvasAddLabelDet1">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddLabelDet1" class="offcanvas-title">Diskon Perbaikan</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-200">
      <form class="add-new-user pt-0" id="addNewDataFormDet1">
        <input type="hidden" name="id" id="est_dtl1_id">
        <input type="hidden" name="tipe" id="tipe">
        <div class="row g-2">
          <div class="col-md-6">
            <div class="form-floating form-floating-outline form-control-validation">
              <div class="form-check form-check-inline">
                <input class="form-check-input add-tipe-diskon" type="radio" name="tipe_diskon" id="add-tipe-diskon1" value="0" />
                <label class="form-check-label" for="add-tipe-diskon1">Diskon</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input add-tipe-diskon" type="radio" name="tipe_diskon" id="add-tipe-diskon2" value="1" />
                <label class="form-check-label" for="add-tipe-diskon2">Nilai Penawaran</label>
              </div>
            </div>
            <div class="form-floating form-floating-outline mb-2 form-control-validation">
              <input type="text" class="form-control invoice-price input-wajib" id="add-diskon" name="diskon" />
              <label for="add-diskon">Diskon</label>
            </div>
            <button type="submit" class="btn btn-primary me-sm-3 me-1" id="btn-simpan">Simpan</button>
            <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="offcanvas">Batal</button>
          </div>
          <div class="col-md-6">&nbsp;</div>
        </div>
      </form>
    </div>
  </div>

  <!-- Offcanvas to add detail sparepart -->
  <div class="offcanvas offcanvas-bottom" tabindex="-1" id="offcanvasAddDet2" aria-labelledby="offcanvasAddLabelDet2">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddLabelDet2" class="offcanvas-title">Salvage</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="add-new-user pt-0" id="addNewDataFormDet2">
        <input type="hidden" name="id" id="est_dtl2_id">
        <div class="row g-2">
          <div class="col-md-6">
            <div class="form-floating form-floating-outline mb-5 form-control-validation">
              <input type="text" class="form-control invoice-price input-wajib" id="add-salvage" name="salvage" placeholder="Salvage" />
              <label for="add-salvage">Salvage</label>
            </div>
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Simpan</button>
            <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="offcanvas">Batal</button>
          </div>
          <div class="col-md-6">&nbsp;</div>
        </div>
      </form>
    </div>
  </div>
  
@endsection
