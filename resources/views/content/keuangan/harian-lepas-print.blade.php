@extends('layouts/layoutMaster')
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
            font-size: 15px;
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
            font-size: 13px;
            white-space: nowrap;
        }

        .info-table td:first-child {
            width: 110px;
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

        .tbl-detail tfoot td {
            font-weight: bold;
        }

        /* TTD */
        .ttd-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }

        .ttd-box {
            text-align: center;
            width: 22%;
            font-size: 15px;
        }

        .ttd-name {
            margin-top: 60px;
            padding-bottom: 2px;
            min-width: 80px;
            display: inline-block;
        }

        .ttd-paren {
            font-size: 20px;
            margin-top: 4px;
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

        {{-- Header Perusahaan --}}
        <div class="header-company">{{ $cabang->nama_cabang ?? 'PT. PERMATA GADING AUTOCENTER' }}</div>

        {{-- Judul Dokumen --}}
        <div class="doc-title">BUKTI PENERIMAAN UANG</div>
        <div class="doc-subtitle">Upah Borongan Kerja : <strong>{{ $data->nama_jenis_pekerjaan ?? 'Upah Borongan' }}</strong>
        </div>

        {{-- Info Header --}}
        <table class="info-table">
            <tr>
                <td>Nama</td>
                <td>:</td>
                <td>{{ $data->nama_pekerja }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td>{{ $data->tanggal_fmt }}</td>
            </tr>
            <tr>
                <td>No. Transaksi</td>
                <td>:</td>
                <td>{{ $data->no_transaksi }}</td>
            </tr>
        </table>

        {{-- Tabel Detail SPK --}}
        <table class="tbl-detail">
            <thead>
                <tr>
                    <th rowspan="2" style="width:30px;">No</th>
                    <th rowspan="2" style="width:110px; white-space:nowrap;">SPK</th>
                    <th rowspan="2">Merk Mobil</th>
                    <th rowspan="2" style="width:90px;">No Polisi</th>
                    {{-- <th rowspan="2" class="th-sub" style="width:90px;">Sisa Upah<br>Kerja</th>
                    <th colspan="2" class="th-sub">PROSES KERJA</th>
                    <th rowspan="2" class="th-sub" style="width:130px;">Total<br>Upah Dibayar</th> --}}
                    <th rowspan="2" class="th-sub" style="width:90px;">Upah<br>Kerja</th>
                    <th rowspan="2" class="th-sub" style="width:80px;">Sisa Upah<br>Kerja</th>
                    <th colspan="3" class="th-sub">PROSES KERJA</th>
                    <th rowspan="2" class="th-sub" style="width:110px;">Keterangan</th>
                </tr>
                <tr>
                    {{-- <th class="th-sub" style="width:55px;">Kerja</th>
                    <th class="th-sub" style="width:60px;">Selesai</th> --}}
                    <th class="th-sub" style="width:45px;">kerja</th>
                    <th class="th-sub" style="width:50px;">selesai</th>
                    <th class="th-sub" style="width:70px;">total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($details as $i => $row)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td class="text-center" style="white-space:nowrap;">{{ $row->kode_spk }}</td>
                        <td class="text-left">{{ $row->nama_tipe }}</td>
                        <td class="text-center">{{ $row->no_polisi }}</td>
                        {{-- <td class="text-right">{{ $row->upah_fmt }}</td> --}}
                        <td class="text-right">{{ $row->upah_kerja_fmt ?? $row->upah_fmt }}</td>
                        <td class="text-right">{{ $row->sisa_upah_fmt ?? $row->upah_fmt }}</td>
                        {{-- Kolom Proses Kerja: tampilkan persen di kolom "kerja" atau "selesai" --}}
                        {{-- @if ((int) $row->persen === 100)
                            <td class="text-center"></td>
                            <td class="text-center">{{ $row->persen_fmt }}</td>
                        @else
                            <td class="text-center">{{ $row->persen_fmt }}</td>
                            <td class="text-center"></td>
                        @endif --}}
                        <td class="text-center">{{ $row->persen_fmt }}</td>
                        <td class="text-center">{{ $row->persen_selesai_fmt }}</td>
                        {{-- <td class="text-right">{{ $row->nilai_fmt }}</td> --}}
                        <td class="text-right">{{ $row->nilai_fmt }}</td>
                        <td class="text-left">{{ $row->keterangan ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    {{-- <td colspan="7" class="text-right" style="font-weight:bold;">Grand Total</td> --}}
                    <td colspan="8" class="text-right" style="font-weight:bold;">Grand Total</td>
                    <td class="text-right" style="font-weight:bold;">{{ $data->total_nilai_fmt }}</td>
                     <td></td>
                </tr>
            </tfoot>
        </table>

        {{-- Tanda Tangan --}}
        <div class="ttd-section">
            <div class="ttd-box">
                <div>Diketahui</div>
                <div><span
                        class="ttd-name">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                </div>
                <div class="ttd-paren">(
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
            </div>
            <div class="ttd-box">
                <div>Disetujui</div>
                <div><span
                        class="ttd-name">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                </div>
                <div class="ttd-paren">(
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
            </div>
            <div class="ttd-box">
                <div>Dibayar</div>
                <div><span
                        class="ttd-name">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                </div>
                <div class="ttd-paren">(
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
            </div>
            <div class="ttd-box">
                <div>Di Terima</div>
                <div><span
                        class="ttd-name">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                </div>
                <div class="ttd-paren">(
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
            </div>
        </div>

    </div>
@endsection
