<?php

namespace App\Http\Controllers\keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Spk;
use App\Models\Parameter;
use App\Models\LogActivity;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel; // EXPORT EXCEL
use App\Exports\LaporanKwitansiLunasExport;    // EXPORT EXCEL

use App\Helpers\Helpers as Helper;

class LaporanKwitansiLunasController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function LaporanKwitansiLunas(): View
  {
    $isList = Helper::AuthIsPerm("list");
    $isAdd = Helper::AuthIsPerm("add");
    $isEdit = Helper::AuthIsPerm("edit");
    $isDel = Helper::AuthIsPerm("delete");
    if (!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path = request()->path();
    $title = Helper::getTitleMenu($path) ?? 'Kwitansi Lunas';

    $user_cabang = session('kd_cabang');

    $jenis_laporan = [
      '' => 'Pilih Jenis Laporan',
      'voucher' => 'Voucher',
      'rekap' => 'Rekap',
      'rinci' => 'Rinci'
    ];

    $datafilter = session('datafilter');
    if (empty($datafilter)) {
      $datafilter['jenis_laporan'] = '';
      $datafilter['tgl_awal'] = date("d/m/Y");
      $datafilter['tgl_akhir'] = date("d/m/Y");
      $datafilter['no_voucher'] = '';
    }

    return view('content.keuangan.laporan.laporan-kwitansi-lunas', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'jenis_laporan' => $jenis_laporan,
      'datafilter' => $datafilter,
    ]);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request): JsonResponse
  {
    $user_cabang = session('kd_cabang');

    $totalData = 0;
    $totalFiltered = 0;
    $data = [];
    if ($request->filled('jenis_laporan')) {

      $startDate = ($request->filled('tgl_awal'))
        ? Carbon::createFromFormat('d/m/Y', $request->tgl_awal, 'Asia/Jakarta')->format('Y-m-d')
        : date("Y-m-d");

      $endDate = ($request->filled('tgl_akhir'))
        ? Carbon::createFromFormat('d/m/Y', $request->tgl_akhir, 'Asia/Jakarta')->format('Y-m-d')
        : date("Y-m-d");

      // if ($request->jenis_laporan == "voucher") {
      //   // Base query
      //   $base = DB::table('v_rep_kwitansi_lunas as k')
      //     ->whereBetween('k.tgl_lunas', [$startDate, $endDate])
      //     ->where('k.kode_cabang', $user_cabang);

      //   // Filtering (search global)
      //   $query = (clone $base);

      // if ($request->filled('tgl_awal')) {
      //   $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
      //   $query->whereDate('k.tgl_lunas', '>=', $startDate);
      // }
      // if ($request->filled('tgl_akhir')) {
      //   $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_akhir, 'Asia/Jakarta')->format('Y-m-d');
      //   $query->whereDate('k.tgl_lunas', '<=', $endDate);
      // }

      // // Total baris tanpa filter
      // $totalData = (clone $query)->count();

      // // Hitung setelah filter (tanpa limit/offset)
      // $totalFiltered = (clone $query)->count();

      // // Ambil data halaman saat ini
      // $datas = $query
      //   ->select([
      //     'k.kode_voucher',
      //     'k.tgl_lunas',
      //     'k.kode_lunas_kwitansi',
      //     'k.kode_spk',
      //     'k.kode_kwitansi',
      //     'k.kode_estimasi',
      //     'k.no_polisi',
      //     'k.jasa',
      //     'k.bahan',
      //     'k.total_sparepart_s',
      //     'k.ppn',
      //     'k.total_lain_s',
      //     'k.total_or_ass',
      //     'k.tagihan',
      //     'k.pph',
      //     'k.materai',
      //     'k.uang_muka',
      //     'k.diterima',
      //     'k.tot_estimasi',
      //     'k.biaya_real'
      //   ])
      //   // ->orderBy('k.tgl_lunas', 'asc')
      //   ->orderBy('k.kode_voucher', 'asc')  // UBAH: grouping per voucher
      //   ->orderBy('k.tgl_lunas', 'asc')
      //   ->get();

      // // Susun payload DataTables
      // $data = [];
      // $fake = 0; //$start;
      // foreach ($datas as $row) {
      //   $data[] = [
      //     'no' => ++$fake,
      //     'tgl_lunas' => blank($row->tgl_lunas) ? '' : date("d/m/Y", strtotime($row->tgl_lunas)),
      //     'kode_voucher' => $row->kode_voucher,
      //     'kode_lunas_kwitansi' => $row->kode_lunas_kwitansi,
      //     'kode_spk' => $row->kode_spk,
      //     'kode_kwitansi' => $row->kode_kwitansi,
      //     'kode_estimasi' => $row->kode_estimasi,
      //     'no_polisi' => $row->no_polisi,
      //     'jasa' => number_format($row->jasa, 0, ".", ","),
      //     'bahan' => number_format($row->bahan, 0, ".", ","),
      //     'total_sparepart_s' => number_format($row->total_sparepart_s, 0, ".", ","),
      //     'ppn' => number_format($row->ppn, 0, ".", ","),
      //     'total_lain_s' => number_format($row->total_lain_s, 0, ".", ","),
      //     'total_or_ass' => number_format($row->total_or_ass, 0, ".", ","),
      //     'tagihan' => number_format($row->tagihan, 0, ".", ","),
      //     'pph' => number_format($row->pph, 0, ".", ","),
      //     'materai' => number_format($row->materai, 0, ".", ","),
      //     'uang_muka' => number_format($row->uang_muka, 0, ".", ","),
      //     'diterima' => number_format($row->diterima, 0, ".", ","),
      //     'tot_estimasi' => number_format($row->tot_estimasi, 0, ".", ","),
      //     'biaya_real' => number_format($row->biaya_real, 0, ".", ","),
      //   ];
      // }
      if ($request->jenis_laporan == "voucher") {
        // Base query
        $base = DB::table('v_rep_kwitansi_lunas as k')
          ->whereBetween('k.tgl_lunas', [$startDate, $endDate])
          ->where('k.kode_cabang', $user_cabang);

        $query = (clone $base);

        if ($request->filled('no_voucher')) {
          $query->where('k.kode_voucher', $request->no_voucher);
        }

        $datas = $query
          ->select([
            'k.kode_voucher',
            'k.tgl_lunas',
            'k.kode_lunas_kwitansi',
            'k.kode_spk',
            'k.kode_kwitansi',
            'k.kode_estimasi',
            'k.no_polisi',
            'k.nama_pelanggan',
            'k.pembayaran',
            'k.memo',
            'k.jasa',
            'k.bahan',
            'k.total_sparepart_s',
            'k.ppn',
            'k.total_lain_s',
            'k.total_or_ass',
            'k.tagihan',
            'k.pph',
            'k.materai',
            'k.uang_muka',
            'k.diterima',
            'k.tot_estimasi',
            'k.biaya_real'
          ])
          ->orderBy('k.nama_pelanggan', 'asc')
          ->orderBy('k.kode_voucher', 'asc')
          ->orderBy('k.tgl_lunas', 'asc')
          ->get();

        $totalData = $datas->count();
        $totalFiltered = $totalData;

        // Group per voucher (sama seperti printData)
        $grouped = $datas->groupBy('kode_voucher');

        $data = [];
        $fake = 0;
        $grandTotalAll = [
          'jasa' => 0,
          'bahan' => 0,
          'total_sparepart_s' => 0,
          'ppn' => 0,
          'total_lain_s' => 0,
          'total_or_ass' => 0,
          'tagihan' => 0,
          'pph' => 0,
          'materai' => 0,
          'uang_muka' => 0,
          'diterima' => 0,
          'tot_estimasi' => 0,
          'biaya_real' => 0,
        ];

        foreach ($grouped as $kodeVoucher => $rows) {

          // Baris header grup (nama pelanggan + no voucher)
          $data[] = [
            'row_type' => 'header',
            'no' => '',
            'kode_voucher' => $kodeVoucher,
            'tgl_lunas' => '',
            'kode_lunas_kwitansi' => '',
            'kode_spk' => '',
            'kode_kwitansi' => '',
            'kode_estimasi' => '',
            'no_polisi' => '',
            'header_label' => 'Asuransi : ' . $rows->first()->nama_pelanggan . ' | No. Voucher : ' . $kodeVoucher,
            'jasa' => '',
            'bahan' => '',
            'total_sparepart_s' => '',
            'ppn' => '',
            'total_lain_s' => '',
            'total_or_ass' => '',
            'tagihan' => '',
            'pph' => '',
            'materai' => '',
            'uang_muka' => '',
            'diterima' => '',
            'tot_estimasi' => '',
            'biaya_real' => '',
          ];

          // TAMBAHKAN INI: baris header kolom per grup
          $data[] = [
            'row_type' => 'columnheader',
            'no' => 'No',
            'kode_voucher' => 'No. Voucher',
            'tgl_lunas' => 'Tanggal Lunas',
            'kode_lunas_kwitansi' => 'No. Kwitansi',
            'kode_spk' => 'No. SPK',
            'kode_kwitansi' => 'No. Invoice',
            'kode_estimasi' => 'No. Estimasi',
            'no_polisi' => 'No. Polisi',
            'header_label' => '',
            'jasa' => 'Jasa',
            'bahan' => 'Bahan',
            'total_sparepart_s' => 'Sparepart',
            'ppn' => 'PPN',
            'total_lain_s' => 'Lain',
            'total_or_ass' => 'OR',
            'tagihan' => 'Tagihan',
            'pph' => 'PPh',
            'materai' => 'Materai & Transfer',
            'uang_muka' => 'Uang Muka',
            'diterima' => 'Diterima',
            'tot_estimasi' => 'Total Estimasi',
            'biaya_real' => 'Biaya Real',
          ];

          // Subtotal per grup
          $subTotal = [
            'jasa' => 0,
            'bahan' => 0,
            'total_sparepart_s' => 0,
            'ppn' => 0,
            'total_lain_s' => 0,
            'total_or_ass' => 0,
            'tagihan' => 0,
            'pph' => 0,
            'materai' => 0,
            'uang_muka' => 0,
            'diterima' => 0,
            'tot_estimasi' => 0,
            'biaya_real' => 0,
          ];

          // Baris detail
          foreach ($rows as $row) {
            foreach ($subTotal as $key => $val) {
              $subTotal[$key] += $row->$key;
              $grandTotalAll[$key] += $row->$key;
            }

            $data[] = [
              'row_type' => 'detail',
              'no' => ++$fake,
              'kode_voucher' => $row->kode_voucher,
              'tgl_lunas' => blank($row->tgl_lunas) ? '' : date("d/m/Y", strtotime($row->tgl_lunas)),
              'kode_lunas_kwitansi' => $row->kode_lunas_kwitansi,
              'kode_spk' => $row->kode_spk,
              'kode_kwitansi' => $row->kode_kwitansi,
              'kode_estimasi' => $row->kode_estimasi,
              'no_polisi' => $row->no_polisi,
              'header_label' => '',
              'jasa' => number_format($row->jasa, 0, ".", ","),
              'bahan' => number_format($row->bahan, 0, ".", ","),
              'total_sparepart_s' => number_format($row->total_sparepart_s, 0, ".", ","),
              'ppn' => number_format($row->ppn, 0, ".", ","),
              'total_lain_s' => number_format($row->total_lain_s, 0, ".", ","),
              'total_or_ass' => number_format($row->total_or_ass, 0, ".", ","),
              'tagihan' => number_format($row->tagihan, 0, ".", ","),
              'pph' => number_format($row->pph, 0, ".", ","),
              'materai' => number_format($row->materai, 0, ".", ","),
              'uang_muka' => number_format($row->uang_muka, 0, ".", ","),
              'diterima' => number_format($row->diterima, 0, ".", ","),
              'tot_estimasi' => number_format($row->tot_estimasi, 0, ".", ","),
              'biaya_real' => number_format($row->biaya_real, 0, ".", ","),
            ];
          }

          // Baris subtotal per voucher
          $data[] = [
            'row_type' => 'subtotal',
            'no' => '',
            'kode_voucher' => 'Sub Total ' . $kodeVoucher,
            'tgl_lunas' => '',
            'kode_lunas_kwitansi' => '',
            'kode_spk' => '',
            'kode_kwitansi' => '',
            'kode_estimasi' => '',
            'no_polisi' => '',
            'header_label' => '',
            'jasa' => number_format($subTotal['jasa'], 0, ".", ","),
            'bahan' => number_format($subTotal['bahan'], 0, ".", ","),
            'total_sparepart_s' => number_format($subTotal['total_sparepart_s'], 0, ".", ","),
            'ppn' => number_format($subTotal['ppn'], 0, ".", ","),
            'total_lain_s' => number_format($subTotal['total_lain_s'], 0, ".", ","),
            'total_or_ass' => number_format($subTotal['total_or_ass'], 0, ".", ","),
            'tagihan' => number_format($subTotal['tagihan'], 0, ".", ","),
            'pph' => number_format($subTotal['pph'], 0, ".", ","),
            'materai' => number_format($subTotal['materai'], 0, ".", ","),
            'uang_muka' => number_format($subTotal['uang_muka'], 0, ".", ","),
            'diterima' => number_format($subTotal['diterima'], 0, ".", ","),
            'tot_estimasi' => number_format($subTotal['tot_estimasi'], 0, ".", ","),
            'biaya_real' => number_format($subTotal['biaya_real'], 0, ".", ","),
          ];

          // Baris info tambahan: Tanggal Lunas / Pembayaran / Memo (sama seperti print PDF)
          $lastRow = $rows->last();
          $data[] = [
            'row_type' => 'info',
            'no' => '',
            'kode_voucher' => '',
            'tgl_lunas' => '',
            'kode_lunas_kwitansi' => '',
            'kode_spk' => '',
            'kode_kwitansi' => '',
            'kode_estimasi' => '',
            'no_polisi' => '',
            'header_label' => '',
            'info_label' =>
              'Tanggal Lunas&nbsp;&nbsp;' . (blank($lastRow->tgl_lunas) ? '' : date("d-M-Y", strtotime($lastRow->tgl_lunas))) . '<br>' .
              'Pembayaran&nbsp;&nbsp;&nbsp;&nbsp;' . $lastRow->pembayaran . '<br>' .
              $lastRow->memo,
            'jasa' => '',
            'bahan' => '',
            'total_sparepart_s' => '',
            'ppn' => '',
            'total_lain_s' => '',
            'total_or_ass' => '',
            'tagihan' => '',
            'pph' => '',
            'materai' => '',
            'uang_muka' => '',
            'diterima' => '',
            'tot_estimasi' => '',
            'biaya_real' => '',
          ];
        }

        // Grand Total keseluruhan di baris terakhir
        if ($datas->count() > 0) {
          $data[] = [
            'row_type' => 'grandtotal',
            'no' => '',
            'kode_voucher' => 'Grand Total',
            'tgl_lunas' => '',
            'kode_lunas_kwitansi' => '',
            'kode_spk' => '',
            'kode_kwitansi' => '',
            'kode_estimasi' => '',
            'no_polisi' => '',
            'header_label' => '',
            'jasa' => number_format($grandTotalAll['jasa'], 0, ".", ","),
            'bahan' => number_format($grandTotalAll['bahan'], 0, ".", ","),
            'total_sparepart_s' => number_format($grandTotalAll['total_sparepart_s'], 0, ".", ","),
            'ppn' => number_format($grandTotalAll['ppn'], 0, ".", ","),
            'total_lain_s' => number_format($grandTotalAll['total_lain_s'], 0, ".", ","),
            'total_or_ass' => number_format($grandTotalAll['total_or_ass'], 0, ".", ","),
            'tagihan' => number_format($grandTotalAll['tagihan'], 0, ".", ","),
            'pph' => number_format($grandTotalAll['pph'], 0, ".", ","),
            'materai' => number_format($grandTotalAll['materai'], 0, ".", ","),
            'uang_muka' => number_format($grandTotalAll['uang_muka'], 0, ".", ","),
            'diterima' => number_format($grandTotalAll['diterima'], 0, ".", ","),
            'tot_estimasi' => number_format($grandTotalAll['tot_estimasi'], 0, ".", ","),
            'biaya_real' => number_format($grandTotalAll['biaya_real'], 0, ".", ","),
          ];
        }
      } elseif ($request->jenis_laporan == "rekap") {
        // Base query
        $base = DB::table('v_rekap_kwitansi_lunas as k')
          ->whereBetween('k.tanggal', [$startDate, $endDate])
          ->where('k.kode_cabang', $user_cabang);

        // Filtering (search global)
        $query = (clone $base);

        // Total baris tanpa filter
        $totalData = (clone $query)->count();

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count();

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'k.kode_pembayaran',
            'k.nama_pelanggan',
            'k.nama_cabang',
            DB::raw('SUM(unit) AS unit'),
            DB::raw('SUM(pph) AS pph'),
            DB::raw('SUM(materai) AS materai'),
            DB::raw('SUM(tagihan) AS tagihan'),
            // Perhitungan penjumlahan di dalam SUM
            // DB::raw('SUM(diterima) + SUM(uang_muka) AS diterima'),
            DB::raw('(SUM(tagihan) - SUM(uang_muka) - SUM(pph) - SUM(materai)) AS diterima'),
            DB::raw('SUM(tot_estimasi) AS tot_estimasi'),
            DB::raw('SUM(perbaikan) AS perbaikan'),
            DB::raw('SUM(sparepart) AS sparepart'),
            DB::raw('SUM(lain) AS lain'),
            DB::raw('SUM(or_asuransi) AS or_asuransi'),
            // Logika CASE WHEN
            DB::raw("SUM(CASE WHEN kode_pembayaran = '00002' THEN (diterima - uang_muka) ELSE 0 END) AS tunai"),
            DB::raw("SUM(CASE WHEN kode_pembayaran IN ('00001', '00003') THEN (diterima - uang_muka) ELSE 0 END) AS bank"),
            DB::raw("SUM(CASE WHEN kode_pembayaran = '00005' THEN (diterima - uang_muka) ELSE 0 END) AS free"),
            DB::raw('SUM(uang_muka) AS uang_muka')
          ])
          ->orderBy('k.nama_pelanggan', 'asc')
          ->groupBy(
            'nama_pelanggan',
            'kode_pembayaran',
            'nama_cabang'
          )
          ->get();

        // // Susun payload DataTables
        // $data = [];
        // $fake = 0; //$start;
        // foreach ($datas as $row) {
        //   $data[] = [
        //     'no' => ++$fake,
        //     'kode_pembayaran' => $row->kode_pembayaran,
        //     'nama_pelanggan' => $row->nama_pelanggan,
        //     'nama_cabang' => $row->nama_cabang,
        //     'unit' => number_format($row->unit, 0, ".", ","),
        //     'pph' => number_format($row->pph, 0, ".", ","),
        //     'materai' => number_format($row->materai, 0, ".", ","),
        //     'tagihan' => number_format($row->tagihan, 0, ".", ","),
        //     'diterima' => number_format($row->diterima, 0, ".", ","),
        //     'tot_estimasi' => number_format($row->tot_estimasi, 0, ".", ","),
        //     'perbaikan' => number_format($row->perbaikan, 0, ".", ","),
        //     'sparepart' => number_format($row->sparepart, 0, ".", ","),
        //     'lain' => number_format($row->lain, 0, ".", ","),
        //     'or_asuransi' => number_format($row->or_asuransi, 0, ".", ","),
        //     'tunai' => number_format($row->tunai, 0, ".", ","),
        //     'bank' => number_format($row->bank, 0, ".", ","),
        //     'free' => number_format($row->free, 0, ".", ","),
        //     'uang_muka' => number_format($row->uang_muka, 0, ".", ","),
        //   ];
        // }
        // Susun payload DataTables + Grand Total
        $data = [];
        $fake = 0;
        $grandTotal = [
          'unit' => 0,
          'pph' => 0,
          'materai' => 0,
          'tagihan' => 0,
          'diterima' => 0,
          'tot_estimasi' => 0,
          'perbaikan' => 0,
          'sparepart' => 0,
          'lain' => 0,
          'or_asuransi' => 0,
          'tunai' => 0,
          'bank' => 0,
          'free' => 0,
          'uang_muka' => 0,
        ];

        foreach ($datas as $row) {
          foreach ($grandTotal as $key => $val) {
            $grandTotal[$key] += $row->$key;
          }

          $data[] = [
            'no' => ++$fake,
            'kode_pembayaran' => $row->kode_pembayaran,
            'nama_pelanggan' => $row->nama_pelanggan,
            'nama_cabang' => $row->nama_cabang,
            'unit' => number_format($row->unit, 0, ".", ","),
            'pph' => number_format($row->pph, 0, ".", ","),
            'materai' => number_format($row->materai, 0, ".", ","),
            'tagihan' => number_format($row->tagihan, 0, ".", ","),
            'diterima' => number_format($row->diterima, 0, ".", ","),
            'tot_estimasi' => number_format($row->tot_estimasi, 0, ".", ","),
            'perbaikan' => number_format($row->perbaikan, 0, ".", ","),
            'sparepart' => number_format($row->sparepart, 0, ".", ","),
            'lain' => number_format($row->lain, 0, ".", ","),
            'or_asuransi' => number_format($row->or_asuransi, 0, ".", ","),
            'tunai' => number_format($row->tunai, 0, ".", ","),
            'bank' => number_format($row->bank, 0, ".", ","),
            'free' => number_format($row->free, 0, ".", ","),
            'uang_muka' => number_format($row->uang_muka, 0, ".", ","),
          ];
        }

        if (count($datas) > 0) {
          $data[] = [
            'no' => '',
            'kode_pembayaran' => '',
            'nama_pelanggan' => 'Grand Total',
            'nama_cabang' => '',
            'unit' => number_format($grandTotal['unit'], 0, ".", ","),
            'pph' => number_format($grandTotal['pph'], 0, ".", ","),
            'materai' => number_format($grandTotal['materai'], 0, ".", ","),
            'tagihan' => number_format($grandTotal['tagihan'], 0, ".", ","),
            'diterima' => number_format($grandTotal['diterima'], 0, ".", ","),
            'tot_estimasi' => number_format($grandTotal['tot_estimasi'], 0, ".", ","),
            'perbaikan' => number_format($grandTotal['perbaikan'], 0, ".", ","),
            'sparepart' => number_format($grandTotal['sparepart'], 0, ".", ","),
            'lain' => number_format($grandTotal['lain'], 0, ".", ","),
            'or_asuransi' => number_format($grandTotal['or_asuransi'], 0, ".", ","),
            'tunai' => number_format($grandTotal['tunai'], 0, ".", ","),
            'bank' => number_format($grandTotal['bank'], 0, ".", ","),
            'free' => number_format($grandTotal['free'], 0, ".", ","),
            'uang_muka' => number_format($grandTotal['uang_muka'], 0, ".", ","),
          ];
        }
      } elseif ($request->jenis_laporan == "rinci") {
        // Base query
        $base = DB::table('v_rep_kwitansi_lunas as k')
          ->whereBetween('k.tgl_lunas', [$startDate, $endDate])
          ->where('k.kode_cabang', $user_cabang);

        // Filtering (search global)
        $query = (clone $base);

        // if ($request->filled('tgl_awal')) {
        //   $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
        //   $query->whereDate('k.tgl_lunas', '>=', $startDate);
        // }
        // if ($request->filled('tgl_akhir')) {
        //   $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_akhir, 'Asia/Jakarta')->format('Y-m-d');
        //   $query->whereDate('k.tgl_lunas', '<=', $endDate);
        // }

        // Total baris tanpa filter
        $totalData = (clone $query)->count();

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count();

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'k.kode_voucher',
            'k.tgl_lunas',
            'k.kode_lunas_kwitansi',
            'k.kode_spk',
            'k.kode_kwitansi',
            'k.kode_estimasi',
            'k.no_polisi',
            'k.merek_tipe',
            'k.nama_pelanggan',
            DB::raw("CASE WHEN kode_pembayaran = '00002' THEN (diterima - uang_muka) ELSE 0 END AS tunai"),
            DB::raw("CASE WHEN kode_pembayaran IN ('00001', '00003') THEN (diterima - uang_muka) ELSE 0 END AS bank"),
            DB::raw("CASE WHEN kode_pembayaran = '00005' THEN (diterima - uang_muka) ELSE 0 END AS free"),
            'k.uang_muka',
            'k.pph',
            'k.materai',
            'k.tagihan',
            'k.diterima',
            'k.tot_estimasi',
            DB::raw("(nilai_jasa + nilai_sparepart + nilai_lain + nilai_upah_borongan + nilai_komisi + nilai_or_free) AS biaya"),
          ])
          ->orderBy('k.tgl_lunas', 'asc')
          ->get();

        //   // Susun payload DataTables
        //   $data = [];
        //   $fake = 0; //$start;
        //   foreach ($datas as $row) {
        //     $data[] = [
        //       'no' => ++$fake,
        //       'tgl_lunas' => blank($row->tgl_lunas) ? '' : date("d/m/Y", strtotime($row->tgl_lunas)),
        //       'kode_voucher' => $row->kode_voucher,
        //       'kode_lunas_kwitansi' => $row->kode_lunas_kwitansi,
        //       'kode_spk' => $row->kode_spk,
        //       'kode_kwitansi' => $row->kode_kwitansi,
        //       'kode_estimasi' => $row->kode_estimasi,
        //       'no_polisi' => $row->no_polisi,
        //       'merek_tipe' => $row->merek_tipe,
        //       'nama_pelanggan' => $row->nama_pelanggan,
        //       'tunai' => number_format($row->tunai, 0, ".", ","),
        //       'bank' => number_format($row->bank, 0, ".", ","),
        //       'free' => number_format($row->free, 0, ".", ","),
        //       'uang_muka' => number_format($row->uang_muka, 0, ".", ","),
        //       'pph' => number_format($row->pph, 0, ".", ","),
        //       'materai' => number_format($row->materai, 0, ".", ","),
        //       'tagihan' => number_format($row->tagihan, 0, ".", ","),
        //       'diterima' => number_format($row->diterima, 0, ".", ","),
        //       'tot_estimasi' => number_format($row->tot_estimasi, 0, ".", ","),
        //       'biaya' => number_format($row->biaya, 0, ".", ","),
        //     ];
        //   }
        // }
        // Susun payload DataTables + Grand Total
        $data = [];
        $fake = 0;
        $grandTotal = [
          'tunai' => 0,
          'bank' => 0,
          'free' => 0,
          'uang_muka' => 0,
          'pph' => 0,
          'materai' => 0,
          'tagihan' => 0,
          'diterima' => 0,
          'tot_estimasi' => 0,
          'biaya' => 0,
        ];

        foreach ($datas as $row) {
          foreach ($grandTotal as $key => $val) {
            $grandTotal[$key] += $row->$key;
          }

          $data[] = [
            'no' => ++$fake,
            'tgl_lunas' => blank($row->tgl_lunas) ? '' : date("d/m/Y", strtotime($row->tgl_lunas)),
            'kode_voucher' => $row->kode_voucher,
            'kode_lunas_kwitansi' => $row->kode_lunas_kwitansi,
            'kode_spk' => $row->kode_spk,
            'kode_kwitansi' => $row->kode_kwitansi,
            'kode_estimasi' => $row->kode_estimasi,
            'no_polisi' => $row->no_polisi,
            'merek_tipe' => $row->merek_tipe,
            'nama_pelanggan' => $row->nama_pelanggan,
            'tunai' => number_format($row->tunai, 0, ".", ","),
            'bank' => number_format($row->bank, 0, ".", ","),
            'free' => number_format($row->free, 0, ".", ","),
            'uang_muka' => number_format($row->uang_muka, 0, ".", ","),
            'pph' => number_format($row->pph, 0, ".", ","),
            'materai' => number_format($row->materai, 0, ".", ","),
            'tagihan' => number_format($row->tagihan, 0, ".", ","),
            'diterima' => number_format($row->diterima, 0, ".", ","),
            'tot_estimasi' => number_format($row->tot_estimasi, 0, ".", ","),
            'biaya' => number_format($row->biaya, 0, ".", ","),
          ];
        }

        if (count($datas) > 0) {
          $data[] = [
            'no' => '',
            'tgl_lunas' => '',
            'kode_voucher' => 'Grand Total',
            'kode_lunas_kwitansi' => '',
            'kode_spk' => '',
            'kode_kwitansi' => '',
            'kode_estimasi' => '',
            'no_polisi' => '',
            'merek_tipe' => '',
            'nama_pelanggan' => '',
            'tunai' => number_format($grandTotal['tunai'], 0, ".", ","),
            'bank' => number_format($grandTotal['bank'], 0, ".", ","),
            'free' => number_format($grandTotal['free'], 0, ".", ","),
            'uang_muka' => number_format($grandTotal['uang_muka'], 0, ".", ","),
            'pph' => number_format($grandTotal['pph'], 0, ".", ","),
            'materai' => number_format($grandTotal['materai'], 0, ".", ","),
            'tagihan' => number_format($grandTotal['tagihan'], 0, ".", ","),
            'diterima' => number_format($grandTotal['diterima'], 0, ".", ","),
            'tot_estimasi' => number_format($grandTotal['tot_estimasi'], 0, ".", ","),
            'biaya' => number_format($grandTotal['biaya'], 0, ".", ","),
          ];
        }
      }

    }

    // ✅ Always return full DataTables structure, even if no results
    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => intval($totalData),
      'recordsFiltered' => intval($totalFiltered),
      'data' => $data,
    ]);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $validatedData = $request->validate(
      [
        'jenis_laporan' => 'required'
      ],
      [ // custom messages
        'jenis_laporan.required' => 'Jenis Laporan wajib diisi.',
      ]
    );

    $dataArray['jenis_laporan'] = $request->jenis_laporan;

    // if ($dataArray['jenis_laporan'] == "periode") {
    //   // $dataArray['tgl_awal'] = blank($request->tgl_awal) ? date("d/m/Y") : Carbon::createFromFormat('d/m/Y', $request->tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
    //   $dataArray['tgl_awal'] = $request->tgl_awal;
    //   $dataArray['tgl_akhir'] = $request->tgl_akhir;
    //   $dataArray['bulan'] = date("m");
    //   $dataArray['tahun2'] = date("Y");
    //   $dataArray['tahun'] = date("Y");
    // } elseif ($dataArray['jenis_laporan'] == "bulan") {
    //   $dataArray['bulan'] = $request->bulan;
    //   $dataArray['tahun2'] = $request->tahun2;
    //   $dataArray['tgl_awal'] = date("d/m/Y");
    //   $dataArray['tgl_akhir'] = date("d/m/Y");
    //   $dataArray['tahun'] = date("Y");
    // } elseif ($dataArray['jenis_laporan'] == "tahun") {
    //   $dataArray['tahun'] = $request->tahun;
    //   $dataArray['tgl_awal'] = date("d/m/Y");
    //   $dataArray['tgl_akhir'] = date("d/m/Y");
    //   $dataArray['bulan'] = date("m");
    //   $dataArray['tahun2'] = date("Y");
    // }

    $dataArray['tgl_awal'] = $request->tgl_awal;
    $dataArray['tgl_akhir'] = $request->tgl_akhir;
    $dataArray['no_voucher'] = $request->no_voucher;

    ## Log Activity
    $desc = "View Laporan Kwitansi Lunas";
    LogActivity::saveLogActivity($desc, $dataArray);

    return redirect('keuangan/laporan-kwitansi-lunas')->with('datafilter', $dataArray);
  }

  /**
   * Export data to Excel.
   */
  public function exportExcel(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    // --- TAMBAHAN: Ambil Nama Cabang untuk Header Excel ---
    // Asumsi ada helper atau query untuk ambil nama cabang.
    // Jika tidak ada tabel cabang, ganti dengan hardcode string atau session nama cabang.
    // $cabangInfo = DB::table('cabang')->where('kode_cabang', $user_cabang)->first();
    // $namaCabang = $cabangInfo ? $cabangInfo->nama_cabang : $user_cabang;

    // Siapkan struktur data cabang
    $cabangData = [
      'kode' => $user_cabang,
      'nama' => $namaCabang
    ];
    // -----------------------------------------------------

    $filters = $request->all();

    // --- TAMBAHAN: Format String Periode ---
    $tglAwal = $request->input('tgl_awal', date('d/m/Y'));
    $tglAkhir = $request->input('tgl_akhir', date('d/m/Y'));
    $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;
    // ---------------------------------------

    if ($request->jenis_laporan == "voucher") {
      $fileName = 'Laporan_KwitansiLunas_Voucher_' . date('Ymd_His') . '.xlsx';
    } elseif ($request->jenis_laporan == "rekap") {
      $fileName = 'Laporan_KwitansiLunas_Rekap_' . date('Ymd_His') . '.xlsx';
    } elseif ($request->jenis_laporan == "rinci") {
      $fileName = 'Laporan_KwitansiLunas_Rinci_' . date('Ymd_His') . '.xlsx';
    }

    ## Log Activity
    $desc = "Export Laporan Kwitansi Lunas";
    LogActivity::saveLogActivity($desc, $filters);

    // Masukkan $periodeStr dan $cabangData ke dalam use App\Helpers\Helpers as Helper;

class Export
    return Excel::download(new LaporanKwitansiLunasExport($filters, $cabangData, $periodeStr), $fileName);
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $filters = $request->all();

    // --- TAMBAHAN: Format String Periode ---
    $tglAwal = $filters['tgl_awal']; //date('d/m/Y', strtotime($filters['tgl_awal']));
    $tglAkhir = $filters['tgl_akhir']; //date('d/m/Y', strtotime($filters['tgl_akhir']));
    $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;
    // ---------------------------------------

    // --- DATA ---
    $datas = [];
    if ($filters['jenis_laporan'] == "voucher") {
      $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
      $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');

      $query = DB::table('v_rep_kwitansi_lunas as k')
        ->where('k.kode_cabang', $user_cabang)
        ->whereBetween('k.tgl_lunas', [$startDate, $endDate])
        ->select([
          'k.kode_voucher',
          'k.tgl_lunas',
          'k.kode_lunas_kwitansi',
          'k.kode_spk',
          'k.kode_kwitansi',
          'k.kode_estimasi',
          'k.no_polisi',
          'k.nama_pelanggan',   // <-- WAJIB, dipakai untuk groupBy
          'k.pembayaran',   // <-- tambahkan, ini jenis bayar (BANK/TUNAI)
          'k.memo',         // <-- tambahkan, ini "Keterangan"
          'k.jasa',
          'k.bahan',
          'k.total_sparepart_s',
          'k.ppn',
          'k.total_lain_s',
          'k.total_or_ass',
          'k.tagihan',
          'k.pph',
          'k.materai',
          'k.uang_muka',
          'k.diterima',
          'k.tot_estimasi',
          'k.biaya_real'
        ])
        // ->orderBy('k.tgl_lunas', 'asc');
        ->orderBy('k.nama_pelanggan', 'asc')
        ->orderBy('k.kode_voucher', 'asc');

        if (strlen($filters['no_voucher'])) {
          $query->where('k.kode_voucher', $filters['no_voucher']);
        }

      // $datas = $query->get()->groupBy('nama_pelanggan');  // <-- ganti dari kode_voucher
      $datas = $query->get()->groupBy('kode_voucher');
      // $datas = $query->get();

      $title = 'Laporan Kwitansi Lunas Voucher';
      // $pages = 'content.keuangan.laporan.laporan-kwitansi-lunas-print';

    } elseif ($filters['jenis_laporan'] == "rekap") {
      $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
      $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');

      $query = DB::table('v_rekap_kwitansi_lunas as k')
        ->whereBetween('k.tanggal', [$startDate, $endDate])
        ->where('k.kode_cabang', $user_cabang)
        ->select([
          'k.kode_pembayaran',
          'k.nama_pelanggan',
          'k.nama_cabang',
          DB::raw('SUM(unit) AS unit'),
          DB::raw('SUM(pph) AS pph'),
          DB::raw('SUM(materai) AS materai'),
          DB::raw('SUM(tagihan) AS tagihan'),
          // Perhitungan penjumlahan di dalam SUM
          // DB::raw('SUM(diterima) + SUM(uang_muka) AS diterima'),
          DB::raw('(SUM(tagihan) - SUM(uang_muka) - SUM(pph) - SUM(materai)) AS diterima'),
          DB::raw('SUM(tot_estimasi) AS tot_estimasi'),
          DB::raw('SUM(perbaikan) AS perbaikan'),
          DB::raw('SUM(sparepart) AS sparepart'),
          DB::raw('SUM(lain) AS lain'),
          DB::raw('SUM(or_asuransi) AS or_asuransi'),
          // Logika CASE WHEN
          DB::raw("SUM(CASE WHEN kode_pembayaran = '00002' THEN (diterima - uang_muka) ELSE 0 END) AS tunai"),
          DB::raw("SUM(CASE WHEN kode_pembayaran IN ('00001', '00003') THEN (diterima - uang_muka) ELSE 0 END) AS bank"),
          DB::raw("SUM(CASE WHEN kode_pembayaran = '00005' THEN (diterima - uang_muka) ELSE 0 END) AS free"),
          DB::raw('SUM(uang_muka) AS uang_muka')
        ])
        ->orderBy('k.nama_pelanggan', 'asc')
        ->groupBy(
          'nama_pelanggan',
          'kode_pembayaran',
          'nama_cabang'
        );

      $datas = $query->get();

      $title = 'Laporan Kwitansi Lunas Rekap';

      // $pages = 'content.keuangan.laporan.laporan-outstanding-tahun-print';
    } elseif ($filters['jenis_laporan'] == "rinci") {
      $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
      $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');

      $query = DB::table('v_rep_kwitansi_lunas as k')
        ->whereBetween('k.tgl_lunas', [$startDate, $endDate])
        ->where('k.kode_cabang', $user_cabang)
        ->select([
          'k.kode_voucher',
          'k.tgl_lunas',
          'k.kode_lunas_kwitansi',
          'k.kode_spk',
          'k.kode_kwitansi',
          'k.kode_estimasi',
          'k.no_polisi',
          'k.merek_tipe',
          'k.nama_pelanggan',
          DB::raw("CASE WHEN kode_pembayaran = '00002' THEN (diterima - uang_muka) ELSE 0 END AS tunai"),
          DB::raw("CASE WHEN kode_pembayaran IN ('00001', '00003') THEN (diterima - uang_muka) ELSE 0 END AS bank"),
          DB::raw("CASE WHEN kode_pembayaran = '00005' THEN (diterima - uang_muka) ELSE 0 END AS free"),
          'k.uang_muka',
          'k.pph',
          'k.materai',
          'k.tagihan',
          'k.diterima',
          'k.tot_estimasi',
          DB::raw("(nilai_jasa + nilai_sparepart + nilai_lain + nilai_upah_borongan + nilai_komisi + nilai_or_free) AS biaya"),
        ])
        // ->orderBy('k.tgl_lunas', 'asc');
        ->orderBy('k.kode_voucher', 'asc')
        ->orderBy('k.tgl_lunas', 'asc');
      $datas = $query->get();

      $title = 'Laporan Kwitansi Lunas Rinci';
    }

    ## Log Activity
    $desc = "Print Laporan Kwitansi Lunas";
    LogActivity::saveLogActivity($desc, $filters);

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.keuangan.laporan.laporan-kwitansi-lunas-print', [
      'title' => $title,
      'namaCabang' => $namaCabang,
      'periodeStr' => $periodeStr,
      'no' => 1,
      'datas' => $datas,
      'datafilter' => $filters,
      'pageConfigs' => $pageConfigs,
    ]);
  }

}
