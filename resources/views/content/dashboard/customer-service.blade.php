@extends('layouts/layoutMaster')

@section('title', 'Dashboard')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/chartjs/chartjs.scss'
])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite([
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/chartjs/chartjs.js'
])
@endsection

@section('page-script')
<script>
  let dataSpkMasuk = @json($data['spkMasukData']);
  let dataSpkKeluar = @json($data['spkKeluarData']);

  let maxNilai = Math.max(...dataSpkMasuk, ...dataSpkKeluar);
  let maxScaleY = maxNilai > 0 ? (Math.ceil(maxNilai / 100) * 100) + 50 : 400;
</script>

@vite(['resources/assets/js/charts-chartjs-legend.js'])
<script src="{{ asset('assets/js/dashboard-customer-service.js') }}"></script>
@endsection


@section('content')

{{-- ALERT BANNER --}}
@if(($data['SPK_BLM_TURUN_LAP'] + $data['SPK_PENDING'] + $data['EST_BLM_BUAT'] + $data['EST_BLM_KIRIM']) > 0)
<div class="alert alert-danger alert-dismissible mb-6" role="alert" id="alertPending" style="cursor:pointer; border-left: 5px solid #dc3545;" onclick="scrollToPending()">
  <div class="d-flex align-items-center gap-3">
    <i class="ri ri-alarm-warning-fill icon-24px" style="font-size:1.5rem;"></i>
    <div>
      <strong>HARAP SEGERA UPDATE PROGRESS PERBAIKAN KENDARAAN</strong><br>
      <small>Terdapat pekerjaan yang belum diselesaikan. Klik untuk melihat detail.</small>
    </div>
  </div>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" onclick="event.stopPropagation()"></button>
</div>
@endif
{{-- END ALERT BANNER --}}

<!-- Header -->
<div class="row">
  <div class="col-12">
    <div class="card mb-6">
      <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-5">
        <div class="flex-grow-1 mt-2 mt-sm-6">
          <div
            class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-6">
            <div class="user-profile-info">
              <h4 class="mb-2">Selamat Datang, {{ $users->name }}</h4>
              <ul
                class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4">
                <li class="list-inline-item"><i class="icon-base ri ri-map-pin-line me-2 icon-24px"></i><span
                    class="fw-medium">{{ $nama_cabang }}</span></li>
                <li class="list-inline-item"><i class="icon-base ri ri-calendar-line me-2 icon-24px"></i><span
                    class="fw-medium"> Login: {{ date("d F Y, H:i:s", strtotime($startlogin)) }}</span></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--/ Header -->

@php
    $namaBulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                  7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
@endphp

