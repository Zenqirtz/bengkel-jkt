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
  <script src="{{ asset('assets/js/laporan-aging-penagihan.js') }}"></script>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    @if(session()->has('success'))
    <div class="alert alert-solid-success alert-dismissible d-flex align-items-center flex-wrap row-gap-2" role="alert">
      <span class="alert-icon rounded">
        <i class="icon-base ri ri-checkbox-circle-line icon-md"></i>
      </span>
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session()->has('error'))
    <div class="alert alert-solid-danger alert-dismissible d-flex align-items-center flex-wrap row-gap-2" role="alert">
      <span class="alert-icon rounded">
        <i class="icon-base ri ri-error-warning-line icon-md"></i>
      </span>
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if ($errors->any())
      @foreach ($errors->all() as $error)
          <div class="alert alert-danger">
          {{$error}}
          </div>
      @endforeach
    @endif
  </div>
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Filter</h5>
        {{-- <h5 class="mb-0">Laporan {{ $title }}</h5> --}}
      </div>
      <div class="card-body">
        <form id="filterForm" class="form-control-validation" method="post" action="{{ url('laporan-aging-penagihan-list') }}" autocomplete="off">
          @csrf
          <div class="row mb-4">
            <label class="col-sm-2 col-form-label" for=filter-jenis-laporan">Jenis Laporan</label>
            <div class="col-sm-10">
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="jenis_laporan" id="jenis-rinci" value="rinci" {{ @$datafilter['jenis_laporan'] == 'rinci' ? 'checked' : '' }}>
                <label class="form-check-label" for="jenis-rinci">Rinci</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="jenis_laporan" id="jenis-rekap" value="rekap" {{ @$datafilter['jenis_laporan'] == 'rekap' ? 'checked' : '' }}>
                <label class="form-check-label" for="jenis-rekap">Rekap</label>
              </div>
            </div>
          </div>
          <div class="row mb-4" id="jns-lap-periode">
            <label class="col-sm-2 col-form-label" for="filter-tgl-awal">Tanggal</label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="text" id="filter-tgl-awal" name="tgl_awal" class="form-control dt-date" value="{{ @$datafilter['tgl_awal'] }}" placeholder="Tanggal Awal" />
              </div>
            </div>
          </div>
          <div class="row justify-content-end">
            <div class="col-sm-10">
              <button type="submit" class="btn btn-primary">Tampilkan</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@if (@$datafilter['jenis_laporan'] == "rinci")
<div class="row mt-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Laporan Rincian Kwitansi Belum Ditagih [Outstanding Penagihan]</h5>
        </div>
        <div class="demo-inline-spacing">
          <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
          <button type="button" class="btn btn-primary btn-print">Print</button>
        </div>
      </div>
      <div class="card-datatable text-nowrap">
        <table class="datatables-aging-penagihan-rinci table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
          <thead>
            <tr>
              <th>No</th>
              <th>Tanggal Kwitansi</th>
              <th>No. Kwitansi</th>
              <th>No. SPK</th>
              <th>No. Polisi</th>
              <th>Merek Tipe</th>
              <th>No. Klaim</th>
              <th>No. Polis / No. Tiket</th>
              <th>Tertanggung</th>
              <th>Total<br> Tagihan</th>
              <th>Total OR</th>
              <th>Uang Muka</th>
              <th>Sisa Tagihan</th>
              <th>Tanggal Kirim</th>
              <th>Hari</th>
              <th>No. Keluar</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>
