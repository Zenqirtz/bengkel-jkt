{{-- @extends('layouts/layoutMaster')

@section('title', $title)

<!-- Vendor Styles -->
@section('vendor-style')
<style>
html,
body {
  background: var(--bs-white);
}
body > :not(.invoice-print) {
  display: none !important;
}
.invoice-print {
  font-size: 15px;
  min-inline-size: 768px !important;
}
.invoice-print * {
  color: #676a7b !important;
}
.invoice-print .table thead tr th {
  background-color: var(--bs-table-header-bg-color);
}
.invoice-print .text-primary * {
  color: var(--bs-primary) !important;
}
[data-bs-theme="dark"] .invoice-print th {
  color: var(--bs-white) !important;
}
@media print {
  @page {
    size: landscape;
    /* Opsi lain: margin: 0; */
  }
}
</style>
@endsection

<!-- Page Scripts -->
@section('page-script')
<script>
'use strict';
(function () {
  window.print();
})();
</script>
@endsection

@section('content')
<div class="invoice-print p-12">
  <div class="d-flex justify-content-between flex-row">
    <div class="mb-6">
      <div class="d-flex svg-illustration mb-6 gap-2">
        <span class="app-brand-text fw-bold">{{ $title }}</span>
      </div>
      <p class="mb-1">Cabang : {{ $namaCabang }}</p>
      <p class="mb-1">Periode : {{ $periodeStr }}</p>
    </div>
  </div>

  <hr class="mb-6" />

  @if ($datafilter['jenis_laporan'] == 'voucher')
  <div class="border-bottom-0 border-top-0 rounded">
    <table class="table m-0">
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
      <tbody>
        @foreach ($datas as $row)
        <tr>
          <td>{{ $no++ }}</td>
          <td>{{ $row->kode_voucher }}</td>
          <td>{{ blank($row->tgl_lunas) ? '' : date("d/m/Y", strtotime($row->tgl_lunas ))}}</td>
          <td>{{ $row->kode_lunas_kwitansi }}</td>
          <td>{{ $row->kode_spk }}</td>
          <td>{{ $row->kode_kwitansi }}</td>
          <td>{{ $row->kode_estimasi }}</td>
          <td>{{ $row->no_polisi }}</td>
          <td>{{ number_format($row->jasa,0,".",",") }}</td>
          <td>{{ number_format($row->bahan,0,".",",") }}</td>
          <td>{{ number_format($row->total_sparepart_s,0,".",",") }}</td>
          <td>{{ number_format($row->ppn,0,".",",") }}</td>
          <td>{{ number_format($row->total_lain_s,0,".",",") }}</td>
          <td>{{ number_format($row->total_or_ass,0,".",",") }}</td>
          <td>{{ number_format($row->tagihan,0,".",",") }}</td>
          <td>{{ number_format($row->pph,0,".",",") }}</td>
          <td>{{ number_format($row->materai,0,".",",") }}</td>
          <td>{{ number_format($row->uang_muka,0,".",",") }}</td>
          <td>{{ number_format($row->diterima,0,".",",") }}</td>
          <td>{{ number_format($row->tot_estimasi,0,".",",") }}</td>
          <td>{{ number_format($row->biaya_real,0,".",",") }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @elseif($datafilter['jenis_laporan'] == "rekap")
  <div class="border-bottom-0 border-top-0 rounded">
    <table class="table m-0">
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
      <tbody>
        @foreach ($datas as $row)
        <tr>
          <td>{{ $no++ }}</td>
          <td>{{ $row->nama_pelanggan }}</td>
          <td>{{ $row->unit }}</td>
          <td>{{ number_format($row->tunai,0,".",",") }}</td>
          <td>{{ number_format($row->bank,0,".",",") }}</td>
          <td>{{ number_format($row->free,0,".",",") }}</td>
          <td>{{ number_format($row->uang_muka,0,".",",") }}</td>
          <td>{{ number_format($row->pph,0,".",",") }}</td>
          <td>{{ number_format($row->materai,0,".",",") }}</td>
          <td>{{ number_format($row->tagihan,0,".",",") }}</td>
          <td>{{ number_format($row->diterima,0,".",",") }}</td>
          <td>{{ number_format($row->materai,0,".",",") }}</td>
          <td>{{ number_format($row->uang_muka,0,".",",") }}</td>
          <td>{{ number_format($row->diterima,0,".",",") }}</td>
          <td>{{ number_format($row->tot_estimasi,0,".",",") }}</td>
          <td>{{ number_format($row->perbaikan,0,".",",") }}</td>
          <td>{{ number_format($row->sparepart,0,".",",") }}</td>
          <td>{{ number_format($row->lain,0,".",",") }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @elseif($datafilter['jenis_laporan'] == "rinci")
  <div class="border-bottom-0 border-top-0 rounded">
    <table class="table m-0">
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
      <tbody>
        @foreach ($datas as $row)
        <tr>
          <td>{{ $no++ }}</td>
          <td>{{ $row->kode_voucher }}</td>
          <td>{{ blank($row->tgl_lunas) ? '' : date("d/m/Y", strtotime($row->tgl_lunas ))}}</td>
          <td>{{ $row->kode_lunas_kwitansi }}</td>
          <td>{{ $row->kode_spk }}</td>
          <td>{{ $row->kode_kwitansi }}</td>
          <td>{{ $row->kode_estimasi }}</td>
          <td>{{ $row->no_polisi }}</td>
          <td>{{ $row->merek_tipe }}</td>
          <td>{{ $row->nama_pelanggan }}</td>
          <td>{{ number_format($row->tunai,0,".",",") }}</td>
          <td>{{ number_format($row->bank,0,".",",") }}</td>
          <td>{{ number_format($row->free,0,".",",") }}</td>
          <td>{{ number_format($row->uang_muka,0,".",",") }}</td>
          <td>{{ number_format($row->pph,0,".",",") }}</td>
          <td>{{ number_format($row->materai,0,".",",") }}</td>
          <td>{{ number_format($row->tagihan,0,".",",") }}</td>
          <td>{{ number_format($row->diterima,0,".",",") }}</td>
          <td>{{ number_format($row->tot_estimasi,0,".",",") }}</td>
          <td>{{ number_format($row->biaya,0,".",",") }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif



</div>
@endsection --}}
@extends('layouts/layoutMaster')

@section('title', $title)

<!-- Vendor Styles -->
@section('vendor-style')
    <style>
        html,
        body {
            background: var(--bs-white);
        }

        body> :not(.invoice-print) {
            display: none !important;
        }

        .invoice-print {
            font-size: 15px;
            min-inline-size: 768px !important;
        }

        .invoice-print * {
            color: #676a7b !important;
        }

        .invoice-print .table thead tr th {
            background-color: var(--bs-table-header-bg-color);
        }

        .invoice-print .text-primary * {
            color: var(--bs-primary) !important;
        }

        [data-bs-theme="dark"] .invoice-print th {
            color: var(--bs-white) !important;
        }

        .row-total td {
            background-color: #DBEAFE !important;
            color: #1E40AF !important;
            font-weight: bold;
            border-top: 2px solid #3B82F6;
            border-bottom: 2px solid #3B82F6;
        }

        @media print {
            @page {
                size: landscape;
            }

            .voucher-group {
                page-break-after: always;
                break-after: page;
            }

            .voucher-group:last-child {
                page-break-after: auto;
                break-after: auto;
            }

            .voucher-group tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .voucher-group thead {
                display: table-header-group;
            }
        }
    </style>
@endsection

<!-- Page Scripts -->
@section('page-script')
    <script>
        'use strict';
        (function() {
            window.print();
        })();
    </script>
@endsection

@section('content')
    <div class="invoice-print p-12">
        <div class="d-flex justify-content-between flex-row">
            <div class="mb-6">
                <div class="d-flex svg-illustration mb-6 gap-2">
                    <span class="app-brand-text fw-bold">{{ $title }}</span>
                </div>
                <p class="mb-1">Cabang : {{ $namaCabang }}</p>
                <p class="mb-1">Periode : {{ $periodeStr }}</p>
            </div>
        </div>

        <hr class="mb-6" />

        @if ($datafilter['jenis_laporan'] == 'voucher')
            @foreach ($datas as $kodeVoucher => $rows)
                <div class="voucher-group border-bottom-0 border-top-0 rounded">
                    <table class="table m-0">
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
                        <tbody>
                            <tr class="table-light">
                                <td colspan="20" style="font-weight:bold;">
                                    Asuransi &nbsp;&nbsp;&nbsp;: {{ $rows->first()->nama_pelanggan }}<br>
                                    No. voucher : {{ $kodeVoucher }}
                                </td>
                            </tr>
                            @foreach ($rows as $row)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $row->kode_voucher }}</td>
                                    <td>{{ blank($row->tgl_lunas) ? '' : date('d/m/Y', strtotime($row->tgl_lunas)) }}</td>
                                    <td>{{ $row->kode_lunas_kwitansi }}</td>
                                    <td>{{ $row->kode_spk }}</td>
                                    <td>{{ $row->kode_kwitansi }}</td>
                                    <td>{{ $row->kode_estimasi }}</td>
                                    <td>{{ $row->no_polisi }}</td>
                                    <td>{{ number_format($row->jasa, 0, '.', ',') }}</td>
                                    <td>{{ number_format($row->bahan, 0, '.', ',') }}</td>
                                    <td>{{ number_format($row->total_sparepart_s, 0, '.', ',') }}</td>
                                    <td>{{ number_format($row->ppn, 0, '.', ',') }}</td>
                                    <td>{{ number_format($row->total_lain_s, 0, '.', ',') }}</td>
                                    <td>{{ number_format($row->total_or_ass, 0, '.', ',') }}</td>
                                    <td>{{ number_format($row->tagihan, 0, '.', ',') }}</td>
                                    <td>{{ number_format($row->pph, 0, '.', ',') }}</td>
                                    <td>{{ number_format($row->materai, 0, '.', ',') }}</td>
                                    <td>{{ number_format($row->uang_muka, 0, '.', ',') }}</td>
                                    <td>{{ number_format($row->diterima, 0, '.', ',') }}</td>
                                    <td>{{ number_format($row->tot_estimasi, 0, '.', ',') }}</td>
                                    <td>{{ number_format($row->biaya_real, 0, '.', ',') }}</td>
                                </tr>
                            @endforeach

                            <tr class="row-total">
                                <td colspan="8" style="text-align:right;">Grand Total {{ $kodeVoucher }}</td>
                                <td>{{ number_format($rows->sum('jasa'), 0, '.', ',') }}</td>
                                <td>{{ number_format($rows->sum('bahan'), 0, '.', ',') }}</td>
                                <td>{{ number_format($rows->sum('total_sparepart_s'), 0, '.', ',') }}</td>
                                <td>{{ number_format($rows->sum('ppn'), 0, '.', ',') }}</td>
                                <td>{{ number_format($rows->sum('total_lain_s'), 0, '.', ',') }}</td>
                                <td>{{ number_format($rows->sum('total_or_ass'), 0, '.', ',') }}</td>
                                <td>{{ number_format($rows->sum('tagihan'), 0, '.', ',') }}</td>
                                <td>{{ number_format($rows->sum('pph'), 0, '.', ',') }}</td>
                                <td>{{ number_format($rows->sum('materai'), 0, '.', ',') }}</td>
                                <td>{{ number_format($rows->sum('uang_muka'), 0, '.', ',') }}</td>
                                <td>{{ number_format($rows->sum('diterima'), 0, '.', ',') }}</td>
                                <td>{{ number_format($rows->sum('tot_estimasi'), 0, '.', ',') }}</td>
                                <td>{{ number_format($rows->sum('biaya_real'), 0, '.', ',') }}</td>
                            </tr>

                            <tr>
                                <td colspan="20" style="padding-top: 12px; padding-bottom: 12px;">
                                    @php $lastRow = $rows->last(); @endphp
                                    <div>
                                        <div>Tanggal Lunas
                                            &nbsp;&nbsp;{{ blank($lastRow->tgl_lunas) ? '' : date('d-M-Y', strtotime($lastRow->tgl_lunas)) }}
                                        </div>
                                        <div>Pembayaran &nbsp;&nbsp;&nbsp;&nbsp;{{ $lastRow->pembayaran }}</div>
                                        <div>{{ $lastRow->memo }}</div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endforeach
        @elseif($datafilter['jenis_laporan'] == 'rekap')
            <div class="border-bottom-0 border-top-0 rounded">
                <table class="table m-0">
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
                    <tbody>
                        @foreach ($datas as $row)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $row->nama_pelanggan }}</td>
                                <td>{{ $row->unit }}</td>
                                <td>{{ number_format($row->tunai, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->bank, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->free, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->uang_muka, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->pph, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->materai, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->tagihan, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->diterima, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->tot_estimasi, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->perbaikan, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->sparepart, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->lain, 0, '.', ',') }}</td>
                            </tr>
                        @endforeach

                        {{-- Grand Total --}}
                        @if (count($datas) > 0)
                            <tr class="row-total">
                                <td colspan="2" style="text-align:right;">Grand Total</td>
                                <td>{{ number_format($datas->sum('unit'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('tunai'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('bank'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('free'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('uang_muka'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('pph'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('materai'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('tagihan'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('diterima'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('tot_estimasi'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('perbaikan'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('sparepart'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('lain'), 0, '.', ',') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        @elseif($datafilter['jenis_laporan'] == 'rinci')
            <div class="border-bottom-0 border-top-0 rounded">
                <table class="table m-0">
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
                    <tbody>
                        @foreach ($datas as $row)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $row->kode_voucher }}</td>
                                <td>{{ blank($row->tgl_lunas) ? '' : date('d/m/Y', strtotime($row->tgl_lunas)) }}</td>
                                <td>{{ $row->kode_lunas_kwitansi }}</td>
                                <td>{{ $row->kode_spk }}</td>
                                <td>{{ $row->kode_kwitansi }}</td>
                                <td>{{ $row->kode_estimasi }}</td>
                                <td>{{ $row->no_polisi }}</td>
                                <td>{{ $row->merek_tipe }}</td>
                                <td>{{ $row->nama_pelanggan }}</td>
                                <td>{{ number_format($row->tunai, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->bank, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->free, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->uang_muka, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->pph, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->materai, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->tagihan, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->diterima, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->tot_estimasi, 0, '.', ',') }}</td>
                                <td>{{ number_format($row->biaya, 0, '.', ',') }}</td>
                            </tr>
                        @endforeach

                        {{-- Grand Total --}}
                        @if (count($datas) > 0)
                            <tr class="row-total">
                                <td colspan="10" style="text-align:right;">Grand Total</td>
                                <td>{{ number_format($datas->sum('tunai'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('bank'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('free'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('uang_muka'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('pph'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('materai'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('tagihan'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('diterima'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('tot_estimasi'), 0, '.', ',') }}</td>
                                <td>{{ number_format($datas->sum('biaya'), 0, '.', ',') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
