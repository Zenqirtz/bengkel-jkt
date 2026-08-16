<div class="row mt-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Laporan SPK Batal</h5>
        </div>
        <div class="demo-inline-spacing">
          <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
          <button type="button" class="btn btn-primary btn-print">Print</button>
        </div>
      </div>
      <div class="card-datatable">
        <table class="datatables-spk-batal table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
          <thead>
            <tr>
              <th>No</th>
              <th>Tanggal Batal</th>
              <th>No. SPK</th>
              <th>Tipe Kendaraan</th>
              <th>No. Polisi</th>
              <th>Nama Asuransi</th>
              <th>Nama Pemilik</th>
              <th>Dibatalkan Oleh</th>
              <th>Alasan Pembatalan</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>