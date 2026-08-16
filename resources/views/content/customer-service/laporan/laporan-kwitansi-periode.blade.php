<div class="row mt-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Laporan Kwitansi OR Lunas</h5>
        </div>
        <div class="demo-inline-spacing">
          <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
          <button type="button" class="btn btn-primary btn-print">Print</button>
        </div>
      </div>
      <div class="card-datatable text-nowrap">
        <table class="datatables-kwitansi-periode table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
          <thead>
            <tr>
              <th>No</th>
              <th>Tanggal Lunas</th>
              <th>No. Voucher</th>
              <th>No. Kwitansi</th>
              <th>No. SPK</th>
              <th>No. Polisi</th>
              <th>Tertanggung</th>
              <th>Nama Asuransi</th>
              <th>No. Invoice</th>
              <th>Tanggal Invoice</th>
              <th>Kas</th>
              <th>Free</th>
              <th>Bank</th>
              <th>Total OR</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th colspan="10" class="text-center">Total</th>
              <th id="grand-total-kas" class="text-end">0</th>
              <th id="grand-total-free" class="text-end">0</th>
              <th id="grand-total-bank" class="text-end">0</th>
              <th id="grand-total-or" class="text-end">0</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>