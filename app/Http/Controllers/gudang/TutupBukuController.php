<?php

namespace App\Http\Controllers\gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Parameter;
use App\Models\SaldoBahan;
use App\Models\SaldoSparepart;
use App\Models\LogActivity;
use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class TutupBukuController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function TutupBuku(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Tutup Buku';

    $user_cabang = session('kd_cabang');

    $months = Helper::listMonths();
    $years = Helper::listYears();
    $tipe_barang = Parameter::query()->where('nama_tabel', 'TIPE_BARANG')->orderBy('no_urut', 'asc')->get();

    return view('content.gudang.tutup-buku', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'months' => $months,
      'years' => $years,
      'user_cabang' => $user_cabang,
      'tipe_barang' => $tipe_barang,
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

    if($request->tipe == "total-data") {
      $months = Helper::listMonths();

      ## Jumlah Saldo Bahan
      $saldo_bahan = SaldoBahan::select('bulan', 'tahun', DB::raw('COUNT(1) AS total'))
      ->where('kode_cabang', $user_cabang)
      ->where('kode_group_bahan', '00001')
      ->groupBy('tahun', 'bulan')
      ->orderBy('tahun', 'desc')
      ->orderBy('bulan', 'desc')
      ->first();

      $bulan = ($saldo_bahan) ? @$months[$saldo_bahan->bulan] : '';
      $tahun = ($saldo_bahan) ? $saldo_bahan->tahun : '';
      $totalSaldoBahan = ($saldo_bahan) ? $saldo_bahan->total : '0';

      $data['periode_bahan']  = sprintf("%s %s", $bulan, $tahun);
      $data['saldo_bahan']    = number_format($totalSaldoBahan, 0, ".", ",");

      ## Jumlah Saldo Cat
      $saldo_cat = SaldoBahan::select('bulan', 'tahun', DB::raw('COUNT(1) AS total'))
      ->where('kode_cabang', $user_cabang)
      ->where('kode_group_bahan', '00002')
      ->groupBy('tahun', 'bulan')
      ->orderBy('tahun', 'desc')
      ->orderBy('bulan', 'desc')
      ->first();

      $bulan = ($saldo_cat) ? @$months[$saldo_cat->bulan] : '';
      $tahun = ($saldo_cat) ? $saldo_cat->tahun : '';
      $totalSaldoCat = ($saldo_cat) ? $saldo_cat->total : '0';

      $data['periode_cat']  = sprintf("%s %s", $bulan, $tahun);
      $data['saldo_cat']    = number_format($totalSaldoCat, 0, ".", ",");
  
      ## Jumlah Saldo Sparepart
      $saldo_sparepart = SaldoSparepart::select('bulan', 'tahun', DB::raw('COUNT(1) AS total'))
      ->where('kode_cabang', $user_cabang)
      ->groupBy('tahun', 'bulan')
      ->orderBy('tahun', 'desc')
      ->orderBy('bulan', 'desc')
      ->first();

      $bulan = ($saldo_sparepart) ? @$months[$saldo_sparepart->bulan] : '';
      $tahun = ($saldo_sparepart) ? $saldo_sparepart->tahun : '';
      $totalSaldoSparepart = ($saldo_sparepart) ? $saldo_sparepart->total : '0';

      $data['periode_sparepart']  = sprintf("%s %s", $bulan, $tahun);
      $data['saldo_sparepart']    = number_format($totalSaldoSparepart, 0, ".", ",");

      return response()->json($data);

    } else {
      $totalData = 0;
      $totalFiltered = 0;
      $data = [];
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
    $user_cabang = session('kd_cabang');
    try {

      $rules = [
        'tipe' => 'required',
        'bulan' => 'required',
        'tahun' => 'required',
      ];
  
      $messages = [
        'tipe.required' => 'Tipe Barang Wajib diisi',
        'bulan.required' => 'Periode Bulan Wajib diisi',
        'tahun.required'  => 'Periode Tahun Wajib diisi',
      ];
  
      $validator = Validator::make($request->all(), $rules, $messages);
  
      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ], 200);
      }

      if($request->tipe == "P" || $request->tipe == "C") {
        $bahan = ($request->tipe == "P") ? "00001" : "00002";

        $cek = SaldoBahan::where('kode_cabang', $user_cabang)
        ->where('kode_group_bahan', $bahan)
        ->where('bulan', $request->bulan)
        ->where('tahun', $request->tahun)
        ->count();

        if($cek > 0) {
          return response()->json([
            'status' => false,
            'message' => "Tutup Buku sudah di proses pada periode tersebut"
          ], 200);
        }

        $results = DB::select('CALL up_apl_generate_saldo_bahan_bulanan(?, ?, ?, ?, ?)', [
          $user_cabang, $request->bulan, $request->tahun, $bahan, Auth::user()->username
        ]);

        $data = $results[0];

        $status = ($data->status == "SUCCESS") ? true : false;
        LogActivity::saveLogActivity($data->message, (array)$data);
  
        return response()->json([
          'status'  => $status,
          'message' => $data->message
        ]);

      } elseif($request->tipe == "S") {

        $cek = SaldoSparepart::where('kode_cabang', $user_cabang)
        ->where('bulan', $request->bulan)
        ->where('tahun', $request->tahun)
        ->count();

        if($cek > 0) {
          return response()->json([
            'status' => false,
            'message' => "Tutup Buku sudah di proses pada periode tersebut"
          ], 200);
        }

        $results = DB::select('CALL up_apl_generate_saldo_sparepart_bulanan(?, ?, ?, ?)', [
          $user_cabang, $request->bulan, $request->tahun, Auth::user()->username
        ]);

        $data = $results[0];

        $status = ($data->status == "SUCCESS") ? true : false;
        LogActivity::saveLogActivity($data->message, (array)$data);
  
        return response()->json([
          'status'  => $status,
          'message' => $data->message
        ]);

      } else {
        return response()->json([
          'status' => false,
          'message' => "Tutup Buku Gagal: Tipe barang tidak ada"
        ], 200);
      }

    } catch (\Exception $e) {
      // Tangkap error jika terjadi masalah saat insert ke database
      return response()->json([
          'status' => false,
          'message' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 200);
    }

  }

}
