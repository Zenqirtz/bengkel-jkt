@extends('layouts/layoutMaster')

@section('title', $title)

<!-- Vendor Styles -->
@section('vendor-style')
    <style>
        body {
            /* background-color: #f4f4f4; */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            color: #000;
        }

        .paper {
            background: white;
            width: 210mm;
            min-height: 297mm;
            /* A4 Height */
            margin: 20px auto;
            padding: 10mm 15mm;
            /* box-shadow: 0 0 10px rgba(0,0,0,0.1); */
            position: relative;
        }

        /* Pengaturan Khusus Tabel */
        .table-custom {
            border-color: #000 !important;
        }

        /* .table-custom th,
                            .table-custom td {
                                border-color: #000 !important;
                                padding: 8px;
                                vertical-align: middle;
                                white-space: nowrap;
                            } */
        .table-custom th,
        .table-custom td {
            border-color: #000 !important;
            padding: 6px 8px;
            vertical-align: middle;
            word-break: break-word;
            /* biar panjang tetap muat */
        }

        /* .table-custom th {
                            background-color: #eaeaea !important;
                            Warna abu-abu pudar seperti digambar
                            -webkit-print-color-adjust: exact;
                        } */
        .table-custom th {
            background-color: #eaeaea !important;
            -webkit-print-color-adjust: exact;
            white-space: nowrap;
            /* ← tambah ini */
        }

        /* Jarak antar baris teks alamat */
        .address-text p {
            margin-bottom: 2px;
        }

        /* Area Tanda Tangan */
        .signature-box {
            height: 100px;
            /* Jarak untuk area tanda tangan/stempel */
        }

        .paper {
            page-break-after: always;
        }

        .paper:last-child {
            page-break-after: avoid;
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

    @foreach ($grouped as $group)
        @php
            $pelanggan = $group['pelanggan'];
            $items = $group['items'];
            $grandTotal = $group['grand_total'];
            $terbilang = $group['terbilang'];
            $tglInvoice = $items->last()->tgl_invoice;
        @endphp

        <div class="paper">
            <div class="mb-4 address-text">
                <h5 class="fw-bold mb-3">{{ $cabang->nama_cabang }}</h5>
                <p>{{ $cabang->alamat1 }}</p>
                <p>Kode Pos {{ $cabang->kode_pos }}</p>
                <p>Telp. {{ $cabang->telepon }} Fax. {{ $cabang->fax }}</p>
            </div>

            <h5 class="text-center fw-bold mb-4" style="letter-spacing: 1px;">TANDA TERIMA SERAH INVOICE OR</h5>

            <div class="mb-4 address-text">
                <p>Telah diserahkan invoice asli kepada :</p>
                {{-- <p class="fw-bold">Pak Thomas</p> --}}
                {{-- <p class="fw-bold">{{ $data->pemilik }}</p> --}}
                <p class="fw-bold">{{ $pelanggan->pemilik }}</p>
                {{-- <p>{{ $data->alamat }}</p> --}}
                <p>{{ $pelanggan->alamat }}</p>
            </div>

            {{-- <table class="tableX table-bordered table-custom mb-3">
                <thead>
                    <tr class="text-center">
                        <th rowspan="2" style="width: 5%;">No.</th>
                        <th rowspan="2" style="width: 15%;">No SPK</th>
                        <th rowspan="2" style="width: 15%;">No. Invoice OR</th>
                        <th rowspan="2" style="width: 20%;">Merk Kendaraan</th>
                        <th rowspan="2" style="width: 15%;">No Polisi</th>
                        <th rowspan="2" style="width: 25%;">No. Klaim</th>
                        <th style="width: 20%;">Jumlah</th>
                    </tr>
                    <tr class="text-center">
                        <th>(Rp.)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td class="text-center">{{ $data->kode_spk }}</td>
                        <td>{{ $data->nama_merek }}</td>
                        <td class="text-center">{{ $data->no_polisi }}</td>
                        <td class="text-center">{{ $data->kode_claim }}</td>
                        <td class="text-end">{{ $data->total_or }}</td>
                    </tr>
                    @foreach ($items as $i => $item)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td class="text-center">{{ $item->kode_spk }}</td>
                            <td class="text-center">{{ $item->no_invoice }}</td>
                            <td>{{ $item->nama_merek }}</td>
                            <td class="text-center">{{ $item->no_polisi }}</td>
                            <td class="text-center">{{ $item->kode_claim }}</td>
                            <td class="text-end">{{ number_format($item->total_or, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="5" class="text-center fw-bold">Grand Total</td>
                        <td class="text-end fw-bold">{{ $data->total_or }}</td>
                        <td class="text-end fw-bold">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                        <td colspan="6" class="text-center fw-bold">Grand Total</td>
                        <td class="text-end fw-bold">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table> --}}

            <table class="tableX table-bordered table-custom mb-3" style="width: 100%;">
                <thead>
                    <tr class="text-center">
                        <th style="white-space: nowrap;">No.</th>
                        <th style="white-space: nowrap;">No SPK</th>
                        <th style="white-space: nowrap;">No. Invoice OR</th>
                        <th style="white-space: nowrap;">Merk Kendaraan</th>
                        <th style="white-space: nowrap;">No Polisi</th>
                        <th style="white-space: nowrap;">No. Klaim</th>
                        <th style="white-space: nowrap;">Jumlah (Rp.)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $i => $item)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td class="text-center">{{ $item->kode_spk }}</td>
                            <td class="text-center">{{ $item->no_invoice }}</td>
                            <td>{{ $item->nama_merek }}</td>
                            <td class="text-center">{{ $item->no_polisi }}</td>
                            <td class="text-center">{{ $item->kode_claim }}</td>
                            <td class="text-end">{{ number_format($item->total_or, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="6" class="text-center fw-bold">Grand Total</td>
                        <td class="text-end fw-bold">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="row mb-5">
                <div class="col-sm-2">Terbilang :</div>
                {{-- <div class="col-sm-10">{{ $data->terbilang }}</div> --}}
                <div class="col-sm-10">{{ $terbilang }}</div>
            </div>

            <div class="row mt-4">
                <div class="col-6">
                    <div style="padding-left: 20px;">
                        <div class="text-center mb-1" style="width: 200px;">
                            <p class="mb-0">Diserahkan oleh,</p>
                            <div class="signature-box"></div>
                        </div>
                        {{-- <div style="padding-left: 20px;">Tgl. diserahkan :
                            {{ date('d/m/Y', strtotime($data->tgl_invoice)) }}</div> --}}
                        <div style="padding-left: 20px;">Tgl. diserahkan : {{ date('d/m/Y', strtotime($tglInvoice)) }}
                        </div>

                    </div>
                </div>

                <div class="col-6">
                    <div style="padding-left: 20px;">
                        <div class="text-center mb-1" style="width: 250px;">
                            {{-- <p class="mb-0">Jakarta, {{ date('d F Y', strtotime($data->tgl_invoice)) }}</p> --}}
                            <p class="mb-0">Jakarta, {{ date('d F Y', strtotime($tglInvoice)) }}</p>
                            <div class="signature-box"></div>
                        </div>
                        {{-- <div style="padding-left: 50px;">Tgl. diterima : {{ date('d/m/Y', strtotime($data->tgl_invoice)) }} --}}
                        <div style="padding-left: 50px;">Tgl. diterima : {{ date('d/m/Y', strtotime($tglInvoice)) }}</div>
                    </div>
                </div>
            </div>
        </div>

        </div>
    @endforeach

@endsection
