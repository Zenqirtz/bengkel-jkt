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
  <script src="{{ asset('assets/js/user-privilege.js') }}"></script>

  <script>
    // ganti 'user-privilege' sesuai route GET yang menampilkan halaman index
    const USER_PRIV_INDEX_URL = @json(url('user-privilege-list'));
  </script>
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
<div class="col-12">
  <div class="card">
    <h5 class="card-header">{{ $title }}</h5>
    <div class="card-body">
      <form name="myForm" id="myForm" method="post" action="{{ url('user-privilege-list') }}" autocomplete="off">
      @csrf
      <div class="row mb-4">
        <label class="col-sm-2 col-form-label" for="add-user">Nama User</label>
        <div class="col-sm-10">
          <select id="add-user" name="userid" class="select2 form-select" data-allow-clear="true" data-add="{{ $isAdd }}">
            <option value="">Pilih Nama User</option>
            @foreach($data_users as $row)
              <option value="{{ $row->id }}" @if(old('userid', $userid) == $row->id) selected @endif>{{ $row->username }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <hr>
      <div class="row">
        <div class="col-lg-5 p-6 pt-0">
          <small class="fw-medium">Daftar Hak Akses Yang Dimiliki User</small>
          <div class="demo-inline-spacing mt-4">
            <div class="list-group">
              <label class="list-group-item">
                <span class="form-check mb-0">
                  {{-- <input class="form-check-input me-1" type="checkbox" value="" /> --}}
                  NAMA GROUP
                </span>
              </label>
              @foreach($data_user_groups as $row)
              <label class="list-group-item">
                <span class="form-check mb-0">
                  <input class="form-check-input me-1" type="checkbox" name="group[]" value="{{ $row->id }}" />
                  {{ $row->nama }}
                </span>
              </label>
              @endforeach
            </div>
          </div>
        </div>
        <div class="col-lg-1 p-6 pt-0 mt-5">
          <div class="demo-inline-spacing">
            <button type="submit" class="btn btn-icon btn-primary" name="btnSimpan" value="diselected">
              <span class="icon-base ri ri-arrow-right-double-fill icon-22px"></span>
            </button>
          </div>
          <div class="demo-inline-spacing">
            <button type="submit" class="btn btn-icon btn-success" name="btnSimpan" value="selected">
              <span class="icon-base ri ri-arrow-left-double-fill icon-22px"></span>
            </button>
          </div>
        </div>
        <div class="col-lg-6 p-6 pt-0">
          <small class="fw-medium">Daftar Hak Akses Yang Ada Pada Sistem</small>
          <div class="demo-inline-spacing mt-4">
            <div class="list-group">
              <label class="list-group-item">
                <span class="form-check mb-0">
                  {{-- <input class="form-check-input me-1" type="checkbox" value="" /> --}}
                  NAMA GROUP
                </span>
              </label>
              @foreach($data_groups as $row)
              <label class="list-group-item">
                <span class="form-check mb-0">
                  <input class="form-check-input me-1" type="checkbox" name="group2[]" value="{{ $row->id }}" />
                  {{ $row->nama }}
                </span>
              </label>
              @endforeach
            </div>
          </div>
        </div>
      </div>
      </form>
    </div>
  </div>
</div>
@endsection
