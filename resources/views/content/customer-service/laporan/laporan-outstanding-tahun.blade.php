<div class="row mt-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Laporan Rekap Outstanding OR per Tahun</h5>
        </div>
        <div class="demo-inline-spacing">
          <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
          <button type="button" class="btn btn-primary btn-print">Print</button>
        </div>
      </div>
      <div class="card-datatable">
        <table class="datatables-outstanding-tahun table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
          <thead>
            <tr>
              <th rowspan="2">No</th>
              <th rowspan="2">Nama Asuransi</th>
              <th colspan="12" style="text-align: center;">Tahun {{ @$datafilter['tahun'] }}</th>
              <th rowspan="2">Total</th>
            </tr>
            <tr>
              <th>JAN</th>
              <th>FEB</th>
              <th>MAR</th>
              <th>APR</th>
              <th>MEI</th>
              <th>JUN</th>
              <th>JUL</th>
              <th>AGS</th>
              <th>SEP</th>
              <th>OKT</th>
              <th>NOV</th>
              <th>DES</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>