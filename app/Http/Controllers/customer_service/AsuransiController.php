<?php

namespace App\Http\Controllers\customer_service;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Asuransi;
use App\Models\Parameter;
// use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class AsuransiController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Asuransi(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Asuransi';

    $user_cabang = session('kd_cabang');
    $status_aktif = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();
    $jenis_pelanggan = Parameter::query()->where('nama_tabel', 'JENIS_PERANTARA')->orderBy('no_urut', 'asc')->get();

    return view('content.customer-service.asuransi', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'status_aktif' => $status_aktif,
      'jenis_pelanggan' => $jenis_pelanggan,
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
      2 => 'k.nama_pelanggan',
      3 => 'k.kode_jenis_pelanggan',
      4 => 'k.telepon',
      5 => 'b.keterangan',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'k.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('pelanggan as k')
        ->leftJoin('parameter as b', function ($join) {
          $join->on('b.kode', '=', 'k.is_active')
              ->where('b.nama_tabel', '=', 'STATUS'); // syarat di JOIN
        })
        ->leftJoin('parameter as c', function ($join) {
          $join->on('c.kode', '=', 'k.kode_jenis_pelanggan')
              ->where('c.nama_tabel', '=', 'JENIS_PERANTARA'); // syarat di JOIN
        })
        ->where('k.kode_cabang', $user_cabang);

    // Total baris tanpa filter
    $totalData = (clone $base)->count('k.id');

    // Filtering (search global)
    $query = (clone $base);
    if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
            $q->where('k.id', 'like', "%{$search}%")
              ->orWhere('k.nama_pelanggan', 'like', "%{$search}%")
              ->orWhere('k.telepon', 'like', "%{$search}%")
              ->orWhere('c.keterangan', 'like', "%{$search}%")
              ->orWhere('b.keterangan', 'like', "%{$search}%");
        });
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('k.id');

    // Ambil data halaman saat ini
    $datas = $query
      ->select([
          'k.id',
          'k.nama_pelanggan',
          'k.kode_jenis_pelanggan',
          'k.telepon',
          'k.is_active',
          'b.keterangan as status_aktif',
          'c.keterangan as jenis_pelanggan',
      ])
      ->orderBy($order, $dir)
      ->offset($start)
      ->limit($limit)
      ->get();

    // Susun payload DataTables
    $data = [];
    $fake = $start;
    foreach ($datas as $row) {
        $data[] = [
            'id'              => $row->id,
            'fake_id'         => ++$fake,
            'nama_pelanggan'  => $row->nama_pelanggan,
            'kode_jenis_pelanggan' => $row->kode_jenis_pelanggan,
            'telepon'         => $row->telepon,
            'is_active'       => $row->is_active,
            'status_aktif'    => $row->status_aktif,
            'jenis_pelanggan' => $row->jenis_pelanggan,
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
    $dataID = $request->id;

    if ($dataID) {
      // update the value
      $datas = Asuransi::updateOrCreate(
        ['id' => $dataID],
        [
          'kode_cabang' => $request->kode_cabang,
          'nama_pelanggan' => $request->nama_pelanggan,
          'kode_jenis_pelanggan' => $request->kode_jenis_pelanggan,
          'telepon' => $request->telepon,
          'is_active' => $request->is_active,
          'updated_by' => Auth::user()->username
        ]
      );

      // user updated
      return response()->json(['status' => true, 'message' => "Berhasil ubah data"]);
    } else {
      $lastNum = Asuransi::query()->where('kode_cabang', $request->kode_cabang)->max(DB::raw('CAST(kode_pelanggan AS UNSIGNED)')) ?? 0;
      $kode = sprintf('%05d', $lastNum + 1);

      $datas = Asuransi::updateOrCreate(
        ['id' => $dataID],
        [
          'kode_pelanggan' => $kode,
          'kode_cabang' => $request->kode_cabang,
          'nama_pelanggan' => $request->nama_pelanggan,
          'kode_jenis_pelanggan' => $request->kode_jenis_pelanggan,
          'telepon' => $request->telepon,
          'is_active' => $request->is_active,
          'created_by' => Auth::user()->username
        ]
      );

      // user created
      return response()->json(['status' => true, 'message' => "Berhasil tambah data"]);
    }
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
    $data = Asuransi::findOrFail($id);
    return response()->json($data);
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
    $datas = Asuransi::where('id', $id)->delete();
  }

  public function getNamaAsuransi(Request $request): JsonResponse
  {
    $jenisId = $request->query('jenis_id');

    if (!$jenisId) {
        return response()->json([]);
    }

    $user_cabang = session('kd_cabang');
    $data = Asuransi::where('kode_cabang', $user_cabang)
              ->where('kode_jenis_pelanggan', $jenisId) 
              ->where('is_active', 'Y')
              ->select('kode_pelanggan', 'nama_pelanggan') 
              ->orderBy('nama_pelanggan', 'asc')
              ->get();

    return response()->json($data);
  }
}