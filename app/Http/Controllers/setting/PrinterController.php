<?php

namespace App\Http\Controllers\setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Printer;
// use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class PrinterController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Printer(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Template Layout';

    return view('content.setting.printer', [
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
      2 => 'printer',
      3 => 'kertas',
      4 => 'font',
      5 => 'paragraph'
    ];

    $totalData = Printer::count(); // Total records without filtering
    $totalFiltered = $totalData;

    $limit = $request->input('length');
    $start = $request->input('start');
    $order = $columns[$request->input('order.0.column')] ?? 'id';
    $dir = $request->input('order.0.dir') ?? 'desc';

    $query = Printer::query();

      // Search handling
    if (!empty($request->input('search.value'))) {
      $search = $request->input('search.value');

      $query->where(function ($q) use ($search) {
        $q->where('id', 'LIKE', "%{$search}%")
          ->orWhere('printer', 'LIKE', "%{$search}%")
          ->orWhere('kertas', 'LIKE', "%{$search}%");
      });

      $totalFiltered = $query->count();
    }

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
        'printer' => $row->printer,
        'kertas' => $row->kertas,
        'font' => $row->font,
        'paragraph' => $row->paragraph
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
      $datas = Printer::updateOrCreate(
        ['id' => $dataID],
        [
          'printer' => $request->printer,
          'kertas' => $request->kertas,
          'font' => $request->font,
          'paragraph' => $request->paragraph,
          'updated_by' => Auth::user()->username,
          'updated_at' => date("Y-m-d H:i:s")
        ]
      );

      // user updated
      return response()->json(['status' => true, 'message' => "Berhasil ubah data"]);
    } else {
      $datas = Printer::updateOrCreate(
        ['id' => $dataID],
        [
          'printer' => $request->printer,
          'kertas' => $request->kertas,
          'font' => $request->font,
          'paragraph' => $request->paragraph,
          'created_by' => Auth::user()->username,
          'created_at' => date("Y-m-d H:i:s")
        ]
      );

      // user created
      // return response()->json('Created');
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
    $data = Printer::findOrFail($id);
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
    $datas = Printer::where('id', $id)->delete();
  }
}