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
            font-size: 14px;
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

        .invoice-print .table td,
        .invoice-print .table th {
            padding: 0.5rem;
            font-size: 13px;
        }

        @media print {
            @page {
                size: landscape;
                margin: 10mm;
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
                <p class="mb-1">Laporan Insentif Marketing</p>
                <p class="mb-1">Periode : {{ $periodeStr }}</p>
                <p class="mb-1">Nama Marketing : {{ $namaMarketing }}</p>
            </div>
        </div>

        <hr class="mb-6" />

        <div class="border-bottom-0 border-top-0 rounded">
            <table class="table m-0 table-sm">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Tanggal</th>
                        <th>No. SPK</th>
                        <th>No. Polisi</th>
                        <th>Merek Tipe</th>
                        <th>Nama Asuransi</th>
                        <th>No. Estimasi</th>
                        <th class="text-end">Total Estimasi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalEstimasi = 0;
                    @endphp
                    @foreach ($datas as $row)
                        @php
                            $jumlahEstimasi =
                                $row->total_perbaikan + $row->total_sparepart + $row->total_lain + $row->ppn;
                            $totalEstimasi += $jumlahEstimasi;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $no++ }}</td>
                            <td class="text-center">
                                {{ blank($row->tanggal) ? '' : date('d/m/Y', strtotime($row->tanggal)) }}
                            </td>
                            <td>{{ $row->kode_spk }}</td>
                            <td>{{ $row->no_polisi }}</td>
                            <td>{{ $row->tipe_kendaraan }}</td>
                            <td>{{ $row->nama_pelanggan }}</td>
                            <td>{{ $row->kode_estimasi }}</td>
                            <td class="text-end">{{ number_format($jumlahEstimasi, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    @if (count($datas) > 0)
                        <tr>
                            <td colspan="7" class="text-end fw-bold">Total Insentive</td>
                            <td class="text-end fw-bold">{{ number_format($totalEstimasi, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

    </div>
@endsection
