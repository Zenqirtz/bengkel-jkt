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
    .input-total {
      font-weight: bold;
      font-size: 14px;
    }
  </style>
@endsection

<!-- Page Scripts -->
@section('page-script')
  <script src="{{ asset('assets/js/stock-opname.js') }}"></script>
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
        {{-- <h5 class="mb-0">Filter</h5> --}}
        <h5 class="mb-0">{{ $title }}</h5>
      </div>
      <div class="card-body">
        <form id="filterForm" class="form-control-validation" method="post" action="{{ url('stock-opname-list') }}" autocomplete="off">
          @csrf
          <div class="row mb-4">
            <label class="col-sm-2 col-form-label" for=filter-jenis-laporan">Tipe Barang</label>
            <div class="col-sm-10">
              <select id="filter-tipe-barang" name="tipe" class="select2 form-select" data-allow-clear="true">
                <option value="">Pilih Tipe Barang</option>
                @foreach($tipe_barang as $row)
                  <option value="{{ $row->kode }}" @if(old('tipe', @$datafilter['tipe']) == $row->kode) selected @endif>{{ $row->keterangan }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row mb-4" id="jns-lap-bulan">
            <label class="col-sm-2 col-form-label" for="filter-bulan">Periode</label>
            <div class="col-sm-10">
              <div class="d-flex align-items-center gap-2">
                <div class="flex-grow-1">
                  <select id="filter-bulan" name="bulan" class="select2 form-select" data-allow-clear="true" style="width: 100%;">
                    <option value="">Pilih Bulan</option> @foreach($months as $key => $row)
                      <option value="{{ $key }}" @if(old('bulan', @$datafilter['bulan']) == $key) selected @endif>{{ $row }}</option>
                    @endforeach
                  </select>
                </div>
                &nbsp;
                <div class="flex-grow-1">
                  <select id="filter-tahun" name="tahun" class="select2 form-select" data-allow-clear="true" style="width: 100%;">
                    <option value="">Pilih Tahun</option>
                    @foreach($years as $key => $row)
                      <option value="{{ $key }}" @if(old('tahun', @$datafilter['tahun']) == $key) selected @endif>{{ $row }}</option>
                    @endforeach
                  </select>
                </div>
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

@if (isset($datafilter['tipe']))
<div class="row mt-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Stock dan Saldo {{ $nama_barang }}</h5>
        </div>
        <div class="demo-inline-spacing">
          @if ($isEdit)
          <button type="button" class="btn btn-primary btn-adjust">Adjust Saldo</button>
          <button type="button" class="btn btn-primary btn-konsolidasi">Konsolidasi Saldo</button>
          @endif
          @if ($isAdd)
          <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
          @endif
        </div>
      </div>
      <div class="card-datatable">
        @if (@$datafilter['tipe'] == "S" || @$datafilter['tipe'] == "T")
        <table class="datatables-sparepart table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
        @else
        <table class="datatables-barang table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
        @endif
          <thead>
            <tr>
              <th rowspan="2"></th>
              <th rowspan="2" class="text-center align-middle">No</th>
              {{-- <th rowspan="2" class="text-center align-middle">Bulan</th> --}}
              {{-- <th rowspan="2" class="text-center align-middle">Tahun</th> --}}
              @if (@$datafilter['tipe'] == "S" || @$datafilter['tipe'] == "T")
              <th rowspan="2" class="text-center align-middle">Merek Kendaraan</th>
              <th rowspan="2" class="text-center align-middle">Tipe Kendaraan</th>
              <th rowspan="2" class="text-center align-middle">No. Input</th>
              @endif
              <th rowspan="2" class="text-center align-middle">Nama Barang</th>
              <th rowspan="2" class="text-center align-middle">Satuan</th>
              <th colspan="3" class="text-center">Saldo Awal</th>
              <th colspan="3" class="text-center">Penambahan</th>
              <th colspan="3" class="text-center">Pengurangan</th>
              <th colspan="3" class="text-center">Retur</th>
              <th colspan="3" class="text-center">Adjust</th>
              <th colspan="3" class="text-center">Saldo Akhir</th>
            </tr>
            <tr>
              <th class="text-center">Qty</th>
              <th class="text-center">Harga</th>
              <th class="text-center">Jumlah</th>
              <th class="text-center">Qty</th>
              <th class="text-center">Harga</th>
              <th class="text-center">Jumlah</th>
              <th class="text-center">Qty</th>
              <th class="text-center">Harga</th>
              <th class="text-center">Jumlah</th>
              <th class="text-center">Qty</th>
              <th class="text-center">Harga</th>
              <th class="text-center">Jumlah</th>
              <th class="text-center">Qty</th>
              <th class="text-center">Harga</th>
              <th class="text-center">Jumlah</th>
              <th class="text-center">Qty</th>
              <th class="text-center">Harga</th>
              <th class="text-center">Jumlah</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>
@endif

<!-- Adjust Saldo Modal -->
<div class="modal" id="addRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel1">Adjust Stock dan Saldo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addNewDataForm" method="post" action="{{ url('stock-opname-list') }}" onSubmit="return false">
        @csrf
        <input type="hidden" name="id" id="user_id">
        <input type="hidden" name="tipe" id="add-tipe-barang">

        <div class="row g-2" id="view-part">
          <div class="col-md-6">
            <div class="row mb-1 align-items-center">
              <label class="col-sm-4 col-form-label">Nomor Input</label>
              <div class="col-sm-8 form-control-validation">
                <input type="text" id="add-nomor-input" name="kode_input" class="form-control form-control-sm text-primary" disabled />
              </div>
            </div>
          </div>
          <div class="col-md-6">
            &nbsp;
          </div>
          <div class="col-md-6">
            <div class="row mb-1 align-items-center">
              <label class="col-sm-4 col-form-label">Merek Kendaraan</label>
              <div class="col-sm-8">
                <input type="text" id="add-merek-kendaraan" name="nama_merek" class="form-control form-control-sm text-primary" disabled />
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="row mb-1 align-items-center">
              <label class="col-sm-4 col-form-label">Tipe Kendaraan</label>
              <div class="col-sm-8">
                <input type="text" id="add-tipe-kendaraan" name="nama_tipe" class="form-control form-control-sm text-primary" disabled />
              </div>
            </div>
          </div>
        </div>

        <div class="row g-2">
          <div class="col-md-6">
            <div class="row mb-1 align-items-center">
              <label class="col-sm-4 col-form-label">Nama Barang</label>
              <div class="col-sm-8 form-control-validation">
                <input type="text" id="add-nama-barang" name="nama_barang" class="form-control form-control-sm text-primary" disabled />
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="row mb-1 align-items-center">
              <label class="col-sm-4 col-form-label">Satuan</label>
              <div class="col-sm-8">
                <input type="text" id="add-satuan" name="nama_satuan" class="form-control form-control-sm text-primary" disabled />
              </div>
            </div>
          </div>
        </div>

        <hr class="container-m-nx mt-2" />

        <div class="row g-2">
          <div class="col-md-6">
            <h6 class="m-0 me-2">Saldo Akhir</h6>
            <div class="row mb-1 align-items-center">
              <label class="col-sm-4 col-form-label">Qty</label>
              <div class="col-sm-8 form-control-validation">
                <input type="text" id="add-unit-akhir" name="unit_akhir" class="form-control form-control-sm text-primary input-readonly" readonly />
              </div>
            </div>
            <div class="row mb-1 align-items-center">
              <label class="col-sm-4 col-form-label">Harga</label>
              <div class="col-sm-8 form-control-validation">
                <input type="text" id="add-harga-akhir" name="harga_akhir" class="form-control form-control-sm text-primary input-readonly" readonly />
              </div>
            </div>
            <div class="row mb-1 align-items-center">
              <label class="col-sm-4 col-form-label">Jumlah</label>
              <div class="col-sm-8 form-control-validation">
                <input type="text" id="add-jumlah-akhir" name="jumlah_akhir" class="form-control form-control-sm text-primary input-readonly" readonly />
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <h6 class="m-0 me-2">Saldo Adjust</h6>
            <div class="row mb-1 align-items-center">
              <label class="col-sm-4 col-form-label">Qty</label>
              <div class="col-sm-8 form-control-validation">
                <input type="text" id="add-unit-adjust" name="unit_adjust" class="form-control form-control-sm text-primary invoice-price" />
              </div>
            </div>
            <div class="row mb-1 align-items-center">
              <label class="col-sm-4 col-form-label">Harga</label>
              <div class="col-sm-8 form-control-validation">
                <input type="text" id="add-harga-adjust" name="harga_adjust" class="form-control form-control-sm text-primary input-readonly" readonly />
              </div>
            </div>
            <div class="row mb-1 align-items-center">
              <label class="col-sm-4 col-form-label">Jumlah</label>
              <div class="col-sm-8 form-control-validation">
                <input type="text" id="add-jumlah-adjust" name="jumlah_adjust" class="form-control form-control-sm text-primary input-readonly" readonly />
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
<!--/ Adjust Saldo Modal -->   

<!-- Konsolidasi Saldo Modal -->
<div class="modal" id="updateRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel1">Konsolidasi Stock dan Saldo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="konsolidasiDataForm" method="post" action="{{ url('gudang/konsolidasi-saldo') }}" onSubmit="return false">
        @csrf
        <input type="hidden" name="bulan" id="kons-bulan">
        <input type="hidden" name="tahun" id="kons-tahun">
        <input type="hidden" name="tipe" id="kons-tipe-barang">

        <div class="row g-2">
          <div class="col-md-12">
            <div class="row mb-1 align-items-center">
              <label class="col-sm-3 col-form-label">Tipe Barang</label>
              <div class="col-sm-9 form-control-validation">
                <input type="text" id="kons-nama-tipe-barang" name="nama_tipe_barang" class="form-control form-control-sm text-primary" disabled />
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="row mb-1 align-items-center">
              <label class="col-sm-3 col-form-label">Periode</label>
              <div class="col-sm-9 form-control-validation">
                <input type="text" id="kons-periode" name="periode" class="form-control form-control-sm text-primary" disabled />
              </div>
            </div>
          </div>
        </div>

        <hr class="container-m-nx mt-2" />

        <div class="row mt-3 g-5">
          <div class="col-12 d-flex justify-content-between">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            <button type="submit" class="btn btn-primary btn-submit">Proses</button>
          </div>
        </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Adjust Saldo Modal -->   

@endsection
