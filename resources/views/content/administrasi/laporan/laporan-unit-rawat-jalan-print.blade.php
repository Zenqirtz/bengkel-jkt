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
                <p class="mb-1">Laporan Unit Rawat Jalan</p>
                <p class="mb-1">Periode : {{ $periodeStr }}</p>
            </div>
        </div>

        <hr class="mb-6" />

        <div class="border-bottom-0 border-top-0 rounded">
            <table class="table m-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No. SPK</th>
                        <th>Tanggal Masuk</th>
                        <th>Tanggal Rawat Jalan</th>
                        <th>Tanggal Selesai</th>
                        <th>No. Polisi</th>
                        <th>Merek / Tipe</th>
                        <th>Pemilik</th>
                        <th>Nama Asuransi</th>
                        <th>No. Polis</th>
                        <th>No. Klaim</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($datas as $row)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $row->kode_spk }}</td>
                            <td>{{ blank($row->tgl_masuk) ? '' : date('d/m/Y', strtotime($row->tgl_masuk)) }}</td>
                            <td>{{ blank($row->tgl_rawat_jalan1) ? '' : date('d/m/Y', strtotime($row->tgl_rawat_jalan1)) }}
                            </td>
                            <td>{{ blank($row->tgl_rawat_jalan2) ? '' : date('d/m/Y', strtotime($row->tgl_rawat_jalan2)) }}
                            </td>
                            <td>{{ $row->no_polisi }}</td>
                            <td>{{ trim(($row->nama_merek ?? '') . ' ' . ($row->nama_tipe ?? '')) }}</td>
                            <td>{{ $row->pemilik }}</td>
                            <td>{{ $row->nama_pelanggan }}</td>
                            <td>{{ $row->no_polis }}</td>
                            <td>{{ $row->kode_claim }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
@endsection
