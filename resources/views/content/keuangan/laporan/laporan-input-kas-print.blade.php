@extends('layouts/layoutMaster')

@section('title', $title)

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
        <div class="mb-6">
            <div class="d-flex svg-illustration mb-6 gap-2">
                <span class="app-brand-text fw-bold">{{ $namaCabang }}</span>
            </div>
            <p class="mb-1">Laporan Input Kas</p>
            <p class="mb-1">Periode : {{ $periodeStr }}</p>
        </div>

        <hr class="mb-6" />

        <table class="table m-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>No. Bukti</th>
                    <th>Memo</th>
                    <th>No. SPK</th>
                    <th>No. Input Gudang</th>
                    <th>Debet</th>
                    <th>Kredit</th>
                    <th>Saldo</th>
                    <th>OR Free/Lain-lain</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $saldo = $saldoAwal ?? 0;
                @endphp

                {{-- Baris Saldo Awal --}}
                <tr class="row-saldo-awal">
                    <td>{{ $no++ }}</td>
                    <td style="text-align:center;">
                        {{ !empty($datafilter['tgl_awal']) ? $datafilter['tgl_awal'] : '' }}
                    </td>
                    <td></td>
                    <td>Saldo Awal</td>
                    <td></td>
                    <td></td>
                    <td style="text-align:right;">{{ number_format($saldoAwal, 0, '.', '.') }}</td>
                    <td style="text-align:right;">{{ number_format(0, 0, '.', '.') }}</td>
                    <td style="text-align:right;">{{ number_format($saldo, 0, '.', '.') }}</td>
                    <td></td>
                </tr>

                @foreach ($datas as $index => $row)
                    @php
                        $saldo = $saldo + $row->debet - $row->kredit;
                    @endphp

                    <tr>
                        <td>{{ $no++ }}</td>
                        <td style="text-align:center;">{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                        <td>{{ $row->no_bukti }}</td>
                        <td>{{ $row->memo }}</td>
                        <td>{{ $row->no_spk ?? '' }}</td>
                        <td>{{ $row->no_input_gudang ?? '' }}</td>
                        <td style="text-align:right;">{{ number_format($row->debet, 0, '.', '.') }}</td>
                        <td style="text-align:right;">{{ number_format($row->kredit, 0, '.', '.') }}</td>
                        <td style="text-align:right;">{{ number_format($saldo, 0, '.', '.') }}</td>
                        <td>{{ $row->or_free ?? '' }}</td>
                    </tr>
                @endforeach

                {{-- Grand Total / Saldo Akhir - SELALU ditampilkan --}}
                <tr class="row-total">
                    <td colspan="8" style="text-align: right;">Grand Total</td>
                    <td style="text-align: right;">{{ number_format($saldo, 0, '.', '.') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
