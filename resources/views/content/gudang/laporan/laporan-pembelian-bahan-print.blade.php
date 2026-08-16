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
                    <span class="app-brand-text fw-bold">{{ $namaCabang }}</span>
                </div>
                <p class="mb-1">Laporan Pembelian Bahan</p>
                <p class="mb-1">Periode : {{ $periodeStr }}</p>
            </div>
        </div>

        <hr class="mb-6" />

        <div class="border-bottom-0 border-top-0 rounded">
            <table class="table m-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>No Input Gudang</th>
                        <th>No. PO</th>
                        <th>Nama Pemasok</th>
                        <th>Kode</th>
                        <th>Nama Bahan</th>
                        <th>Unit</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>PPN</th>
                        <th>Total</th>
                    </tr>
                </thead>
                {{-- <tbody>
                    @foreach ($datas as $row)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td style="text-align: center;">{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ $row->kode_input }}</td>
                            <td>{{ $row->no_po ?? '' }}</td>
                            <td>{{ $row->nama_pemasok }}</td>
                            <td>{{ $row->kode_bahan }}</td>
                            <td>{{ $row->nama_bahan }}</td>
                            <td style="text-align: right;">{{ number_format($row->qty, 2, '.', '.') }}</td>
                            <td style="text-align: center;">{{ $row->kode_satuan }}</td>
                            <td style="text-align: right;">{{ number_format($row->harga, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->jumlah_sebelum, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->ppn, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->jumlah, 0, '.', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody> --}}
                <tbody>
                    @php
                        $grandTotal = ['unit' => 0, 'jumlah' => 0, 'ppn' => 0, 'total' => 0];
                    @endphp
                    @foreach ($datas as $row)
                        @php
                            $grandTotal['unit'] += $row->qty;
                            $grandTotal['jumlah'] += $row->jumlah_sebelum;
                            $grandTotal['ppn'] += $row->ppn;
                            $grandTotal['total'] += $row->jumlah;
                        @endphp
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td style="text-align: center;">{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ $row->kode_input }}</td>
                            <td>{{ $row->no_po ?? '' }}</td>
                            <td>{{ $row->nama_pemasok }}</td>
                            <td>{{ $row->kode_bahan }}</td>
                            <td>{{ $row->nama_bahan }}</td>
                            <td style="text-align: right;">{{ number_format($row->qty, 2, '.', '.') }}</td>
                            <td style="text-align: center;">{{ $row->kode_satuan }}</td>
                            <td style="text-align: right;">{{ number_format($row->harga, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->jumlah_sebelum, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->ppn, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->jumlah, 0, '.', '.') }}</td>
                        </tr>
                    @endforeach
                    @if (count($datas) > 0)
                        <tr class="row-total">
                            <td colspan="7" style="text-align: right; white-space: nowrap;">Grand Total</td>
                            <td style="text-align: right;">{{ number_format($grandTotal['unit'], 2, '.', '.') }}</td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right;">{{ number_format($grandTotal['jumlah'], 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($grandTotal['ppn'], 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($grandTotal['total'], 0, '.', '.') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
