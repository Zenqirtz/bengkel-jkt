@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;
@endphp

{{-- Config + Script Notifikasi --}}
@auth
    <span id="notif-config" class="d-none" data-url="{{ route('notifikasi.index') }}"
        data-mark-read-url="{{ url('notifikasi/:id/read') }}" data-mark-all-url="{{ route('notifikasi.readAll') }}"
        data-csrf="{{ csrf_token() }}">
    </span>
    <script src="{{ asset('assets/js/notifikasi.js') }}"></script>
@endauth


@auth
    <span id="manual-book-config" class="d-none" data-cek-url="{{ route('manual-book.cek') }}"
        data-store-url="{{ route('manual-book.store') }}" data-destroy-url="{{ route('manual-book.destroy') }}"
        data-flipbook-url="{{ route('manual-book.flipbook') }}" data-csrf="{{ csrf_token() }}">
    </span>
    <script src="{{ asset('assets/js/manual-book.js') }}"></script>
@endauth

<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if (isset($navbarFull))
    <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-6">
        <a href="{{ url('/') }}" class="app-brand-link gap-2">
            <span class="app-brand-logo demo">@include('_partials.macros')</span>
            <span class="app-brand-text demo menu-text fw-semibold ms-1">{{ config('variables.templateName') }}</span>
        </a>

        <!-- Display menu close icon only for horizontal-menu with navbar-full -->
        @if (isset($menuHorizontal))
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
                <i class="icon-base ri ri-close-line icon-sm"></i>
            </a>
        @endif
    </div>
@endif

