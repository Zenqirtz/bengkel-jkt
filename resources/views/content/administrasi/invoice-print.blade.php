<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<title>{{ @$title }}</title>

	<link href="{{ asset('assets/css/paper.css') }}" rel="stylesheet" type="text/css">
	<script>
		function printData() {
			window.print();
		}
	</script>
	<style>
		.label-col { width: 130px; }
		.sep-col { width: 10px; text-align: center; }
		.val-col { font-weight: 500; }
		/* .title { font-size: 18px; }
		.subtitle { font-size: 12px; } */

         /* Table overrides */
        .table-custom {
            border-collapse: collapse; /* Ini kunci agar border menyatu */
            width: 100%; /* Lebar bisa dipindah ke sini agar HTML lebih bersih */
        }
        
        .table-custom th {
            border: 1px solid #000 !important;
            padding: 4px;
            font-size: 12px;
        }
        .table-custom td {
            border: 1px solid #000 !important;
            padding: 4px;
            font-size: 12px;
        }

        .table-custom2 {
            border-collapse: collapse; /* Ini kunci agar border menyatu */
            width: 100%; /* Lebar bisa dipindah ke sini agar HTML lebih bersih */
        }
        
        .table-custom2 th {
            border: 0px solid #000 !important;
            padding: 2px;
        }
        .table-custom2 td {
            border: 0px solid #000 !important;
            padding: 2px;
        }

        .doc-footer {
            font-size: 12px;
            font-style: italic; /* Sesuai gaya umum dokumen rangkap */
        }

		/* @media print {
			@page {
				size: A5 landscape;
				Opsional: Anda juga bisa mengatur margin default di sini
				margin: 10mm; 
			}

			Opsional: Sembunyikan elemen yang tidak perlu di-print (seperti tombol/navbar)
			.no-print {
				display: none !important;
			}
		} */
	</style>
</head>

<body class="A5 landscape" onload="printData()">
	<section class="sheet padding-10mm">
		<table width="100%" align="center" border="0">
		<tr>
			<td width="180" valign="top">
				@if (@$file_logo)
					<img src="{{ asset('assets/img/cabang/' . @$cabang->logo_cabang) }}" alt="" width="150">
				@else
					<div class="logo-oval">
						<h2>{{ @$cabang->nama_singkat }}</h2>
					</div>
				@endif
			</td>
			<td valign="top" class="title">
				<div class="mb-1">{{ @$cabang->nama_cabang }}</div>
                <div class="mb-1">CAR BODY REPAIR & PAINT SPECIALIST</div>
				<div class="mb-1">{{ @$cabang->alamat1 }}</div>
				<div class="mb-1">Telp : {{ @$cabang->telepon }} Fax : {{ @$cabang->fax }}</div>
			</td>
		</tr>
		</table>

        <table class="table" width="100%" align="center" border="0">
        <tr>
            <td width="65%" align="left" class="subtitle">
                NO. KWITANSI : {{ $data->kode_kwitansi }}
            </td>
            <td align="right" class="subtitle">
                NO. KLAIM : {{ $data->kode_claim }}
            </td>
        </tr>
        </table>

        <table class="table-custom" width="100%" align="center" border="0">
        <tr>
            <td width="30%" height="50" align="left">
                Kepada Yth
            </td>
            <td align="left">
                <span class="fw-bold">{{ $data->nama_pelanggan }}</span> <br>
                {{ $data->alamat }}
            </td>
        </tr>
        <tr>
            <td height="50" align="left">
                Nilai Tagihan
            </td>
            <td align="left">
                <span class="fw-bold">Rp. {{ $data->grand_total }}</span> <br>
                # {{ $data->terbilang }} #
            </td>
        </tr>
        <tr>
            <td height="100" align="left">
                Untuk Tagihan
            </td>
            <td align="left" valign="top">
                <table class="table-custom2" width="100%" align="center" border="0">
                    <tr>
                        <td width="150">Jasa</td>
                        <td width="30">Rp</td>
                        <td width="300" align="right">{{ $data->total_jasa }}</td>
                        <td width="50">&nbsp;</td>
                        <td width="150">Total OR</td>
                        <td width="30">Rp</td>
                        <td width="300" align="right">{{ $data->total_or_ass }}</td>
                    </tr>
                    <tr>
                        <td>Bahan</td>
                        <td>Rp</td>
                        <td align="right">{{ $data->total_bahan }}</td>
                        <td>&nbsp;</td>
                        <td>PPh</td>
                        <td>Rp</td>
                        <td align="right">{{ $data->pph }}</td>
                    </tr>
                    <tr>
                        <td>Sparepart</td>
                        <td>Rp</td>
                        <td align="right">{{ $data->total_sparepart }}</td>
                        <td>&nbsp;</td>
                        <td>PPN</td>
                        <td>Rp</td>
                        <td align="right">{{ $data->ppn }}</td>
                    </tr>
                    <tr>
                        <td>Derek / Lain</td>
                        <td>Rp</td>
                        <td align="right">{{ $data->total_lain }}</td>
                        <td>&nbsp;</td>
                        <td>Salvage</td>
                        <td>Rp</td>
                        <td align="right">{{ $data->salvage }}</td>
                    </tr>
                    <tr><td colspan="7"><div class="signature-line"></div></td></tr>
                    <tr>
                        <td>Sub Total</td>
                        <td>Rp</td>
                        <td align="right">{{ $data->subtotal }}</td>
                        <td>&nbsp;</td>
                        <td>Sub Total</td>
                        <td>Rp</td>
                        <td align="right">{{ $data->subtotal2 }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        </table>

        <table class="table" width="100%" align="center" border="0">
            <tr>
                <td width="70%" align="left">
                    <div class="doc-footer mt-2">
                        Sesuai Estimasi : {{ $data->kode_estimasi }}, 
                        No. SPK : {{ $data->kode_spk }}, 
                        No. Polis / No. Tiket : {{ $data->no_polis }},
                        Merek Kendaraan : {{ $data->merek_tipe }},
                        No. Polisi : {{ $data->no_polisi }}
                    </div>
                </td>
                <td align="right" valign="top">
                    Jakarta, {{ date("d F Y", strtotime($data->tgl_kwitansi)) }}
                </td>
            </tr>
            <tr>
                <td>
                    * Pembayaran dapat ditransfer ke rekening : <br>
                    @if (!blank($cabang->rekening1))
                        <span class="fw-bold">{{ $cabang->rekening1 }}</span> <br>
                    @endif
                    @if (!blank($cabang->rekening2))
                        <span class="fw-bold">{{ $cabang->rekening2 }}</span>
                    @endif
                </td>
                <td>&nbsp;</td>
            </tr>
        </table>

	</section>
</body>

</html>