@extends('layouts/layoutMaster')

@section('title', $title)

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss'
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
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'
])
@endsection

<!-- Page Scripts -->
@section('page-script')
{{-- @vite([
  'resources/assets/js/pages-profile-user.js'
]) --}}
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
    <div class="col-xl-4 col-lg-5 col-md-5">
      <!-- About User -->
      <div class="card mb-6">
        <div class="card-header align-items-center">
          <h5 class="card-action-title mb-0">Profil</h5>
        </div>
        <div class="card-body">
          <ul class="list-unstyled my-3 py-1">
            <li class="d-flex align-items-center mb-4">
              <i class="icon-base ri ri-user-3-line icon-24px"></i>
              <span class="fw-medium mx-2">Nama Pengguna:</span> 
              <span>{{ $users->username }}</span>
            </li>
            <li class="d-flex align-items-center mb-4">
              <i class="icon-base ri ri-flag-2-line icon-24px"></i>
              <span class="fw-medium mx-2">Nama Lengkap:</span> <span>{{ $users->name }}</span>
            </li>
            <li class="d-flex align-items-center mb-4">
              <i class="icon-base ri ri-check-line icon-24px"></i>
              <span class="fw-medium mx-2">Status:</span> 
              @if ($users->status == "Y")
              <span class="badge bg-label-success rounded-pill">Aktif</span>
              @else
              <span class="badge bg-label-danger rounded-pill">Tidak Aktif</span>
              @endif
            </li>
            <li class="d-flex align-items-center mb-2">
              <i class="icon-base ri ri-mail-open-line icon-24px"></i>
              <span class="fw-medium mx-2">Email:</span> <span>{{ $users->email }}</span>
            </li>
          </ul>
        </div>
      </div>
      <!--/ About User -->
    </div>
    <div class="col-xl-8 col-lg-7 col-md-7">
      <div class="row">
        <!-- Group Akses -->
        <div class="col-lg-12 col-xl-6">
          <div class="card card-action mb-6">
            <div class="card-header align-items-center">
              <h5 class="card-action-title mb-0">Group Akses</h5>
            </div>
            <div class="card-body">
              <ul class="list-unstyled mb-0">
                @foreach($group_akses as $row)
                <li class="mb-4">
                  <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center">
                      <div class="me-2">
                        <h6 class="mb-1">{{ $row->nama }}</h6>
                        <small>{{ $row->keterangan }}</small>
                      </div>
                    </div>
                    <div class="ms-auto">
                      <button class="btn btn-primary btn-icon"><i class="icon-base ri ri-user-3-line icon-22px"></i></button>
                    </div>
                  </div>
                </li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
        <!--/ Group Akses -->
        <!-- Cabang Akses -->
        <div class="col-lg-12 col-xl-6">
          <div class="card card-action mb-6">
            <div class="card-header align-items-center">
              <h5 class="card-action-title mb-0">Cabang Akses</h5>
            </div>
            <div class="card-body">
              <ul class="list-unstyled mb-0">
                @foreach($cabang_akses as $row)
                <li class="mb-4">
                  <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center">
                      <div class="me-2">
                        <h6 class="mb-1">{{ $row->nama_cabang }}</h6>
                        <small>{{ $row->kode_cabang }}</small>
                      </div>
                    </div>
                    <div class="ms-auto">
                      <button class="btn btn-outline-primary btn-icon"><i class="icon-base ri ri-reactjs-line icon-22px"></i></button>
                    </div>
                  </div>
                </li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
        <!--/ Cabang Akses -->
      </div>
    </div>
  </div>
  <!--/ User Profile Content -->
@endsection