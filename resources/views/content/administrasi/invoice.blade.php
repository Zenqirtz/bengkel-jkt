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
  <script src="{{ asset('assets/js/invoice.js') }}"></script>
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
        @if ($isAdd || $isEdit)
        <button type="button" class="btn btn-primary edit-terbit-invoice">Terbit Invoice</button>
        <button type="button" class="btn btn-primary edit-kirim-invoice">Kirim Invoice</button>
        <button type="button" class="btn btn-primary edit-terima-invoice">Cetak Invoice</button>
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
            <th>No. NPWP</th>
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

  <!-- Add Terbit Invoice -->
  <div class="modal" id="addRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel1">Terbit Invoice</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="addNewDataForm" method="post" action="{{ url('invoice-list') }}" onSubmit="return false">
          @csrf
          <input type="hidden" name="tipe" value="terbit-invoice">
          <input type="hidden" name="id" id="user_id">
          <input type="hidden" name="kode_spk" id="kode_spk">
          <input type="hidden" name="kode_estimasi" id="kode_estimasi">
          <input type="hidden" name="ppn_persen" id="add-ppn-persen" value="{{ $ppn_persen }}">

          <div class="row g-2">
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. Estimasi</label>
                <div class="col-sm-9">
                  <input type="text" id="add-nomor-estimasi" name="kode_estimasi" class="form-control form-control-sm text-primary fw-bold" disabled />
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
                <label class="col-sm-3 col-form-label">No. Polis / No. Tiket</label>
                <div class="col-sm-9">
                  <input type="text" id="add-nomor-polis" name="no_polis" class="form-control form-control-sm" disabled />
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">No. Invoice</label>
                <div class="col-sm-8">
                  <input type="text" id="add-nomor-kwitansi" name="kode_kwitansi" class="form-control form-control-sm text-primary fw-bold" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Tanggal Invoice</label>
                <div class="col-sm-8 form-control-validation">
                  <input type="text" id="add-tanggal-kwitansi" name="tgl_kwitansi" class="form-control form-control-sm dt-date input-wajib" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Tipe Invoice</label>
                <div class="col-sm-8 form-control-validation">
                  <select id="add-tipe-kwitansi" name="kode_tipe_kwitansi" class="select2 form-select input-wajib" data-allow-clear="true">
                    <option value="">Pilih Tipe Invoice</option>
                    @foreach($tipe_kwitansi as $row)
                      <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Split Perbaikan</label>
                <div class="col-sm-4 form-control-validation">
                  <div class="input-group">
                    <div class="form-floating form-floating-outline">
                      <input type="text" id="add-persen-bahan" name="persen_bahan" class="form-control form-control-sm invoice-price input-wajib" placeholder="Bahan" />
                      <label for="add-persen-bahan">Bahan</label>
                    </div>
                    <span class="input-group-text cursor-pointer">%</span>
                  </div>
                </div>
                <div class="col-sm-4 form-control-validation">
                  <div class="input-group">
                    <div class="form-floating form-floating-outline">
                      <input type="text" id="add-persen-jasa" name="persen_jasa" class="form-control form-control-sm invoice-price input-readonly" placeholder="Jasa" readonly  />
                      <label for="add-persen-jasa">Jasa</label>
                    </div>
                    <span class="input-group-text cursor-pointer">%</span>
                  </div>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
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
              <div class="row mb-1 align-items-center">
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
              <div class="row mb-1 align-items-center">
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

          <div class="row g-4">
            <div class="col-md-12">
              <h6 class="section-title">Rincian Tagihan</h6>
            </div>
            <div class="col-md-6">
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total Perbaikan</label>
                <div class="col-sm-4">
                  {{-- <input type="text" id="add-total-perbaikan" name="total_perbaikan" class="form-control form-control-sm text-end invoice-price" /> --}}
                  <input type="hidden" id="add-total-perbaikan" name="total_perbaikan" />
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="add-total-bahan" name="total_bahan" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" readonly />
                    <label for="add-total-bahan">Bahan</label>
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="add-total-jasa" name="total_jasa" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" readonly />
                    <label for="add-total-jasa">Jasa</label>
                  </div>
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total Sparepart</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-sparepart" name="total_sparepart" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" readonly />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total Derek / Lain</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-lain" name="total_lain" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" readonly />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label"></label>
                <div class="col-sm-8">
                  <div class="divider-black"><span class="operator-sign">+</span></div>
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total Invoice</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-keseluruhan" name="total" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" readonly />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total PPN</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-ppn" name="ppn" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" readonly />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total OR</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-or" name="total_or_ass" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" readonly />
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
                  <input type="text" id="add-total-tagihan" name="grand_total" class="form-control form-control-sm text-end text-primary fw-bold input-readonly" readonly />
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Prorata</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-prorata" name="prorata" class="form-control form-control-sm text-end input-readonly" readonly />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">PPh</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-pph" name="pph" class="form-control form-control-sm text-end invoice-price" />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Penyusutan</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-penyusutan" name="penyusutan" class="form-control form-control-sm text-end invoice-price" />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Salvage</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-salvage" name="salvage" class="form-control form-control-sm text-end invoice-price" />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Discount</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-discount" name="discount" class="form-control form-control-sm text-end invoice-price" />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Memo</label>
                <div class="col-sm-8 form-control-validation">
                  <textarea id="add-memo" name="memo" class="form-control form-control-sm" style="height: 70px;"></textarea>
                </div>
              </div>
            </div>
          </div>

          <hr class="container-m-nx mt-2" />

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
  <!--/ Add Terbit Invoice -->

  <!-- Add Kirim Invoice -->
  <div class="modal" id="addModalKirimInv" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="titleKirimInv">Kirim Invoice</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="kirimInvoiceForm" method="post" action="{{ url('invoice-list') }}" onSubmit="return false">
          @csrf
          <input type="hidden" name="tipe" value="kirim-invoice">
          <input type="hidden" name="id" id="user_id2">
          <input type="hidden" name="kode_spk" id="kode_spk2">
          <input type="hidden" name="kode_estimasi" id="kode_estimasi2">
          <input type="hidden" name="kode_kwitansi" id="kode_kwitansi2">
          <input type="hidden" name="ppn_persen" id="add-ppn-persen2" value="{{ $ppn_persen }}">

          <div class="row g-2">
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. Estimasi</label>
                <div class="col-sm-9">
                  <input type="text" id="add-nomor-estimasi2" name="kode_estimasi" class="form-control form-control-sm text-primary fw-bold" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. SPK</label>
                <div class="col-sm-5">
                  <input type="text" id="add-nomor-spk2" name="kode_spk2" class="form-control form-control-sm" disabled />
                </div>
                <div class="col-sm-4">
                  <input type="text" id="add-nomor-polisi2" name="no_polisi" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Tipe Kendaraan</label>
                <div class="col-sm-9">
                  <input type="text" id="add-tipe-kendaraan2" name="tipe_kendaraan" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Nama Pemilik</label>
                <div class="col-sm-9">
                  <input type="text" id="add-nama-pemilik2" name="nama_pemilik" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Nama Asuransi</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" id="add-nama-pelanggan2" name="nama_pelanggan" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. Polis</label>
                <div class="col-sm-9">
                  <input type="text" id="add-nomor-polis2" name="no_polis" class="form-control form-control-sm" disabled />
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">No. Invoice</label>
                <div class="col-sm-5">
                  <input type="text" id="add-nomor-kwitansi2" name="kode_kwitansi" class="form-control form-control-sm text-primary fw-bold" disabled />
                </div>
                <div class="col-sm-3">
                  <input type="text" id="add-tanggal-kwitansi2" name="tgl_kwitansi" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Tipe Invoice</label>
                <div class="col-sm-8 form-control-validation">
                  <input type="text" id="add-tipe-kwitansi2" name="tipe_kwitansi" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">No. Pengiriman</label>
                <div class="col-sm-8">
                  <input type="text" id="add-nomor-pengiriman" name="kode_kirim_kwitansi" class="form-control form-control-sm text-primary fw-bold" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-4 col-form-label">Tanggal Pengiriman</label>
                <div class="col-sm-8 form-control-validation">
                  <input type="text" id="add-tanggal-kirim-kwitansi" name="tgl_kirim_kwitansi" class="form-control form-control-sm dt-date input-wajib" />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Memo</label>
                <div class="col-sm-8 form-control-validation">
                  <textarea id="add-memo2" name="memo" class="form-control form-control-sm" style="height: 70px;"></textarea>
                </div>
              </div>
            </div>
          </div>

          <hr class="container-m-nx mt-2" />

          <div class="row g-4">
            <div class="col-md-12">
              <h6 class="section-title">Rincian Tagihan</h6>
            </div>
            <div class="col-md-6">
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total Perbaikan</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-perbaikan2" name="total_perbaikan" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total Sparepart</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-sparepart2" name="total_sparepart" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total Lain-lain</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-lain2" name="total_lain" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label"></label>
                <div class="col-sm-8">
                  <div class="divider-black"><span class="operator-sign">+</span></div>
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total Invoice</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-keseluruhan2" name="total" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Transport</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-transport2" name="transport" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Total OR</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-or2" name="total_or" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
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
                  <input type="text" id="add-total-tagihan2" name="total_s" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">PPN ({{ $ppn_persen }}%)</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-ppn2" name="ppn_s" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Prorata</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-prorata2" name="prorata" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">PPh</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-pph2" name="pph" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Penyusutan</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-penyusutan2" name="penyusutan" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Salvage</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-salvage2" name="salvage" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label">Discount</label>
                <div class="col-sm-8">
                  <input type="text" id="add-total-discount2" name="discount" class="form-control form-control-sm text-end text-primary fw-bold" disabled />
                </div>
              </div>
            </div>
          </div>

          <hr class="container-m-nx mt-2" />

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
  <!--/ Add Kirim Invoice -->

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
