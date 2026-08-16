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
                <p class="mb-1">Laporan Insentif S & T</p>
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
                        <th rowspan="2">Tanggal Estimasi</th>
                        <th colspan="5" style="text-align: center;">Insentif S</th>
                        <th colspan="5" style="text-align: center;">Insentif T</th>
                    </tr>
                    <tr>
                        <th>Nilai</th>
                        <th>WM</th>
                        <th>Marketing</th>
                        <th>Kabeng</th>
                        <th>SA</th>
                        <th>Nilai</th>
                        <th>WM</th>
                        <th>Marketing</th>
                        <th>Kabeng</th>
                        <th>SA</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grandInsentifS = 0;
                        $grandInsentifT = 0;
                        $grandWmS = 0;
                        $grandMarketingS = 0;
                        $grandKabengS = 0;
                        $grandSaS = 0;
                        $grandWmT = 0;
                        $grandMarketingT = 0;
                        $grandKabengT = 0;
                        $grandSaT = 0;
                    @endphp
                    @foreach ($datas as $row)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $row->kode_spk }}</td>
                            <td>{{ $row->no_polisi }}</td>
                            <td>{{ $row->kode_estimasi }}</td>
                            <td>{{ $row->nama_asuransi }}</td>
                            <td>{{ blank($row->tgl_estimasi) ? '' : date('d/m/Y', strtotime($row->tgl_estimasi)) }}</td>
                            <td>{{ number_format($row->insentif_s, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->wm_s, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->marketing_s, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->kabeng_s, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->sa_s, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->insentif_t, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->wm_t, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->marketing_t, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->kabeng_t, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->sa_t, 0, ',', '.') }}</td>
                        </tr>
                        @php
                            $grandInsentifS += $row->insentif_s;
                            $grandInsentifT += $row->insentif_t;
                            $grandWmS += $row->wm_s;
                            $grandMarketingS += $row->marketing_s;
                            $grandKabengS += $row->kabeng_s;
                            $grandSaS += $row->sa_s;
                            $grandWmT += $row->wm_t;
                            $grandMarketingT += $row->marketing_t;
                            $grandKabengT += $row->kabeng_t;
                            $grandSaT += $row->sa_t;
                        @endphp
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
