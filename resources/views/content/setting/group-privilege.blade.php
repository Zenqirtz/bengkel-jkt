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
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
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
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
  ])
@endsection


<!-- Page Scripts -->
@section('page-script')
  <script src="{{ asset('assets/js/group-privilege.js') }}"></script>

  <script>
    // ganti 'user-privilege' sesuai route GET yang menampilkan halaman index
    const USER_PRIV_INDEX_URL = @json(url('group-privilege-list'));
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
      <form name="addRoleForm" id="addRoleForm" method="post" action="{{ url('group-privilege-list') }}" autocomplete="off">
      {{-- <form id="addRoleForm" class="row g-3" onsubmit="return false"> --}}
      @csrf
      <input type="hidden" name="btnSimpan" value="simpan">
      <div class="row form-control-validation mb-4">
        <label class="col-sm-2 col-form-label" for="add-group">Nama Group</label>
        <div class="col-sm-10">
          <select id="add-group" name="groupid" class="select2 form-select" data-allow-clear="true" data-add="{{ $isAdd }}">
            <option value="">Pilih Nama Group</option>
            @foreach($data_groups as $row)
              <option value="{{ $row->id }}" @if(old('groupid', $groupid) == $row->id) selected @endif>{{ $row->nama }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <hr>
      <div class="col-12">
        <h5 class="mb-6">Role Permissions</h5>
        <!-- Permission table -->
        <div class="table-responsive">
          <table class="table table-flush-spacing">
            <tbody>
              <tr>
                <td class="text-nowrap fw-medium">
                  Administrator Access
                  <i class="icon-base ri ri-information-line icon-sm" data-bs-toggle="tooltip"
                    data-bs-placement="top" title="Allows a full access to the system"></i>
                </td>
                <td>
                  <div class="d-flex justify-content-end">
                    <div class="form-check mb-0 mt-1">
                      <input class="form-check-input" type="checkbox" id="selectAll" name="selectAll" value="1" />
                      <label class="form-check-label" for="selectAll"> Select All </label>
                    </div>
                  </div>
                </td>
              </tr>
              @foreach($data_menus as $row)
              <tr>
                @if($row->level == '1') 
                <td class="text-nowrap fw-medium">&nbsp;&nbsp;&nbsp; - {{ $row->title }}</td>
                @elseif($row->level == '2') 
                <td class="text-nowrap fw-medium">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - {{ $row->title }}</td>
                @else
                <td class="text-nowrap fw-medium">{{ $row->title }}</td>
                @endif
                <td>
                  <div class="d-flex justify-content-end">
                    <div class="form-check mb-0 mt-1 me-4 me-lg-12">
                      <input class="form-check-input" type="checkbox" id="{{ $row->title }}Lihat" name="frmChk[{{ $row->id }}][lihat]" value="1" @if($row->isList == '1') checked @endif />
                      <label class="form-check-label" for="{{ $row->title }}Lihat"> Lihat </label>
                    </div>
                    <div class="form-check mb-0 mt-1 me-4 me-lg-12">
                      <input class="form-check-input" type="checkbox" id="{{ $row->title }}Tambah" name="frmChk[{{ $row->id }}][tambah]" value="1" @if($row->isAdd == '1') checked @endif />
                      <label class="form-check-label" for="{{ $row->title }}Tambah"> Tambah </label>
                    </div>
                    <div class="form-check mb-0 mt-1 me-4 me-lg-12">
                      <input class="form-check-input" type="checkbox" id="{{ $row->title }}Ubah" name="frmChk[{{ $row->id }}][ubah]" value="1" @if($row->isEdit == '1') checked @endif />
                      <label class="form-check-label" for="{{ $row->title }}Ubah"> Ubah </label>
                    </div>
                    <div class="form-check mb-0 mt-1">
                      <input class="form-check-input" type="checkbox" id="{{ $row->title }}Hapus" name="frmChk[{{ $row->id }}][hapus]" value="1" @if($row->isDelete == '1') checked @endif />
                      <label class="form-check-label" for="{{ $row->title }}Hapus"> Hapus </label>
                    </div>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <!-- Permission table -->
      </div>
      <div class="col-12 mt-4">
        <button type="submit" class="btn btn-primary me-3 data-submit">Simpan</button>
      </div>
      </form>
    </div>
  </div>
</div>
@endsection
