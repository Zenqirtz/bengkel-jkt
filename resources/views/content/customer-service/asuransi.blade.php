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
  <script src="{{ asset('assets/js/asuransi.js') }}"></script>
@endsection

@section('content')
  <!-- Users List Table -->
  <div class="card">
    <div class="card-datatable">
      <table class="datatables-asuransi table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
        <thead>
          <tr>
            <th></th>
            <th>No</th>
            <th>Nama Customer</th>
            <th>Jenis Customer</th>
            <th>Telepon</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
      </table>
    </div>
    <!-- Offcanvas to add new user -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAdd" data-bs-backdrop="static" aria-labelledby="offcanvasAddLabel">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasAddLabel" class="offcanvas-title">{{ $title }}</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100">
        <form class="add-new-user pt-0" id="addNewDataForm">
          <input type="hidden" name="id" id="user_id">
          <input type="hidden" name="kode_cabang" id="kode_cabang" value="{{ $user_cabang }}">
          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="text" class="form-control is-invalid" id="add-nama" name="nama_pelanggan" placeholder="Nama Customer" maxlength="50" />
            <label for="add-nama">Nama Customer</label>
          </div>
          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <select id="add-jenis" name="kode_jenis_pelanggan" class="select2 form-select is-invalid">
                <option value="">Select Jenis Customer</option>
                @foreach($jenis_pelanggan as $row)
                  <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                @endforeach
            </select>
            <label for="add-jenis">Jenis Customer</label>
          </div>
          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="text" class="form-control phone-mask" id="add-telepon" name="telepon" placeholder="Telepon Selular" maxlength="15" />
            <label for="add-telepon">Telepon Selular</label>
          </div>
          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <select id="add-status" name="is_active" class="select2 form-select is-invalid">
                <option value="">Select Status</option>
                @foreach($status_aktif as $row)
                  <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                @endforeach
            </select>
            <label for="add-status">Status</label>
          </div>
          <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Simpan</button>
          <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="offcanvas">Batal</button>
        </form>
      </div>
    </div>
  </div>

@endsection
