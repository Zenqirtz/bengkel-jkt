<?php

namespace App\Http\Controllers\administrasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Parameter;
use App\Models\LogActivity;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel; // EXPORT EXCEL
use App\Exports\PosisiPerbaikanExport;    // EXPORT EXCEL

use App\Helpers\Helpers as Helper;

class PosisiPerbaikanController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function PosisiPerbaikan(): View
  {
    $isList = Helper::AuthIsPerm("list");
    $isAdd = Helper::AuthIsPerm("add");
    $isEdit = Helper::AuthIsPerm("edit");
    $isDel = Helper::AuthIsPerm("delete");
    if(!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path = request()->path();
    $title = Helper::getTitleMenu($path) ?? 'Posisi Perbaikan';

    $user_cabang = session('kd_cabang');
    // $status_spk = Parameter::query()->where('nama_tabel', 'STATUS_SPK')->orderBy('no_urut', 'asc')->get();
    // $status = Parameter::query()->where('nama_tabel', 'STATUS_SPK_KET')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);
    
    return view('content.administrasi.posisi-perbaikan', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      // 'status_spk' => $status_spk,
      // 'status' => $status,
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
    
    $columns = [
      1 => 'k.id',
      2 => 'k.tgl_masuk',
      3 => 'k.kode_spk',
      4 => 'k.pemilik', 
      5 => 'k.nama_pelanggan',
      6 => 'k.no_polisi',
      7 => 'k.merek_tipe',
      8 => 'k.tgl_turun_lapangan',
      9 => 'k.tgl_rencana_selesai',
      10 => 'k.tgl_bongkar2',
      11 => 'k.tgl_las2',
      12 => 'k.tgl_dempul2',
      13 => 'k.tgl_mixing2',
      14 => 'k.tgl_cat2',
      15 => 'k.tgl_poles2',
      16 => 'k.tgl_finishing2',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'k.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('v_posisi_perbaikan as k')
    ->where('k.kode_cabang', $user_cabang);

    // Total baris tanpa filter
    $totalData = (clone $base)->count('k.id');

    // Filtering (search global)
    $query = (clone $base);
    if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
            $q->where('k.kode_spk', 'like', "%{$search}%")
              ->orWhere('k.no_polisi', 'like', "%{$search}%")
              ->orWhere('k.pemilik', 'like', "%{$search}%");
        });
    }

    // Filter berdasarkan input yang dikirim dari DataTables
    if ($request->filled('kode_spk')) {
      $query->where('k.kode_spk', 'like', '%' . $request->kode_spk . '%');
    }
    if ($request->filled('no_polisi')) {
      $query->where('k.no_polisi', 'like', '%' . $request->no_polisi . '%');
    }
    if ($request->filled('tgl_masuk_awal')) {
      $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_masuk_awal, 'Asia/Jakarta')->format('Y-m-d');
      $query->whereDate('k.tgl_masuk', '>=', $startDate);
    }
    if ($request->filled('tgl_masuk_akhir')) {
      $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_masuk_akhir, 'Asia/Jakarta')->format('Y-m-d');
      $query->whereDate('k.tgl_masuk', '<=', $endDate);
    }
    if ($request->filled('nama_pelanggan')) {
      $query->where('k.nama_pelanggan', 'like', '%' . $request->nama_pelanggan . '%');
    }
    if ($request->filled('nama_pemilik')) {
      $query->where('k.pemilik', 'like', '%' . $request->nama_pemilik . '%');
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('k.id');

    // Ambil data halaman saat ini
    $datas = $query
      ->select()
      ->orderBy($order, $dir)
      ->offset($start)
      ->limit($limit)
      ->get();
 
    // Susun payload DataTables
    $data = [];
    $fake = $start;
    foreach ($datas as $row) {
        $data[] = [
            'id'  => $row->id,
            'fake_id' => ++$fake,
            'kode_cabang' => $row->kode_cabang,
            'kode_spk' => $row->kode_spk,
            'no_polisi' => $row->no_polisi,
            'merek_tipe' => $row->merek_tipe,
            'tgl_masuk' => $row->tgl_masuk,
            'tgl_turun_lapangan' => blank($row->tgl_turun_lapangan) ? '' : date("d/m/Y", strtotime($row->tgl_turun_lapangan)),
            'tgl_rencana_selesai' => blank($row->tgl_rencana_selesai) ? '' : date("d/m/Y", strtotime($row->tgl_rencana_selesai)),
            'tgl_bongkar2' => blank($row->tgl_bongkar2) ? '' : date("d/m/Y", strtotime($row->tgl_bongkar2)),
            'tgl_las2' => blank($row->tgl_las2) ? '' : date("d/m/Y", strtotime($row->tgl_las2)),
            'tgl_dempul2' => blank($row->tgl_dempul2) ? '' : date("d/m/Y", strtotime($row->tgl_dempul2)),
            'tgl_mixing2' => blank($row->tgl_mixing2) ? '' : date("d/m/Y", strtotime($row->tgl_mixing2)),
            'tgl_cat2' => blank($row->tgl_cat2) ? '' : date("d/m/Y", strtotime($row->tgl_cat2)),
            'tgl_poles2' => blank($row->tgl_poles2) ? '' : date("d/m/Y", strtotime($row->tgl_poles2)),
            'tgl_finishing2' => blank($row->tgl_finishing2) ? '' : date("d/m/Y", strtotime($row->tgl_finishing2)),
            'pemilik' => $row->pemilik,
            'nama_pelanggan' => $row->nama_pelanggan,
        ];
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
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id): JsonResponse
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id) {}

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    // 
  }

  /**
   * Export data to Excel.
   */
  public function exportExcel(Request $request)
  {
    // Tambah batas waktu eksekusi (misal 5 menit / 300 detik)
    // ini_set('max_execution_time', 300);
    // Tambah batas memori (jika error memory exhausted)
    // ini_set('memory_limit', '512M');
    
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

    $periodeStr = date('d/m/Y');
    // -----------------------------------------------------

    $filters = $request->all();

    // ---------------------------------------

    $fileName = 'Posisi_Perbaikan_' . date('Ymd_His') . '.xlsx';

    ## Log Activity
    $desc = "Export Posisi Perbaikan";
    LogActivity::saveLogActivity($desc, $filters);

    return Excel::download(new PosisiPerbaikanExport($filters, $cabangData, $periodeStr), $fileName);
  }

}