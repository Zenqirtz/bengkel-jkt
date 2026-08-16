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
                <p class="mb-1">Laporan Analisa Pemakaian Cat</p>
                <p class="mb-1">Bulan / Tahun : {{ $periodeStr }}</p>
                <p class="mb-1">Jumlah Panel : {{ number_format($jumlahPanel, 2, '.', '.') }}</p>
            </div>
        </div>

        <hr class="mb-6" />

        <div class="border-bottom-0 border-top-0 rounded">
            <table class="table m-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Bahan</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                        <th>Qty/ Point Panel</th>
                        <th>Rupiah/ Point Panel</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalJumlah = 0;
                        $totalQtyPoint = 0;
                        $totalRupiahPoint = 0;
                    @endphp

                    @foreach ($datas as $index => $row)
                        @php
                            $totalJumlah += $row->jumlah;
                            $totalQtyPoint += $row->qty_per_point;
                            $totalRupiahPoint += $row->rupiah_per_point;
                        @endphp

                        <tr>
                            <td style="text-align: center;">{{ $no++ }}</td>
                            <td>{{ $row->nama_bahan }}</td>
                            <td style="text-align: right;">{{ number_format($row->qty, 2, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->harga, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->jumlah, 0, '.', '.') }}</td>
                            <td>{{ $row->satuan ?? '' }}</td>
                            <td style="text-align: right;">{{ number_format($row->qty_per_point, 2, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->rupiah_per_point, 0, '.', '.') }}</td>
                        </tr>
                    @endforeach

                    @if (count($datas) > 0)
                        {{-- <tr style="font-weight: bold;"> --}}
                        <tr class="row-total">
                            {{-- <td colspan="4" style="text-align: right;"></td> --}}
                            <td colspan="4" style="text-align: right;">Grand Total</td>
                            <td style="text-align: right;">{{ number_format($totalJumlah, 0, '.', '.') }}</td>
                            <td></td>
                            <td style="text-align: right;">{{ number_format($totalQtyPoint, 2, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($totalRupiahPoint, 0, '.', '.') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
