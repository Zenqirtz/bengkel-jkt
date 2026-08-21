<?php

namespace App\Http\Controllers\setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\LogActivity;
use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class LogActivityController extends Controller
{
  /**
   * Tampilkan halaman view.
   */
  public function LogActivity(): View
  {
    $isList = Helper::AuthIsPerm("list");

    if (!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path  = request()->path();
    $title = Helper::getTitleMenu($path) ?? 'Log Activity';

    // Default filter hari ini — hanya untuk nilai awal input di view
    $datafilter = [
      'tgl_awal'    => date("d/m/Y"),
      'tgl_akhir'   => date("d/m/Y"),
      'created_by'  => '',
      'description' => '',
    ];

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.setting.log-activity', [
      'title'      => $title,
      'isList'     => $isList,
      'datafilter' => $datafilter,
    ]);
  }

  /**
   * DataTables AJAX — filter dikirim langsung via GET (tidak pakai session).
   */
  public function index(Request $request): JsonResponse
  {
    try {
      $tgl_awal    = $request->input('tgl_awal',    date("d/m/Y"));
      $tgl_akhir   = $request->input('tgl_akhir',   date("d/m/Y"));
      $created_by  = $request->input('created_by',  '');
      $description = $request->input('description', '');
      $search      = $request->input('search.value', '');

      $query = DB::table('log_activity');
        // ->where(function ($q) {
        //   $q->where('description',  'not like', '%view%')
        //     ->where('description', 'not like', '%login%')
        //     ->where('description', 'not like', '%logout%');
        // });

      // Filter tanggal
      if (!empty($tgl_awal)) {
        try {
          $startDate = Carbon::createFromFormat('d/m/Y', $tgl_awal)->startOfDay();
          $query->where('created_at', '>=', $startDate);
        } catch (\Exception $e) {}
      }

      if (!empty($tgl_akhir)) {
        try {
          $endDate = Carbon::createFromFormat('d/m/Y', $tgl_akhir)->endOfDay();
          $query->where('created_at', '<=', $endDate);
        } catch (\Exception $e) {}
      }

      // Filter tambahan
      if (!empty($created_by)) {
        $query->where('created_by', 'like', '%' . $created_by . '%');
      }

      if (!empty($description)) {
        $query->where('description', 'like', '%' . $description . '%');
      }

      $totalData = (clone $query)->count();

      // DataTables global search (kotak "Cari..." di atas tabel)
      if (!empty($search)) {
        $query->where(function ($q) use ($search) {
          $q->where('created_by',  'like', '%' . $search . '%')
            ->orWhere('description', 'like', '%' . $search . '%');
        });
      }

      $totalFiltered = (clone $query)->count();

      // Sorting dari DataTables
      $columnMap = [
        1 => 'created_at',
        2 => 'updated_at',
        3 => 'created_by',
        4 => 'description',
      ];

      $orderColumnIndex = (int) $request->input('order.0.column', 1);
      $orderDir         = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
      $orderColumn      = $columnMap[$orderColumnIndex] ?? 'created_at';

      $start  = (int) $request->input('start',  0);
      $length = (int) $request->input('length', 10);

      $datas = (clone $query)
        ->select(['id', 'description', 'created_at', 'created_by', 'updated_at', 'updated_by'])
        ->orderBy($orderColumn, $orderDir)
        ->offset($start)
        ->limit($length)
        ->get();

      $data = [];
      $no   = $start + 1;
      foreach ($datas as $row) {
        $data[] = [
          'no'          => $no++,
          'created_at'  => $row->created_at ? Carbon::parse($row->created_at)->format('d/m/Y H:i:s') : '',
          'updated_at'  => $row->updated_at ? Carbon::parse($row->updated_at)->format('d/m/Y H:i:s') : '',
          'created_by'  => $row->created_by  ?? '',
          'description' => $row->description ?? '',
        ];
      }

      return response()->json([
        'draw'            => intval($request->input('draw')),
        'recordsTotal'    => $totalData,
        'recordsFiltered' => $totalFiltered,
        'data'            => $data,
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'draw'            => intval($request->input('draw')),
        'recordsTotal'    => 0,
        'recordsFiltered' => 0,
        'data'            => [],
        'error'           => $e->getMessage(),
      ]);
    }
  }

  /**
   * store() tidak dipakai untuk filter — filter via AJAX langsung.
   */
  public function store(Request $request)
  {
    return response()->json(['message' => 'Not used'], 405);
  }

  // Stub methods required by Route::resource
  public function create() {}
  public function show($id) {}
  public function edit($id) {}
  public function update(Request $request, $id) {}
  public function destroy($id) {}
}
