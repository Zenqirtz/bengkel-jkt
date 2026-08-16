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
                <p class="mb-1">Laporan Pengeluaran Sparepart</p>
                <p class="mb-1">Per Tanggal : {{ $periodeStr }}</p>
            </div>
        </div>

        <hr class="mb-6" />

        <div class="border-bottom-0 border-top-0 rounded">
            <table class="table m-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No. Pengeluaran</th>
                        <th>Tanggal</th>
                        <th>Bon</th>
                        <th>No. Input Gudang</th>
                        <th>SPK</th>
                        <th>No. Polisi</th>
                        <th>Kode SP</th>
                        <th>Nama Sparepart</th>
                        <th>Qty</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // $currentSparepart = null;
                        $currentInputGudang = null;
                        $subtotal = [
                            'qty' => 0,
                            'harga' => 0,
                            'jumlah' => 0,
                            'satuan' => '',
                        ];
                    @endphp

                    @foreach ($datas as $index => $row)
                        {{-- @if ($currentSparepart !== null && $currentSparepart !== $row->nama_sparepart) --}}
                        @if ($currentInputGudang !== null && $currentInputGudang !== $row->no_input_gudang)
                            {{-- Tampilkan subtotal --}}
                            {{-- <tr style="font-weight: bold;"> --}}
                            <tr class="row-total">
                                <td colspan="8"></td>
                                <td>Grand Total</td>
                                <td style="text-align: right;">{{ number_format($subtotal['qty'], 2, '.', '.') }}</td>
                                <td>{{ $subtotal['satuan'] }}</td>
                                <td style="text-align: right;">{{ number_format($subtotal['harga'], 0, '.', '.') }}</td>
                                <td style="text-align: right;">{{ number_format($subtotal['jumlah'], 0, '.', '.') }}</td>
                            </tr>
                            @php
                                // Reset subtotal
                                $subtotal = [
                                    'qty' => 0,
                                    'harga' => 0,
                                    'jumlah' => 0,
                                    'satuan' => '',
                                ];
                            @endphp
                        @endif

                        @php
                            // $currentSparepart = $row->nama_sparepart;
                            $currentInputGudang = $row->no_input_gudang;
                            $subtotal['qty'] += $row->qty;
                            $subtotal['harga'] += $row->harga;
                            $subtotal['jumlah'] += $row->jumlah;
                            $subtotal['satuan'] = $row->satuan ?? '';
                        @endphp

                        <tr>
                            <td style="text-align: center;">{{ $no++ }}</td>
                            <td>{{ $row->kode_pengeluaran }}</td>
                            <td style="text-align: center;">
                                {{ \Carbon\Carbon::parse($row->tgl_pengeluaran)->format('d/m/Y') }}</td>
                            <td>{{ $row->no_bon ?? '' }}</td>
                            <td>{{ $row->no_input_gudang ?? '' }}</td>
                            <td>{{ $row->kode_spk ?? '' }}</td>
                            <td>{{ $row->no_polisi ?? '' }}</td>
                            <td>{{ $row->kode_sp }}</td>
                            <td>{{ $row->nama_sparepart }}</td>
                            <td style="text-align: right;">{{ number_format($row->qty, 2, '.', '.') }}</td>
                            <td>{{ $row->satuan ?? '' }}</td>
                            <td style="text-align: right;">{{ number_format($row->harga, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->jumlah, 0, '.', '.') }}</td>
                        </tr>
                    @endforeach

                    {{-- Tampilkan subtotal terakhir --}}
                    @if (count($datas) > 0)
                        {{-- <tr style="font-weight: bold;"> --}}
                        <tr class="row-total">
                            <td colspan="8"></td>
                            <td>Grand Total</td>
                            <td style="text-align: right;">{{ number_format($subtotal['qty'], 2, '.', '.') }}</td>
                            <td>{{ $subtotal['satuan'] }}</td>
                            <td style="text-align: right;">{{ number_format($subtotal['harga'], 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($subtotal['jumlah'], 0, '.', '.') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
