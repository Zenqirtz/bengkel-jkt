@extends('layouts/layoutMaster')

@section('title', $title)

<!-- Vendor Styles -->
@section('vendor-style')
<style>
    /* --- GENERAL PRINT STYLING --- */
    body {
        color: #000;
        background-color: #f0f0f0;
        font-family: Arial, sans-serif;
        font-size: 12px;
        -webkit-print-color-adjust: exact;
    }
    
    /* Container Kertas A4 */
    .sheet {
        background: white;
        width: 210mm;
        min-height: 297mm;
        margin: 20px auto;
        padding: 10mm 15mm;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        position: relative;
    }

    /* --- HEADER SECTION --- */
    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    /* Kiri: Info Perusahaan */
    .company-info {
        width: 60%;
    }
    .recipient-line {
        font-size: 16px;
        margin-bottom: 2px;
    }
    .company-name {
        font-weight: bold;
        font-size: 18px;
        margin-bottom: 2px;
    }
    .company-sub {
        font-weight: bold;
        font-size: 12px;
        margin-bottom: 5px;
    }
    .company-address {
        font-size: 11px;
        line-height: 1.4;
    }

    /* Kanan: Judul & Tabel Kecil */
    .po-meta {
        width: 38%;
    }
    .doc-title {
        font-weight: bold;
        font-size: 20px;
        margin-bottom: 5px;
        text-align: left; /* Sesuai gambar rata kiri terhadap kolomnya */
    }
    
    /* Tabel Kecil di Header */
    .meta-table {
        width: 100%;
        border-collapse: collapse;
        border: 2px solid #000;
    }
    .meta-table td {
        border: 1px solid #000;
        padding: 3px 5px;
        font-size: 11px;
    }
    .meta-label {
        background-color: #fff;
        width: 100px;
    }
    .meta-val {
        font-weight: bold;
    }

    /* --- MAIN ITEM TABLE --- */
    .main-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
        border: 2px solid #000; /* Border luar tebal */
    }
    .main-table th, .main-table td {
        border: 1px solid #000;
        padding: 5px 8px;
        vertical-align: middle;
    }
    .main-table th {
        text-align: center;
        background-color: #fff; /* Putih polos */
        font-weight: normal; /* Font header tidak terlalu bold di gambar */
    }

    /* --- FOOTER & SIGNATURE --- */
    .footer-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 5px;
        margin-bottom: 30px;
        font-size: 12px;
    }
    .date-left {
        
    }
    .code-right {
        font-weight: bold;
        text-transform: uppercase;
    }

    .signature-section {
        display: flex;
        justify-content: space-between;
        text-align: center;
        padding: 0 20px;
    }
    .sig-box {
        width: 180px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 120px; /* Tinggi ruang tanda tangan */
    }
    .sig-role {
        text-transform: uppercase;
        font-size: 12px;
    }
    .sig-name {
        text-decoration: underline; /* Garis bawah nama */
        font-weight: bold;
        text-transform: uppercase;
    }
    .sig-line-only {
        border-bottom: 2px solid #000; /* Garis saja tanpa nama */
        height: 20px;
        margin-top: auto;
    }

    /* --- PRINT SETTINGS --- */
    @media print {
        @page {
            size: A4;
            margin: 0;
        }
        body { 
            margin: 0;
            background-color: white;
        }
        .sheet { 
            width: 100%;
            margin: 0;
            box-shadow: none;
            padding: 10mm 15mm;
            min-height: auto;
        }
    }
</style>
@endsection

<!-- Page Scripts -->
@section('page-script')
<script>
'use strict';
(function () {
  window.print();
})();
</script>
@endsection

@section('content')
<div class="sheet">
        
    <div class="header-top">
        <div class="company-info">
            <div class="company-name">{{ $datas->nama_cabang }}</div>
            <div class="company-sub">BODY REPAIR & PAINT SPECIALIST</div>
            <div class="company-address">
              {{ $datas->alamat1 }}<br><br>
                Telp.: {{ $datas->telepon }} Fax.: {{ $datas->fax }}
            </div>

            <div class="recipient-line mt-5">
                No Bon {{ $datas->tipe_barang }} : <strong>{{ $datas->no_bon }}</strong>
            </div>
        </div>

        <div class="po-meta">
            <table class="meta-table">
                <tr>
                    <td class="meta-label">TANGGAL</td>
                    <td class="meta-val">{{ $datas->tanggal }}</td>
                </tr>
                <tr>
                    <td class="meta-label">KENDARAAN</td>
                    <td class="meta-val">@if (!blank($spk)) {{ $spk->merek_tipe }} @endif</td>
                </tr>
                <tr>
                    <td class="meta-label">NOMOR POLISI</td>
                    <td class="meta-val">@if (!blank($spk)) {{ $spk->no_polisi }} @endif</td>
                </tr>
                <tr>
                    <td class="meta-label">NO. SPK</td>
                    <td class="meta-val">{{ $datas->kode_spk }}</td>
                </tr>
            </table>
        </div>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 50px;">No.</th>
                <th>Nama Barang</th>
                <th style="width: 100px;">No. Parts</th>
                <th style="width: 60px;">Qty</th>
                <th style="width: 120px;">Harga</th>
                <th style="width: 120px;">Total</th>
            </tr>
        </thead>
        <tbody>
          @php $TOTAL = 0; @endphp
          @foreach ($details as $row)
          @php $TOTAL += $row->jumlah; @endphp
          <tr>
            <td class="text-center">{{ $row->no_urut }}</td>
            <td>{{ $row->nama_barang }}</td>
            <td>{{ $row->no_sparepart }}</td>
            <td class="text-center">{{ number_format($row->qty, 2, ".", ",") }}</td>
            <td class="text-end">{{ number_format($row->harga, 0, ".", ",") }}</td>
            <td class="text-end">{{ number_format($row->jumlah, 0, ".", ",") }}</td>
          </tr>   
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="5"><strong>Total</strong></td>
            <td class="text-end">{{ number_format($TOTAL, 0, ".", ",") }}</td>
          </tr>
        </tfoot>
    </table>

    {{-- <div class="footer-row">
        <div class="date-left">Jakarta, {{ $datas->tanggal }}</div>
        <div class="code-right">{{ $datas->tipe_barang }}</div>
    </div> --}}

    <div class="signature-section">
        <div class="sig-box">
            <div class="sig-role">DISETUJUI OLEH</div>
            <div class="sig-name">PIMPINAN</div>
        </div>

        <div class="sig-box">
            <div class="sig-role">DIKELUARKAN OLEH</div>
            <div class="sig-name">BAGIAN GUDANG</div>
        </div>

        <div class="sig-box">
            <div class="sig-role">PEMOHON</div>
            <div class="sig-line-only"></div>
        </div>
    </div>

</div>
@endsection
