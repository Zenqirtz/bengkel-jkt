@extends('layouts/layoutMaster')

@section('title', 'Dashboard')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss'
])
@endsection

@section('page-style')
@vite('resources/assets/vendor/scss/pages/app-logistics-dashboard.scss')
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/apex-charts/apexcharts.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'
])
@endsection

@section('page-script')
@vite('resources/assets/js/app-logistics-dashboard.js')
@endsection


@section('content')
<!-- Card Border Shadow - 5 Metrics -->
<div class="row g-6 mb-6">
  <div class="col-sm-6 col-lg-3 col-xl">
    <div class="card card-border-shadow-primary h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded-3 bg-label-primary"><i
                class="icon-base ri ri-file-list-3-line icon-24px"></i></span>
          </div>
          <h4 class="mb-0">Rp 45.8jt</h4>
        </div>
        <h6 class="mb-0 fw-normal">Invoice Terbit OR & Asuransi</h6>
        <p class="mb-0">
          <span class="me-1 fw-medium">+18.2%</span>
          <small class="text-body-secondary">dibanding minggu lalu</small>
        </p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3 col-xl">
    <div class="card card-border-shadow-info h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded-3 bg-label-info"><i
                class="icon-base ri ri-car-line icon-24px"></i></span>
          </div>
          <h4 class="mb-0">18</h4>
        </div>
        <h6 class="mb-0 fw-normal">Jumlah Mobil Masuk</h6>
        <p class="mb-0">
          <span class="me-1 fw-medium">+12.0%</span>
          <small class="text-body-secondary">dibanding minggu lalu</small>
        </p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3 col-xl">
    <div class="card card-border-shadow-success h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded-3 bg-label-success"><i
                class="icon-base ri ri-check-double-line icon-24px"></i></span>
          </div>
          <h4 class="mb-0">12</h4>
        </div>
        <h6 class="mb-0 fw-normal">Jumlah Mobil Selesai</h6>
        <p class="mb-0">
          <span class="me-1 fw-medium">+8.0%</span>
          <small class="text-body-secondary">dibanding minggu lalu</small>
        </p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3 col-xl">
    <div class="card card-border-shadow-warning h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded-3 bg-label-warning"><i
                class="icon-base ri ri-money-dollar-circle-line icon-24px"></i></span>
          </div>
          <h4 class="mb-0">Rp 12.5jt</h4>
        </div>
        <h6 class="mb-0 fw-normal">Piutang Jatuh Tempo</h6>
        <p class="mb-0">
          <span class="me-1 fw-medium">8 Tagihan</span>
          <small class="text-body-secondary">perlu ditagih segera</small>
        </p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3 col-xl">
    <div class="card card-border-shadow-danger h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded-3 bg-label-danger"><i
                class="icon-base ri ri-arrow-left-up-line icon-24px"></i></span>
          </div>
          <h4 class="mb-0">Rp 8.2jt</h4>
        </div>
        <h6 class="mb-0 fw-normal">Hutang Jatuh Tempo</h6>
        <p class="mb-0">
          <span class="me-1 fw-medium">5 Tagihan</span>
          <small class="text-body-secondary">perlu dibayar segera</small>
        </p>
      </div>
    </div>
  </div>
</div>
<!--/ Card Border Shadow -->

