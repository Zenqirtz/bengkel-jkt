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

@section('page-script')
  <script src="{{ asset('assets/js/laporan-bank-urut-voucher.js') }}"></script>
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
      </div>
      <div class="card-body">
        <form id="filterForm" class="form-control-validation" method="post" action="{{ url('laporan-bank-urut-voucher-list') }}" autocomplete="off">
            @csrf
            {{-- Kategori --}}
            <div class="row mb-4">
                <label class="col-sm-2 col-form-label" for="filter-kategori">Kategori</label>
                <div class="col-sm-10">
                    <select id="filter-kategori" name="kategori" class="select2 form-select">
                        <option value="">Pilih Kategori</option>
                        <option value="Tanggal Voucher" @selected((@$datafilter['kategori'] ?? '') == 'Tanggal Voucher')>Tanggal Voucher</option>
                        <option value="Tanggal CH/BG" @selected((@$datafilter['kategori'] ?? '') == 'Tanggal CH/BG')>Tanggal CH/BG</option>
                        <option value="Tanggal Kliring" @selected((@$datafilter['kategori'] ?? '') == 'Tanggal Kliring')>Tanggal Kliring</option>
                    </select>
                </div>
            </div>

            {{-- Periode --}}
            <div class="row mb-4">
                <label class="col-sm-2 col-form-label">Periode</label>
                <div class="col-sm-10">
                    <div class="input-group">
                        <input type="text" id="filter-tgl-awal" name="tgl_awal" class="form-control dt-date" value="{{ @$datafilter['tgl_awal'] }}" data-default="{{ @$datafilter['tgl_awal'] }}" placeholder="Tanggal Awal" />
                        <span class="input-group-text">s/d</span>
                        <input type="text" id="filter-tgl-akhir" name="tgl_akhir" class="form-control dt-date" value="{{ @$datafilter['tgl_akhir'] }}" data-default="{{ @$datafilter['tgl_akhir'] }}" placeholder="Tanggal Akhir" />
                    </div>
                </div>
            </div>

            {{-- Bank --}}
            <div class="row mb-4">
                <label class="col-sm-2 col-form-label" for="filter-bank">Bank</label>
                <div class="col-sm-10">
                    <select id="filter-bank" name="kode_bank" class="select2 form-select">
                        <option value="">Pilih Bank</option>
                        @foreach ($banks as $bank)
                            <option value="{{ $bank->kode_bank }}" @selected((@$datafilter['kode_bank'] ?? '') == $bank->kode_bank)>
                                {{ $bank->nama_bank }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Urut 1, 2, 3 --}}
            <div class="row mb-4">
                <label class="col-sm-2 col-form-label">Urutan</label>
                <div class="col-sm-10">
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label">Urut 1</label>
                            <select name="urut1" class="select2 form-select">
                                <option value="tgl_voucher" @selected((@$datafilter['urut1'] ?? 'tgl_voucher') == 'tgl_voucher')>Tgl. Voucher</option>
                                <option value="voucher_masuk" @selected((@$datafilter['urut1'] ?? '') == 'voucher_masuk')>Voucher Masuk
                                </option>
                                <option value="voucher_keluar" @selected((@$datafilter['urut1'] ?? '') == 'voucher_keluar')>Voucher Keluar
                                </option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Urut 2</label>
                            <select name="urut2" class="select2 form-select">
                                <option value="tgl_voucher" @selected((@$datafilter['urut2'] ?? '') == 'tgl_voucher')>Tgl. Voucher</option>
                                <option value="voucher_masuk" @selected((@$datafilter['urut2'] ?? '') == 'voucher_masuk')>Voucher Masuk
                                </option>
                                <option value="voucher_keluar" @selected((@$datafilter['urut2'] ?? 'voucher_keluar') == 'voucher_keluar')>Voucher Keluar
                                </option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Urut 3</label>
                            <select name="urut3" class="select2 form-select">
                                <option value="tgl_voucher" @selected((@$datafilter['urut3'] ?? '') == 'tgl_voucher')>Tgl. Voucher</option>
                                <option value="voucher_masuk" @selected((@$datafilter['urut3'] ?? 'voucher_masuk') == 'voucher_masuk')>Voucher Masuk
                                </option>
                                <option value="voucher_keluar" @selected((@$datafilter['urut3'] ?? '') == 'voucher_keluar')>Voucher Keluar
                                </option>
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


@if (session('datafilter'))
<div class="row mt-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Laporan {{ $title }}</h5>
        </div>
        <div class="demo-inline-spacing">
          <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
          <button type="button" class="btn btn-primary btn-print">Print</button>
        </div>
      </div>
      <div class="card-datatable text-nowrap">
        <table class="datatables-bank-urut-voucher table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
          <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Voucher</th>
                <th>Tanggal CH BG</th>
                <th>Pelanggan &amp; Memo</th>
                <th>No. CH BG</th>
                <th>Voucher Masuk</th>
                <th>Voucher Keluar</th>
                <th>Debit</th>
                <th>Kredit</th>
                <th>Saldo</th>
                <th>Tanggal Kliring</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
                <th colspan="7" class="text-end">TOTAL</th>
                <th id="footer-debit" class="text-end">0</th>
                <th id="footer-kredit" class="text-end">0</th>
                <th id="footer-saldo" class="text-end">0</th>
                <th></th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>
@endif



@endsection
