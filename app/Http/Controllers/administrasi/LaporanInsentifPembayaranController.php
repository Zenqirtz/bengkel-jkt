<?php

namespace App\Http\Controllers\administrasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanInsentifPembayaranExport;

class LaporanInsentifPembayaranController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanInsentifPembayaran(): View
  {
    $isList = \Helper::AuthIsPerm("list");
    $isAdd = \Helper::AuthIsPerm("add");
    $isEdit = \Helper::AuthIsPerm("edit");
    $isDel = \Helper::AuthIsPerm("delete");

    if (!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path = request()->path();
    $title = \Helper::getTitleMenu($path) ?? 'Insentif Pembayaran';

    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter');
    if (empty($datafilter)) {
      $datafilter['tanggal_dari'] = date("d/m/Y");
      $datafilter['tanggal_sampai'] = date("d/m/Y");
    }

    return view('content.administrasi.laporan.laporan-insentif-pembayaran', [
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
    $user_cabang = session('kd_cabang');

    $base = DB::table('v_rep_insentif_pembayaran as k')
      ->where('k.kode_cabang', $user_cabang);

    $query = (clone $base);

    // Ambil tanggal dari request atau session
    $tanggalDari = $request->filled('tanggal_dari')
      ? $request->tanggal_dari
      : session('datafilter.tanggal_dari', date("d/m/Y"));

    $tanggalSampai = $request->filled('tanggal_sampai')
      ? $request->tanggal_sampai
      : session('datafilter.tanggal_sampai', date("d/m/Y"));

    // Filter berdasarkan tanggal
    if (!empty($tanggalDari) && !empty($tanggalSampai)) {
      try {
        $tglDari = Carbon::createFromFormat('d/m/Y', $tanggalDari, 'Asia/Jakarta')->format('Y-m-d');
        $tglSampai = Carbon::createFromFormat('d/m/Y', $tanggalSampai, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereBetween('k.tgl_lunas', [$tglDari, $tglSampai]);
      } catch (\Exception $e) {
        // Handle error format tanggal
      }
    }

    $totalData = (clone $query)->count('k.kode_estimasi');
    $totalFiltered = (clone $query)->count('k.kode_estimasi');

    $datas = $query
      ->select([
        'k.kode_lunas_kwitansi',
        'k.kode_kwitansi',
        'k.kode_voucher',
        'k.kode_estimasi',
        'k.kode_spk',
        'k.no_polisi',
        'k.merek_tipe',
        'k.nama_pelanggan',
        'k.jasa',
        'k.sparepart',
        'k.tgl_lunas',
        'k.tgl_kwitansi',
        'k.hari',
        'k.kode_cabang',
        'k.nama_cabang',
        'k.nama_marketing',
      ])
      ->orderBy('k.tgl_lunas', 'asc')
      ->get();

    $data = [];
    $fake = 0;
    foreach ($datas as $row) {
      $data[] = [
        'no' => ++$fake,
        'kode_lunas' => $row->kode_lunas_kwitansi,
        'kode_kwitansi' => $row->kode_kwitansi,
        'kode_voucher' => $row->kode_voucher,
        'kode_estimasi' => $row->kode_estimasi,
        'kode_spk' => $row->kode_spk,
        'no_polisi' => $row->no_polisi,
        'merek_tipe' => $row->merek_tipe,
        'nama_pelanggan' => $row->nama_pelanggan,
        'jasa' => number_format($row->jasa, 0, ',', '.'),
        'sparepart' => number_format($row->sparepart, 0, ',', '.'),
        'jumlah' => number_format($row->jasa + $row->sparepart, 0, ',', '.'),
        'tgl_lunas' => blank($row->tgl_lunas) ? '' : date("d/m/Y", strtotime($row->tgl_lunas)),
        'tgl_kwitansi' => blank($row->tgl_kwitansi) ? '' : date("d/m/Y", strtotime($row->tgl_kwitansi)),
        'hari' => $row->hari,
      ];
    }

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
        'tanggal_dari' => 'required',
        'tanggal_sampai' => 'required'
      ],
      [
        'tanggal_dari.required' => 'Tanggal Dari wajib diisi.',
        'tanggal_sampai.required' => 'Tanggal Sampai wajib diisi.',
      ]
    );

    $dataArray['tanggal_dari'] = $request->tanggal_dari;
    $dataArray['tanggal_sampai'] = $request->tanggal_sampai;

    return redirect('administrasi/laporan-insentif-pembayaran')->with('datafilter', $dataArray);
  }

  /**
   * Export data to Excel.
   */
  public function exportExcel(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $cabangData = [
      'kode' => $user_cabang,
      'nama' => $namaCabang
    ];

    $filters = $request->all();

    // Format Periode
    $tglDari = $filters['tanggal_dari'] ?? date('d/m/Y');
    $tglSampai = $filters['tanggal_sampai'] ?? date('d/m/Y');
    $periodeStr = $tglDari . ' s/d ' . $tglSampai;

    $fileName = 'Laporan_Insentif_Pembayaran_' . date('Ymd_His') . '.xlsx';

    return Excel::download(new LaporanInsentifPembayaranExport($filters, $cabangData, $periodeStr), $fileName);
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan Insentif Pembayaran';

    $filters = $request->all();

    // Format Periode
    $tglDari = $filters['tanggal_dari'] ?? date('d/m/Y');
    $tglSampai = $filters['tanggal_sampai'] ?? date('d/m/Y');
    $periodeStr = $tglDari . ' s/d ' . $tglSampai;

    // Data
    $query = DB::table('v_rep_insentif_pembayaran as k')
      ->where('k.kode_cabang', $user_cabang)
      ->select([
        'k.kode_lunas_kwitansi',
        'k.kode_kwitansi',
        'k.kode_voucher',
        'k.kode_estimasi',
        'k.kode_spk',
        'k.no_polisi',
        'k.merek_tipe',
        'k.nama_pelanggan',
        'k.jasa',
        'k.sparepart',
        'k.tgl_lunas',
        'k.tgl_kwitansi',
        'k.hari',
      ])
      ->orderBy('k.tgl_lunas', 'asc');

    // Filtering berdasarkan tanggal
    if (!empty($filters['tanggal_dari']) && !empty($filters['tanggal_sampai'])) {
      try {
        $tanggalDari = Carbon::createFromFormat('d/m/Y', $filters['tanggal_dari'])->format('Y-m-d');
        $tanggalSampai = Carbon::createFromFormat('d/m/Y', $filters['tanggal_sampai'])->format('Y-m-d');
        $query->whereBetween('k.tgl_lunas', [$tanggalDari, $tanggalSampai]);
      } catch (\Exception $e) {
      }
    }

    $datas = $query->get();
    $pages = 'content.administrasi.laporan.laporan-insentif-pembayaran-print';

    $pageConfigs = ['myLayout' => 'blank'];
    return view($pages, [
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
