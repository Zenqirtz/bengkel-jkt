<?php

namespace App\Http\Controllers\setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PeriodePembukuan;
use App\Models\LogActivity;
// use Carbon\Carbon;

class PeriodePembukuanController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function PeriodePembukuan(): View
  {
    $isList = \Helper::AuthIsPerm("list");
    $isAdd = \Helper::AuthIsPerm("add");
    $isEdit = \Helper::AuthIsPerm("edit");
    $isDel = \Helper::AuthIsPerm("delete");
    if(!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path = request()->path();
    $title = \Helper::getTitleMenu($path) ?? 'Periode Pembukuan';

    // $tgls = [];
    // for ($i=1; $i <= 31; $i++) { 
    //   $tgls[$i] = $i;
    // }

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.setting.periode-pembukuan', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      // 'data_tgl' => $tgls
    ]);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request): JsonResponse
  {
    $columns = [
      1 => 'id',
      2 => 'tgl_periode',
      3 => 'keterangan'
    ];

    $totalData = PeriodePembukuan::count(); // Total records without filtering
    $totalFiltered = $totalData;

    $limit = $request->input('length');
    $start = $request->input('start');
    $order = $columns[$request->input('order.0.column')] ?? 'id';
    $dir = $request->input('order.0.dir') ?? 'desc';

    $query = PeriodePembukuan::query();

    // Search handling
    // if (!empty($request->input('search.value'))) {
    //   $search = $request->input('search.value');

    //   $query->where(function ($q) use ($search) {
    //     $q->where('id', 'LIKE', "%{$search}%")
    //       ->orWhere('tgl_periode', 'LIKE', "%{$search}%")
    //       ->orWhere('keterangan', 'LIKE', "%{$search}%");
    //   });

    //   $totalFiltered = $query->count();
    // }

    if ($request->filled('tgl_periode')) {
      $query->where('tgl_periode', 'like', '%' . $request->tgl_periode . '%');
    }
    if ($request->filled('keterangan')) {
      $query->where('keterangan', 'like', '%' . $request->keterangan . '%');
    }

    $totalFiltered = $query->count();

    $datas = $query->offset($start)
      ->limit($limit)
      ->orderBy($order, $dir)
      ->get();

    $data = [];
    $ids = $start;

    foreach ($datas as $row) {
      $data[] = [
        'id' => $row->id,
        'fake_id' => ++$ids,
        'tgl_periode' => $row->tgl_periode,
        'tgl_periode2' => sprintf("%02d/%02d/%04d", $row->tgl_periode, date("m"), date("Y")),
        'keterangan' => $row->keterangan
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
      $data = [
        'tgl_periode' => $request->tgl_periode,
        'keterangan' => $request->keterangan,
        'updated_by' => Auth::user()->username
      ];

      $ok = PeriodePembukuan::updateOrCreate(
        ['id' => $dataID],
        $data
      );

      ## Log Activity
      $desc = $ok ? 'Berhasil Ubah Data Periode Pembukuan' : 'Gagal Ubah Data Periode Pembukuan';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } else {
      // cek data duplikat
      $cekData = PeriodePembukuan::first();

      if (empty($cekData)) {
        $data = [
          'tgl_periode' => $request->tgl_periode,
          'keterangan' => $request->keterangan,
          'created_by' => Auth::user()->username
        ];
  
        $ok = PeriodePembukuan::create($data);

        ## Log Activity
        $desc = $ok ? 'Berhasil Tambah Data Periode Pembukuan' : 'Gagal Tambah Data Periode Pembukuan';
        LogActivity::saveLogActivity($desc, $data);

        return response()->json([
          'status'  => (bool)$ok,
          'message' => $desc
        ]);
      } else {
        // user already exist
        // return response()->json(['message' => "Kode Akun sudah digunakan"], 422);
        return response()->json(['status' => false, 'message' => "Tanggal Periode Pembukuan sudah tersedia"]);
      }
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
    $data = PeriodePembukuan::findOrFail($id);
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
    $data = PeriodePembukuan::query()->where('id', $id)->first()->toArray();

    $ok = PeriodePembukuan::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Periode Pembukuan' : 'Gagal Hapus Data Periode Pembukuan';
    LogActivity::saveLogActivity($desc, $data);
  }
}