@elseif (@$datafilter['jenis_laporan'] == "rekap")
<div class="row mt-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Laporan Rekap Kwitansi Belum Ditagih [Outstanding Penagihan]</h5>
        </div>
        <div class="demo-inline-spacing">
          <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
          <button type="button" class="btn btn-primary btn-print">Print</button>
        </div>
      </div>
      <div class="card-datatable text-nowrap">
        <table class="datatables-aging-penagihan-rekap table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
          <thead>
            <tr>
              <th rowspan="2" class="text-center">No</th>
              <th rowspan="2" class="text-center">Nama Asuransi</th>
              <th colspan="4" class="text-center">1 - 2 Minggu</th>
              <th colspan="4" class="text-center">3 - 5 Minggu</th>
              <th colspan="4" class="text-center">> 5 Minggu</th>
              <th colspan="4" class="text-center">Belum Diterima</th>
              <th colspan="4" class="text-center">Total</th>
            </tr>
            <tr>
              <!-- 1-2 Minggu -->
              <th class="text-center">Unit</th>
              <th class="text-center">%</th>
              <th class="text-center">Rupiah</th>
              <th class="text-center">%</th>
              <!-- 3-5 Minggu -->
              <th class="text-center">Unit</th>
              <th class="text-center">%</th>
              <th class="text-center">Rupiah</th>
              <th class="text-center">%</th>
              <!-- >5 Minggu -->
              <th class="text-center">Unit</th>
              <th class="text-center">%</th>
              <th class="text-center">Rupiah</th>
              <th class="text-center">%</th>
              <!-- Belum Diterima -->
              <th class="text-center">Unit</th>
              <th class="text-center">%</th>
              <th class="text-center">Rupiah</th>
              <th class="text-center">%</th>
              <!-- Total -->
              <th class="text-center">Unit</th>
              <th class="text-center">%</th>
              <th class="text-center">Rupiah</th>
              <th class="text-center">%</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th colspan="2" style="text-align:center">Grand Total</th>
              <th id="footer-unit_1_2">0</th>
              <th id="footer-unit_1_2_persen">0.00</th>
              <th id="footer-nilai_1_2">0</th>
              <th id="footer-nilai_1_2_persen">0.00</th>
              <th id="footer-unit_3_5">0</th>
              <th id="footer-unit_3_5_persen">0.00</th>
              <th id="footer-nilai_3_5">0</th>
              <th id="footer-nilai_3_5_persen">0.00</th>
              <th id="footer-unit_5">0</th>
              <th id="footer-unit_5_persen">0.00</th>
              <th id="footer-nilai_5">0</th>
              <th id="footer-nilai_5_persen">0.00</th>
              <th id="footer-unit_blm_dikirim">0</th>
              <th id="footer-unit_blm_dikirim_persen">0.00</th>
              <th id="footer-nilai_blm_dikirim">0</th>
              <th id="footer-nilai_blm_dikirim_persen">0.00</th>
              <th id="footer-unit_total">0</th>
              <th id="footer-unit_total_persen">0.00</th>
              <th id="footer-nilai_total">0</th>
              <th id="footer-nilai_total_persen">0.00</th>
            </tr>
            <tr>
              <th colspan="2" style="text-align:center">Ketentuan Persentase</th>
              <th colspan="2" style="text-align:center">30</th>
              <th colspan="2" style="text-align:center">30</th>
              <th colspan="2" style="text-align:center">60</th>
              <th colspan="2" style="text-align:center">60</th>
              <th colspan="2" style="text-align:center">10</th>
              <th colspan="2" style="text-align:center">10</th>
              <th colspan="2" style="text-align:center">0</th>
              <th colspan="2" style="text-align:center">0</th>
              <th colspan="2" style="text-align:center">100</th>
              <th colspan="2" style="text-align:center">100</th>
            </tr>
            <tr>
              <th colspan="2" style="text-align:center">Lebih Kurang dari Ketentuan</th>
              <th id="footer2-unit_1_2_persen" colspan="2" style="text-align:center">0</th>
              <th id="footer2-nilai_1_2_persen" colspan="2" style="text-align:center">0</th>
              <th id="footer2-unit_3_5_persen" colspan="2" style="text-align:center">0</th>
              <th id="footer2-nilai_3_5_persen" colspan="2" style="text-align:center">0</th>
              <th id="footer2-unit_5_persen" colspan="2" style="text-align:center">0</th>
              <th id="footer2-nilai_5_persen" colspan="2" style="text-align:center">0</th>
              <th id="footer2-unit_blm_dikirim_persen" colspan="2" style="text-align:center">0</th>
              <th id="footer2-nilai_blm_dikirim_persen" colspan="2" style="text-align:center">0</th>
              <th id="footer2-unit_total_persen" colspan="2" style="text-align:center">100</th>
              <th id="footer2-nilai_total_persen" colspan="2" style="text-align:center">100</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>
@endif

@endsection
