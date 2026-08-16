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

        {{-- Header --}}
        <div class="d-flex justify-content-between flex-row">
            <div class="mb-6">
                {{-- <div class="d-flex svg-illustration mb-2 gap-2">
                    <span class="app-brand-text fw-bold">{{ $namaCabang }}</span>
                </div> --}}
                <p class="mb-1 fw-bold">FORMULIR KONTROL PEMAKAIAN BAHAN</p>
            </div>
        </div>
        {{-- <div class="mb-3">
            <h5 class="fw-bold mb-1">FORMULIR KONTROL PEMAKAIAN BAHAN</h5>
        </div> --}}

        {{-- <hr class="mb-4" /> --}}

        {{-- Meta info --}}
        <div class="d-flex mb-4" style="gap: 80px;">
            <table style="font-size:14px; border:none; border-collapse:collapse;">
                <tr>
                    <td style="width:95px; padding:1px 0; font-weight:600;">No. SPK</td>
                    <td style="padding:1px 4px;">: {{ $no_spk }}</td>
                </tr>
                <tr>
                    <td style="padding:1px 0; font-weight:600;">Point Panel</td>
                    <td style="padding:1px 4px;">: {{ $meta['point_panel'] ?? '' }}</td>
                </tr>
            </table>
            <table style="font-size:14px; border:none; border-collapse:collapse;">
                <tr>
                    <td style="width:95px; padding:1px 0; font-weight:600;">Pemilik</td>
                    <td style="padding:1px 4px;">: {{ $meta['nama_pemilik'] ?? '' }}</td>
                </tr>
                <tr>
                    <td style="padding:1px 0; font-weight:600;">Merek Tipe</td>
                    <td style="padding:1px 4px;">: {{ $meta['merek_tipe'] ?? '' }}</td>
                </tr>
            </table>
        </div>

        {{-- Table --}}
        <div class="border-bottom-0 border-top-0 rounded">
            <table class="table m-0">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center align-middle">No</th>
                        <th rowspan="2" class="text-center align-middle">Bagian</th>
                        <th rowspan="2" class="text-center align-middle">Nama Bahan</th>
                        <th colspan="2" class="text-center">Standard Pemakaian</th>
                        <th colspan="3" class="text-center">Aktual Pemakaian</th>
                        <th colspan="2" class="text-center">Variance</th>
                    </tr>
                    <tr>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Harga</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Harga</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $no = 1;
                        $sumStdQty = 0;
                        $sumStdHarga = 0;
                        $sumAktualQty = 0;
                        $sumAktualHarga = 0;
                        $sumAktualTotal = 0;
                        $sumVarQty = 0;
                        $sumVarHarga = 0;
                    @endphp
                    @foreach ($datas as $row)
                        @php
                            $sumStdQty += $row->qty ?? 0;
                            $sumStdHarga += $row->harga ?? 0;
                            $sumAktualQty += $row->qty_actual ?? 0;
                            $sumAktualHarga += $row->harga_actual ?? 0;
                            $sumAktualTotal += $row->tot_harga_actual ?? 0;
                            $sumVarQty += $row->qty_variance ?? 0;
                            $sumVarHarga += $row->tot_harga_variance ?? 0;
                        @endphp
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $row->posisi_pekerjaan ?? '' }}</td>
                            <td>{{ $row->nama_bahan ?? '' }}</td>
                            <td class="text-end">{{ number_format($row->qty ?? 0, 2, '.', '.') }}</td>
                            <td class="text-end">{{ number_format($row->harga ?? 0, 0, '.', '.') }}</td>
                            <td class="text-end">{{ number_format($row->qty_actual ?? 0, 2, '.', '.') }}</td>
                            <td class="text-end">{{ number_format($row->harga_actual ?? 0, 0, '.', '.') }}</td>
                            <td class="text-end">{{ number_format($row->tot_harga_actual ?? 0, 0, '.', '.') }}</td>
                            <td class="text-end">{{ number_format($row->qty_variance ?? 0, 2, '.', '.') }}</td>
                            <td class="text-end">{{ number_format($row->tot_harga_variance ?? 0, 0, '.', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="row-total">
                        <td colspan="3" class="text-center">Grand Total</td>
                        <td class="text-end">{{ number_format($sumStdQty, 2, '.', '.') }}</td>
                        <td class="text-end">{{ number_format($sumStdHarga, 0, '.', '.') }}</td>
                        <td class="text-end">{{ number_format($sumAktualQty, 2, '.', '.') }}</td>
                        <td class="text-end">{{ number_format($sumAktualHarga, 0, '.', '.') }}</td>
                        <td class="text-end">{{ number_format($sumAktualTotal, 0, '.', '.') }}</td>
                        <td class="text-end">{{ number_format($sumVarQty, 2, '.', '.') }}</td>
                        <td class="text-end">{{ number_format($sumVarHarga, 0, '.', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
@endsection
