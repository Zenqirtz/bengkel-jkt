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
                <p class="mb-1">Laporan Pengeluaran Bahan</p>
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
                        <th>SPK</th>
                        <th>No. Polisi</th>
                        <th>Bagian</th>
                        <th>Point Panel</th>
                        <th>Kode Bahan</th>
                        <th>Nama Bahan</th>
                        <th>Qty</th>
                        <th>Satuan</th>
                        <th>Harga Lama</th>
                        <th>Harga Baru</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // $currentBahan = null;
                        $currentPengeluaran = null;
                        $subtotal = [
                            'qty' => 0,
                            'harga_lama' => 0,
                            'harga' => 0,
                            'jumlah' => 0,
                            'satuan' => '',
                        ];
                    @endphp

                    @foreach ($datas as $index => $row)
                        {{-- @if ($currentBahan !== null && $currentBahan !== $row->nama_bahan) --}}
                        @if ($currentPengeluaran !== null && $currentPengeluaran !== $row->kode_pengeluaran)
                            {{-- Tampilkan subtotal --}}
                            @php
                                $totalHarga = $subtotal['harga_lama'] + $subtotal['harga'];
                            @endphp
                            {{-- <tr style="font-weight: bold;"> --}}
                            <tr class="row-total">
                                <td colspan="9"></td>
                                <td>Grand Total</td>
                                <td style="text-align: right;">{{ number_format($subtotal['qty'], 2, '.', '.') }}</td>
                                <td>{{ $subtotal['satuan'] }}</td>
                                <td colspan="2" style="text-align: right;">{{ number_format($totalHarga, 0, '.', '.') }}
                                </td>
                                <td style="text-align: right;">{{ number_format($subtotal['jumlah'], 0, '.', '.') }}</td>
                            </tr>
                            @php
                                // Reset subtotal
                                $subtotal = [
                                    'qty' => 0,
                                    'harga_lama' => 0,
                                    'harga' => 0,
                                    'jumlah' => 0,
                                    'satuan' => '',
                                ];
                            @endphp
                        @endif

                        @php
                            // $currentBahan = $row->nama_bahan;
                            $currentPengeluaran = $row->kode_pengeluaran;
                            $subtotal['qty'] += $row->qty;
                            $subtotal['harga_lama'] += $row->harga_lama;
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
                            <td>{{ $row->kode_spk ?? '' }}</td>
                            <td>{{ $row->no_polisi ?? '' }}</td>
                            <td>{{ $row->posisi_pekerjaan ?? '' }}</td>
                            <td>{{ $row->point_panel ?? '' }}</td>
                            <td>{{ $row->kode_barang }}</td>
                            <td>{{ $row->nama_bahan }}</td>
                            <td style="text-align: right;">{{ number_format($row->qty, 2, '.', '.') }}</td>
                            <td>{{ $row->satuan ?? '' }}</td>
                            <td style="text-align: right;">{{ number_format($row->harga_lama, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->harga, 0, '.', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->jumlah, 0, '.', '.') }}</td>
                        </tr>
                    @endforeach

                    {{-- Tampilkan subtotal terakhir --}}
                    @if (count($datas) > 0)
                        @php
                            $totalHarga = $subtotal['harga_lama'] + $subtotal['harga'];
                        @endphp
                        {{-- <tr style="font-weight: bold;"> --}}
                        <tr class="row-total">
                            <td colspan="9"></td>
                            <td>Grand Total</td>
                            <td style="text-align: right;">{{ number_format($subtotal['qty'], 2, '.', '.') }}</td>
                            <td>{{ $subtotal['satuan'] }}</td>
                            <td colspan="2" style="text-align: right;">{{ number_format($totalHarga, 0, '.', '.') }}
                            </td>
                            <td style="text-align: right;">{{ number_format($subtotal['jumlah'], 0, '.', '.') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
