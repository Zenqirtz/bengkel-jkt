@extends('layouts/layoutMaster')

@section('title', $title)

<!-- Vendor Styles -->
@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
  @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss', 'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleave-zen/cleave-zen.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
  @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js', 'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js', 'resources/assets/vendor/libs/pickr/pickr.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
  <script src="{{ asset('assets/js/backup-db.js') }}"></script>
@endsection

@section('content')
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
  <!-- Users List Table -->
  <div class="card">
    <div class="card-datatable">
      <table class="datatables-backup table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
        <thead>
          <tr>
            <th></th>
            <th>No</th>
            <th>Nama File</th>
            <th>Ukuran File</th>
            <th>Tanggal Backup</th>
            <th>Aksi</th>
          </tr>
        </thead>
      </table>
    </div>

@endsection
