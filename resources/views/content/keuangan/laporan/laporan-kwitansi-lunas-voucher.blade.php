<div class="row mt-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Laporan Kwitansi Lunas Voucher</h5>
        </div>
        <div class="demo-inline-spacing">
          <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
          <button type="button" class="btn btn-primary btn-print">Print</button>
        </div>
      </div>
      <div class="card-datatable text-nowrap">
        <table class="datatables-kwitansi-voucher table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
          <thead>
            <tr>
              <th>No</th>
              <th>No. Voucher</th>
              <th>Tanggal Lunas</th>
              <th>No. Kwitansi</th>
              <th>No. SPK</th>
              <th>No. Invoice</th>
              <th>No. Estimasi</th>
              <th>No. Polisi</th>
              <th>Jasa</th>
              <th>Bahan</th>
              <th>Sparepart</th>
              <th>PPN</th>
              <th>Lain</th>
              <th>OR</th>
              <th>Tagihan</th>
              <th>PPh</th>
              <th>Materai & Transfer</th>
              <th>Uang Muka</th>
              <th>Diterima</th>
              <th>Total Estimasi</th>
              <th>Biaya Real</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>