<!-- Baris 2: Antrian Servis (kiri) & Statistik Pendapatan (kanan) -->
<div class="row g-6 mb-6">
  <!-- Antrian Servis -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Antrian Servis</h5>
        </div>
        <div class="dropdown">
          <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-1" type="button"
            id="deliveryExceptions" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="icon-base ri ri-more-2-line"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="deliveryExceptions">
            <a class="dropdown-item" href="javascript:void(0);">Pilih Semua</a>
            <a class="dropdown-item" href="javascript:void(0);">Muat Ulang</a>
            <a class="dropdown-item" href="javascript:void(0);">Bagikan</a>
          </div>
        </div>
      </div>
      <div class="card-body pb-2">
        <div class="d-none d-lg-flex vehicles-progress-labels mb-5">
          <div class="vehicles-progress-label on-the-way-text" style="width: 39.7%;">Dalam Proses</div>
          <div class="vehicles-progress-label unloading-text" style="width: 28.3%;">Menunggu Sparepart</div>
          <div class="vehicles-progress-label loading-text" style="width: 17.4%;">Estimasi</div>
          <div class="vehicles-progress-label waiting-text" style="width: 14.6%;">Selesai</div>
        </div>
        <div class="vehicles-overview-progress progress rounded-4 bg-transparent mb-2" style="height: 46px;">
          <div class="progress-bar small fw-medium text-start rounded-start bg-lightest text-heading px-1 px-lg-4"
            role="progressbar" style="width: 39.7%" aria-valuenow="39.7" aria-valuemin="0" aria-valuemax="100">39.7%
          </div>
          <div class="progress-bar small fw-medium text-start bg-primary px-1 px-lg-4" role="progressbar"
            style="width: 28.3%" aria-valuenow="28.3" aria-valuemin="0" aria-valuemax="100">28.3%</div>
          <div class="progress-bar small fw-medium text-start text-bg-info px-1 px-lg-4" role="progressbar"
            style="width: 17.4%" aria-valuenow="17.4" aria-valuemin="0" aria-valuemax="100">17.4%</div>
          <div class="progress-bar small fw-medium text-start rounded-end bg-gray-900 px-1 px-lg-4" role="progressbar"
            style="width: 14.6%" aria-valuenow="14.6" aria-valuemin="0" aria-valuemax="100">14.6%</div>
        </div>
        <div class="table-responsive">
          <table class="table card-table">
            <tbody class="table-border-bottom-0">
              <tr>
                <td class="w-75 ps-0">
                  <div class="d-flex justify-content-start align-items-center">
                    <div class="me-2">
                      <i class="text-heading icon-base ri ri-gear-line icon-24px"></i>
                    </div>
                    <h6 class="mb-0 fw-normal">Dalam Proses</h6>
                  </div>
                </td>
                <td class="text-end pe-0 text-nowrap">
                  <h6 class="mb-0">2hr 10min</h6>
                </td>
                <td class="text-end pe-0 ps-6">
                  <span>39.7%</span>
                </td>
              </tr>
              <tr>
                <td class="w-75 ps-0">
                  <div class="d-flex justify-content-start align-items-center">
                    <div class="me-2">
                      <i class="text-heading icon-base ri ri-time-line icon-24px"></i>
                    </div>
                    <h6 class="mb-0 fw-normal">Menunggu Sparepart</h6>
                  </div>
                </td>
                <td class="text-end pe-0 text-nowrap">
                  <h6 class="mb-0">3hr 15min</h6>
                </td>
                <td class="text-end pe-0 ps-6">
                  <span>28.3%</span>
                </td>
              </tr>
              <tr>
                <td class="w-75 ps-0">
                  <div class="d-flex justify-content-start align-items-center">
                    <div class="me-2">
                      <i class="text-heading icon-base ri ri-file-list-3-line icon-24px"></i>
                    </div>
                    <h6 class="mb-0 fw-normal">Estimasi</h6>
                  </div>
                </td>
                <td class="text-end pe-0 text-nowrap">
                  <h6 class="mb-0">1hr 24min</h6>
                </td>
                <td class="text-end pe-0 ps-6">
                  <span>17.4%</span>
                </td>
              </tr>
              <tr>
                <td class="w-75 ps-0">
                  <div class="d-flex justify-content-start align-items-center">
                    <div class="me-2">
                      <i class="text-heading icon-base ri ri-check-double-line icon-24px"></i>
                    </div>
                    <h6 class="mb-0 fw-normal">Selesai</h6>
                  </div>
                </td>
                <td class="text-end pe-0 text-nowrap">
                  <h6 class="mb-0">5hr 19min</h6>
                </td>
                <td class="text-end pe-0 ps-6">
                  <span>14.6%</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistik Pendapatan -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2 mb-1">Statistik Pendapatan</h5>
          <p class="card-subtitle mb-0">Total pendapatan bulan ini Rp 128jt</p>
        </div>
        <div class="btn-group">
          <button type="button" class="btn btn-outline-primary">Januari</button>
          <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split"
            data-bs-toggle="dropdown" aria-expanded="false">
            <span class="visually-hidden">Toggle Dropdown</span>
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="javascript:void(0);">Januari</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">Februari</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">Maret</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">April</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">Mei</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">Juni</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">Juli</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">Agustus</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">September</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">Oktober</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">November</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">Desember</a></li>
          </ul>
        </div>
      </div>
      <div class="card-body">
        <div id="shipmentStatisticsChart"></div>
      </div>
    </div>
  </div>
