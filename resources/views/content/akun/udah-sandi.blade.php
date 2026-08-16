@extends('layouts/layoutMaster')

@section('title', $title)

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/animate-css/animate.scss', 
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
@endsection

<!-- Page Styles -->
@section('page-style')
@vite([
  'resources/assets/vendor/scss/pages/page-profile.scss'
])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js'
])
@endsection

<!-- Page Scripts -->
@section('page-script')
<script src="{{ asset('assets/js/ubah-sandi.js') }}"></script>
@endsection

@section('content')
  <!-- Header -->
  <div class="row">
    <div class="col-12">
      <div class="card mb-6">
        <div class="user-profile-header-banner">
          <img src="{{ asset('assets/img/profile-banner.png') }}" alt="Banner image" class="rounded-top" />
        </div>
        <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-5">
          <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
            <img src="{{ $users->profile_photo_url ? sprintf('%s/%s',asset('assets/img/avatars/'),$users->profile_photo_url) : asset('assets/img/avatars/1.png') }}" alt="user image"
              class="d-block h-auto ms-0 ms-sm-5 rounded-4 user-profile-img" />
          </div>
          <div class="flex-grow-1 mt-4 mt-sm-12">
            <div
              class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-6">
              <div class="user-profile-info">
                <h4 class="mb-2">{{ $users->name }}</h4>
                <ul
                  class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4">
                  <li class="list-inline-item"><i class="icon-base ri ri-map-pin-line me-2 icon-24px"></i><span
                      class="fw-medium">{{ $nama_cabang }}</span></li>
                  <li class="list-inline-item"><i class="icon-base ri ri-calendar-line me-2 icon-24px"></i><span
                      class="fw-medium"> Dibuat {{ date("d F Y", strtotime($users->created_at)) }}</span></li>
                </ul>
              </div>
              {{-- <a href="javascript:void(0)" class="btn btn-primary"> <i
                  class="icon-base ri ri-user-follow-line icon-16px me-2"></i>Connected </a> --}}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Header -->

  <!-- User Profile Content -->
  <div class="row">
    <div class="col-xl-12 col-lg-12 col-md-7">
      <!-- Rubah Sandi -->
      <div class="card card-action mb-6">
        <div class="card-header align-items-center">
          <h5 class="card-action-title mb-0">Rubah Sandi</h5>
        </div>
        <div class="card-body">
          <form id="formChangePassword" method="POST">
            <div class="row gx-5">
              <div class="mb-3 col-12 col-sm-6 form-password-toggle form-control-validation">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input class="form-control" type="password" id="newPassword" name="newPassword"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                    <label for="newPassword">Sandi Baru</label>
                  </div>
                  <span class="input-group-text cursor-pointer"><i
                      class="icon-base ri ri-eye-off-line icon-20px"></i></span>
                </div>
              </div>
              <div class="mb-3 col-12 col-sm-6 form-password-toggle form-control-validation">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input class="form-control" type="password" name="confirmPassword" id="confirmPassword"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                    <label for="confirmPassword">Ulangi Sandi Baru</label>
                  </div>
                  <span class="input-group-text cursor-pointer"><i
                      class="icon-base ri ri-eye-off-line icon-20px"></i></span>
                </div>
              </div>
              <div>
                <button type="submit" class="btn btn-primary me-2">Simpan</button>
              </div>
            </div>
          </form>
        </div>
      </div>
      <!--/ Rubah Sandi -->
    </div>
  </div>
  <!--/ User Profile Content -->
@endsection