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
                <p class="mb-1">Laporan Insentif Kwitansi</p>
                <p class="mb-1">Periode : {{ $periodeStr }}</p>
            </div>
        </div>

        <hr class="mb-6" />

        <div class="border-bottom-0 border-top-0 rounded">
            <table class="table m-0 table-sm">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th>No. Kwitansi</th>
                        <th>No. Estimasi</th>
                        <th>No. SPK</th>
                        <th>No. Polisi</th>
                        <th>Merek Tipe</th>
                        <th>Nama Asuransi</th>
                        <th class="text-end">Jasa</th>
                        <th class="text-end">Sparepart</th>
                        <th class="text-end">Jumlah</th>
                        <th class="text-center">Tgl Kirim Estimasi</th>
                        <th class="text-center">Tanggal Kwitansi</th>
                        <th class="text-center">Hari</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalJasa = 0;
                        $totalSparepart = 0;
                        $totalJumlah = 0;
                    @endphp
                    @foreach ($datas as $row)
                        @php
                            $jumlah = $row->jasa + $row->sparepart;
                            $totalJasa += $row->jasa;
                            $totalSparepart += $row->sparepart;
                            $totalJumlah += $jumlah;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $no++ }}</td>
                            <td>{{ $row->kode_kwitansi }}</td>
                            <td>{{ $row->kode_estimasi }}</td>
                            <td>{{ $row->kode_spk }}</td>
                            <td>{{ $row->no_polisi }}</td>
                            <td>{{ $row->merek_tipe }}</td>
                            <td>{{ $row->nama_pelanggan }}</td>
                            <td class="text-end">{{ number_format($row->jasa, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->sparepart, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($jumlah, 0, ',', '.') }}</td>
                            <td class="text-center">
                                {{ blank($row->tgl_pengiriman) ? '' : date('d/m/Y', strtotime($row->tgl_pengiriman)) }}
                            </td>
                            <td class="text-center">
                                {{ blank($row->tgl_kwitansi) ? '' : date('d/m/Y', strtotime($row->tgl_kwitansi)) }}</td>
                            <td class="text-center">{{ $row->hari }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
@endsection