</div>

<!-- Baris 3: Stok Sparepart | Distribusi Jenis Servis | Pelanggan Aktif -->
<div class="row g-6 mb-6">
  <!-- Stok Sparepart -->
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0">Stok Sparepart</h5>
        <button type="button" class="btn btn-primary btn-sm">
          <i class="icon-base ri ri-settings-3-line me-1"></i>
          Kelola Stok
        </button>
      </div>
      <div class="card-body p-0">
        <!-- 1. Oli Mesin -->
        <div class="d-flex align-items-start gap-2 p-2 mb-2 mx-3 rounded bg-label-success border border-success">
          <div class="flex-grow-1">
            <h6 class="mb-0 fw-normal small">Oli Mesin</h6>
            <div class="d-flex align-items-center gap-2">
              <span class="fw-medium">15</span>
              <small class="text-body-secondary">Min: 10 liter</small>
            </div>
          </div>
          <i class="icon-base ri ri-check-line text-success icon-16px"></i>
        </div>

        <!-- 2. Filter Oli -->
        <div class="d-flex align-items-start gap-2 p-2 mb-2 mx-3 rounded bg-label-warning border border-warning">
          <div class="flex-grow-1">
            <h6 class="mb-0 fw-normal small">Filter Oli</h6>
            <div class="d-flex align-items-center gap-2">
              <span class="fw-medium">8</span>
              <small class="text-body-secondary">Min: 15 pcs</small>
            </div>
            <a href="javascript:void(0)" class="small fw-medium text-warning mt-1 d-block">Order Sekarang →</a>
          </div>
          <i class="icon-base ri ri-alert-line text-warning icon-16px"></i>
        </div>

        <!-- 3. Busi -->
        <div class="d-flex align-items-start gap-2 p-2 mb-2 mx-3 rounded bg-label-danger border border-danger">
          <div class="flex-grow-1">
            <h6 class="mb-0 fw-normal small">Busi</h6>
            <div class="d-flex align-items-center gap-2">
              <span class="fw-medium">5</span>
              <small class="text-body-secondary">Min: 20 set</small>
            </div>
            <a href="javascript:void(0)" class="small fw-medium text-danger mt-1 d-block">Order Sekarang →</a>
          </div>
          <i class="icon-base ri ri-error-warning-line text-danger icon-16px"></i>
        </div>

        <!-- 4. Kampas Rem -->
        <div class="d-flex align-items-start gap-2 p-2 mb-2 mx-3 rounded bg-label-success border border-success">
          <div class="flex-grow-1">
            <h6 class="mb-0 fw-normal small">Kampas Rem</h6>
            <div class="d-flex align-items-center gap-2">
              <span class="fw-medium">25</span>
              <small class="text-body-secondary">Min: 10 set</small>
            </div>
          </div>
          <i class="icon-base ri ri-check-line text-success icon-16px"></i>
        </div>

        <!-- 5. Filter Udara -->
        <div class="d-flex align-items-start gap-2 p-2 mb-2 mx-3 rounded bg-label-success border border-success">
          <div class="flex-grow-1">
            <h6 class="mb-0 fw-normal small">Filter Udara</h6>
            <div class="d-flex align-items-center gap-2">
              <span class="fw-medium">12</span>
              <small class="text-body-secondary">Min: 10 pcs</small>
            </div>
          </div>
          <i class="icon-base ri ri-check-line text-success icon-16px"></i>
        </div>

        <!-- 6. Oli Transmisi -->
        <div class="d-flex align-items-start gap-2 p-2 mx-3 rounded bg-label-info border border-info mb-5">
          <div class="flex-grow-1">
            <h6 class="mb-0 fw-normal small">Oli Transmisi</h6>
            <div class="d-flex align-items-center gap-2">
              <span class="fw-medium">7</span>
              <small class="text-body-secondary">Min: 5 liter</small>
            </div>
            <a href="javascript:void(0)" class="small fw-medium text-info mt-1 d-block">Cek Stok →</a>
          </div>
          <i class="icon-base ri ri-gear-line text-info icon-16px"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Distribusi Jenis Servis -->
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Distribusi Jenis Servis</h5>
        </div>
        <div class="dropdown">
          <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-1" type="button"
            id="deliveryExceptionsReasons" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="icon-base ri ri-more-2-line"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="deliveryExceptionsReasons">
            <a class="dropdown-item" href="javascript:void(0);">Pilih Semua</a>
            <a class="dropdown-item" href="javascript:void(0);">Muat Ulang</a>
            <a class="dropdown-item" href="javascript:void(0);">Bagikan</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="deliveryExceptionsChart"></div>
      </div>
    </div>
  </div>

  <!-- Pelanggan Aktif -->
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Pelanggan Aktif</h5>
          <span class="text-body mb-0">62 pelanggan sedang dilayani</span>
        </div>
        <div class="dropdown">
          <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-1" type="button"
            id="ordersCountries" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="icon-base ri ri-more-2-line"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="ordersCountries">
            <a class="dropdown-item" href="javascript:void(0);">Pilih Semua</a>
            <a class="dropdown-item" href="javascript:void(0);">Muat Ulang</a>
            <a class="dropdown-item" href="javascript:void(0);">Bagikan</a>
          </div>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="nav-align-top">
          <ul class="nav nav-tabs nav-fill" role="tablist">
            <li class="nav-item">
              <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                data-bs-target="#navs-justified-new" aria-controls="navs-justified-new"
                aria-selected="true">Baru</button>
            </li>
            <li class="nav-item">
              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                data-bs-target="#navs-justified-link-preparing" aria-controls="navs-justified-link-preparing"
                aria-selected="false">Dalam Proses</button>
            </li>
            <li class="nav-item">
              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                data-bs-target="#navs-justified-link-shipping" aria-controls="navs-justified-link-shipping"
                aria-selected="false">Selesai</button>
            </li>
          </ul>
          <div class="tab-content border-0 pb-0 px-6 mx-1">
            <div class="tab-pane fade show active" id="navs-justified-new" role="tabpanel">
              <ul class="timeline mb-0">
                <li class="timeline-item ps-6 border-dashed">
                  <span class="timeline-indicator-advanced border-0 shadow-none">
                    <i class="icon-base ri ri-checkbox-circle-line text-success"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase">Pelanggan</small>
                    </div>
                    <h6 class="my-50">Budi Santoso</h6>
                    <p class="mb-0 small">B 1234 XYZ | Toyota Avanza</p>
                  </div>
                </li>
                <li class="timeline-item ps-6 border-transparent">
                  <span class="timeline-indicator-advanced text-primary border-0 shadow-none">
                    <i class="icon-base ri ri-map-pin-line"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase">Layanan</small>
                    </div>
                    <h6 class="my-50">Ganti Oli + Filter</h6>
                    <p class="mb-0 small">Mekanik: Andi | Estimasi: 2 jam</p>
                  </div>
                </li>
              </ul>
              <div class="border-1 border-light border-dashed mb-2"></div>
              <ul class="timeline mb-0">
                <li class="timeline-item ps-6 border-dashed">
                  <span class="timeline-indicator-advanced border-0 shadow-none">
                    <i class="icon-base ri ri-checkbox-circle-line text-success"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase">Pelanggan</small>
                    </div>
                    <h6 class="my-50">Siti Aminah</h6>
                    <p class="mb-0 small">B 5678 ABC | Honda Jazz</p>
                  </div>
                </li>
                <li class="timeline-item ps-6 border-transparent">
                  <span class="timeline-indicator-advanced text-primary border-0 shadow-none">
                    <i class="icon-base ri ri-map-pin-line"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase">Layanan</small>
                    </div>
                    <h6 class="my-50">Service Berkala</h6>
                    <p class="mb-0 small">Mekanik: Budi | Menunggu Sparepart</p>
                  </div>
                </li>
              </ul>
            </div>
            <div class="tab-pane fade" id="navs-justified-link-preparing" role="tabpanel">
              <ul class="timeline mb-0">
                <li class="timeline-item ps-6 border-dashed">
                  <span class="timeline-indicator-advanced border-0 shadow-none">
                    <i class="icon-base ri ri-checkbox-circle-line text-success"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase">Pelanggan</small>
                    </div>
                    <h6 class="my-50">Ahmad Yani</h6>
                    <p class="mb-0 small">B 9012 DEF | Suzuki Ertiga</p>
                  </div>
                </li>
                <li class="timeline-item ps-6 border-transparent border-dashed">
                  <span class="timeline-indicator-advanced text-primary border-0 shadow-none">
                    <i class="icon-base ri ri-map-pin-line"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase">Layanan</small>
                    </div>
                    <h6 class="my-50">Tune Up + AC</h6>
                    <p class="mb-0 small">Mekanik: - | Estimasi: 4 jam</p>
                  </div>
                </li>
              </ul>
              <div class="border-1 border-light border-dashed mb-2 "></div>
              <ul class="timeline mb-0">
                <li class="timeline-item ps-6 border-dashed">
                  <span class="timeline-indicator-advanced border-0 shadow-none">
                    <i class="icon-base ri ri-checkbox-circle-line text-success"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase">Pelanggan</small>
                    </div>
                    <h6 class="my-50">Dewi Lestari</h6>
                    <p class="mb-0 small">B 3456 GHI | Daihatsu Xenia</p>
                  </div>
                </li>
                <li class="timeline-item ps-6 border-transparent">
                  <span class="timeline-indicator-advanced text-primary border-0 shadow-none">
                    <i class="icon-base ri ri-map-pin-line"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase">Layanan</small>
                    </div>
                    <h6 class="my-50">Ganti Ban</h6>
                    <p class="mb-0 small">Mekanik: Catur | Progress: 85%</p>
                  </div>
                </li>
              </ul>
            </div>
            <div class="tab-pane fade" id="navs-justified-link-shipping" role="tabpanel">
              <ul class="timeline mb-0">
                <li class="timeline-item ps-6 border-dashed">
                  <span class="timeline-indicator-advanced border-0 shadow-none">
                    <i class="icon-base ri ri-checkbox-circle-line text-success"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase">Pelanggan</small>
                    </div>
                    <h6 class="my-50">Rudi Hartono</h6>
                    <p class="mb-0 small">B 7890 JKL | Mitsubishi Pajero</p>
                  </div>
                </li>
                <li class="timeline-item ps-6 border-transparent">
                  <span class="timeline-indicator-advanced text-primary border-0 shadow-none">
                    <i class="icon-base ri ri-map-pin-line"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase">Layanan</small>
                    </div>
                    <h6 class="my-50">Perbaikan Transmisi</h6>
                    <p class="mb-0 small">Mekanik: Andi | Selesai: 15.00 WIB</p>
                  </div>
                </li>
              </ul>
              <div class="border-1 border-light border-dashed mb-2 "></div>
              <ul class="timeline mb-0">
                <li class="timeline-item ps-6 border-dashed">
                  <span class="timeline-indicator-advanced border-0 shadow-none">
                    <i class="icon-base ri ri-checkbox-circle-line text-success"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase">Pelanggan</small>
                    </div>
                    <h6 class="my-50">Veronica Herman</h6>
                    <p class="mb-0 small">B 162 Windsor | Honda Jazz</p>
                  </div>
                </li>
                <li class="timeline-item ps-6 border-transparent">
                  <span class="timeline-indicator-advanced text-primary border-0 shadow-none">
                    <i class="icon-base ri ri-map-pin-line"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase">Layanan</small>
                    </div>
                    <h6 class="my-50">Ganti Oli</h6>
                    <p class="mb-0 small">Mekanik: Budi | Selesai: 14.30 WIB</p>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Baris 4: Tabel Antrian Servis Full Width -->
<div class="row g-6">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Antrian Servis</h5>
        </div>
        <div class="dropdown">
          <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-1" type="button"
            id="routeVehicles" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="icon-base ri ri-more-2-line"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="routeVehicles">
            <a class="dropdown-item" href="javascript:void(0);">Pilih Semua</a>
            <a class="dropdown-item" href="javascript:void(0);">Muat Ulang</a>
            <a class="dropdown-item" href="javascript:void(0);">Bagikan</a>
          </div>
        </div>
      </div>
      <div class="card-datatable table-responsive">
        <table class="dt-route-vehicles table">
          <thead>
            <tr>
              <th></th>
              <th></th>
              <th>Pelanggan</th>
              <th>Kendaraan</th>
              <th>Jenis Servis</th>
              <th>Status</th>
              <th class="w-20">Progress</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection
