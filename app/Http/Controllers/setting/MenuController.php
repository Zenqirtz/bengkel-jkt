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
use App\Models\Menu;
use App\Models\Parameter;
use App\Models\LogActivity;
// use Carbon\Carbon;

class MenuController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Menu(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Menu';

    $menus = Menu::query()->orderBy('id', 'asc')->get();
    $status_aktif = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.setting.menu', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'status_aktif' => $status_aktif,
      'data_menu' => $menus
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
      2 => 'm.custom_title',
      3 => 'm2.custom_title',
      4 => 'm.url_menu',
      5 => 'm.tid',
      6 => 'b.keterangan',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'm.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('menu as m')
    ->leftJoin('parameter as b', function ($join) {
      $join->on('b.kode', '=', 'm.active')
          ->where('b.nama_tabel', '=', 'STATUS'); // syarat di JOIN
    })
    ->leftJoin('menu as m2', 'm2.id', '=', 'm.parent_id');

    // Total baris tanpa filter
    $totalData = (clone $base)->count('m.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('m.url_menu', 'like', "%{$search}%")
    //           ->orWhere('m.custom_title', 'like', "%{$search}%")
    //           ->orWhere('m.slug', 'like', "%{$search}%")
    //           ->orWhere('b.keterangan', 'like', "%{$search}%"); 
    //     });
    // }
    if ($request->filled('custom_title')) {
      $query->where('m.custom_title', 'like', '%' . $request->custom_title . '%');
    }
    if ($request->filled('parent_id')) {
      if ($request->parent_id == 'top') {
        $query->whereNull('m.parent_id');
      } elseif ($request->parent_id <> 'all') {
        $query->where('m.parent_id', '=', $request->parent_id);
      }
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
            'm.custom_title',
            'm2.custom_title as custom_title_parent',
            'm.url_menu',
            'm.tid',
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
            'id'            => $row->id,
            'fake_id'       => ++$fake,
            'custom_title'  => $row->custom_title,
            'url_menu'      => $row->url_menu,
            'tid'           => $row->tid,
            'active'        => $row->active,
            'status'        => $row->status,
            'custom_title_parent' => $row->custom_title_parent,
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
          'parent_id'     => 'required',
          'custom_title'  => 'required',
          'url_menu'      => 'required',
          'active'        => 'required',
        ];
    
        $messages = [
          'parent_id.required' => 'Sub Menu Wajib diisi',
          'custom_title.required'  => 'Nama Menu Wajib diisi',
          'url_menu.required'  => 'URL Wajib diisi',
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
          'parent_id' => $request->parent_id,
          'custom_title' => $request->custom_title,
          'url_menu' => $request->url_menu,
          'slug' => $request->slug,
          'path_icon' => $request->path_icon,
          'tid' => $request->tid,
          'active' => $request->active,
          'updated_by' => Auth::user()->username,
        ];

        if($request->parent_id == "top") {
          $data['parent_id'] = null;
        }
  
        // update the value
        $ok = Menu::updateOrCreate(
          ['id' => $dataID],
          $data
        );
  
        ## Log Activity
        $desc = $ok ? 'Berhasil Ubah Data Menu' : 'Gagal Ubah Data Menu';
        LogActivity::saveLogActivity($desc, $data);
  
        return response()->json([
          'status'  => (bool)$ok,
          'message' => $desc
        ]);
      } else {
        $rules = [
          'parent_id'     => 'required',
          'custom_title'  => 'required',
          'url_menu'      => 'required',
          'active'        => 'required',
        ];
    
        $messages = [
          'parent_id.required' => 'Sub Menu Wajib diisi',
          'custom_title.required'  => 'Nama Menu Wajib diisi',
          'url_menu.required'  => 'URL Wajib diisi',
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
          'parent_id' => $request->parent_id,
          'custom_title' => $request->custom_title,
          'url_menu' => $request->url_menu,
          'slug' => $request->slug,
          'path_icon' => $request->path_icon,
          'tid' => $request->tid,
          'active' => $request->active,
          'created_by' => Auth::user()->username,
        ];

        if($request->parent_id == "top") {
          $data['parent_id'] = null;
        }

        $ok = Menu::create($data);

        ## Log Activity
        $desc = $ok ? 'Berhasil Tambah Data Menu' : 'Gagal Tambah Data Menu';
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
    $data = Menu::findOrFail($id);
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
    $data = Menu::query()->where('id', $id)->first()->toArray();

    $ok = Menu::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Menu' : 'Gagal Hapus Data Menu';
    LogActivity::saveLogActivity($desc, $data);
  }
}