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
                <p class="mb-1">Laporan Retur Sparepart</p>
                <p class="mb-1">Periode : {{ $periodeStr }}</p>
            </div>
        </div>

        <hr class="mb-6" />

        <div class="border-bottom-0 border-top-0 rounded">
            <table class="table m-0">
                <thead>
                    <tr>
                        <th rowspan="2" style="text-align: center; vertical-align: middle;">No</th>
                        <th colspan="2" style="text-align: center;">Retur</th>
                        <th colspan="2" style="text-align: center;">Input Gudang</th>
                        <th rowspan="2" style="text-align: center; vertical-align: middle;">Nama Supplier</th>
                        <th rowspan="2" style="text-align: center; vertical-align: middle;">Nama Sparepart</th>
                        <th rowspan="2" style="text-align: center; vertical-align: middle;">No. SPK</th>
                        <th rowspan="2" style="text-align: center; vertical-align: middle;">No. Polisi</th>
                        <th rowspan="2" style="text-align: center; vertical-align: middle;">Jumlah</th>
                        <th rowspan="2" style="text-align: center; vertical-align: middle;">Harga</th>
                        <th rowspan="2" style="text-align: center; vertical-align: middle;">Total</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;">Tanggal</th>
                        <th style="text-align: center;">Nomor</th>
                        <th style="text-align: center;">Tanggal</th>
                        <th style="text-align: center;">Nomor</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @php
                        $currentSparepart = null;
                        $subtotal = [
                            'jumlah' => 0,
                            'harga' => 0,
                            'total' => 0,
                        ];
                    @endphp

                    @foreach ($datas as $index => $row)
                        @if ($currentSparepart !== null && $currentSparepart !== $row->nama_sparepart)
                            Tampilkan subtotal
                            <tr style="font-weight: bold;">
                                <td colspan="6"></td>
                                <td>Total</td>
                                <td colspan="2"></td>
                                <td style="text-align: right;">{{ number_format($subtotal['jumlah'], 0, '.', '.') }}</td>
                                <td style="text-align: right;">{{ number_format($subtotal['harga'], 0, '.', '.') }}</td>
                                <td style="text-align: right;">{{ number_format($subtotal['total'], 0, '.', '.') }}</td>
                            </tr>
                            @php
                                Reset subtotal
                                $subtotal = [
                                    'jumlah' => 0,
                                    'harga' => 0,
                                    'total' => 0,
                                ];
                            @endphp
                        @endif

                        @php
                            $currentSparepart = $row->nama_sparepart;
                            $subtotal['jumlah'] += $row->jumlah;
                            $subtotal['harga'] += $row->harga;
                            $subtotal['total'] += $row->total;
                        @endphp

                        <tr>
                            <td style="text-align: center;">{{ $no++ }}</td>
                            <td style="text-align: center;">
                                {{ $row->tgl_retur ? \Carbon\Carbon::parse($row->tgl_retur)->format('d/m/Y') : '' }}</td>
                            <td>{{ $row->no_retur ?? '' }}</td>
                            <td style="text-align: center;">
                                {{ $row->tgl_input_gudang ? \Carbon\Carbon::parse($row->tgl_input_gudang)->format('d/m/Y') : '' }}
                            </td>
                            <td>{{ $row->no_input_gudang ?? '' }}</td>
                            <td>{{ $row->nama_supplier ?? '' }}</td>
                            <td>{{ $row->nama_sparepart }}</td>
                            <td>{{ $row->no_spk ?? '' }}</td>
                            <td>{{ $row->no_polisi ?? '' }}</td>
                            <td style="text-align: right;">{{ number_format($row->jumlah, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->harga, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->total, 0, '.', '.') }}</td>
                        </tr>
                    @endforeach

                    Tampilkan subtotal terakhir
                    @if (count($datas) > 0)
                        <tr style="font-weight: bold;">
                            <td colspan="6"></td>
                            <td>Total</td>
                            <td colspan="2"></td>
                            <td style="text-align: right;">{{ number_format($subtotal['jumlah'], 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($subtotal['harga'], 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($subtotal['total'], 0, '.', '.') }}</td>
                        </tr>
                    @endif --}}
                    @php
                        $currentNoRetur = null;
                        $subtotal = [
                            'jumlah' => 0,
                            'harga' => 0,
                            'total' => 0,
                        ];
                    @endphp

                    @foreach ($datas as $index => $row)
                        @if ($currentNoRetur !== null && $currentNoRetur !== $row->no_retur)
                            <tr style="font-weight: bold;">
                                <td colspan="6"></td>
                                <td>Total</td>
                                <td colspan="2"></td>
                                <td style="text-align: right;">{{ number_format($subtotal['jumlah'], 0, '.', '.') }}</td>
                                <td style="text-align: right;">{{ number_format($subtotal['harga'], 0, '.', '.') }}</td>
                                <td style="text-align: right;">{{ number_format($subtotal['total'], 0, '.', '.') }}</td>
                            </tr>
                            @php
                                $subtotal = ['jumlah' => 0, 'harga' => 0, 'total' => 0];
                            @endphp
                        @endif

                        @php
                            $currentNoRetur = $row->no_retur;
                            $subtotal['jumlah'] += $row->jumlah;
                            $subtotal['harga'] += $row->harga;
                            $subtotal['total'] += $row->total;
                        @endphp

                        <tr>
                            <td style="text-align: center;">{{ $no++ }}</td>
                            <td style="text-align: center;">
                                {{ $row->tgl_retur ? \Carbon\Carbon::parse($row->tgl_retur)->format('d/m/Y') : '' }}</td>
                            <td>{{ $row->no_retur ?? '' }}</td>
                            <td style="text-align: center;">
                                {{ $row->tgl_input_gudang ? \Carbon\Carbon::parse($row->tgl_input_gudang)->format('d/m/Y') : '' }}
                            </td>
                            <td>{{ $row->no_input_gudang ?? '' }}</td>
                            <td>{{ $row->nama_supplier ?? '' }}</td>
                            <td>{{ $row->nama_sparepart }}</td>
                            <td>{{ $row->no_spk ?? '' }}</td>
                            <td>{{ $row->no_polisi ?? '' }}</td>
                            <td style="text-align: right;">{{ number_format($row->jumlah, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->harga, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->total, 0, '.', '.') }}</td>
                        </tr>
                    @endforeach

                    @if (count($datas) > 0)
                        <tr class="row-total">
                            <td colspan="6"></td>
                            <td>Grand Total</td>
                            <td colspan="2"></td>
                            <td style="text-align: right;">{{ number_format($subtotal['jumlah'], 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($subtotal['harga'], 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($subtotal['total'], 0, '.', '.') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
