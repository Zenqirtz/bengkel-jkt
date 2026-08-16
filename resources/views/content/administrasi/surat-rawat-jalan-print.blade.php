@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: #fff;
            font-family: 'Times New Roman', Times, serif;
            font-size: 16px;
            color: #000;
        }

        .paper {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 10px auto;
            padding: 15mm 15mm 10mm 20mm;
            position: relative;
            page-break-after: always;
        }

        .paper:last-child {
            page-break-after: avoid;
        }

        /* ── Header ── */
        .header-area {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
        }

        .header-left {
            font-size: 17px;
            font-weight: bold;
        }

        .header-left .sub {
            font-weight: normal;
            font-size: 12px;
        }

        .header-right {
            text-align: right;
            font-size: 17px;
        }

        .header-right .spk-label {
            font-weight: bold;
        }

        .header-right .spk-value {
            text-decoration: line-through;
        }

        /* ── Title ── */
        .doc-title {
            text-align: center;
            font-size: 30px;
            font-weight: bold;
            text-decoration: underline;
            margin: 18px 0 16px;
            letter-spacing: 1px;
        }

        /* ── Opening line ── */
        .opening {
            margin-bottom: 10px;
            font-size: 16px;
        }

        /* ── Info Table ── */
        .info-table {
            margin-left: 45px;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 1px 4px;
            font-size: 15px;
            vertical-align: top;
        }

        .info-table td:first-child {
            width: 155px;
        }

        .info-table td.colon {
            width: 14px;
        }

        /* ── Body text ── */
        .insured-line {
            margin-bottom: 10px;
            font-size: 16px;
        }

        .body-text {
            margin-bottom: 8px;
            font-size: 16px;
        }

        .body-text-strike {
            text-decoration: line-through;
            font-size: 16px;
            margin-bottom: 4px;
        }

        /* ── Signature ── */
        .signature-section {
            margin-top: 55px;
            display: flex;
            justify-content: space-between;
        }

        .sig-box {
            text-align: center;
            width: 220px;
        }

        .sig-role {
            margin-bottom: 65px;
            font-size: 16px;
        }

        .sig-line {
            border-top: 1px solid #000;
            padding-top: 3px;
            font-size: 14px;
        }

        @media print {
            body {
                background: #fff;
            }

            .paper {
                margin: 0;
                box-shadow: none;
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

    @foreach ($datas as $row)
        @php
            $merek_tipe = trim(($row->nama_merek ?? '') . ' ' . ($row->nama_tipe ?? ''));
            $tgl_masuk = blank($row->tgl_masuk) ? '' : \Carbon\Carbon::parse($row->tgl_masuk)->format('d/m/y');
        @endphp

        <div class="paper">

            {{-- ── Header ── --}}
            <div class="header-area">
                <div class="header-left">
                    {{ $cabang->nama_cabang }}
                    <div class="sub">Telp: {{ $cabang->telepon ?? '' }} &nbsp; Fax: {{ $cabang->fax ?? '' }}</div>
                </div>
                <div class="header-right">
                    <span class="spk-label">No SPK: </span>
                    <span>{{ $row->kode_spk }}</span>
                </div>
            </div>

            {{-- ── Title ── --}}
            <div class="doc-title">Surat Rawat Jalan</div>

            {{-- ── Opening ── --}}
            <div class="opening">Dengan ini selaku pemilik kendaraan:</div>

            {{-- ── Vehicle Info ── --}}
            <table class="info-table">
                <tr>
                    <td>No Polisi</td>
                    <td class="colon">:</td>
                    <td>{{ $row->no_polisi }}</td>
                </tr>
                <tr>
                    <td>Type Kendaraan</td>
                    <td class="colon">:</td>
                    <td>{{ $merek_tipe }}</td>
                </tr>
                <tr>
                    <td>No Rangka</td>
                    <td class="colon">:</td>
                    <td>{{ $row->no_rangka ?? '' }}</td>
                </tr>
                <tr>
                    <td>No Mesin</td>
                    <td class="colon">:</td>
                    <td>{{ $row->no_mesin ?? '' }}</td>
                </tr>
                <tr>
                    <td>Tgl. Masuk Kendaraan</td>
                    <td class="colon">:</td>
                    <td>{{ $tgl_masuk }}</td>
                </tr>
            </table>

            {{-- ── Insurance Line ── --}}
            <div class="insured-line">
                yang dipertanggungkan pada {{ $row->nama_pelanggan ?? '' }}
            </div>
            <div class="body-text">dengan kendaraan sebagai berikut</div>

            {{-- ── Owner Info ── --}}
            <table class="info-table">
                <tr>
                    <td>No Polisi</td>
                    <td class="colon">:</td>
                    <td>{{ $row->no_polisi }}</td>
                </tr>
                <tr>
                    <td>Pemilik Kendaraan</td>
                    <td class="colon">:</td>
                    <td>{{ strtoupper($row->pemilik ?? '') }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td class="colon">:</td>
                    <td>{{ strtoupper($row->alamat ?? '') }}</td>
                </tr>
            </table>

            <div style="margin-top:14px;"></div>
            <div class="body-text">Kami,</div>
            <div class="body-text">
                akan mengeluarkan kendaraan ini sementara, karena kendaraan ini masih belum sempurna perbaikannya.<br>
                Ada pun perbaikan yang kurang adalah sbb:
            </div>
            <div class="body-text">
                UNIT AKAN MASUK BENGKEL KEMBALI JIKA PART SUDAH LENGKAP
            </div>

            {{-- ── Signature ── --}}
            <div class="signature-section">
                <div class="sig-box">
                    <div class="sig-role">Service Advistor</div>
                    <div>(.................................)</div>
                </div>
                <div class="sig-box">
                    <div class="sig-role">Customer</div>
                    <div>(.................................)</div>
                </div>
            </div>

        </div>{{-- .paper --}}
    @endforeach

@endsection
