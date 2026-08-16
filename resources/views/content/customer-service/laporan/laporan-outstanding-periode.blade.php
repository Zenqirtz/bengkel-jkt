<div class="row mt-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Laporan Outstanding OR</h5>
        </div>
        <div class="demo-inline-spacing">
          <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
          <button type="button" class="btn btn-primary btn-print">Print</button>
        </div>
      </div>
      <div class="card-datatable">
        <table class="datatables-outstanding-periode table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
          <thead>
            <tr>
              <th>No</th>
              <th>No. SPK</th>
              <th>No. Polisi</th>
              <th>Tipe Kendaraan</th>
              <th>Nama Asuransi</th>
              <th>Tertanggung</th>
              <th>No. Invoice</th>
              <th>Tanggal Invoice</th>
              <th>Total OR</th>
              <th>No. Keluar</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>