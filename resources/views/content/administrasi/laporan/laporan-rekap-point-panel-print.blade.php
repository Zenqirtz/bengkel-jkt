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
                size: portrait;
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
                <p class="mb-1">Laporan Rekap Point Panel</p>
                <p class="mb-1">Tahun : {{ $tahun }}</p>
            </div>
        </div>

        <hr class="mb-6" />

        <div class="border-bottom-0 border-top-0 rounded">
            <table class="table m-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Bulan</th>
                        <th>Jumlah Unit</th>
                        <th>Jumlah Panel</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grandJumlahSpk = 0;
                        $grandTotalPanel = 0;
                        $bulanNama = [
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember',
                        ];
                    @endphp
                    @foreach ($datas as $row)
                        @php
                            // bulan adalah integer 1-12
                            $bulanAngka = (int) $row->bulan;
                        @endphp
                        <tr>
                            <td style="text-align: center;">{{ $no++ }}</td>
                            <td>{{ $bulanNama[$bulanAngka] ?? '' }}</td>
                            <td style="text-align: right;">{{ number_format($row->jumlah_spk, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->total_panel, 2, ',', '.') }}</td>
                        </tr>
                        @php
                            $grandJumlahSpk += $row->jumlah_spk;
                            $grandTotalPanel += $row->total_panel;
                        @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold;">
                        <td colspan="2"></td>
                        <td style="text-align: right;">{{ number_format($grandJumlahSpk, 0, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($grandTotalPanel, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
