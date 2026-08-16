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
                <p class="mb-1">Laporan Estimasi Disetujui</p>
                <p class="mb-1">Periode : {{ $periodeStr }}</p>
            </div>
        </div>

        <hr class="mb-6" />

        <div class="border-bottom-0 border-top-0 rounded">
            <table class="table m-0">
                <thead>
                    <tr>
                        <th rowspan="2">No</th>
                        <th rowspan="2">No. SPK</th>
                        <th rowspan="2">No. Polisi</th>
                        <th rowspan="2">No. Estimasi</th>
                        <th rowspan="2">Nama Asuransi</th>
                        <th rowspan="2">Tanggal SPK</th>
                        <th rowspan="2">Tanggal Estimasi</th>
                        <th rowspan="2">Tanggal Disetujui</th>
                        <th colspan="4" style="text-align: center;">Estimasi Belum Ditawar</th>
                        <th colspan="4" style="text-align: center;">Estimasi Disetujui</th>
                        <th rowspan="2">Total OR</th>
                    </tr>
                    <tr>
                        <th>Jasa</th>
                        <th>Sparepart</th>
                        <th>Lain-lain</th>
                        <th>Total</th>
                        <th>Jasa</th>
                        <th>Sparepart</th>
                        <th>Lain-lain</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grandTotalPerbaikan = 0;
                        $grandTotalSparepart = 0;
                        $grandTotalLain = 0;
                        $grandTotal = 0;
                        $grandTotalPerbaikanS = 0;
                        $grandTotalSparepartS = 0;
                        $grandTotalLainS = 0;
                        $grandTotalS = 0;
                        $grandTotalOrAss = 0;
                    @endphp
                    @foreach ($datas as $row)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $row->kode_spk }}</td>
                            <td>{{ $row->no_polisi }}</td>
                            <td>{{ $row->kode_estimasi }}</td>
                            <td>{{ $row->nama_pelanggan }}</td>
                            <td>{{ blank($row->tgl_konsep) ? '' : date('d/m/Y', strtotime($row->tgl_konsep)) }}</td>
                            <td>{{ blank($row->tgl_estimasi) ? '' : date('d/m/Y', strtotime($row->tgl_estimasi)) }}</td>
                            <td>{{ blank($row->tgl_persetujuan) ? '' : date('d/m/Y', strtotime($row->tgl_persetujuan)) }}
                            </td>
                            <td>{{ number_format($row->total_perbaikan, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->total_sparepart, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->total_lain, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->total, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->total_perbaikan_s, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->total_sparepart_s, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->total_lain_s, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->total_s, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->total_or_ass, 0, ',', '.') }}</td>
                        </tr>
                        @php
                            $grandTotalPerbaikan += $row->total_perbaikan;
                            $grandTotalSparepart += $row->total_sparepart;
                            $grandTotalLain += $row->total_lain;
                            $grandTotal += $row->total;
                            $grandTotalPerbaikanS += $row->total_perbaikan_s;
                            $grandTotalSparepartS += $row->total_sparepart_s;
                            $grandTotalLainS += $row->total_lain_s;
                            $grandTotalS += $row->total_s;
                            $grandTotalOrAss += $row->total_or_ass;
                        @endphp
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
@endsection
