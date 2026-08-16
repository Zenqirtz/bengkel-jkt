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

        .signature-section { margin-top: 10px; }
        .line { border-bottom: 1px solid black; width: 200px; display: inline-block; margin-top: 80px; }
        .check-mark { font-weight: bold; color: black; }

        /* Table overrides */
        .table-custom {
            border-collapse: collapse; /* Ini kunci agar border menyatu */
            width: 100%; /* Lebar bisa dipindah ke sini agar HTML lebih bersih */
        }
        
        .table-custom th {
            border: 1px solid #000 !important;
            padding: 4px;
        }
        .table-custom td {
            border: 1px solid #000 !important;
            padding: 4px;
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

<body class="A4" onload="printData()">
	<section class="sheet padding-10mm">
		<table class="table" width="100%" align="center" border="0">
		<tr>
			<td width="50%" valign="top">
				<div class="mb-1">Kepada Yth :</div>
                <div class="title mb-1">Bagian Claim</div>
                <div class="title">{{ $data->nama_pelanggan }}</div>
			</td>
			<td valign="top" align="right">
				<p>Tanggal : {{ $data->tgl_salvage }}</p>
			</td>
		</tr>
        <tr>
            <td colspan="2" align="center">
                <h2 class="mb-1 text-decoration-underline">Daftar Salvage</h2>
                <div class="fw-bold">BENGKEL : {{ $cabang->nama_cabang }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="left">
                <p>Bersama ini kami sertakan salvage dari kendaraan :</p>
            </td>
        </tr>
        <tr>
			<td valign="top">
				<table>
                    <tr>
                        <td width="30%">Merek / Type</td>
                        <td width="5%">:</td>
                        <td class="fw-bold">{{ $data->merek_tipe }}</td>
                    </tr>
                    <tr>
                        <td>No. Polisi</td>
                        <td>:</td>
                        <td class="fw-bold">{{ $data->no_polisi }}</td>
                    </tr>
                </table>
			</td>
			<td valign="top">
				<table>
                    <tr>
                        <td width="30%">No. SPK</td>
                        <td width="5%">:</td>
                        <td class="fw-bold">{{ $data->kode_spk }}</td>
                    </tr>
                    <tr>
                        <td>No. Polis / No. Tiket</td>
                        <td>:</td>
                        <td class="fw-bold">{{ $data->no_polis }}</td>
                    </tr>
                </table>
			</td>
		</tr>
        <tr>
            <td colspan="2" align="left">
                <p>Berikut adalah daftar salvage dari kendaraan tersebut :</p>
            </td>
        </tr>
		</table>

        <table class="table-custom" width="100%">
            <thead>
                <tr>
                    <th width="50" align="center">No.</th>
                    <th align="center">Keterangan</th>
                    <th width="80" align="center">Ada</th>
                    <th width="80" align="center">Tdk Ada</th>
                    <th width="100" align="center">Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data_detail as $row)
                <tr>
                    <td align="center">{{ $row->line_no }}</td>
                    <td>{{ $row->nama_sparepart }}</td>
                    <td align="center" class="check-mark">@if ($row->cek == '1') ✓ @endif</td>
                    <td align="center" class="check-mark">@if ($row->cek == '0') ✓ @endif</td>
                    <td align="center">{{ number_format($row->qty, 0, ".", ",") }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="table" width="100%" border="0">
        <tr>
            <td width="50%" valign="top" align="center">
                <p class="mb-0">Diterima oleh</p>
                <div class="line"></div>
            </td>
            <td width="50%" valign="top" align="center">
                <p class="mb-0">Diserahkan oleh</p>
                <div class="line"></div>
            </td>
        </tr>
        </table>

        <p class="fst-italic">Demikian kami sampaikan dan atas perhatian dan kerjasama yang baik kami ucapkan terima kasih.</p>

	</section>
</body>

</html>