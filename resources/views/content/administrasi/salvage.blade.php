@extends('layouts/layoutMaster')

@section('title', $title)

<!-- Page Style -->
@section('page-style')
<style>
  /* ===== CARD ENTRANCE ANIMATION ===== */
  .card {
    animation: cardSlideUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
  }
  .card:hover {
    box-shadow: 0 8px 32px rgba(0,0,0,0.13) !important;
    transform: translateY(-2px);
  }

  @keyframes cardSlideUp {
    from { opacity: 0; transform: translateY(28px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* ===== CARD HEADER ===== */
  .card-header {
    animation: fadeInDown 0.45s cubic-bezier(0.22, 1, 0.36, 1) both;
    animation-delay: 0.08s;
  }

  @keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-12px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* ===== TITLE ===== */
  .card-header .card-title h5 {
    position: relative;
    display: inline-block;
  }
  .card-header .card-title h5::after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--bs-primary);
    border-radius: 2px;
    transition: width 0.5s cubic-bezier(0.22, 1, 0.36, 1);
  }
  .card-header .card-title h5:hover::after {
    width: 100%;
  }

  /* ===== BUTTON SMOOTH & RIPPLE ===== */
  .btn {
    position: relative;
    overflow: hidden;
    transition: all 0.28s cubic-bezier(0.22, 1, 0.36, 1) !important;
  }
  .btn:hover {
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
  }
  .btn:active {
    transform: translateY(0) scale(0.97);
    box-shadow: none;
  }
  .btn .ripple-effect {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.38);
    transform: scale(0);
    animation: rippleAnim 0.55s linear;
    pointer-events: none;
  }
  @keyframes rippleAnim {
    to { transform: scale(4); opacity: 0; }
  }

  /* ===== TABLE ROW FADE-IN ===== */
  .datatables-spk tbody tr,
  .datatables-salvage tbody tr {
    animation: rowFadeIn 0.35s ease both;
    transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
  }
  .datatables-spk tbody tr:hover,
  .datatables-salvage tbody tr:hover {
    transform: scale(1.002);
    box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    z-index: 1;
    position: relative;
  }
  @keyframes rowFadeIn {
    from { opacity: 0; transform: translateX(-8px); }
    to   { opacity: 1; transform: translateX(0); }
  }

  /* Stagger row animation via nth-child */
  .datatables-spk tbody tr:nth-child(1)  { animation-delay: 0.03s; }
  .datatables-spk tbody tr:nth-child(2)  { animation-delay: 0.06s; }
  .datatables-spk tbody tr:nth-child(3)  { animation-delay: 0.09s; }
  .datatables-spk tbody tr:nth-child(4)  { animation-delay: 0.12s; }
  .datatables-spk tbody tr:nth-child(5)  { animation-delay: 0.15s; }
  .datatables-spk tbody tr:nth-child(6)  { animation-delay: 0.18s; }
  .datatables-spk tbody tr:nth-child(7)  { animation-delay: 0.21s; }
  .datatables-spk tbody tr:nth-child(8)  { animation-delay: 0.24s; }
  .datatables-spk tbody tr:nth-child(9)  { animation-delay: 0.27s; }
  .datatables-spk tbody tr:nth-child(10) { animation-delay: 0.30s; }

  /* ===== MODAL TRANSITIONS ===== */
  .modal.fade .modal-dialog {
    transition: transform 0.38s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.38s ease !important;
    transform: translateY(-30px) scale(0.97);
    opacity: 0;
  }
  .modal.show .modal-dialog {
    transform: translateY(0) scale(1);
    opacity: 1;
  }
  .modal-content {
    transition: box-shadow 0.3s ease;
    box-shadow: 0 20px 60px rgba(0,0,0,0.18);
  }

  /* ===== FORM INPUT FOCUS GLOW ===== */
  .form-control, .form-select {
    transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.2s ease !important;
  }
  .form-control:focus, .form-select:focus {
    transform: scale(1.012);
    box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.2) !important;
  }

  /* ===== HR DIVIDER SLIDE-IN ===== */
  hr.container-m-nx {
    animation: hrSlide 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
    animation-delay: 0.2s;
    transform-origin: left;
  }
  @keyframes hrSlide {
    from { transform: scaleX(0); opacity: 0; }
    to   { transform: scaleX(1); opacity: 1; }
  }

  /* ===== INLINE SPACING BUTTONS STAGGER ===== */
  .demo-inline-spacing .btn:nth-child(1) { animation: btnPopIn 0.4s cubic-bezier(0.22,1,0.36,1) 0.15s both; }
  .demo-inline-spacing .btn:nth-child(2) { animation: btnPopIn 0.4s cubic-bezier(0.22,1,0.36,1) 0.25s both; }
  .demo-inline-spacing .btn:nth-child(3) { animation: btnPopIn 0.4s cubic-bezier(0.22,1,0.36,1) 0.35s both; }
  @keyframes btnPopIn {
    from { opacity: 0; transform: translateY(10px) scale(0.9); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
  }

  /* ===== CHECKBOX SMOOTH ===== */
  input[type="checkbox"] {
    transition: transform 0.2s ease;
    cursor: pointer;
  }
  input[type="checkbox"]:hover {
    transform: scale(1.15);
  }
</style>
@endsection


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
'resources/assets/vendor/libs/tagify/tagify.scss'
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
'resources/assets/vendor/libs/tagify/tagify.js'
])
@endsection

<!-- Page Scripts -->
@section('page-script')
  <script src="{{ asset('assets/js/salvage.js') }}"></script>
  <script>
    // ===== Ripple Effect on Buttons =====
    document.addEventListener('click', function(e) {
      const btn = e.target.closest('.btn');
      if (!btn) return;
      const ripple = document.createElement('span');
      ripple.classList.add('ripple-effect');
      const rect = btn.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      ripple.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX - rect.left - size/2}px;top:${e.clientY - rect.top - size/2}px;`;
      btn.appendChild(ripple);
      ripple.addEventListener('animationend', () => ripple.remove());
    });

    // ===== Re-animate table rows on DataTable draw =====
    $(document).on('draw.dt', '.datatables-spk, .datatables-salvage', function() {
      $(this).find('tbody tr').each(function(i) {
        const tr = this;
        tr.style.animation = 'none';
        tr.offsetHeight; // reflow
        tr.style.animation = '';
        tr.style.animationDelay = (i * 0.04) + 's';
      });
    });
  </script>
@endsection

@section('content')
  <!-- Users List Table -->
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="card-title mb-0">
        <h5 class="m-0 me-2">{{ $title }}</h5>
      </div>
      <div class="demo-inline-spacing">
        <button type="button" class="btn btn-primary filter-spk" data-bs-toggle="modal" data-bs-target="#filterRoleModal">Cari</button>
        <button type="button" class="btn btn-primary edit-selected-spk">Ubah</button>
        <button type="button" class="btn btn-primary cetak-data">Cetak</button>
      </div>
    </div>
    <div class="card-datatable text-nowrap">
      <table class="datatables-spk table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
        <thead>
          <tr>
            <th></th>
            <th>No</th>
            <th>Tgl Input SPK</th>
            <th>No SPK</th>
            <th>Keterangan</th>
            <th class="text-nowrap">No. Polisi</th>
            <th>Tipe Kendaraan</th>
            <th>Nama Pemilik</th>
            <th>Nama Pelanggan</th>
            <th>Tanggal Kirim</th>
            <th>Tanggal Batal</th>
            <th>Tanggal Turun Lap.</th>
            <th>Tanggal Selesai</th>
            <th>Tanggal Keluar</th>
            <th>Status SPK</th>
            <th>No. Polis</th>
            <th>No. Klaim</th>
            <th>Aksi</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>

   <!-- Add SPK Modal -->
   <div class="modal" id="addRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel1">{{ $title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="addNewDataForm" method="post" action="{{ url('salvage-list') }}" onSubmit="return false">
          @csrf
          <input type="hidden" name="id" id="user_id">
          <input type="hidden" name="kode_spk" id="kode_spk">
          <input type="hidden" name="no_polisi" id="no_polisi">
          <input type="hidden" name="no_polis" id="no_polis">
          <input type="hidden" name="kode_merek" id="kode_merek">
          <input type="hidden" name="kode_tipe" id="kode_tipe">
          <input type="hidden" name="kode_pelanggan" id="kode_pelanggan">

          <div class="row g-2">
            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. Salvage</label>
                <div class="col-sm-5">
                  <input type="text" id="add-nomor-salvage" name="no_salvage" class="form-control form-control-sm text-primary fw-bold" disabled />
                </div>
                <div class="col-sm-4">
                  <input type="text" id="add-tanggal-salvage" name="tgl_salvage" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. SPK</label>
                <div class="col-sm-5">
                  <input type="text" id="add-nomor-spk" name="kode_spk2" class="form-control form-control-sm" disabled />
                </div>
                <div class="col-sm-4">
                  <input type="text" id="add-nomor-polisi" name="no_polisi" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Tipe Kendaraan</label>
                <div class="col-sm-9">
                  <input type="text" id="add-tipe-kendaraan" name="tipe_kendaraan" class="form-control form-control-sm" disabled />
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Nama Pemilik</label>
                <div class="col-sm-9">
                  <input type="text" id="add-nama-pemilik" name="nama_pemilik" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">Nama Asuransi</label>
                <div class="col-sm-9 form-control-validation">
                  <input type="text" id="add-nama-pelanggan" name="nama_pelanggan" class="form-control form-control-sm" disabled />
                </div>
              </div>
              <div class="row mb-1 align-items-center">
                <label class="col-sm-3 col-form-label">No. Polis</label>
                <div class="col-sm-9">
                  <input type="text" id="add-nomor-polis" name="no_polis" class="form-control form-control-sm" disabled />
                </div>
              </div>
            </div>
          </div>

          <hr class="container-m-nx mt-2" />

          <div class="row g-2 mt-3">
            <div class="col-md-12">
              <div class="table-responsive text-nowrap">
                <table class="datatables-salvage table table-bordered">
                  <thead>
                    <tr>
                      <th>No</th>
                      <th>Nama Barang</th>
                      <th>Qty</th>
                      <th>Ada</th>
                      <th>Tidak Ada</th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>
          </div>

          <div class="row mt-3 g-5">
            <div class="col-12 d-flex justify-content-between">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
              <button type="submit" class="btn btn-primary btn-submit">Simpan</button>
            </div>
          </div>
          </form>
        </div>
        {{-- <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary">Simpan</button>
        </div> --}}
      </div>
    </div>
  </div>
  <!--/ Add SPK Modal -->   

  <!-- Add CARI SPK Modal -->
  <div class="modal" id="filterRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="filterModalLabel">Cari {{ $title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="formFilterSpk" onsubmit="return false">
            <div class="row g-4">
              <div class="col-md-6">
                <div class="row">
                  <label class="col-sm-3 col-form-label text-sm-end" for="filter-nomor-spk">Nomor SPK</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-nomor-spk" class="form-control" name="kode_spk" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row">
                  <label class="col-sm-3 col-form-label text-sm-end" for="filter-no-polisi">Nomor Polisi</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-no-polisi" class="form-control" name="no_polisi" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row">
                  <label class="col-sm-3 col-form-label text-sm-end" for="filter-tgl-spk-awal">Tanggal SPK</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-tgl-spk-awal" class="form-control dt-date" name="tgl_masuk_awal" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row">
                  <label class="col-sm-3 col-form-label text-sm-center" for="filter-tgl-spk-akhir">s/d</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-tgl-spk-akhir" class="form-control dt-date" name="tgl_masuk_akhir" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row">
                  <label class="col-sm-3 col-form-label text-sm-end" for="filter-nama-pelanggan">Nama Pelanggan</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-nama-pelanggan" class="form-control" name="nama_pelanggan" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row">
                  <label class="col-sm-3 col-form-label text-sm-end" for="filter-nama-pemilik">Nama Pemilik</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-nama-pemilik" class="form-control" name="nama_pemilik" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row">
                  <label class="col-sm-3 col-form-label text-sm-end" for="filter-no-polis">Nomor Polis</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-no-polis" class="form-control" name="no_polis" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row">
                  <label class="col-sm-3 col-form-label text-sm-end" for="filter-no-klaim">Nomor Klaim</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-no-klaim" class="form-control" name="kode_claim" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row">
                  <label class="col-sm-3 col-form-label text-sm-end" for="filter-status-spk">Status SPK</label>
                  <div class="col-sm-9">
                    <select id="filter-status-spk" class="select2 form-select" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($status_spk as $row)
                        <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row">
                  <label class="col-sm-3 col-form-label text-sm-end" for="filter-status">Status</label>
                  <div class="col-sm-9">
                    <select id="filter-status" class="select2 form-select" data-allow-clear="true">
                      <option value="all">Pilih Semua</option>
                      @foreach($status as $row)
                        <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <hr class="container-m-nx mt-2" />
            <div class="row mt-3 g-5">
              <div class="col-12 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary me-sm-3 me-1">Cari</button>
              </div>
            </div>
            {{-- <div class="row mt-6">
              <div class="col-md-6">
                <div class="row justify-content-end">
                  <div class="col-sm-9">
                    <button type="submit" class="btn btn-primary me-sm-3 me-1">Cari</button>
                    <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="modal">Batal</button>
                  </div>
                </div>
              </div>
            </div> --}}
          </form>
        </div>
      </div>
    </div>
  </div>
  <!--/ Add CARI SPK Modal -->   
  
@endsection