{{-- FILTER + CARD RINGKASAN (dinamis sesuai role) --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <h5 class="m-0">Ringkasan Bulanan</h5>
  <div class="d-flex gap-2">
    <div class="btn-group">
      <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
        {{ $namaBulan[(int)$bulan] }}
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        @foreach ($namaBulan as $no => $nama)
          <li><a class="dropdown-item {{ $bulan == $no ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['bulan' => $no]) }}">{{ $nama }}</a></li>
        @endforeach
      </ul>
    </div>
    <div class="btn-group">
      <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
        {{ $tahun }}
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        @for ($y = $tahunMax; $y >= $tahunMin; $y--)
          <li><a class="dropdown-item {{ $tahun == $y ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tahun' => $y]) }}">{{ $y }}</a></li>
        @endfor
      </ul>
    </div>
  </div>
</div>

<div class="row g-3 mb-6">
  @foreach ($data['RINGKASAN_CARDS'] as $card)
  <div class="col-6 col-md-4 col-xl">
    <div class="card card-border-shadow-{{ $card['color'] }} h-100">
      <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <span class="rounded-2 bg-label-{{ $card['color'] }} d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
            <i class="ri {{ $card['icon'] }}" style="font-size:16px;"></i>
          </span>
          @if($card['badge'])
            <span class="badge bg-label-{{ $card['color'] }}" style="font-size:9px;">{{ $card['badge'] }}</span>
          @endif
        </div>
        <p class="mb-0 text-uppercase text-body-secondary" style="font-size:10px;">{{ $card['label'] }}</p>
        <h5 class="mb-0">{{ $card['display_value'] }}</h5>
        @if($card['sub'])
          <small class="text-{{ $card['badge'] ? $card['color'] : 'body-secondary' }}">{{ $card['sub'] }}</small>
        @endif
      </div>
    </div>
  </div>
  @endforeach
</div>
{{-- END CARD RINGKASAN --}}

<!-- Baris 2: Pending Pekerjaan (kiri) & Statistik SPK (kanan) -->
<div class="row g-6 mb-6">
  <!-- Pending Pekerjaan -->
  <div class="col-lg-6" id="pendingPekerjaan">
    <div class="card h-100" style="transition: box-shadow 0.3s;">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Pending Pekerjaan</h5>
        </div>
      </div>
      <div class="card-body pb-2">
        <div class="table-responsive">
          <table class="table card-table">
            <tbody class="table-border-bottom-0">
              <tr>
                <td class="w-75 ps-0">
                  <div class="d-flex justify-content-start align-items-center">
                    <div class="me-2">
                      <i class="text-heading icon-base ri ri-gear-line icon-24px"></i>
                    </div>
                    <h6 class="mb-0 fw-normal">SPK Belum Turun Lapangan</h6>
                  </div>
                </td>
                <td class="text-end pe-0 text-nowrap">
                  <h6 class="mb-0">{{ number_format($data['SPK_BLM_TURUN_LAP'], 0, ".", ",") }}</h6>
                </td>
                <td class="text-end pe-0 ps-6">
                  <button class="btn btn-icon btn-text-secondary btn-sm rounded-pill view-detail" data-tipe="1" title="Lihat Detail"><i class="icon-base ri ri-eye-line icon-22px"></i></button>
                </td>
              </tr>
              <tr>
                <td class="w-75 ps-0">
                  <div class="d-flex justify-content-start align-items-center">
                    <div class="me-2">
                      <i class="text-heading icon-base ri ri-time-line icon-24px"></i>
                    </div>
                    <h6 class="mb-0 fw-normal">SPK Yang Harus Diselesaikan</h6>
                  </div>
                </td>
                <td class="text-end pe-0 text-nowrap">
                  <h6 class="mb-0">{{ number_format($data['SPK_PENDING'], 0, ".", ",") }}</h6>
                </td>
                <td class="text-end pe-0 ps-6">
                  <button class="btn btn-icon btn-text-secondary btn-sm rounded-pill view-detail" data-tipe="2" data-bs-toggle="modal" data-bs-target="#viewRoleModal" title="Lihat Detail"><i class="icon-base ri ri-eye-line icon-22px"></i></button>
                </td>
              </tr>
              <tr>
                <td class="w-75 ps-0">
                  <div class="d-flex justify-content-start align-items-center">
                    <div class="me-2">
                      <i class="text-heading icon-base ri ri-file-list-3-line icon-24px"></i>
                    </div>
                    <h6 class="mb-0 fw-normal">Estimasi Belum Dibuat</h6>
                  </div>
                </td>
                <td class="text-end pe-0 text-nowrap">
                  <h6 class="mb-0">{{ number_format($data['EST_BLM_BUAT'], 0, ".", ",") }}</h6>
                </td>
                <td class="text-end pe-0 ps-6">
                  <button class="btn btn-icon btn-text-secondary btn-sm rounded-pill view-detail" data-tipe="3" data-bs-toggle="modal" data-bs-target="#viewRoleModal" title="Lihat Detail"><i class="icon-base ri ri-eye-line icon-22px"></i></button>
                </td>
              </tr>
              <tr>
                <td class="w-75 ps-0">
                  <div class="d-flex justify-content-start align-items-center">
                    <div class="me-2">
                      <i class="text-heading icon-base ri ri-check-double-line icon-24px"></i>
                    </div>
                    <h6 class="mb-0 fw-normal">Estimasi Belum Dikirim</h6>
                  </div>
                </td>
                <td class="text-end pe-0 text-nowrap">
                  <h6 class="mb-0">{{ number_format($data['EST_BLM_KIRIM'], 0, ".", ",") }}</h6>
                </td>
                <td class="text-end pe-0 ps-6">
                  <button class="btn btn-icon btn-text-secondary btn-sm rounded-pill view-detail" data-tipe="4" data-bs-toggle="modal" data-bs-target="#viewRoleModal" title="Lihat Detail"><i class="icon-base ri ri-eye-line icon-22px"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistik SPK -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2 mb-1">Statistik SPK</h5>
        </div>
        <div class="btn-group">
        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          {{ $tahun }}
        </button>
        <ul class="dropdown-menu">
          @for ($y = date('Y'); $y >= date('Y') - 4; $y--)
            <li>
              <a class="dropdown-item {{ $tahun == $y ? 'active' : '' }}"
                href="{{ request()->fullUrlWithQuery(['tahun' => $y]) }}">{{ $y }}</a>
            </li>
          @endfor
        </ul>
      </div>
      </div>
      <div class="card-body">
        <canvas id="spkChart" class="chartjs" data-height="400"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Baris 2: Tabel SPK Master Full Width -->
<div class="row g-6">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">SPK Master</h5>
        </div>
      </div>
      <div class="card-datatable text-nowrap">
        <table class="datatables-spk table table-bordered table-responsive">
          <thead>
            <tr>
              <th>No</th>
              <th>Tgl Input</th>
              <th>No SPK</th>
              <th>Keterangan</th>
              <th>No. Polisi</th>
              <th>Tipe Kendaraan</th>
              <th>Nama Pemilik</th>
              <th>Nama Pelanggan</th>
              <th>Status SPK</th>
              <th>No. Polis</th>
              <th>No. Klaim</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection
