{{-- @extends('layouts/layoutMaster')
@section('title', $title)

@section('vendor-style')
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background-color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            color: #000;
        }

        .paper {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 10px auto;
            padding: 10mm 12mm;
            position: relative;
        }

        .header-company {
            text-align: center;
            font-weight: bold;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .doc-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .doc-subtitle {
            font-size: 15px;
            margin-bottom: 20px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 12px;
        }

        .info-table td {
            padding: 2px 4px;
            font-size: 16px;
            white-space: nowrap;
        }

        .info-table td:first-child {
            width: 130px;
            white-space: nowrap;
        }

        .info-table td:nth-child(2) {
            width: 10px;
        }

        .info-table td:nth-child(3) {
            white-space: nowrap;
        }

        /* Tabel detail */
        .tbl-detail {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .tbl-detail th,
        .tbl-detail td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: middle;
        }

        .tbl-detail thead th {
            background-color: #d0d0d0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            text-align: center;
            font-weight: bold;
        }

        .tbl-detail .th-sub {
            text-align: center;
            font-size: 13px;
        }

        .tbl-detail td.text-center {
            text-align: center;
            white-space: nowrap;
        }

        .tbl-detail td.text-right {
            text-align: right;
            white-space: nowrap;
        }

        .tbl-detail td.text-left {
            text-align: left;
        }

        .tbl-detail td.coa-cell {
            font-size: 12px;
            line-height: 1.3;
        }

        .tbl-detail tfoot td {
            font-weight: bold;
        }

        .tbl-detail td.inv-cell div {
            padding: 0;
            margin: 0;
            white-space: nowrap;
        }

        /* Footer: TTD */
        .footer-ttd-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-top: 30px;
        }

        .footer-ttd-table th,
        .footer-ttd-table td {
            border: 1px solid #000;
            text-align: center;
            padding: 6px;
        }

        .footer-ttd-table thead th {
            background-color: #d0d0d0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: bold;
        }

        .footer-ttd-table .footer-ttd-signrow td {
            height: 70px;
        }

        @media print {
            body {
                margin: 0;
            }

            .paper {
                margin: 0;
                padding: 8mm 10mm;
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
    <div class="paper">

        <div class="header-company">{{ $cabang->nama_cabang ?? 'PT. PERMATA GADING AUTOCENTER' }}</div>

        <div class="info-head">
            <div class="doc-title">{{ $data->judul }}</div>
            <table class="info-table">
                <tr>
                    <td>TIPE</td>
                    <td>:</td>
                    <td>{{ $data->tipe }}</td>
                </tr>
                <tr>
                    <td>NO CH/BG</td>
                    <td>:</td>
                    <td>{{ $data->no_chbg }}</td>
                </tr>
                <tr>
                    <td>Tanggal CH/BG</td>
                    <td>:</td>
                    <td>{{ $data->tgl_chbg_fmt }}</td>
                </tr>
                <tr>
                    <td>Cabang</td>
                    <td>:</td>
                    <td>{{ $cabang->nama_cabang ?? '' }}</td>
                </tr>
            </table>
        </div>

        <table class="tbl-detail">
            <thead>
                <tr>
                    <th style="width:13%;">No. Voucher</th>
                    <th style="width:22%;">Account/COA</th>
                    <th style="width:20%;">Uraian</th>
                    <th style="width:8%;">No. SPK</th>
                    <th style="width:16%;">No. Inv. Gabung</th>
                    <th style="width:19%;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($details as $d)
                    <tr>
                        <td class="text-center">{{ $d->no_voucher }}</td>
                        <td class="text-left coa-cell">{{ $d->account_coa }}</td>
                        <td class="text-left">{{ $d->uraian }}</td>
                        <td class="text-center">{{ $d->no_spk }}</td>
                        <td class="text-left inv-cell">
                            @if (!blank($d->no_inv_gabung))
                                @foreach (explode(',', $d->no_inv_gabung) as $inv)
                                    <div>{{ trim($inv) }}</div>
                                @endforeach
                            @endif
                        </td>
                        <td class="text-right">{{ $d->nilai_fmt }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right">Total</td>
                    <td class="text-right">{{ $data->total_nilai_fmt }}</td>
                </tr>
            </tfoot>
        </table>

        <table class="info-table">
            <tr>
                <td>Terbilang</td>
                <td>:</td>
                <td style="white-space:normal; font-weight:bold;">{{ $data->terbilang }}</td>
            </tr>
        </table>

        <table class="footer-ttd-table">
            <thead>
                <tr>
                    <th>Disiapkan Oleh</th>
                    <th>Diperiksa Oleh</th>
                    <th>Disetujui Oleh</th>
                    <th>Diterima Oleh</th>
                    <th>Dibukukan Oleh</th>
                </tr>
            </thead>
            <tbody>
                <tr class="footer-ttd-signrow">
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </tbody>
        </table>

    </div>
@endsection --}}

@extends('layouts/layoutMaster')
@section('title', $title)

@section('vendor-style')
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            color: #000;
        }

        .paper {
            background: white;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 10mm 15mm;
            display: flex;
            flex-direction: column;
        }

        .header-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }

        .info-header {
            text-align: right;
            font-size: 12px;
        }

        .info-label {
            display: inline-block;
            width: 70px;
        }

        .info-value-header {
            display: inline-block;
            width: 150px;
            text-align: left;
        }

        .date-box {
            border: 1px solid #000;
            padding: 2px 10px;
            display: inline-block;
        }

        .divider {
            border-top: 2px solid #000;
            margin: 15px 0;
        }

        .divider-thick {
            border-top: 3px solid #000;
            margin: 15px 0;
        }

        .boxed-input {
            border: 1px solid #000;
            padding: 5px 10px;
            display: inline-block;
            width: 100%;
        }

        .text-bold {
            font-weight: bold;
        }

        .mt-4-custom {
            margin-top: 25px;
        }

        .g-tight {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.5rem;
        }

        .signature-table th,
        .signature-table td {
            text-align: center;
            vertical-align: middle;
            border-color: #000;
        }

        .signature-table td {
            height: 60px;
        }

        .tableX {
            width: 100%;
        }

        .tableX th,
        .tableX td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .tableX td div {
            white-space: nowrap;
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
    <div class="paper">
        <div class="top-section">
            <div class="row align-items-center">
                <div class="col-6">
                    <p class="mb-0 text-bold">{{ $cabang->nama_cabang ?? '' }}</p>
                </div>
                <div class="col-6 text-end">
                    <div class="info-header">
                        <span class="info-label text-bold">NOMOR</span>
                        <span
                            class="info-value-header text-bold">{{ $data->no_voucher_utama ?? ($details->first()->no_voucher ?? '') }}</span><br>
                        <span class="info-label text-bold">Tanggal</span>
                        <span class="date-box">{{ $data->tanggal_fmt }}</span>
                    </div>
                </div>
            </div>

            <div class="header-title">
                {{ $data->judul }}
            </div>

            <div class="divider"></div>

            <div class="row g-tight mb-3">
                <div class="col-12">
                    <span class="boxed-input">&nbsp;</span>
                </div>
            </div>

            <div class="row table-responsive">
                <table class="tableX table-borderless">
                    <thead class="border-bottom border-dark border-2">
                        <tr>
                            <th style="width:17%;">No. Voucher</th>
                            <th style="width:20%;">Account/COA</th>
                            <th style="width:18%;">Uraian</th>
                            <th style="width:8%;">No. SPK</th>
                            <th style="width:18%;">No. Inv. Gabung</th>
                            <th class="text-end" style="width:19%;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($details as $d)
                            <tr>
                                <td class="text-nowrap">{{ $d->no_voucher }}</td>
                                <td>{{ $d->account_coa }}</td>
                                <td>{{ $d->uraian }}</td>
                                <td>{{ $d->no_spk }}</td>
                                <td>
                                    @if (!blank($d->no_inv_gabung))
                                        @foreach (explode(',', $d->no_inv_gabung) as $inv)
                                            <div>{{ trim($inv) }}</div>
                                        @endforeach
                                    @endif
                                </td>
                                <td class="text-end">{{ $d->nilai_fmt }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bottom-section mt-auto">
            <div class="divider-thick"></div>

            <div class="row align-items-center g-tight mb-3">
                <div class="col-sm-2 text-bold">Terbilang</div>
                <div class="col-sm-7">
                    <span class="boxed-input text-bold">{{ $data->terbilang }}</span>
                </div>
                <div class="col-sm-3 text-end">
                    <span class="boxed-input text-end text-bold">{{ $data->total_nilai_fmt }}</span>
                </div>
            </div>

            <div class="divider"></div>

            <div class="row g-tight mb-3">
                <div class="col-sm-5">
                    <div class="row g-tight align-items-center mb-1">
                        <div class="col-sm-4 text-bold">TIPE</div>
                        <div class="col-sm-8">
                            <span class="boxed-input text-bold">{{ $data->tipe }}</span>
                        </div>
                    </div>
                    <div class="row g-tight align-items-center mb-1">
                        <div class="col-sm-4 text-bold">NO CH/BG</div>
                        <div class="col-sm-8">
                            <span class="boxed-input text-bold">{{ $data->no_chbg }}&nbsp;</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-7">
                    <div class="row g-tight align-items-center mb-1">
                        <div class="col-sm-4 text-end text-bold">Tanggal CH/BG</div>
                        <div class="col-sm-8">
                            <span class="boxed-input text-bold">{{ $data->tgl_chbg_fmt }}&nbsp;</span>
                        </div>
                    </div>
                    <div class="row g-tight align-items-center mb-1">
                        <div class="col-sm-4 text-end text-bold">Cabang</div>
                        <div class="col-sm-8">
                            <span
                                class="boxed-input text-bold">{{ $cabang->nama_singkat ?? ($cabang->nama_cabang ?? '') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row table-responsive mt-4-custom">
                <table class="tableX table-bordered signature-table">
                    <thead class="border-dark border-1">
                        <tr>
                            <th style="width:20%;">Disiapkan Oleh</th>
                            <th style="width:20%;">Diperiksa Oleh</th>
                            <th style="width:20%;">Disetujui Oleh</th>
                            <th style="width:20%;">Diterima Oleh</th>
                            <th style="width:20%;">Dibukukan Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