<!-- ! Not required for layout-without-menu -->
@if (!isset($navbarHideToggle))
    <div
        class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 {{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="icon-base ri ri-menu-line icon-md"></i>
        </a>
    </div>
@endif

<div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">

    @if (!isset($menuHorizontal))
        <!-- Search -->
        <div class="navbar-nav align-items-center">
            <div class="nav-item navbar-search-wrapper mb-0">
                <a class="nav-item nav-link search-toggler px-0" href="javascript:void(0);">
                    <span class="d-inline-block text-body-secondary fw-normal" id="autocomplete"
                        data-placeholder="{{ session('nm_cabang') ? session('nm_cabang') : 'Pilih Cabang' }}"></span>
                </a>
            </div>
        </div>
        <!-- /Search -->
    @endif

    <ul class="navbar-nav flex-row align-items-center ms-md-auto">
        @if (isset($menuHorizontal))
            <!-- Search -->
            <li class="nav-item navbar-search-wrapper mb-0">
                <a class="nav-item nav-link search-toggler px-0" href="javascript:void(0);">
                    <span class="d-inline-block text-body-secondary fw-normal" id="autocomplete"
                        data-placeholder="{{ session('nm_cabang') ? session('nm_cabang') : 'Pilih Cabang' }}"></span>
                </a>
            </li>
            <!-- /Search -->
        @endif

        @if ($configData['hasCustomizer'] == true)
            <!-- Style Switcher -->
            <li class="nav-item dropdown me-sm-2 me-xl-0">
                <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                    id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <i class="icon-base ri ri-sun-line icon-22px theme-icon-active"></i>
                    <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
                    <li>
                        <button type="button" class="dropdown-item align-items-center active"
                            data-bs-theme-value="light" aria-pressed="false">
                            <span><i class="icon-base ri ri-sun-line icon-22px me-3"
                                    data-icon="sun-line"></i>Light</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark"
                            aria-pressed="true">
                            <span><i class="icon-base ri ri-moon-clear-line icon-22px me-3"
                                    data-icon="moon-clear-line"></i>Dark</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system"
                            aria-pressed="false">
                            <span><i class="icon-base ri ri-computer-line icon-22px me-3"
                                    data-icon="computer-line"></i>System</span>
                        </button>
                    </li>
                </ul>
            </li>
            <!-- / Style Switcher-->
        @endif

        <!-- Quick links  -->
        {{-- <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-sm-2 me-xl-0"> --}}
        <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-2">
            <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <i class="icon-base ri ri-star-smile-line icon-22px"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end p-0">
                <div class="dropdown-menu-header border-bottom">
                    <div class="dropdown-header d-flex align-items-center py-3">
                        <h6 class="mb-0 me-auto">Shortcuts</h6>
                        <a href="javascript:void(0)"
                            class="btn btn-text-secondary rounded-pill btn-icon dropdown-shortcuts-add text-heading"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Add shortcuts"> <i
                                class="icon-base ri ri-add-line text-heading"></i> </a>
                    </div>
                </div>
                <div class="dropdown-shortcuts-list scrollable-container">
                    <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                <i class="icon-base ri ri-calendar-line icon-26px text-heading"></i>
                            </span>
                            <a href="{{ url('customer-service/input-data-pemilik') }}"
                                class="stretched-link">PEMILIK</a>
                            {{-- <small>Appointments</small> --}}
                        </div>
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                <i class="icon-base ri ri-police-car-fill icon-26px text-heading"></i>
                            </span>
                            <a href="{{ url('customer-service/input-data-kendaraan') }}"
                                class="stretched-link">MOBIL</a>
                            {{-- <small>Manage Accounts</small> --}}
                        </div>
                    </div>
                    <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                <i class="icon-base ri ri-file-list-3-fill icon-26px text-heading"></i>
                            </span>
                            <a href="{{ url('customer-service/spk') }}" class="stretched-link">SPK</a>
                            {{-- <small>Manage Users</small> --}}
                        </div>
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                <i class="icon-base ri ri-computer-line icon-26px text-heading"></i>
                            </span>
                            <a href="{{ url('administrasi/estimasi') }}" class="stretched-link">ESTIMASI</a>
                            {{-- <small>Permission</small> --}}
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <!-- Quick links -->

        {{-- <!-- Notification -->
        <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-4 me-xl-1">
            <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                aria-expanded="false">
                <i class="icon-base ri ri-notification-2-line icon-22px"></i>
                <span
                    class="position-absolute top-0 start-50 translate-middle-y badge badge-dot bg-danger mt-2 border"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end py-0">
                <li class="dropdown-menu-header border-bottom py-50">
                    <div class="dropdown-header d-flex align-items-center py-2">
                        <h6 class="mb-0 me-auto">Notification</h6>
                        <div class="d-flex align-items-center h6 mb-0">
                            <span class="badge rounded-pill bg-label-primary fs-xsmall me-2">0 New</span>
                            <a href="javascript:void(0)" class="dropdown-notifications-all p-2"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Mark all as read"><i
                                    class="icon-base ri ri-mail-open-line text-heading"></i> </a>
                        </div>
                    </div>
                </li>
                <li class="dropdown-notifications-list scrollable-container">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item list-group-item-action dropdown-notifications-item">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar">
                                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt="avatar"
                                            class="rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="small mb-1">Congratulation Lettie 🎉</h6>
                                    <small class="mb-1 d-block text-body">Won the monthly best seller gold
                                        badge</small>
                                    <small class="text-body-secondary">1h ago</small>
                                </div>
                                <div class="flex-shrink-0 dropdown-notifications-actions">
                                    <a href="javascript:void(0)" class="dropdown-notifications-read"><span
                                            class="badge badge-dot"></span></a>
                                    <a href="javascript:void(0)" class="dropdown-notifications-archive"><span
                                            class="icon-base ri ri-close-line"></span></a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </li>
                <li class="border-top">
                    <div class="d-grid p-4">
                        <a class="btn btn-primary btn-sm d-flex" href="javascript:void(0);">
                            <small class="align-middle">View all notifications</small>
                        </a>
                    </div>
                </li>
            </ul>
        </li>
        <!--/ Notification --> --}}
        <!-- Notification -->
        {{-- <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-4 me-xl-1" style="overflow: visible;"> --}}
        <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-2" style="overflow: visible;">
            <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                aria-expanded="false">
                <i class="icon-base ri ri-notification-2-line icon-22px"></i>
                <span id="notif-dot"
                    class="position-absolute top-0 start-50 translate-middle-y badge badge-dot bg-danger mt-2 border d-none"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end py-0">
                <li class="dropdown-menu-header border-bottom py-50">
                    <div class="dropdown-header d-flex align-items-center py-2">
                        <h6 class="mb-0 me-auto">Notifikasi</h6>
                        <div class="d-flex align-items-center h6 mb-0">
                            <span class="badge rounded-pill bg-label-primary fs-xsmall me-2" id="notif-count">0
                                Baru</span>
                            <a href="javascript:void(0)" class="dropdown-notifications-all p-2" id="btn-read-all"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Baca semua">
                                <i class="icon-base ri ri-mail-open-line text-heading"></i>
                            </a>
                        </div>
                    </div>
                </li>
                <li class="dropdown-notifications-list scrollable-container">
                    <ul class="list-group list-group-flush" id="notif-list">
                        {{-- Akan diisi via JavaScript --}}
                    </ul>
                </li>
                <li class="border-top">
                    <div class="d-grid p-4">
                        <a class="btn btn-primary btn-sm d-flex" href="{{ route('notifikasi.semua') }}">
                            <small class="align-middle">Lihat semua notifikasi</small>
                        </a>
                    </div>
                </li>
            </ul>
        </li>
        <!--/ Notification -->
        {{-- Manual Book --}}
        <li class="nav-item dropdown-manual-book navbar-dropdown dropdown me-2" style="overflow: visible;">
            <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                aria-expanded="false" title="Manual Book">
                <i class="icon-base ri ri-question-line icon-22px"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end p-3" id="manual-book-dropdown">
                <div id="manual-book-content"></div>
            </div>
        </li>
        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                    <img src="{{ isset(Auth::user()->profile_photo_url) ? sprintf('%s/%s', asset('assets/img/avatars/'), Auth::user()->profile_photo_url) : asset('assets/img/avatars/1.png') }}"
                        alt="avatar" class="rounded-circle" />
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end mt-3 py-2">
                <li>
                    <a class="dropdown-item"
                        href="{{ Route::has('profile.show') ? route('profile.show') : url('pages/profile-user') }}">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-2">
                                <div class="avatar avatar-online">
                                    <img src="{{ Auth::user()->profile_photo_url ? sprintf('%s/%s', asset('assets/img/avatars/'), Auth::user()->profile_photo_url) : asset('assets/img/avatars/1.png') }}"
                                        alt="alt" class="w-px-40 h-auto rounded-circle" />
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 small">
                                    @if (Auth::check())
                                        {{ Auth::user()->name }}
                                    @else
                                        John Doe
                                    @endif
                                </h6>
                                <small class="text-body-secondary">Admin</small>
                            </div>
                        </div>
                    </a>
                </li>
                <li>
                    <div class="dropdown-divider"></div>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ url('akun/profil') }}">
                        <i class="icon-base ri ri-user-3-line icon-22px me-2"></i> <span class="align-middle">Lihat
                            Profil</span> </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ url('akun/ubah-sandi') }}">
                        <i class="icon-base ri ri-lock-2-line icon-22px me-2"></i> <span class="align-middle">Rubah
                            Sandi</span> </a>
                </li>
                <li>
                    <div class="dropdown-divider my-1"></div>
                </li>
                @if (Auth::check())
                    <li>
                        <div class="d-grid px-4 pt-2 pb-1">
                            {{-- <a class="btn btn-danger d-flex" href="{{ route('logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <small class=" align-middle">Keluar</small>
                <i class="icon-base ri ri-logout-box-r-line ms-2 icon-16px"></i>
              </a> --}}
                            <a class="btn btn-danger d-flex" href="{{ route('logout2') }}">
                                <small class=" align-middle">Keluar</small>
                                <i class="icon-base ri ri-logout-box-r-line ms-2 icon-16px"></i>
                            </a>
                        </div>
                    </li>
                    {{-- <form method="POST" id="logout-form" action="{{ route('logout') }}">
            @csrf
          </form> --}}
                @else
                    <li>
                        <div class="d-grid px-4 pt-2 pb-1">
                            <a class="btn btn-danger d-flex"
                                href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}">
                                <small class="align-middle">Masuk</small>
                                <i class="icon-base ri ri-logout-box-r-line ms-2 icon-16px"></i>
                            </a>
                        </div>
                    </li>
                @endif
            </ul>
        </li>
        <!--/ User -->
    </ul>
</div>
