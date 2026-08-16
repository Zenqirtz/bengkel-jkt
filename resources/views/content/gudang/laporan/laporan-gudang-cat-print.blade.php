{{-- @extends('layouts/layoutMaster')

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

        .row-repeat-header th {
            background-color: var(--bs-table-header-bg-color);
            font-weight: bold;
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
                <p class="mb-1">Laporan Input Gudang Cat</p>
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
                        <th>No. Input Gudang</th>
                        <th>Supplier</th>
                        <th>Nama Bahan</th>
                        <th>Tipe</th>
                        <th>No. PO</th>
                        <th>Qty</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>PPN</th>
                        <th>Total</th>
                        <th>Kas</th>
                        <th>Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $currentSupplier = null;
                        $currentKodeInput = null;
                        $subtotal = [
                            'harga' => 0,
                            'jumlah_sebelum' => 0,
                            'ppn' => 0,
                            'jumlah' => 0,
                            'cash' => 0,
                            'credit' => 0,
                        ];
                    @endphp

                    @foreach ($datas as $index => $row)
                        @if ($currentSupplier !== null && $currentSupplier !== $row->nama_pemasok)
                        @if ($currentKodeInput !== null && $currentKodeInput !== $row->kode_input)
                            Tampilkan subtotal
                            <tr class="row-total">
                                <td colspan="9"></td>
                                <td style="text-align: right; white-space: nowrap;">Grand Total</td>
                                <td style="text-align: right;">{{ number_format($subtotal['jumlah_sebelum'], 0, '.', '.') }}
                                </td>
                                <td style="text-align: right;">{{ number_format($subtotal['ppn'], 0, '.', '.') }}</td>
                                <td style="text-align: right;">{{ number_format($subtotal['jumlah'], 0, '.', '.') }}</td>
                                <td style="text-align: right;">{{ number_format($subtotal['cash'], 0, '.', '.') }}</td>
                                <td style="text-align: right;">{{ number_format($subtotal['credit'], 0, '.', '.') }}</td>
                            </tr>
                            Tampilkan header ulang setelah total
                            <tr class="row-repeat-header">
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>No. Input Gudang</th>
                                <th>Supplier</th>
                                <th>Nama Bahan</th>
                                <th>Tipe</th>
                                <th>No. PO</th>
                                <th>Qty</th>
                                <th>Satuan</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>PPN</th>
                                <th>Total</th>
                                <th>Kas</th>
                                <th>Kredit</th>
                            </tr>
                            @php
                                $subtotal = [
                                    'harga' => 0,
                                    'jumlah_sebelum' => 0,
                                    'ppn' => 0,
                                    'jumlah' => 0,
                                    'cash' => 0,
                                    'credit' => 0,
                                ];
                            @endphp
                        @endif

                        @php
                            $currentSupplier = $row->nama_pemasok;
                            $currentKodeInput = $row->kode_input;
                            $subtotal['harga'] += $row->harga;
                            $subtotal['jumlah_sebelum'] += $row->jumlah_sebelum;
                            $subtotal['ppn'] += $row->ppn;
                            $subtotal['jumlah'] += $row->jumlah;
                            $subtotal['cash'] += $row->cash;
                            $subtotal['credit'] += $row->credit;
                        @endphp

                        <tr>
                            <td>{{ $no++ }}</td>
                            <td style="text-align: center;">{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ $row->kode_input }}</td>
                            <td>{{ $row->nama_pemasok }}</td>
                            <td>{{ $row->nama_bahan }}</td>
                            <td>{{ $row->group_bahan }}</td>
                            <td>{{ $row->no_po ?? '' }}</td>
                            <td style="text-align: right;">{{ number_format($row->qty, 2, '.', '.') }}</td>
                            <td style="text-align: center;">{{ $row->kode_satuan ?? '' }}</td>
                            <td style="text-align: right;">{{ number_format($row->harga, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->jumlah_sebelum, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->ppn, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->jumlah, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->cash, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->credit, 0, '.', '.') }}</td>
                        </tr>
                    @endforeach

                    Tampilkan subtotal terakhir
                    @if (count($datas) > 0)
                        <tr class="row-total">
                            <td colspan="9"></td>
                            <td style="text-align: right;">Total</td>
                            <td style="text-align: right;">{{ number_format($subtotal['jumlah_sebelum'], 0, '.', '.') }}
                            </td>
                            <td style="text-align: right;">{{ number_format($subtotal['ppn'], 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($subtotal['jumlah'], 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($subtotal['cash'], 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($subtotal['credit'], 0, '.', '.') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
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
        {{-- Header --}}
        <div class="header-title">{{ $namaCabang }}</div>
        <div class="header-sub">Laporan Input Gudang Cat</div>
        <div class="header-sub">Periode : {{ $periodeStr }}</div>

        {{-- Table --}}
        @php
            $currentKodeInput = null;
            $subtotal = [
                'jumlah_sebelum' => 0,
                'ppn' => 0,
                'jumlah' => 0,
                'cash' => 0,
                'credit' => 0,
            ];
            $grandTotal = [
                'jumlah_sebelum' => 0,
                'ppn' => 0,
                'jumlah' => 0,
                'cash' => 0,
                'credit' => 0,
            ];
        @endphp

        <table class="report-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>No. Input Gudang</th>
                    <th>Supplier</th>
                    <th>Nama Bahan</th>
                    <th>Tipe</th>
                    <th>No. PO</th>
                    <th>Qty</th>
                    <th>Satuan</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>PPN</th>
                    <th>Total</th>
                    <th>Kas</th>
                    <th>Kredit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $index => $row)
                    @if ($currentKodeInput !== null && $currentKodeInput !== $row->kode_input)
                        {{-- Baris Total per group --}}
                        <tr class="row-total">
                            <td class="text-right" style="font-weight:bold;" colspan="10">Total</td>
                            <td class="text-right">{{ number_format($subtotal['jumlah_sebelum'], 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($subtotal['ppn'], 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($subtotal['jumlah'], 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($subtotal['cash'], 0, '.', '.') }}</td>
                            <td class="text-right">{{ number_format($subtotal['credit'], 0, '.', '.') }}</td>
                        </tr>
                        @php
                            $grandTotal['jumlah_sebelum'] += $subtotal['jumlah_sebelum'];
                            $grandTotal['ppn'] += $subtotal['ppn'];
                            $grandTotal['jumlah'] += $subtotal['jumlah'];
                            $grandTotal['cash'] += $subtotal['cash'];
                            $grandTotal['credit'] += $subtotal['credit'];
                            $subtotal = ['jumlah_sebelum' => 0, 'ppn' => 0, 'jumlah' => 0, 'cash' => 0, 'credit' => 0];
                        @endphp
                    @endif

                    @php
                        $currentKodeInput = $row->kode_input;
                        $subtotal['jumlah_sebelum'] += $row->jumlah_sebelum;
                        $subtotal['ppn'] += $row->ppn;
                        $subtotal['jumlah'] += $row->jumlah;
                        $subtotal['cash'] += $row->cash;
                        $subtotal['credit'] += $row->credit;
                    @endphp

                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td class="text-center" style="white-space:nowrap;">
                            {{ date('d/m/Y', strtotime($row->tanggal))}}</td>
                        <td style="white-space:nowrap;">{{ $row->kode_input }}</td>
                        <td>{{ $row->nama_pemasok }}</td>
                        <td>{{ $row->nama_bahan }}</td>
                        <td>{{ $row->group_bahan }}</td>
                        <td>{{ $row->no_po ?? '' }}</td>
                        <td class="text-right">{{ number_format($row->qty, 2, '.', '.') }}</td>
                        <td class="text-center">{{ $row->kode_satuan ?? '' }}</td>
                        <td class="text-right">{{ number_format($row->harga, 0, '.', '.') }}</td>
                        <td class="text-right">{{ number_format($row->jumlah_sebelum, 0, '.', '.') }}</td>
                        <td class="text-right">{{ number_format($row->ppn, 0, '.', '.') }}</td>
                        <td class="text-right">{{ number_format($row->jumlah, 0, '.', '.') }}</td>
                        <td class="text-right">{{ number_format($row->cash, 0, '.', '.') }}</td>
                        <td class="text-right">{{ number_format($row->credit, 0, '.', '.') }}</td>
                    </tr>
                @endforeach

                @if (count($datas) > 0)
                    {{-- Total terakhir --}}
                    <tr class="row-total">
                        <td class="text-right" style="font-weight:bold;" colspan="10">Total</td>
                        <td class="text-right">{{ number_format($subtotal['jumlah_sebelum'], 0, '.', '.') }}</td>
                        <td class="text-right">{{ number_format($subtotal['ppn'], 0, '.', '.') }}</td>
                        <td class="text-right">{{ number_format($subtotal['jumlah'], 0, '.', '.') }}</td>
                        <td class="text-right">{{ number_format($subtotal['cash'], 0, '.', '.') }}</td>
                        <td class="text-right">{{ number_format($subtotal['credit'], 0, '.', '.') }}</td>
                    </tr>
                    @php
                        $grandTotal['jumlah_sebelum'] += $subtotal['jumlah_sebelum'];
                        $grandTotal['ppn'] += $subtotal['ppn'];
                        $grandTotal['jumlah'] += $subtotal['jumlah'];
                        $grandTotal['cash'] += $subtotal['cash'];
                        $grandTotal['credit'] += $subtotal['credit'];
                    @endphp
                    {{-- Grand Total --}}
                    <tr class="row-grand-total">
                        <td class="text-right" style="font-weight:bold;" colspan="10">Grand Total</td>
                        <td class="text-right">{{ number_format($grandTotal['jumlah_sebelum'], 0, '.', '.') }}</td>
                        <td class="text-right">{{ number_format($grandTotal['ppn'], 0, '.', '.') }}</td>
                        <td class="text-right">{{ number_format($grandTotal['jumlah'], 0, '.', '.') }}</td>
                        <td class="text-right">{{ number_format($grandTotal['cash'], 0, '.', '.') }}</td>
                        <td class="text-right">{{ number_format($grandTotal['credit'], 0, '.', '.') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
        @include('content.shared.laporan-print-signature')
    </div>
@endsection
