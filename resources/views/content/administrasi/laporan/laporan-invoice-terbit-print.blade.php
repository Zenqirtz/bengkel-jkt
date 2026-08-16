{{-- @extends('layouts/layoutMaster')

@section('title', $title)

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
        }
    </style>
@endsection

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
                    <span class="app-brand-text fw-bold">{{ $namaCabang }}</span>
                </div>
                <p class="mb-1">Laporan Invoice Terbit - {{ $jenis_report }}</p>
                <p class="mb-1">Bulan / Tahun : {{ $periodeStr }}</p>
                <p class="mb-1">Laporan Invoice Terbit - {{ $jenis_report }}</p>
                <p class="mb-1">Periode : {{ $periodeStr }}</p>
            </div>
        </div>

        <hr class="mb-6" />

        <div class="border-bottom-0 border-top-0 rounded">
            @if ($jenis_report === 'Rekap')
                <table class="table m-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Jenis Pelanggan</th>
                            <th>Nama Pelanggan</th>
                            <th>Jumlah Invoice</th>
                            <th>Total OR</th>
                            <th>Free</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalInvoice = 0;
                            $totalOr = 0;
                            $totalFree = 0;
                        @endphp
                        @foreach ($datas as $row)
                            @php
                                $totalInvoice += $row->jumlah_invoice;
                                $totalOr += $row->total_or;
                                $totalFree += $row->free;
                            @endphp
                            <tr>
                                <td style="text-align:center;">{{ $no++ }}</td>
                                <td>{{ $row->jenis_pelanggan }}</td>
                                <td>{{ $row->nama_pelanggan }}</td>
                                <td style="text-align:right;">{{ number_format($row->jumlah_invoice, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->total_or, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->free, 0, '.', '.') }}</td>
                            </tr>
                        @endforeach
                        @if (count($datas) > 0)
                            <tr class="row-total">
                                <td colspan="3" style="text-align:right;">Grand Total</td>
                                <td style="text-align:right;">{{ number_format($totalInvoice, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalOr, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalFree, 0, '.', '.') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <table class="table m-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Asuransi</th>
                            <th>Unit</th>
                            <th>Jasa</th>
                            <th>Bahan</th>
                            <th>Sparepart</th>
                            <th>PPN</th>
                            <th>Total Lain</th>
                            <th>Total Invoice</th>
                            <th>Total OR</th>
                            <th>Tagihan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalUnit = 0;
                            $totalJasa = 0;
                            $totalBahan = 0;
                            $totalSparepart = 0;
                            $totalPpn = 0;
                            $totalLain = 0;
                            $totalInvoice = 0;
                            $totalOr = 0;
                            $totalTagihan = 0;
                        @endphp
                        @foreach ($datas as $row)
                            @php
                                $totalUnit += $row->unit;
                                $totalJasa += $row->jasa;
                                $totalBahan += $row->bahan;
                                $totalSparepart += $row->sparepart;
                                $totalPpn += $row->ppn;
                                $totalLain += $row->total_lain;
                                $totalInvoice += $row->total_invoice;
                                $totalOr += $row->total_or;
                                $totalTagihan += $row->tagihan;
                            @endphp
                            <tr>
                                <td style="text-align:center;">{{ $no++ }}</td>
                                <td>{{ $row->nama_pelanggan }}</td>
                                <td style="text-align:right;">{{ number_format($row->unit, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->jasa, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->bahan, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->sparepart, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->ppn, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->total_lain, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->total_invoice, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->total_or, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->tagihan, 0, '.', '.') }}</td>
                            </tr>
                        @endforeach
                        @if (count($datas) > 0)
                            <tr class="row-total">
                                <td colspan="2" style="text-align:right;">Grand Total</td>
                                <td style="text-align:right;">{{ number_format($totalUnit, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalJasa, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalBahan, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalSparepart, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalPpn, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalLain, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalInvoice, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalOr, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalTagihan, 0, '.', '.') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            @else
                <table class="table m-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No Invoice</th>
                            <th>Tgl Invoice</th>
                            <th>No SPK</th>
                            <th>No Polisi</th>
                            <th>Kendaraan</th>
                            <th>Jenis Pelanggan</th>
                            <th>Nama Pelanggan</th>
                            <th>Tertanggung</th>
                            <th>Pemilik</th>
                            <th>Jenis Identitas</th>
                            <th>No Identitas</th>
                            <th>Total OR</th>
                            <th>Free</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalOr = 0;
                            $totalFree = 0;
                        @endphp
                        @foreach ($datas as $row)
                            @php
                                $totalOr += $row->total_or;
                                $totalFree += $row->free;
                                $jenisIdentitas =
                                    strtolower(trim((string) $row->jenis_pelanggan)) === 'asuransi' ? 'NPWP' : 'KTP';
                            @endphp
                            <tr>
                                <td style="text-align:center;">{{ $no++ }}</td>
                                <td>{{ $row->no_invoice }}</td>
                                <td style="text-align:center;">
                                    {{ $row->tgl_invoice ? \Carbon\Carbon::parse($row->tgl_invoice)->format('d/m/Y') : '' }}
                                </td>
                                <td>{{ $row->kode_spk }}</td>
                                <td>{{ $row->no_polisi }}</td>
                                <td>{{ $row->merek_tipe }}</td>
                                <td>{{ $row->jenis_pelanggan }}</td>
                                <td>{{ $row->nama_pelanggan }}</td>
                                <td>{{ $row->tertanggung }}</td>
                                <td>{{ $row->pemilik }}</td>
                                <td style="text-align:center;">{{ $jenisIdentitas }}</td>
                                <td>{{ $row->no_identitas ?? '-' }}</td>
                                <td style="text-align:right;">{{ number_format($row->total_or, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->free, 0, '.', '.') }}</td>
                            </tr>
                        @endforeach
                        @if (count($datas) > 0)
                            <tr class="row-total">
                                <td colspan="5" style="text-align:right;">Grand Total</td>
                                <td colspan="7"></td>
                                <td style="text-align:right;">{{ number_format($totalOr, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalFree, 0, '.', '.') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <table class="table m-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No Invoice</th>
                            <th>No SPK</th>
                            <th>No Polisi</th>
                            <th>Nama Asuransi</th>
                            <th>NPWP/KTP</th>
                            <th>Jasa</th>
                            <th>Bahan</th>
                            <th>Sparepart</th>
                            <th>PPN</th>
                            <th>Total Lain</th>
                            <th>Total Invoice</th>
                            <th>Total OR</th>
                            <th>Tagihan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalJasa = 0;
                            $totalBahan = 0;
                            $totalSparepart = 0;
                            $totalPpn = 0;
                            $totalLain = 0;
                            $totalInvoice = 0;
                            $totalOr = 0;
                            $totalTagihan = 0;
                        @endphp
                        @foreach ($datas as $row)
                            @php
                                $totalJasa += $row->jasa;
                                $totalBahan += $row->bahan;
                                $totalSparepart += $row->sparepart;
                                $totalPpn += $row->ppn;
                                $totalLain += $row->total_lain;
                                $totalInvoice += $row->total_invoice;
                                $totalOr += $row->total_or;
                                $totalTagihan += $row->tagihan;
                            @endphp
                            <tr>
                                <td style="text-align:center;">{{ $no++ }}</td>
                                <td>{{ $row->no_invoice }}</td>
                                <td>{{ $row->kode_spk }}</td>
                                <td>{{ $row->no_polisi }}</td>
                                <td>{{ $row->nama_pelanggan }}</td>
                                <td style="text-align:center;">{{ $row->no_identitas}}</td>
                                <td style="text-align:right;">{{ number_format($row->jasa, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->bahan, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->sparepart, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->ppn, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->total_lain, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->total_invoice, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->total_or, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($row->tagihan, 0, '.', '.') }}</td>
                            </tr>
                        @endforeach
                        @if (count($datas) > 0)
                            <tr class="row-total">
                                <td colspan="6" style="text-align:right;">Grand Total</td>
                                <td style="text-align:right;">{{ number_format($totalJasa, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalBahan, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalSparepart, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalPpn, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalLain, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalInvoice, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalOr, 0, '.', '.') }}</td>
                                <td style="text-align:right;">{{ number_format($totalTagihan, 0, '.', '.') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection --}}
@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('content.shared.laporan-print-style')
@endsection

@section('page-script')
    <script>
        'use strict';
        (function() {
            window.print();
        })();
    </script>
@endsection

@section('content')
    <div class="invoice-print">
        <div class="header-title">{{ $namaCabang }}</div>
        <div class="header-sub">Laporan Invoice Terbit - {{ $jenis_report }}</div>
        <div class="header-sub">Periode : {{ $periodeStr }}</div>

        @if ($jenis_report === 'Rekap')
            <table class="report-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Asuransi</th>
                        <th>Unit</th>
                        <th>Jasa</th>
                        <th>Bahan</th>
                        <th>Sparepart</th>
                        <th>PPN</th>
                        <th>Total Lain</th>
                        <th>Total Invoice</th>
                        <th>Total OR</th>
                        <th>Tagihan</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalUnit = 0;
                        $totalJasa = 0;
                        $totalBahan = 0;
                        $totalSparepart = 0;
                        $totalPpn = 0;
                        $totalLain = 0;
                        $totalInvoice = 0;
                        $totalOr = 0;
                        $totalTagihan = 0;
                    @endphp
                    @foreach ($datas as $row)
                        @php
                            $totalUnit += $row->unit;
                            $totalJasa += $row->jasa;
                            $totalBahan += $row->bahan;
                            $totalSparepart += $row->sparepart;
                            $totalPpn += $row->ppn;
                            $totalLain += $row->total_lain;
                            $totalInvoice += $row->total_invoice;
                            $totalOr += $row->total_or;
                            $totalTagihan += $row->tagihan;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $no++ }}</td>
                            <td>{{ $row->nama_pelanggan }}</td>
                            <td class="text-right">{{ number_format($row->unit, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($row->jasa, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($row->bahan, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($row->sparepart, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($row->ppn, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($row->total_lain, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($row->total_invoice, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($row->total_or, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($row->tagihan, 0, '.', '.') }}</td>
                        </tr>
                    @endforeach
                    @if (count($datas) > 0)
                        <tr class="row-total">
                            <td colspan="2" class="text-right">Grand Total</td>
                            <td class="text-right">{{ number_format($totalUnit, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($totalJasa, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($totalBahan, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($totalSparepart, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($totalPpn, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($totalLain, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($totalInvoice, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($totalOr, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($totalTagihan, 0, '.', '.') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        @else
            <table class="report-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No Invoice</th>
                        <th>No SPK</th>
                        <th>No Polisi</th>
                        <th>Nama Asuransi</th>
                        <th>NPWP/KTP</th>
                        <th>Jasa</th>
                        <th>Bahan</th>
                        <th>Sparepart</th>
                        <th>PPN</th>
                        <th>Total Lain</th>
                        <th>Total Invoice</th>
                        <th>Total OR</th>
                        <th>Tagihan</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalJasa = 0;
                        $totalBahan = 0;
                        $totalSparepart = 0;
                        $totalPpn = 0;
                        $totalLain = 0;
                        $totalInvoice = 0;
                        $totalOr = 0;
                        $totalTagihan = 0;
                    @endphp
                    @foreach ($datas as $row)
                        @php
                            $totalJasa += $row->jasa;
                            $totalBahan += $row->bahan;
                            $totalSparepart += $row->sparepart;
                            $totalPpn += $row->ppn;
                            $totalLain += $row->total_lain;
                            $totalInvoice += $row->total_invoice;
                            $totalOr += $row->total_or;
                            $totalTagihan += $row->tagihan;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $no++ }}</td>
                            <td>{{ $row->no_invoice }}</td>
                            <td>{{ $row->kode_spk }}</td>
                            <td>{{ $row->no_polisi }}</td>
                            <td>{{ $row->nama_pelanggan }}</td>
                            <td class="text-center">{{ $row->no_identitas }}</td>
                            <td class="text-right">{{ number_format($row->jasa, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($row->bahan, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($row->sparepart, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($row->ppn, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($row->total_lain, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($row->total_invoice, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($row->total_or, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($row->tagihan, 0, '.', '.') }}</td>
                        </tr>
                    @endforeach
                    @if (count($datas) > 0)
                        <tr class="row-total">
                            <td colspan="6" class="text-right">Grand Total</td>
                            <td class="text-right">{{ number_format($totalJasa, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($totalBahan, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($totalSparepart, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($totalPpn, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($totalLain, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($totalInvoice, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($totalOr, 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($totalTagihan, 0, '.', '.') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        @endif
    </div>
@endsection
