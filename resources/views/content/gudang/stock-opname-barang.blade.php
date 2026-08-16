<div class="row mt-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Stock dan Saldo {{ $nama_barang }}</h5>
        </div>
        <div class="demo-inline-spacing">
          <button type="button" class="btn btn-primary">Adjust Saldo</button>
          <button type="button" class="btn btn-primary btn-konsolidasi">Konsolidasi Saldo</button>
          <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
        </div>
      </div>
      <div class="card-datatable">
        <table class="datatables-barang table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
          <thead>
            <tr>
              <th rowspan="2" class="text-center align-middle">No</th>
              <th rowspan="2" class="text-center align-middle">Nama Bahan</th>
              <th rowspan="2" class="text-center align-middle">Satuan</th>
              <th colspan="3" class="text-center">Saldo Awal</th>
              <th colspan="3" class="text-center">Penambahan</th>
              <th colspan="3" class="text-center">Pengurangan</th>
              <th colspan="3" class="text-center">Retur</th>
              <th colspan="3" class="text-center">Adjust</th>
              <th colspan="3" class="text-center">Saldo Akhir</th>
            </tr>
            <tr>
              <th class="text-center">Qty</th>
              <th class="text-center">Harga</th>
              <th class="text-center">Jumlah</th>
              <th class="text-center">Qty</th>
              <th class="text-center">Harga</th>
              <th class="text-center">Jumlah</th>
              <th class="text-center">Qty</th>
              <th class="text-center">Harga</th>
              <th class="text-center">Jumlah</th>
              <th class="text-center">Qty</th>
              <th class="text-center">Harga</th>
              <th class="text-center">Jumlah</th>
              <th class="text-center">Qty</th>
              <th class="text-center">Harga</th>
              <th class="text-center">Jumlah</th>
              <th class="text-center">Qty</th>
              <th class="text-center">Harga</th>
              <th class="text-center">Jumlah</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>