<?php

namespace App\Http\Controllers\administrasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanInvoiceTerbitExport;

use App\Helpers\Helpers as Helper;

class LaporanInvoiceTerbitController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanInvoiceTerbit(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Laporan Invoice Terbit';
    $user_cabang = session('kd_cabang');

    // $datafilter = session('datafilter_invoice_terbit');
    // if (empty($datafilter)) {
    //   $datafilter['jenis_report'] = 'Rekap';
    //   $datafilter['tahun'] = date("Y");
    //   $datafilter['bulan'] = date("m");
    // }

    // // Generate years untuk dropdown (10 tahun terakhir)
    // $currentYear = date('Y');
    // $years = [];
    // for ($i = 0; $i < 10; $i++) {
    //   $year = $currentYear - $i;
    //   $years[$year] = $year;
    // }

    // // Data bulan
    // $months = [
    //   '01' => 'Januari',
    //   '02' => 'Februari',
    //   '03' => 'Maret',
    //   '04' => 'April',
    //   '05' => 'Mei',
    //   '06' => 'Juni',
    //   '07' => 'Juli',
    //   '08' => 'Agustus',
    //   '09' => 'September',
    //   '10' => 'Oktober',
    //   '11' => 'November',
    //   '12' => 'Desember'
    // ];

    // return view('content.administrasi.laporan.laporan-invoice-terbit', [
    //   'title' => $title,
    //   'isList' => $isList,
    //   'isAdd' => $isAdd,
    //   'isEdit' => $isEdit,
    //   'isDel' => $isDel,
    //   'user_cabang' => $user_cabang,
    //   'datafilter' => $datafilter,
    //   'years' => $years,
    //   'months' => $months,
    // ]);
    $datafilter = session('datafilter_invoice_terbit');
    if (empty($datafilter)) {
      $datafilter['jenis_report'] = 'Rekap';
      $datafilter['tgl_awal'] = date("d/m/Y");
      $datafilter['tgl_akhir'] = date("d/m/Y");
    }

    return view('content.administrasi.laporan.laporan-invoice-terbit', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
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
    try {
      $user_cabang = session('kd_cabang');

      // $jenis_report = $request->input('jenis_report') ?: 'Rekap';
      // $tahun = $request->input('tahun') ?: date('Y');
      // $bulan = $request->input('bulan') ?: date('m');

      // $query = DB::table('v_rpt_invoice_terbit')
      //   ->where('kode_cabang', $user_cabang)
      //   ->whereYear('tgl_invoice', $tahun)
      //   ->whereMonth('tgl_invoice', $bulan);
      $jenis_report = $request->input('jenis_report') ?: 'Rekap';
      $tglAwal = $request->input('tgl_awal') ?: date('d/m/Y');
      $tglAkhir = $request->input('tgl_akhir') ?: date('d/m/Y');

      $startDate = Carbon::createFromFormat('d/m/Y', $tglAwal)->startOfDay();
      $endDate = Carbon::createFromFormat('d/m/Y', $tglAkhir)->endOfDay();

      $query = DB::table('v_rpt_invoice_terbit')
        ->where('kode_cabang', $user_cabang)
        ->whereBetween('tgl_invoice', [$startDate, $endDate]);

      // if ($jenis_report === 'Rekap') {
      //   $datas = (clone $query)
      //     ->select([
      //       'jenis_pelanggan',
      //       'nama_pelanggan',
      //       DB::raw('COUNT(id) as jumlah_invoice'),
      //       DB::raw('SUM(total_or) as total_or'),
      //       DB::raw('SUM(free) as free'),
      //     ])
      //     ->groupBy('jenis_pelanggan', 'nama_pelanggan')
      //     ->orderBy('nama_pelanggan', 'asc')
      //     ->get();
      // } else {
      //   $datas = (clone $query)
      //     ->select([
      //       'no_invoice',
      //       'tgl_invoice',
      //       'kode_spk',
      //       'no_polisi',
      //       'merek_tipe',
      //       'jenis_pelanggan',
      //       'nama_pelanggan',
      //       'tertanggung',
      //       'pemilik',
      //       'jenis_identitas',
      //       'no_identitas',
      //       'total_or',
      //       'free',
      //     ])
      //     ->orderBy('tgl_invoice', 'asc')
      //     ->get();
      // }
      if ($jenis_report === 'Rekap') {
        $datas = (clone $query)
          ->select([
            'nama_pelanggan',
            DB::raw('COUNT(id) as unit'),
            DB::raw('SUM(jasa) as jasa'),
            DB::raw('SUM(bahan) as bahan'),
            DB::raw('SUM(sparepart) as sparepart'),
            DB::raw('SUM(ppn) as ppn'),
            DB::raw('SUM(total_lain) as total_lain'),
            DB::raw('SUM(total_invoice) as total_invoice'),
            DB::raw('SUM(total_or) as total_or'),
            DB::raw('SUM(tagihan) as tagihan'),
          ])
          ->groupBy('nama_pelanggan')
          ->orderBy('nama_pelanggan', 'asc')
          ->get();
      } else {
        $datas = (clone $query)
          ->select([
            'no_invoice',
            'kode_spk',
            'no_polisi',
            'nama_pelanggan',
            'jenis_identitas',
            'no_identitas',
            'jasa',
            'bahan',
            'sparepart',
            'ppn',
            'total_lain',
            'total_invoice',
            'total_or',
            'tagihan',
          ])
          ->orderBy('nama_pelanggan', 'asc')
          ->orderBy('no_invoice', 'asc')
          ->get();
      }

      $totalRecords = $datas->count();

      // Hitung Grand Total dari SELURUH data (bukan hanya yang tampil di halaman)
      // $totalOr = 0;
      // $totalFree = 0;
      // $totalInvoice = 0;

      // foreach ($datas as $row) {
      //   $totalOr += $row->total_or;
      //   $totalFree += $row->free;
      //   if ($jenis_report === 'Rekap') {
      //     $totalInvoice += $row->jumlah_invoice;
      //   }
      // }
      $totalUnit = 0;
      $totalJasa = 0;
      $totalBahan = 0;
      $totalSparepart = 0;
      $totalPpn = 0;
      $totalLain = 0;
      $totalInvoice = 0;
      $totalOr = 0;
      $totalTagihan = 0;

      foreach ($datas as $row) {
        $totalJasa += $row->jasa;
        $totalBahan += $row->bahan;
        $totalSparepart += $row->sparepart;
        $totalPpn += $row->ppn;
        $totalLain += $row->total_lain;
        $totalInvoice += $row->total_invoice;
        $totalOr += $row->total_or;
        $totalTagihan += $row->tagihan;
        if ($jenis_report === 'Rekap') {
          $totalUnit += $row->unit;
        }
      }

      // --- Paginasi ---
      $start = (int) $request->input('start', 0);
      $length = (int) $request->input('length', 10);

      $pagedDatas = $length > 0
        ? $datas->slice($start, $length)
        : $datas;

      $data = [];
      $no = $start;

      foreach ($pagedDatas as $row) {
        $no++;
        if ($jenis_report === 'Rekap') {
          // $data[] = [
          //   'no' => $no,
          //   'jenis_pelanggan' => $row->jenis_pelanggan,
          //   'nama_pelanggan' => $row->nama_pelanggan,
          //   'jumlah_invoice' => number_format($row->jumlah_invoice, 0, '.', '.'),
          //   'total_or' => number_format($row->total_or, 0, '.', '.'),
          //   'free' => number_format($row->free, 0, '.', '.'),
          //   'is_total' => false
          // ];
          $data[] = [
            'no' => $no,
            'nama_pelanggan' => $row->nama_pelanggan,
            'unit' => number_format($row->unit, 0, '.', '.'),
            'jasa' => number_format($row->jasa, 0, '.', '.'),
            'bahan' => number_format($row->bahan, 0, '.', '.'),
            'sparepart' => number_format($row->sparepart, 0, '.', '.'),
            'ppn' => number_format($row->ppn, 0, '.', '.'),
            'total_lain' => number_format($row->total_lain, 0, '.', '.'),
            'total_invoice' => number_format($row->total_invoice, 0, '.', '.'),
            'total_or' => number_format($row->total_or, 0, '.', '.'),
            'tagihan' => number_format($row->tagihan, 0, '.', '.'),
            'is_total' => false
          ];
        } else {
          // $data[] = [
          //   'no' => $no,
          //   'no_invoice' => $row->no_invoice,
          //   'tgl_invoice' => $row->tgl_invoice ? Carbon::parse($row->tgl_invoice)->format('d/m/Y') : '',
          //   'kode_spk' => $row->kode_spk,
          //   'no_polisi' => $row->no_polisi,
          //   'merek_tipe' => $row->merek_tipe,
          //   'jenis_pelanggan' => $row->jenis_pelanggan,
          //   'nama_pelanggan' => $row->nama_pelanggan,
          //   'tertanggung' => $row->tertanggung,
          //   'pemilik' => $row->pemilik,
          //   'jenis_identitas' => $this->resolveJenisIdentitas($row->jenis_pelanggan),
          //   'no_identitas' => $row->no_identitas ?? '-',
          //   'total_or' => number_format($row->total_or, 0, '.', '.'),
          //   'free' => number_format($row->free, 0, '.', '.'),
          //   'is_total' => false
          // ];
          $data[] = [
            'no' => $no,
            'no_invoice' => $row->no_invoice,
            'kode_spk' => $row->kode_spk,
            'no_polisi' => $row->no_polisi,
            'nama_pelanggan' => $row->nama_pelanggan,
            'npwp_ktp' => $row->no_identitas,
            'jasa' => number_format($row->jasa, 0, '.', '.'),
            'bahan' => number_format($row->bahan, 0, '.', '.'),
            'sparepart' => number_format($row->sparepart, 0, '.', '.'),
            'ppn' => number_format($row->ppn, 0, '.', '.'),
            'total_lain' => number_format($row->total_lain, 0, '.', '.'),
            'total_invoice' => number_format($row->total_invoice, 0, '.', '.'),
            'total_or' => number_format($row->total_or, 0, '.', '.'),
            'tagihan' => number_format($row->tagihan, 0, '.', '.'),
            'is_total' => false
          ];
        }
      }

      // Grand Total hanya di halaman terakhir
      $isLastPage = $length <= 0 || ($start + $length) >= $totalRecords;

      // if ($isLastPage && $totalRecords > 0) {
      //   if ($jenis_report === 'Rekap') {
      //     $data[] = [
      //       'no' => '',
      //       'jenis_pelanggan' => '',
      //       'nama_pelanggan' => 'Grand Total',
      //       'jumlah_invoice' => number_format($totalInvoice, 0, '.', '.'),
      //       'total_or' => number_format($totalOr, 0, '.', '.'),
      //       'free' => number_format($totalFree, 0, '.', '.'),
      //       'is_total' => true
      //     ];
      //   } else {
      //     $data[] = [
      //       'no' => '',
      //       'no_invoice' => '',
      //       'tgl_invoice' => '',
      //       'kode_spk' => '',
      //       'no_polisi' => '',
      //       'merek_tipe' => 'Grand Total',
      //       'jenis_pelanggan' => '',
      //       'nama_pelanggan' => '',
      //       'tertanggung' => '',
      //       'pemilik' => '',
      //       'jenis_identitas' => '',
      //       'no_identitas' => '',
      //       'total_or' => number_format($totalOr, 0, '.', '.'),
      //       'free' => number_format($totalFree, 0, '.', '.'),
      //       'is_total' => true
      //     ];
      //   }
      // }
      if ($isLastPage && $totalRecords > 0) {
        if ($jenis_report === 'Rekap') {
          $data[] = [
            'no' => '',
            'nama_pelanggan' => 'Grand Total',
            'unit' => number_format($totalUnit, 0, '.', '.'),
            'jasa' => number_format($totalJasa, 0, '.', '.'),
            'bahan' => number_format($totalBahan, 0, '.', '.'),
            'sparepart' => number_format($totalSparepart, 0, '.', '.'),
            'ppn' => number_format($totalPpn, 0, '.', '.'),
            'total_lain' => number_format($totalLain, 0, '.', '.'),
            'total_invoice' => number_format($totalInvoice, 0, '.', '.'),
            'total_or' => number_format($totalOr, 0, '.', '.'),
            'tagihan' => number_format($totalTagihan, 0, '.', '.'),
            'is_total' => true
          ];
        } else {
          $data[] = [
            'no' => '',
            'no_invoice' => '',
            'kode_spk' => '',
            'no_polisi' => '',
            'nama_pelanggan' => 'Grand Total',
            'npwp_ktp' => '',
            'jasa' => number_format($totalJasa, 0, '.', '.'),
            'bahan' => number_format($totalBahan, 0, '.', '.'),
            'sparepart' => number_format($totalSparepart, 0, '.', '.'),
            'ppn' => number_format($totalPpn, 0, '.', '.'),
            'total_lain' => number_format($totalLain, 0, '.', '.'),
            'total_invoice' => number_format($totalInvoice, 0, '.', '.'),
            'total_or' => number_format($totalOr, 0, '.', '.'),
            'tagihan' => number_format($totalTagihan, 0, '.', '.'),
            'is_total' => true
          ];
        }
      }

      // $months = [
      //   '01' => 'Januari',
      //   '02' => 'Februari',
      //   '03' => 'Maret',
      //   '04' => 'April',
      //   '05' => 'Mei',
      //   '06' => 'Juni',
      //   '07' => 'Juli',
      //   '08' => 'Agustus',
      //   '09' => 'September',
      //   '10' => 'Oktober',
      //   '11' => 'November',
      //   '12' => 'Desember'
      // ];
      // $periode = $months[$bulan] . ' - ' . $tahun;
      $periode = $tglAwal . ' s/d ' . $tglAkhir;

      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => $data,
        'periode' => $periode,
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    // $validatedData = $request->validate(
    //   [
    //     'jenis_report' => 'required',
    //     'tahun' => 'required',
    //     'bulan' => 'required'
    //   ],
    //   [
    //     'jenis_report.required' => 'Jenis Report wajib dipilih.',
    //     'tahun.required' => 'Tahun wajib dipilih.',
    //     'bulan.required' => 'Bulan wajib dipilih.'
    //   ]
    // );

    // $dataArray['jenis_report'] = $request->jenis_report;
    // $dataArray['tahun'] = $request->tahun;
    // $dataArray['bulan'] = $request->bulan;

    // return redirect('administrasi/laporan-invoice-terbit')->with('datafilter_invoice_terbit', $dataArray);
    $validatedData = $request->validate(
      [
        'jenis_report' => 'required',
        'tgl_awal' => 'required',
        'tgl_akhir' => 'required'
      ],
      [
        'jenis_report.required' => 'Jenis Report wajib dipilih.',
        'tgl_awal.required' => 'Tanggal Awal wajib diisi.',
        'tgl_akhir.required' => 'Tanggal Akhir wajib diisi.'
      ]
    );

    $dataArray['jenis_report'] = $request->jenis_report;
    $dataArray['tgl_awal'] = $request->tgl_awal;
    $dataArray['tgl_akhir'] = $request->tgl_akhir;

    return redirect('administrasi/laporan-invoice-terbit')->with('datafilter_invoice_terbit', $dataArray);
  }

  /**
   * Export ke Excel.
   */
  public function exportExcel(Request $request)
  {
    try {
      $user_cabang = session('kd_cabang');
      $namaCabang = session('nm_cabang');

      $cabangData = [
        'kode' => $user_cabang,
        'nama' => $namaCabang
      ];

      // pakai ?: bukan ?? agar string kosong juga ke-fallback
      // $tahun = $request->input('tahun') ?: date('Y');
      // $bulan = $request->input('bulan') ?: date('m');
      // $jenis_report = $request->input('jenis_report') ?: 'Rekap';

      // // satu sumber filter yang sama, dipakai untuk query DAN untuk label periode
      // $filters = [
      //   'tahun' => $tahun,
      //   'bulan' => $bulan,
      //   'jenis_report' => $jenis_report,
      // ];

      // $months = [
      //   '01' => 'Januari',
      //   '02' => 'Februari',
      //   '03' => 'Maret',
      //   '04' => 'April',
      //   '05' => 'Mei',
      //   '06' => 'Juni',
      //   '07' => 'Juli',
      //   '08' => 'Agustus',
      //   '09' => 'September',
      //   '10' => 'Oktober',
      //   '11' => 'November',
      //   '12' => 'Desember'
      // ];

      // $periodeStr = $months[$bulan] . ' - ' . $tahun;
      $tglAwal = $request->input('tgl_awal') ?: date('d/m/Y');
      $tglAkhir = $request->input('tgl_akhir') ?: date('d/m/Y');
      $jenis_report = $request->input('jenis_report') ?: 'Rekap';

      $filters = [
        'tgl_awal' => $tglAwal,
        'tgl_akhir' => $tglAkhir,
        'jenis_report' => $jenis_report,
      ];

      $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;

      $fileName = 'Laporan_Invoice_Terbit_' . date('Ymd_His') . '.xlsx';

      return Excel::download(
        new LaporanInvoiceTerbitExport($filters, $cabangData, $periodeStr),
        $fileName
      );
    } catch (\Exception $e) {
      return redirect()->back()->with('error', 'Gagal export: ' . $e->getMessage());
    }
  }

  /**
   * Print view.
   */
  public function printData(Request $request)
  {
    try {
      $user_cabang = session('kd_cabang');
      $namaCabang = session('nm_cabang');

      // $title = 'Laporan Invoice Terbit';
      // $filters = $request->all();

      // $tahun = $filters['tahun'] ?? date('Y');
      // $bulan = $filters['bulan'] ?? date('m');
      // $jenis_report = $filters['jenis_report'] ?? 'Rekap';

      // $months = [
      //   '01' => 'Januari',
      //   '02' => 'Februari',
      //   '03' => 'Maret',
      //   '04' => 'April',
      //   '05' => 'Mei',
      //   '06' => 'Juni',
      //   '07' => 'Juli',
      //   '08' => 'Agustus',
      //   '09' => 'September',
      //   '10' => 'Oktober',
      //   '11' => 'November',
      //   '12' => 'Desember'
      // ];

      // $periodeStr = $months[$bulan] . ' - ' . $tahun;

      // $query = DB::table('v_rpt_invoice_terbit')
      //   ->where('kode_cabang', $user_cabang)
      //   ->whereYear('tgl_invoice', $tahun)
      //   ->whereMonth('tgl_invoice', $bulan);
      $title = 'Laporan Invoice Terbit';
      $filters = $request->all();

      $tglAwal = $filters['tgl_awal'] ?? date('d/m/Y');
      $tglAkhir = $filters['tgl_akhir'] ?? date('d/m/Y');
      $jenis_report = $filters['jenis_report'] ?? 'Rekap';

      $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;

      $startDate = Carbon::createFromFormat('d/m/Y', $tglAwal)->startOfDay();
      $endDate = Carbon::createFromFormat('d/m/Y', $tglAkhir)->endOfDay();

      $query = DB::table('v_rpt_invoice_terbit')
        ->where('kode_cabang', $user_cabang)
        ->whereBetween('tgl_invoice', [$startDate, $endDate]);

      if ($jenis_report === 'Rekap') {
        $datas = (clone $query)
          ->select([
            'nama_pelanggan',
            DB::raw('COUNT(id) as unit'),
            DB::raw('SUM(jasa) as jasa'),
            DB::raw('SUM(bahan) as bahan'),
            DB::raw('SUM(sparepart) as sparepart'),
            DB::raw('SUM(ppn) as ppn'),
            DB::raw('SUM(total_lain) as total_lain'),
            DB::raw('SUM(total_invoice) as total_invoice'),
            DB::raw('SUM(total_or) as total_or'),
            DB::raw('SUM(tagihan) as tagihan'),
          ])
          ->groupBy('nama_pelanggan')
          ->orderBy('nama_pelanggan', 'asc')
          ->get();
      } else {
        $datas = (clone $query)
          ->select([
            'no_invoice',
            'kode_spk',
            'no_polisi',
            'nama_pelanggan',
            'jenis_identitas',
            'no_identitas',
            'jasa',
            'bahan',
            'sparepart',
            'ppn',
            'total_lain',
            'total_invoice',
            'total_or',
            'tagihan',
          ])
          ->orderBy('nama_pelanggan', 'asc')
          ->orderBy('no_invoice', 'asc')
          ->get();

        $datas = $datas->map(function ($row) {
          $row->no_identitas = $row->no_identitas ?? '';
          return $row;
        });
      }

      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.administrasi.laporan.laporan-invoice-terbit-print', [
        'title' => $title,
        'namaCabang' => $namaCabang,
        'periodeStr' => $periodeStr,
        'jenis_report' => $jenis_report,
        'no' => 1,
        'datas' => $datas,
        'datafilter' => $filters,
        'pageConfigs' => $pageConfigs,
      ]);
    } catch (\Exception $e) {
      return redirect()->back()->with('error', 'Gagal print: ' . $e->getMessage());
    }
  }
}
