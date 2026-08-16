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
  <script src="{{ asset('assets/js/kewajiban.js') }}"></script>
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
          <form id="addNewDataForm" method="post" action="{{ url('kirim-estimasi-list') }}" onSubmit="return false">
          @csrf
          <input type="hidden" name="id" id="user_id">
          <input type="hidden" name="kode_spk" id="kode_spk">
          <input type="hidden" name="kode_estimasi" id="kode_estimasi">
          <input type="hidden" name="kode_pelanggan" id="kode_pelanggan">
          <input type="hidden" name="biaya_komisi" id="biaya_komisi">
          <input type="hidden" name="biaya_estimasi" id="biaya_estimasi">
          <input type="hidden" name="biaya_pribadi" id="biaya_pribadi">

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
            </div>

            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Nama Asuransi</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" id="add-nama-pelanggan" name="nama_pelanggan" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Kontak Person</label>
                <div class="col-sm-5">
                  <input type="text" id="add-contact-person" name="contact_person" class="form-control form-control-sm" disabled />
                </div>
                <div class="col-sm-4">
                  <input type="text" id="add-telepon" name="telepon" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Nilai Estimasi</label>
                <div class="col-sm-9">
                  <input type="text" id="add-nilai-estimasi" name="nilai_estimasi" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Biaya Pribadi</label>
                <div class="col-sm-9">
                  <input type="text" id="add-biaya-pribadi" name="biaya_pribadi" class="form-control form-control-sm" disabled />
                </div>
              </div>
            </div>
          </div>

          <hr class="container-m-nx mt-2" />

          <div class="row g-2">
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Ada OR?</label>
                <div class="col-sm-9 form-control-validation">
                  <div class="form-check form-check-inline mt-3">
                    <input class="form-check-input add-ada-or" type="radio" name="ada_or" id="add-ada-or1" value="01" />
                    <label class="form-check-label" for="add-ada-or1">Ya</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input add-ada-or" type="radio" name="ada_or" id="add-ada-or2" value="02" checked />
                    <label class="form-check-label" for="add-ada-or2">Tidak</label>
                  </div>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Jumlah OR</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" id="add-jumlah-or" name="jumlah_or" class="form-control form-control-sm invoice-price" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Nilai OR</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" id="add-nilai-or" name="nilai_or" class="form-control form-control-sm invoice-price" />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Total OR</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" id="add-total-or" name="total_or" class="form-control form-control-sm invoice-price" readonly />
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="row mb-1 ms-4">
                <div class="col-sm-12">
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="add-cek-polis" name="cek_polis" value="Y" />
                    <label class="form-check-label" for="add-cek-polis">Cek Polis Asuransi</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="add-cek-puas" name="pernyataan_puas" value="Y" />
                    <label class="form-check-label" for="add-cek-puas">Surat Pernyataan Puas</label>
                  </div>
                </div>
              </div>
              <div class="row mb-1 ms-4">
                <div class="col-sm-12">
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="add-cek-kuasa" name="surat_kuasa" value="Y" />
                    <label class="form-check-label" for="add-cek-kuasa">Surat Kuasa &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="add-cek-penyusutan" name="biaya_penyusutan" value="Y" />
                    <label class="form-check-label" for="add-cek-penyusutan">Biaya Penyusutan Sparepart</label>
                  </div>
                </div>
              </div>
              <div class="row mb-1 ms-4">
                <div class="col-sm-12">
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="add-cek-protata" name="prorata" value="Y" />
                    <label class="form-check-label" for="add-cek-protata">Prorata</label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row g-2">
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">OR Free?</label>
                <div class="col-sm-9 form-control-validation">
                  <div class="form-check form-check-inline mt-3">
                    <input class="form-check-input add-free-or" type="radio" name="is_free" id="add-free-or1" value="Y" />
                    <label class="form-check-label" for="add-free-or1">Ya</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input add-free-or" type="radio" name="is_free" id="add-free-or2" value="T" checked />
                    <label class="form-check-label" for="add-free-or2">Tidak</label>
                  </div>
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Jumlah OR</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" id="add-nilai-free-or" name="nilai_free_or" class="form-control form-control-sm invoice-price" />
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="row mb-1">
                <label class="col-sm-3 col-form-label">Keterangan</label>
                <div class="col-sm-9 form-control-validation">
                  {{-- <textarea id="add-keterangan" name="keterangan" class="form-control form-control-sm is-invalid" style="height: 120px;"></textarea> --}}
                  <textarea id="add-keterangan" name="keterangan" class="form-control form-control-sm" style="height: 120px;"></textarea>
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
