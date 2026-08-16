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
  <script src="{{ asset('assets/js/tutup-buku.js') }}"></script>
@endsection

@section('content')
<div class="card mb-6">
  <div class="card-widget-separator-wrapper">
    <div class="card-body card-widget-separator">
      <div class="row gy-4 gy-sm-1">
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-4 pb-sm-0">
            <div>
              <h4 class="mb-0" id="total-bahan">0</h4>
              <p class="mb-0">SALDO BAHAN</p>
              <p class="mb-0"><small class="text-body-secondary">Last Updated: <span id="periode-bahan">{{ date('F Y') }}</span></small></p>
            </div>
            <div class="avatar me-sm-6">
              <span class="avatar-initial rounded-3 text-heading view-approve-po">
                <i class="icon-base ri ri-apps-line icon-26px"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none me-6" />
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-4 pb-sm-0">
            <div>
              <h4 class="mb-0" id="total-cat">0</h4>
              <p class="mb-0">SALDO CAT</p>
              <p class="mb-0"><small class="text-body-secondary">Last Updated: <span id="periode-cat">{{ date('F Y') }}</span></small></p>
            </div>
            <div class="avatar me-lg-6">
              <span class="avatar-initial rounded-3 text-heading">
                <i class="icon-base ri ri-brush-line icon-26px"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none" />
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h4 class="mb-0" id="total-sparepart">0</h4>
              <p class="mb-0">SALDO SPAREPART</p>
              <p class="mb-0"><small class="text-body-secondary">Last Updated: <span id="periode-sparepart">{{ date('F Y') }}</span></small></p>
            </div>
            <div class="avatar me-sm-6">
              <span class="avatar-initial rounded-3 text-heading">
                <i class="icon-base ri ri-archive-drawer-line icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">{{ $title }}</h5>
      </div>
      <div class="card-body">
        <form id="addNewDataForm" method="post" action="{{ url('tutup-buku-list') }}" onSubmit="return false">
          @csrf
          <div class="row mb-4">
            <label class="col-sm-2 col-form-label" for=add-tipe-barang">Tipe Barang</label>
            <div class="col-sm-10 form-control-validation">
              <select id="add-tipe-barang" name="tipe" class="select2 form-select" data-allow-clear="true">
                <option value="">Pilih Tipe Barang</option>
                @foreach($tipe_barang as $row)
                  <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row mb-4">
            <label class="col-sm-2 col-form-label" for="add-bulan">Periode</label>
            <div class="col-sm-5 form-control-validation">
              <select id="add-bulan" name="bulan" class="select2 form-select" data-allow-clear="true" style="width: 100%;">
                <option value="">Pilih Bulan</option> @foreach($months as $key => $row)
                  <option value="{{ $key }}">{{ $row }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-sm-5 form-control-validation">
              <select id="add-tahun" name="tahun" class="select2 form-select" data-allow-clear="true" style="width: 100%;">
                <option value="">Pilih Tahun</option>
                @foreach($years as $key => $row)
                  <option value="{{ $key }}">{{ $row }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row justify-content-end">
            <div class="col-sm-10">
              <button type="submit" class="btn btn-primary btn-submit">Proses</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection
