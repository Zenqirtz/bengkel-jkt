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
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
    'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
    'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.scss',
    'resources/assets/vendor/libs/pickr/pickr-themes.scss'
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
    'resources/assets/vendor/libs/pickr/pickr.js'
  ])
@endsection

<!-- Page Scripts -->
@section('page-script')
  <script src="{{ asset('assets/js/penomoran-transaksi.js') }}"></script>
@endsection

@section('content')
  <!-- Users List Table -->
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="card-title mb-0">
        <h5 class="m-0 me-2">{{ $title }}</h5>
      </div>
      <div class="demo-inline-spacing">
        <button type="button" class="btn btn-primary cari-data" data-bs-toggle="modal" data-bs-target="#filterRoleModal">Cari</button>
        <button type="button" class="btn btn-primary add-new" data-bs-toggle="modal" data-bs-target="#addRoleModal">Tambah</button>
        <button type="button" class="btn btn-primary edit-record">Ubah</button>
        <button type="button" class="btn btn-primary delete-record">Hapus</button>
      </div>
    </div>
    <div class="card-datatable text-nowrap">
      <table class="datatables-penomoran-transaksi table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
        <thead>
          <tr>
            <th></th>
            <th>No</th>
            <th>Modul Transaksi</th>
            <th>Cabang</th>
            <th>Bank</th>
            <th>Auto Reset</th>
            <th>Nomor Terakhir</th>
            <th>Penomoran</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>

  <!-- Form Cari Data Modal -->
  <div class="modal" id="filterRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="filterModalLabel">Cari {{ $title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="formCariData" onsubmit="return false">
            <div class="row g-2">
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-modul">Modul</label>
                  <div class="col-sm-9">
                    <select id="filter-modul" class="select2 form-select">
                      <option value="all">Pilih Semua</option>
                      @foreach($modultrs as $row)
                        <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-contoh">Penomoran</label>
                  <div class="col-sm-9">
                    <input type="text" id="filter-contoh" class="form-control form-control-sm text-uppercase" name="contoh" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row mb-1 align-items-center">
                  <label class="col-sm-3 col-form-label" for="filter-autoreset">Auto Reset</label>
                  <div class="col-sm-9">
                    <select id="filter-autoreset" class="select2 form-select">
                      <option value="all">Pilih Semua</option>
                      <option value="bulan">Bulan</option>
                      <option value="tahun">Tahun</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <hr class="container-m-nx mt-2" />

            <div class="row mt-3 g-5">
              <div class="col-12 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary me-sm-3 me-1">Cari</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!--/ Form Cari Data Modal -->

  <!-- Add Role Modal -->
  <div class="modal" id="addRoleModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple modal-dialog-centered modal-add-new-role">
      <div class="modal-content">
        <div class="modal-body p-0">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          <div class="text-center mb-6">
            <h4 class="role-title mb-2 pb-0">Pengaturan Nomor</h4>
          </div>
          <!-- Add role form -->
          {{-- <form id="addNewDataForm" class="row g-3" autocomplete="off"> --}}
          <form class="row g-3" id="addNewDataForm" method="post" action="{{ url('penomoran-transaksi-list') }}" autocomplete="off">
            @csrf
            <input type="hidden" name="id" id="user_id">
            <input type="hidden" name="contoh" id="contoh_final">
            <input type="hidden" name="kode_cabang" id="kode_cabang" value="{{ $user_cabang }}">

            <div class="col-12">
              <div class="form-floating form-floating-outline mb-5 form-control-validation">
                <select id="add-modul" name="modul" class="select2 form-select is-invalid">
                  <option value="">Pilih Modul Transaksi</option>
                  @foreach($modultrs as $row)
                    <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                  @endforeach
                </select>
                <label for="add-modul">Modul Transaksi</label>
              </div>
            </div>
             <!-- Baris 1 -->
            <div class="col-md-6">
              <div class="form-floating form-floating-outline mb-5 form-control-validation">
                <select id="add-dept" name="dept" class="select2 form-select">
                  <option value="">Pilih Cabang</option>
                  @foreach($cabang as $row)
                    <option value="{{ $row->nama_singkat }}">{{ $row->nama_singkat }}</option>
                  @endforeach
                </select>
                <label for="add-dept">Dept / Cabang</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating form-floating-outline mb-5 form-control-validation">
                <select id="add-bank" name="bank" class="select2 form-select">
                  <option value="">Pilih Bank</option>
                  @foreach($bank as $row)
                    <option value="{{ $row->kode_bank }}">{{ $row->nama_bank }}</option>
                  @endforeach
                </select>
                <label for="add-bank">Bank</label>
              </div>
            </div>

             <!-- Baris 2 -->
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-5 form-control-validation">
                <select id="digitCnt" name="digit_cnt" class="select2 form-select">
                  <option>1</option>
                  <option>2</option>
                  <option>3</option>
                  <option>4</option>
                  <option>5</option>
                  <option>6</option>
                </select>
                <label for="digitCnt">Digit Cnt</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-5 form-control-validation">
                <input type="number" class="form-control" id="noTerakhir" name="nourut" placeholder="No Terakhir"  value="0" min="0" />
                <label for="noTerakhir">No Terakhir</label>
              </div>
            </div>
            
            <div class="col-md-6">
              <small class="fw-medium d-block">Auto Reset</small>
              <div class="form-check form-check-inline mt-4">
                <input class="form-check-input" type="radio" name="autoreset" id="resetBulan" value="bulan">
                <label class="form-check-label" for="resetBulan">Bulan</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="autoreset" id="resetTahun" value="tahun" checked>
                <label class="form-check-label" for="resetTahun">Tahun</label>
              </div>
            </div>

            <div class="col-12">
              <div class="form-floating form-floating-outline mb-5 form-control-validation">
                <select id="tokenPicker" class="form-select">
                  <option value="">➕ Sisipkan Token</option>
                  <option value="[BLN]">[BLN]</option>
                  <option value="[THN]">[THN]</option>
                  <option value="[BLNTHN]">[BLNTHN]</option>
                  <option value="[CNT]">[CNT]</option>
                  <option value="[DEPT]">[DEPT]</option>
                  <option value="[BANK]">[BANK]</option>
                  <option value="[MODUL]">[MODUL]</option>
                </select>
                <label for="add-modul">Sisipkan Token</label>
              </div>
            </div>

            <!-- Baris 3: Format -->
            <div class="col-12">
              <div class="form-control-validation">
                <label for="add-segmen1">Format Penomoran</label>
                <input type="text" name="segmen1" id="add-segmen1" class="form-control fmt" placeholder="Format Penomoran">
              </div>
            </div>
            <div class="col-12">
              <h1 class="display-4 mb-0 flex-grow-1 example-box" id="contoh">&nbsp;</h1>
            </div>

            {{-- <div class="col-12">
              <label class="form-label">Format</label>
              <div class="row g-2">
                <div class="col-6 col-md">
                  <input type="text" name="segmen1" class="form-control fmt" placeholder="Segmen 1">
                </div>
                <div class="col-6 col-md">
                  <input type="text" name="segmen2" class="form-control fmt" placeholder="Segmen 2">
                </div>
                <div class="col-6 col-md">
                  <input type="text" name="segmen3" class="form-control fmt" placeholder="Segmen 3">
                </div>
                <div class="col-6 col-md">
                  <input type="text" name="segmen4" class="form-control fmt" placeholder="Segmen 4">
                </div>
                <div class="col-6 col-md">
                  <input type="text" name="segmen5" class="form-control fmt" placeholder="Segmen 5">
                </div>
                <div class="col-6 col-md">
                  <input type="text" name="segmen6" class="form-control fmt" placeholder="Segmen 6">
                </div>
                <div class="col-6 col-md">
                  <input type="text" name="segmen7" class="form-control fmt" placeholder="Segmen 7">
                </div>
                <div class="col-6 col-md">
                  <select id="tokenPicker" class="form-select">
                    <option value="">➕ Sisipkan Token…</option>
                    <option value="[BLN]">[BLN]</option>
                    <option value="[THN]">[THN]</option>
                    <option value="[BLNTHN]">[BLNTHN]</option>
                    <option value="[CNT]">[CNT]</option>
                    <option value="[DEPT]">[DEPT]</option>
                    <option value="[BANK]">[BANK]</option>
                    <option value="[MODUL]">[MODUL]</option>
                  </select>
                </div>
              </div>
            </div> --}}

            {{-- <div class="col-12">
              <label class="form-label me-2 mb-0">Contoh</label>
              <div class="flex-grow-1 example-box" id="contoh">Contoh</div>
            </div> --}}

            <!-- Keterangan -->
            <div class="col-12">
              <small class="text-muted">
                Kode Otomatis : <br>
                <span class="token-badge">[BLN]</span> = Bulan 2 Digit, <br>
                <span class="token-badge">[THN]</span> = Tahun 2 Digit, <br>
                <span class="token-badge">[BLNTHN]</span> = Bulan dan Tahun 4 Digit, <br>
                <span class="token-badge">[CNT]</span> = Count, <br>
                <span class="token-badge">[DEPT]</span> = Dept/Cabang, <br>
                <span class="token-badge">[BANK]</span> = Bank <br>
                <span class="token-badge">[MODUL]</span> = Modul Transaksi. <br>
                <br>Kode Sendiri : Bisa diketik sendiri.
                <br>Auto Reset : Berdasarkan sistem bulan dan tahun pada komputer/server database.
                <br>Penting : Pastikan hasil dari No Transaksi tidak lebih dari 40 karakter.
              </small>
            </div>

            <hr class="container-m-nx mt-2" />

            <div class="row mt-3 g-5">
              <div class="col-12 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Simpan</button>
              </div>
            </div>

            <!-- Tombol bawah -->
            {{-- <div class="col-12 d-flex justify-content-between align-items-center pt-2">
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-danger" id="btnReset">Reset No Terakhir</button>
                <button type="submit" class="btn btn-primary data-submit">Simpan</button>
              </div>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Tutup</button>
            </div> --}}
          </form>
          <!--/ Add role form -->
        </div>
      </div>
    </div>
  </div>
  <!--/ Add Role Modal -->

@endsection
