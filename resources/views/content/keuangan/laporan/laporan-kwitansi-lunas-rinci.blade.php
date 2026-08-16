<div class="row mt-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Laporan Kwitansi Lunas Rinci</h5>
        </div>
        <div class="demo-inline-spacing">
          <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
          <button type="button" class="btn btn-primary btn-print">Print</button>
        </div>
      </div>
      <div class="card-datatable text-nowrap">
        <table class="datatables-kwitansi-rinci table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
          <thead>
            <tr>
              <th rowspan="2">No</th>
              <th rowspan="2">No. Voucher</th>
              <th rowspan="2">Tanggal Lunas</th>
              <th rowspan="2">No. Kwitansi</th>
              <th rowspan="2">No. SPK</th>
              <th rowspan="2">No. Invoice</th>
              <th rowspan="2">No. Estimasi</th>
              <th rowspan="2">No. Polisi</th>
              <th rowspan="2">Merek Tipe</th>
              <th rowspan="2">Nama Asuransi</th>
              <th colspan="3">Pembayaran Via</th>
              <th rowspan="2">Uang Muka</th>
              <th rowspan="2">PPh</th>
              <th rowspan="2">Materai & Transfer</th>
              <th rowspan="2">Tagihan</th>
              <th rowspan="2">Diterima</th>
              <th rowspan="2">Estimasi</th>
              <th rowspan="2">Biaya</th>
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
