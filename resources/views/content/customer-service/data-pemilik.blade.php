@extends('layouts/layoutMaster')

@section('title', $title)

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss', 'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleave-zen/cleave-zen.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js', 'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js', 'resources/assets/vendor/libs/pickr/pickr.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    <script src="{{ asset('assets/js/data-pemilik.js') }}"></script>
@endsection

@section('content')
    <!-- Data Pemilik Table -->
    <div class="card">
        <div class="card-datatable">
            <table class="datatables-data-pemilik table table-bordered table-responsive">
                <thead>
                    <tr>
                        <th></th>
                        <th>No</th>
                        <th>Nama Pemilik</th>
                        <th>Kode Jenis</th>
                        <th>Alamat</th>
                        <th>Kota</th>
                        <th>Telepon</th>
                        <th>Handphone</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>

        <!-- Offcanvas to add/edit data pemilik -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAdd" data-bs-backdrop="static" aria-labelledby="offcanvasAddLabel">
            <div class="offcanvas-header border-bottom">
                <h5 id="offcanvasAddLabel" class="offcanvas-title">{{ $title }}</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body mx-0 flex-grow-0 h-100">
                <form id="addNewDataForm" method="post" action="{{ url('data-pemilik-list') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="user_id">
                    <input type="hidden" name="kode_cabang" id="kode_cabang" value="{{ $user_cabang }}">
                    <input type="hidden" id="old_file_identitas" name="old_file_identitas" value="">
                    <input type="hidden" id="old_file_npwp" name="old_file_npwp" value="">

                    <div class="form-floating form-floating-outline mb-5 form-control-validation">
                        <input type="text" class="form-control" id="add-nama-pemilik" name="nama_pemilik"
                            placeholder="Nama Pemilik" maxlength="100" />
                        <label for="add-nama-pemilik">Nama Pemilik</label>
                    </div>

                    <div class="form-floating form-floating-outline mb-5 form-control-validation">
                        <select class="form-select select2" id="add-kode-jenis" name="kode_jenis_pemilik">
                            <option value="">Pilih Jenis Pemilik</option>
                            @foreach ($jenis_pemilik as $row)
                                <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                            @endforeach
                        </select>
                        <label for="add-kode-jenis">Kode Jenis Pemilik</label>
                    </div>

                    <!-- Di data-pemilik.blade.php -->
                    <div class="form-floating form-floating-outline mb-5">
                        <textarea class="form-control h-px-100" id="add-alamat" placeholder="Alamat" name="alamat1"></textarea> <!-- ← UBAH JADI alamat1 -->
                        <label for="add-alamat">Alamat</label>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-5">
                                <input type="text" class="form-control" id="add-kota" name="kota" placeholder="Kota"
                                    maxlength="50" />
                                <label for="add-kota">Kota</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-5">
                                <input type="text" class="form-control" id="add-kode-pos" name="kode_pos"
                                    placeholder="Kode Pos" maxlength="5" />
                                <label for="add-kode-pos">Kode Pos</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-floating form-floating-outline mb-5">
                        <input type="text" class="form-control" id="add-po-box" name="po_box" placeholder="PO Box"
                            maxlength="20" />
                        <label for="add-po-box">PO Box</label>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-5">
                                <input type="text" class="form-control phone-mask" id="add-telepon" name="telepon"
                                    placeholder="Telepon Rumah" maxlength="20" />
                                <label for="add-telepon">Telepon Rumah</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-5">
                                <input type="text" class="form-control phone-mask" id="add-fax" name="fax"
                                    placeholder="Nomor Fax" maxlength="20" />
                                <label for="add-fax">Nomor Fax</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-floating form-floating-outline mb-5 form-control-validation">
                        <input type="text" class="form-control phone-mask" id="add-handphone" name="handphone"
                            placeholder="Telepon Selular" maxlength="20" />
                        <label for="add-handphone">Telepon Selular</label>
                    </div>

                    <div class="form-floating form-floating-outline mb-5 form-control-validation">
                        <input type="text" id="add-email" name="email" class="form-control" placeholder="Email" />
                        <label for="add-email" class="form-label">Email</label>
                    </div>

                    <div class="form-floating form-floating-outline mb-5">
                        <input type="text" class="form-control dt-date" id="add-tgl-lahir" name="tgl_lahir"
                            placeholder="DD/MM/YYYY" />
                        <label for="add-tgl-lahir">Tanggal Lahir</label>
                    </div>

                    <div class="form-floating form-floating-outline mb-5">
                        <select class="form-select select2" id="add-kode-agama" name="kode_agama"
                            data-placeholder="Pilih Agama">
                            <option value=""></option>
                            @foreach ($agama as $row)
                                <option value="{{ $row->kode }}">{{ $row->keterangan }}</option>
                            @endforeach
                        </select>
                        <label for="add-kode-agama">Agama</label>
                    </div>

                    <div class="form-floating form-floating-outline mb-5">
                        <input type="text" class="form-control" id="add-no-identitas" name="no_identitas"
                            placeholder="No. Identitas (KTP/SIM)" maxlength="20" />
                        <label for="add-no-identitas">No. Identitas</label>
                    </div>

                    <div class="form-floating form-floating-outline mb-5">
                        <input type="file" class="form-control" id="add-file-identitas" name="file_identitas"
                            placeholder="File No. Identitas" accept=".jpg,.jpeg,.png,image/jpeg,image/png" />
                        <label for="add-file-identitas">File No. Identitas</label>
                        <div class="form-text">Format file: jpg|png</div>
                    </div>

                    <div class="form-floating form-floating-outline mb-5">
                        <input type="text" class="form-control" id="add-npwp" name="npwp" placeholder="NPWP"
                            maxlength="20" />
                        <label for="add-npwp">NPWP</label>
                    </div>

                    <div class="form-floating form-floating-outline mb-5">
                        <input type="file" class="form-control" id="add-file-npwp" name="file_npwp"
                            placeholder="File NPWP" accept=".jpg,.jpeg,.png,image/jpeg,image/png" />
                        <label for="add-file-npwp">File NPWP</label>
                        <div class="form-text">Format file: jpg|png</div>
                    </div>

                    <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Simpan</button>
                    <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="offcanvas">Batal</button>
                </form>
            </div>
        </div>
    </div>

@endsection
