<?php

namespace App\Http\Controllers\setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Group;
use App\Models\Parameter;
use App\Models\LogActivity;
// use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class GroupController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Group(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Group';

    $status_aktif = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.setting.group', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'status_aktif' => $status_aktif,
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
      1 => 'm.id',
      2 => 'm.nama',
      3 => 'm.keterangan',
      4 => 'b.keterangan',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'm.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('group as m')
    ->leftJoin('parameter as b', function ($join) {
      $join->on('b.kode', '=', 'm.active')
          ->where('b.nama_tabel', '=', 'STATUS'); // syarat di JOIN
    });

    // Total baris tanpa filter
    $totalData = (clone $base)->count('m.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('m.nama', 'like', "%{$search}%")
    //           ->orWhere('m.keterangan', 'like', "%{$search}%")
    //           ->orWhere('b.keterangan', 'like', "%{$search}%"); 
    //     });
    // }
    if ($request->filled('nama')) {
      $query->where('m.nama', 'like', '%' . $request->nama . '%');
    }
    if ($request->filled('keterangan')) {
      $query->where('m.keterangan', 'like', '%' . $request->keterangan . '%');
    }
    if ($request->filled('active')) {
      if ($request->active <> 'all') {
        $query->where('m.active', 'like', '%' . $request->active . '%');
      }
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('m.id');

    // Ambil data halaman saat ini
    $datas = $query
        ->select([
            'm.id',
            'm.nama',
            'm.keterangan',
            'm.active',
            'b.keterangan as status',
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
            'id'          => $row->id,
            'fake_id'     => ++$fake,
            'nama'        => $row->nama,
            'keterangan'  => $row->keterangan,
            'active'      => $row->active,
            'status'      => $row->status,
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
    try {
      $dataID = $request->id;

      if ($dataID) {
        $rules = [
          'nama'        => 'required',
          'keterangan'  => 'required',
          'active'      => 'required',
        ];
    
        $messages = [
          'nama.required' => 'Nama Group Wajib diisi',
          'keterangan.required'  => 'Keterangan Wajib diisi',
          'active.required'  => 'Status Aktif Wajib diisi',
        ];
    
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
          return response()->json([
            'status' => false,
            'message' => "Gagal menyimpan data.",
            'errors' => $validator->errors()
          ]);
        }

        $data = [
          'nama' => $request->nama,
          'keterangan' => $request->keterangan,
          'active' => $request->active,
          'updated_by' => Auth::user()->username,
        ];

        // update the value
        $ok = Group::updateOrCreate(
          ['id' => $dataID],
          $data
        );
  
        ## Log Activity
        $desc = $ok ? 'Berhasil Ubah Data Group' : 'Gagal Ubah Data Group';
        LogActivity::saveLogActivity($desc, $data);
  
        return response()->json([
          'status'  => (bool)$ok,
          'message' => $desc
        ]);
      } else {
        $rules = [
          'nama'        => 'required',
          'keterangan'  => 'required',
          'active'      => 'required',
        ];
    
        $messages = [
          'nama.required' => 'Nama Group Wajib diisi',
          'keterangan.required'  => 'Keterangan Wajib diisi',
          'active.required'  => 'Status Aktif Wajib diisi',
        ];
    
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
          return response()->json([
            'status' => false,
            'message' => "Gagal menyimpan data.",
            'errors' => $validator->errors()
          ]);
        }

        $data = [
          'nama' => $request->nama,
          'keterangan' => $request->keterangan,
          'active' => $request->active,
          'created_by' => Auth::user()->username,
        ];

        $ok = Group::create($data);

        ## Log Activity
        $desc = $ok ? 'Berhasil Tambah Data Group' : 'Gagal Tambah Data Group';
        LogActivity::saveLogActivity($desc, $data);

        return response()->json([
          'status'  => (bool)$ok,
          'message' => $desc
        ]);
      }
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 500);
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
    $data = Group::findOrFail($id);
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
    $data = Group::query()->where('id', $id)->first()?->toArray() ?? [];

    $ok = Group::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Group' : 'Gagal Hapus Data Group';
    LogActivity::saveLogActivity($desc, $data);
  }
}