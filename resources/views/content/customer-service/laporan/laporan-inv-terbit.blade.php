<div class="row mt-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Laporan Invoice OR Terbit</h5>
        </div>
        <div class="demo-inline-spacing">
          <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
          <button type="button" class="btn btn-primary btn-print">Print</button>
        </div>
      </div>
      <div class="card-datatable">
        <table class="datatables-inv-terbit table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
          <thead>
            <tr>
              <th>No</th>
              <th>Tanggal Invoice</th>
              <th>No. Invoice</th>
              <th>No. SPK</th>
              <th>Tertanggung</th>
              <th>No. Polisi</th>
              <th>Tipe Kendaraan</th>
              <th>Nama Asuransi</th>
              <th>Nilai OR</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>