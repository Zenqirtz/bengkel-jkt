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

        @media print {
            @page {
                size: landscape;
            }
        }

        .invoice-print .table thead tr th[rowspan] {
            vertical-align: top;
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
                <div class="d-flex svg-illustration mb-4 gap-2">
                    <span class="app-brand-text fw-bold">{{ $namaCabang }}</span>
                </div>
                <p class="mb-1">{{ $title }}</p>
                <p class="mb-1">Periode : {{ $periodeStr }}</p>
            </div>
        </div>

        <hr class="mb-6" />

        <div class="border-bottom-0 border-top-0 rounded">
            <table class="table m-0">
                <thead>
                    <tr>
                        <th rowspan="2">No</th>
                        <th rowspan="2">Nama Asuransi</th>
                        <th rowspan="2">Unit</th>
                        <th colspan="4" class="text-center">Perbaikan</th>
                        <th colspan="4" class="text-center">Sparepart</th>
                        <th colspan="4" class="text-center">Lain-lain</th>
                        <th rowspan="2">Total R</th>
                        <th rowspan="2">Total S</th>
                        <th rowspan="2">Total T</th>
                        <th rowspan="2">PPN</th>
                        <th rowspan="2">Total</th>
                    </tr>
                    <tr>
                        <th class="text-center">R</th>
                        <th class="text-center">S</th>
                        <th class="text-center">T</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">R</th>
                        <th class="text-center">S</th>
                        <th class="text-center">T</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">R</th>
                        <th class="text-center">S</th>
                        <th class="text-center">T</th>
                        <th class="text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grandUnit = 0;
                        $grandPerbaikanR = 0;
                        $grandPerbaikanS = 0;
                        $grandPerbaikanT = 0;
                        $grandTotalPerbaikan = 0;
                        $grandSparepartR = 0;
                        $grandSparepartS = 0;
                        $grandSparepartT = 0;
                        $grandTotalSparepart = 0;
                        $grandLainR = 0;
                        $grandLainS = 0;
                        $grandLainT = 0;
                        $grandTotalLain = 0;
                        $grandTotalR = 0;
                        $grandTotalS = 0;
                        $grandTotalT = 0;
                        $grandPPN = 0;
                        $grandTotal = 0;
                    @endphp
                    @foreach ($datas as $row)
                        <tr>
                            <td class="text-center">{{ $no++ }}</td>
                            <td>{{ $row->nama_pelanggan }}</td>
                            <td class="text-center">{{ $row->unit }}</td>
                            <td class="text-end">{{ number_format($row->perbaikan_r, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->perbaikan_s, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->perbaikan_t, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->total_perbaikan, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->sparepart_r, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->sparepart_s, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->sparepart_t, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->total_sparepart, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->lain_r, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->lain_s, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->lain_t, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->total_lain, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->total_r, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->total_s, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->total_t, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->ppn, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->total, 0, ',', '.') }}</td>
                        </tr>
                        @php
                            $grandUnit += $row->unit;
                            $grandPerbaikanR += $row->perbaikan_r;
                            $grandPerbaikanS += $row->perbaikan_s;
                            $grandPerbaikanT += $row->perbaikan_t;
                            $grandTotalPerbaikan += $row->total_perbaikan;
                            $grandSparepartR += $row->sparepart_r;
                            $grandSparepartS += $row->sparepart_s;
                            $grandSparepartT += $row->sparepart_t;
                            $grandTotalSparepart += $row->total_sparepart;
                            $grandLainR += $row->lain_r;
                            $grandLainS += $row->lain_s;
                            $grandLainT += $row->lain_t;
                            $grandTotalLain += $row->total_lain;
                            $grandTotalR += $row->total_r;
                            $grandTotalS += $row->total_s;
                            $grandTotalT += $row->total_t;
                            $grandPPN += $row->ppn;
                            $grandTotal += $row->total;
                        @endphp
                    @endforeach
                    <tr style="font-weight: bold;">
                        <td colspan="2"></td>
                        <td class="text-center">{{ $grandUnit }}</td>
                        <td class="text-end">{{ number_format($grandPerbaikanR, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($grandPerbaikanS, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($grandPerbaikanT, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($grandTotalPerbaikan, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($grandSparepartR, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($grandSparepartS, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($grandSparepartT, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($grandTotalSparepart, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($grandLainR, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($grandLainS, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($grandLainT, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($grandTotalLain, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($grandTotalR, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($grandTotalS, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($grandTotalT, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($grandPPN, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
@endsection
