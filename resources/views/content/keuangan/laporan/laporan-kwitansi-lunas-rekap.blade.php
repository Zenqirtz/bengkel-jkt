<div class="row mt-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Laporan Kwitansi Lunas Rekap</h5>
        </div>
        <div class="demo-inline-spacing">
          <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
          <button type="button" class="btn btn-primary btn-print">Print</button>
        </div>
      </div>
      <div class="card-datatable text-nowrap">
        <table class="datatables-kwitansi-rekap table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
          <thead>
            <tr>
              <th rowspan="2">No</th>
              <th rowspan="2">Nama Asuransi</th>
              <th rowspan="2">Unit</th>
              <th colspan="3">Penerimaan Via</th>
              <th rowspan="2">Uang Muka</th>
              <th rowspan="2">PPh</th>
              <th rowspan="2">Materai & Transfer</th>
              <th rowspan="2">Tagihan</th>
              <th rowspan="2">Diterima</th>
              <th rowspan="2">Estimasi</th>
              <th rowspan="2">Perbaikan</th>
              <th rowspan="2">Sparepart</th>
              <th rowspan="2">Lain</th>
            </tr>
            <tr>
              <th>Tunai</th>
              <th>Bank</th>
              <th>Free</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>
