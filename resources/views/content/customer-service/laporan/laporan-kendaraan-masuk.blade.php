<div class="row mt-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Laporan Mobil Masuk per Asuransi</h5>
        </div>
        <div class="demo-inline-spacing">
          <button type="button" class="btn btn-primary btn-export-excel">Export Excel</button>
          <button type="button" class="btn btn-primary btn-print">Print</button>
        </div>
      </div>
      <div class="card-datatable">
        <table class="datatables-kendaraan-masuk table table-bordered table-responsive" data-title="{{ $title }}" data-add="{{ $isAdd }}" data-edit="{{ $isEdit }}" data-delete="{{ $isDel }}">
          <thead>
            <tr>
              <th>Nama Asuransi</th>
              @foreach (@$dataMobilMasuk['fields'] as $key => $row)
              @if ($datafilter['jenis_laporan'] == "tahun")
              <th>{{ date("m", strtotime($row)) }}<br>({{ date("M", strtotime($row)) }})</th>
              @else
              <th>{{ date("d/m/Y", strtotime($row)) }}</th>  
              @endif
              @endforeach
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach (@$dataMobilMasuk['data'] as $key => $row)
              @php $total = 0; @endphp
              <tr>
                <td>{{ $key }}</td>
                @foreach (@$dataMobilMasuk['fields'] as $key2 => $row2)
                @if (isset($row[$key2]))
                @php $total += $row[$key2]; @endphp
                <td style="text-align: center;">{{ $row[$key2] }}</td>
                @else
                <td style="text-align: center;">0</td>
                @endif
                @endforeach
                <td style="text-align: center;">{{ $total }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td>Total</td>
              @php $total = 0; @endphp
              @foreach (@$dataMobilMasuk['total'] as $key3 => $row3)
              @php $total += $row3; @endphp
              <td style="text-align: center;">{{ $row3 }}</td>
              @endforeach
              <td style="text-align: center;">{{ $total }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>