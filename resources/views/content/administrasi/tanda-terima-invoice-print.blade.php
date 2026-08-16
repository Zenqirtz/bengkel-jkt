@extends('layouts/layoutMaster')

@section('title', $title)

<!-- Vendor Styles -->
@section('vendor-style')
    <style>
        body {
            background-color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            color: #000;
        }

        .paper {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            /* A4 Height */
            margin: 10px auto;
            padding: 5mm 10mm;
            /* box-shadow: 0 0 10px rgba(0,0,0,0.1); */
            position: relative;
        }

        /* Pengaturan Khusus Tabel */
        .table-custom {
            border-color: #000 !important;
        }

        .table-custom th,
        .table-custom td {
            border-color: #000 !important;
            padding: 8px;
            vertical-align: middle;
        }

        .table-custom th {
            background-color: #eaeaea !important;
            /* Warna abu-abu pudar seperti digambar */
            -webkit-print-color-adjust: exact;
        }

        /* Jarak antar baris teks alamat */
        .address-text p {
            margin-bottom: 2px;
        }

        /* Area Tanda Tangan */
        /* .signature-box {
                                                   height: 100px; /* Jarak untuk area tanda tangan/stempel */
        }

        */

        /* Terbilang Section */
        .terbilang-row {
            margin-top: 20px;
            display: flex;
        }

        .terbilang-label {
            width: 80px;
            flex-shrink: 0;
        }

        .terbilang-text {
            text-transform: uppercase;
            font-weight: 500;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 60px;
        }

        .signature-box {
            text-align: center;
            width: 250px;
        }

        .signature-line {
            margin-top: 80px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            font-weight: bold;
            font-size: 11px;
        }

        .signature-date {
            margin-bottom: 5px;
            text-align: center;
        }

        .date-footer {
            margin-top: 5px;
            text-align: left;
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
            $tglInvoice = $items->last()->tgl_kwitansi;
        @endphp

        <div class="paper">
            <div class="mb-4 address-text">
                <h5 class="fw-bold mb-3">{{ $cabang->nama_cabang }}</h5>
                <p>{{ $cabang->alamat1 }}</p>
                <p>Kode Pos {{ $cabang->kode_pos }}</p>
                <p>Telp. {{ $cabang->telepon }} Fax. {{ $cabang->fax }}</p>
            </div>

            <h5 class="text-center fw-bold mb-4" style="letter-spacing: 1px;">TANDA TERIMA SERAH INVOICE</h5>

            <div class="mb-4 address-text">
                <p>Telah diserahkan invoice asli kepada :</p>
                {{-- <p class="fw-bold">{{ $data->nama_pelanggan }}</p>
                <p>{{ $data->alamat }}</p> --}}
                <p class="fw-bold">{{ $pelanggan->nama_pelanggan }}</p>
                <p>{{ $pelanggan->alamat }}</p>
            </div>

            <table class="tableX table-bordered table-custom mb-3">
                <thead>
                    <tr class="text-center">
                        <th style="width: 5%">No.</th>
                        <th style="width: 10%">No SPK</th>
                        <th style="width: 20%">No Invoice</th>
                        <th style="width: 15%">Merk / Type</th>
                        <th style="width: 12%">No Polisi</th>
                        <th style="width: 20%">No Klaim</th>
                        <th style="width: 18%">Jumlah<br>(Rp.)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- <tr>
                        <td class="text-center">1</td>
                        <td class="text-center">{{ $data->kode_spk }}</td>
                        <td class="text-center">{{ $data->kode_kwitansi }}</td>
                        <td>{{ $data->merek_tipe }}</td>
                        <td class="text-center">{{ $data->no_polisi }}</td>
                        <td class="text-center">{{ $data->kode_claim }}</td>
                        <td class="text-end">{{ $data->grand_total }}</td>
                    </tr> --}}
                    @foreach ($items as $i => $item)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td class="text-center">{{ $item->kode_spk }}</td>
                            <td class="text-center">{{ $item->kode_kwitansi }}</td>
                            <td>{{ $item->merek_tipe }}</td>
                            <td class="text-center">{{ $item->no_polisi }}</td>
                            <td class="text-center">{{ $item->kode_claim }}</td>
                            <td class="text-end">{{ number_format($item->grand_total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="6" class="text-center fw-bold">Grand Total</td>
                        {{-- <td class="text-end fw-bold">{{ $data->grand_total }}</td> --}}
                        <td class="text-end fw-bold">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- <div class="row mb-5">
			<div class="col-sm-2">Terbilang :</div>
			<div class="col-sm-10">{{ $data->terbilang }}</div>
	</div> --}}

            <div class="terbilang-row">
                <div class="terbilang-label">Terbilang :</div>
                <div class="terbilang-text">{{ $terbilang }}</div>
            </div>

            <div class="signature-section row justify-content-between">
                <div class="col-auto">
                    <div class="signature-box" style="margin-top: 24px;">
                        <div class="mb-5">Diserahkan oleh,</div>
                        <div class="signature-line">Nama Jelas</div>
                        <div class="date-footer">Tgl. diserahkan : {{ date('d/m/Y', strtotime($tglInvoice)) }}</div>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="signature-box">
                        <div class="signature-date">Jakarta, {{ date('d F Y', strtotime($tglInvoice)) }}</div>
                        <div class="mb-5">Diterima oleh,</div>
                        <div class="signature-line">Nama Sales & Stempel Perusahaan</div>
                        <div class="date-footer">Tgl. diterima : {{ date('d/m/Y', strtotime($tglInvoice)) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- <div class="row mt-4">
		<div class="col-6">
			<div style="padding-left: 20px;">
				<div class="text-center mb-1" style="width: 200px;">
                    <p class="mb-0">Diserahkan oleh,</p>
                    <div class="signature-box"></div>
				</div>
				<div style="padding-left: 20px;">Tgl. diserahkan : {{ date("d/m/Y") }}</div> </div>
		</div>
        <div class="col-6">
            <div style="padding-left: 20px;">
                <div class="text-center mb-1" style="width: 250px;">
                    <p class="mb-0">Jakarta, {{ date("d F Y") }}</p>
                    <div class="signature-box"></div>
                </div>
                <div style="padding-left: 50px;">Tgl. diterima : {{ date("d/m/Y") }}</div>
            </div>
        </div>
	</div> --}}

        </div>
    @endforeach

@endsection
