@extends('layouts/layoutMaster')

@section('title', $title)

<!-- Vendor Styles -->
@section('vendor-style')
    <style>
        /* Styling khusus agar terlihat seperti kertas dokumen cetak */
        body {
            background-color: #f8f9fa;
        }

        .invoice-container {
            background-color: #ffffff;
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            page-break-after: always;
        }

        .invoice-container:last-child {
            page-break-after: auto;
        }

        .logo-oval {
            border: 2px solid #000;
            border-radius: 50%;
            width: 130px;
            height: 90px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 0 auto;
        }

        .logo-oval h2 {
            margin: 0;
            font-weight: 900;
            line-height: 1;
            letter-spacing: 2px;
        }

        .logo-oval small {
            font-size: 10px;
            letter-spacing: 5px;
            font-weight: bold;
        }

        .table-invoice th,
        .table-invoice td {
            border-color: #000 !important;
            padding: 12px 15px;
        }

        .label-col {
            width: 25%;
            vertical-align: top;
        }

        @media print {
            .invoice-container {
                box-shadow: none;
                margin: 0 auto;
                page-break-after: always;
            }
            .invoice-container:last-child {
                page-break-after: auto;
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
    @forelse ($datas as $data)
        <div class="invoice-container border border-dark">
            <div class="row mb-4 align-items-center">
                <div class="col-sm-3 text-center mb-3 mb-sm-0">
                    @if ($file_logo)
                        <img src="{{ asset('assets/img/cabang/' . $cabang->logo_cabang) }}" alt="" width="150">
                    @else
                        <div class="logo-oval">
                            <h2>{{ $cabang->nama_singkat }}</h2>
                        </div>
                    @endif
                </div>
                <div class="col-sm-9">
                    <h4 class="fw-bold mb-1">{{ $cabang->nama_cabang }}</h4>
                    <p class="mb-1">CAR BODY REPAIR & PAINT SPECIALIST</p>
                    <p class="mb-1">{{ $cabang->alamat1 }}</p>
                    <p class="mb-0">Telp : {{ $cabang->telepon }}</p>
                </div>
            </div>

            <div class="mb-3">
                <h5 class="fw-bold">INVOICE NO. : {{ $data->no_invoice }}</h5>
            </div>

            <table class="table table-bordered border-dark table-invoice mb-4">
                <tbody>
                    <tr>
                        <td class="label-col">Kepada Yth</td>
                        <td>
                            <span class="fw-bold">{{ $data->pemilik }}</span><br>
                            {{ $data->alamat }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Nilai tagihan</td>
                        <td>
                            <span class="fw-bold">Rp. {{ number_format($data->total_or, 0, ',', '.') }}</span><br>
                            # {{ \Helper::terbilang_rupiah($data->total_or) }} #
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Untuk tagihan</td>
                        <td>
                            Biaya resiko perbaikan sendiri<br>
                            {{ $data->nama_pelanggan }}<br>
                            {{ $data->kode_spk }}<br>
                            Merek Kendaraan : {{ $data->merek_tipe }} No. Polisi : {{ $data->no_polisi }}<br>
                            {{ $data->no_polis }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="row mt-5">
                <div class="col-md-8">
                    <ul class="list-unstyled mb-0" style="font-size: 0.9rem;">
                        <li class="mb-1">* Invoice ini bukan sebagai bukti pembayaran yang sah</li>
                        <li class="mb-1">* Pembayaran dapat ditransfer ke rekening : <br>
                            @if (!blank($cabang->rekening1))
                                <span class="fw-bold">{{ $cabang->rekening1 }}</span> <br>
                            @endif
                            @if (!blank($cabang->rekening2))
                                <span class="fw-bold">{{ $cabang->rekening2 }}</span>
                            @endif
                        </li>
                    </ul>
                </div>
                <div class="col-md-4 text-end d-flex flex-column justify-content-between" style="min-height: 120px;">
                    <div>
                        Jakarta,
                        {{ !blank($data->tgl_invoice) ? date('d F Y', strtotime($data->tgl_invoice)) : date('d F Y') }}
                    </div>
                    <div>
                        <br><br><br>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="invoice-container border border-dark text-center py-5">
            <h5 class="text-muted">Tidak ada data Invoice OR yang ditemukan.</h5>
        </div>
    @endforelse
@endsection
