<?php

namespace App\Http\Controllers\setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Pengumuman;
use App\Models\LogActivity;
use Carbon\Carbon;

class PengumumanController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Pengumuman(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Pengumuman';

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.setting.pengumuman', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
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
      2 => 'startdate',
      3 => 'enddate',
      4 => 'notes',
    ];

    $totalData = Pengumuman::count(); // Total records without filtering
    // $totalFiltered = $totalData;

    $limit = $request->input('length');
    $start = $request->input('start');
    $order = $columns[$request->input('order.0.column')] ?? 'id';
    $dir = $request->input('order.0.dir') ?? 'desc';

    $query = Pengumuman::query();

      // Search handling
    // if (!empty($request->input('search.value'))) {
    //   $search = $request->input('search.value');

    //   $query->where(function ($q) use ($search) {
    //     $q->where('id', 'LIKE', "%{$search}%")
    //       ->orWhere('startdate', 'LIKE', "%{$search}%")
    //       ->orWhere('enddate', 'LIKE', "%{$search}%")
    //       ->orWhere('notes', 'LIKE', "%{$search}%");
    //   });

    //   $totalFiltered = $query->count();
    // }
    if ($request->filled('tanggal')) {
      try {
        $tanggal = Carbon::createFromFormat('d/m/Y', $request->tanggal)->endOfDay();
        $query->where('startdate', '<=', $tanggal);
        $query->where('enddate', '>=', $tanggal);
      } catch (\Exception $e) {}
    }
    if ($request->filled('notes')) {
      $query->where('notes', 'like', '%' . $request->notes . '%');
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
        'startdate' => date("d/m/Y", strtotime($row->startdate)),
        'enddate' => date("d/m/Y", strtotime($row->enddate)),
        'notes' => $row->notes,
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
        'startdate' => Carbon::createFromFormat('d/m/Y', $request->startdate, 'Asia/Jakarta')->format('Y-m-d'), 
        'enddate' => Carbon::createFromFormat('d/m/Y', $request->enddate, 'Asia/Jakarta')->format('Y-m-d'),
        'notes' => $request->notes,
        'updated_by' => Auth::user()->username
      ];

      // update the value
      $ok = Pengumuman::updateOrCreate(
        ['id' => $dataID],
        $data
      );

      ## Log Activity
      $desc = $ok ? 'Berhasil Ubah Data Pengumuman' : 'Gagal Ubah Data Pengumuman';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } else {
      $data = [
        'startdate' => Carbon::createFromFormat('d/m/Y', $request->startdate, 'Asia/Jakarta')->format('Y-m-d'), 
        'enddate' => Carbon::createFromFormat('d/m/Y', $request->enddate, 'Asia/Jakarta')->format('Y-m-d'),
        'notes' => $request->notes,
        'created_by' => Auth::user()->username
      ];

      $ok = Pengumuman::create($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil Tambah Data Pengumuman' : 'Gagal Tambah Data Pengumuman';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
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
    $data = Pengumuman::findOrFail($id);
    $data->startdate = date("d/m/Y", strtotime($data->startdate));
    $data->enddate = date("d/m/Y", strtotime($data->enddate));
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
    $data = Pengumuman::query()->where('id', $id)->first()->toArray();

    $ok = Pengumuman::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Pengumuman' : 'Gagal Hapus Data Pengumuman';
    LogActivity::saveLogActivity($desc, $data);
  }
}