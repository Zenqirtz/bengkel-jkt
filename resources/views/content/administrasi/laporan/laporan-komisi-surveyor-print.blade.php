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
                <p class="mb-1">Laporan Komisi Surveyor</p>
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
                        <th>No. Polisi</th>
                        <th>Tipe Kendaraan</th>
                        <th>Nama Asuransi</th>
                        <th>No. Polis</th>
                        <th>No. Estimasi</th>
                        <th>Jasa</th>
                        <th>Sparepart</th>
                        <th>Lain</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grandJasa = 0;
                        $grandSparepart = 0;
                        $grandLain = 0;
                        $grandJumlah = 0;
                    @endphp
                    @foreach ($datas as $row)
                        @php
                            // Hitung jasa = total_perbaikan - (total_sparepart + total_lain)
                            // Gunakan abs() untuk menghilangkan minus
                            $totalJasa = abs($row->total_perbaikan - ($row->total_sparepart + $row->total_lain));
                        @endphp
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $row->kode_spk }}</td>
                            <td>{{ blank($row->tgl_masuk) ? '' : date('d/m/Y', strtotime($row->tgl_masuk)) }}</td>
                            <td>{{ $row->no_polisi }}</td>
                            <td>{{ $row->merek_tipe }}</td>
                            <td>{{ $row->nama_pelanggan }}</td>
                            <td>{{ $row->no_polis }}</td>
                            <td>{{ $row->kode_estimasi }}</td>
                            <td style="text-align: right;">{{ number_format($totalJasa, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->total_sparepart, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->total_lain, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($row->total_perbaikan, 0, ',', '.') }}</td>
                        </tr>
                        @php
                            $grandJasa += $totalJasa;
                            $grandSparepart += $row->total_sparepart;
                            $grandLain += $row->total_lain;
                            $grandJumlah += $row->total_perbaikan;
                        @endphp
                    @endforeach
                    <tr>
                        <td colspan="8" style="text-align: right;"><strong>TOTAL</strong></td>
                        <td style="text-align: right;"><strong>{{ number_format($grandJasa, 0, ',', '.') }}</strong></td>
                        <td style="text-align: right;"><strong>{{ number_format($grandSparepart, 0, ',', '.') }}</strong>
                        </td>
                        <td style="text-align: right;"><strong>{{ number_format($grandLain, 0, ',', '.') }}</strong></td>
                        <td style="text-align: right;"><strong>{{ number_format($grandJumlah, 0, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
