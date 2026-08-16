<?php

namespace App\Http\Controllers\setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\PanelPekerjaan;
use App\Models\Parameter;
use App\Models\LogActivity;
// use Carbon\Carbon;

class PanelPekerjaanController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function PanelPekerjaan(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Panel Pekerjaan';

    $user_cabang = session('kd_cabang');
    $status_aktif = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.setting.panel-pekerjaan', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
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
    $user_cabang = session('kd_cabang');
    
    $columns = [
      1 => 'k.id',
      2 => 'k.panel_pekerjaan',
      3 => 'b.keterangan',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'k.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('m_panel_pekerjaan as k')
        ->leftJoin('parameter as b', function ($join) {
          $join->on('b.kode', '=', 'k.is_active')
              ->where('b.nama_tabel', '=', 'STATUS'); // syarat di JOIN
        })
        ->where('k.kode_cabang', $user_cabang);

    // Total baris tanpa filter
    $totalData = (clone $base)->count('k.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('k.id', 'like', "%{$search}%")
    //           ->orWhere('k.panel_pekerjaan', 'like', "%{$search}%")
    //           ->orWhere('b.keterangan', 'like', "%{$search}%"); // cari di nama posisi juga
    //     });
    // }
    if ($request->filled('panel_pekerjaan')) {
      $query->where('k.panel_pekerjaan', 'like', '%' . $request->panel_pekerjaan . '%');
    }
    if ($request->filled('point')) {
      $query->where('k.point', 'like', '%' . $request->point . '%');
    }
    if ($request->filled('is_active')) {
      if ($request->is_active <> 'all') {
        $query->where('k.is_active', 'like', '%' . $request->is_active . '%');
      }
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('k.id');

    // Ambil data halaman saat ini
    $datas = $query
      ->select([
          'k.id',
          'k.panel_pekerjaan',
          'k.point',
          'k.no_panel',
          'k.harga',
          'k.is_active',
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
            'id' => $row->id,
            'fake_id' => ++$fake,
            'panel_pekerjaan' => $row->panel_pekerjaan,
            'point' => number_format($row->point, 6, ".", ","),
            'no_panel' => $row->no_panel,
            'harga' => number_format($row->harga, 2, '.', ','),
            'is_active' => $row->is_active,
            'status' => $row->status,
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
      $rules = [
        'panel_pekerjaan' => [
          'required',
          'string',
          'max:50',
          // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
          Rule::unique('m_panel_pekerjaan', 'panel_pekerjaan')
              ->where(function ($query) use ($request) {
                return $query
                  ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
              })
              ->ignore($request->id), // Penting: Abaikan ID sendiri saat update
        ],
        'is_active' => 'required'
      ];
  
      $messages = [
        'panel_pekerjaan.required' => 'Panel Pekerjaan Wajib diisi',
        'panel_pekerjaan.unique' => 'Panel Pekerjaan sudah digunakan',
        'is_active.required'  => 'Status Aktif  Wajib diisi',
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
        'kode_cabang' => $request->kode_cabang,
        'panel_pekerjaan' => $request->panel_pekerjaan,
        'point' => $request->point,
        'no_panel' => $request->no_panel,
        'harga' => blank($request->harga) ? 0 : str_replace([","], "", $request->harga),
        'is_active' => $request->is_active,
        'updated_by' => Auth::user()->username
      ];

      // update the value
      $ok = PanelPekerjaan::updateOrCreate(
        ['id' => $dataID],
        $data
      );

      ## Log Activity
      $desc = $ok ? 'Berhasil Ubah Data Panel Pekerjaan' : 'Gagal Ubah Data Panel Pekerjaan';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } else {
      $rules = [
        'panel_pekerjaan' => [
          'required',
          'string',
          'max:50',
          // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
          Rule::unique('m_panel_pekerjaan', 'panel_pekerjaan')
              ->where(function ($query) use ($request) {
                return $query
                  ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
              })
              ->ignore($request->id), // Penting: Abaikan ID sendiri saat update
        ],
        'is_active' => 'required'
      ];
  
      $messages = [
        'panel_pekerjaan.required' => 'Panel Pekerjaan Wajib diisi',
        'panel_pekerjaan.unique' => 'Panel Pekerjaan sudah digunakan',
        'is_active.required'  => 'Status Aktif  Wajib diisi',
      ];
  
      $validator = Validator::make($request->all(), $rules, $messages);
  
      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ]);
      }

      $lastNum = PanelPekerjaan::query()->where('kode_cabang', $request->kode_cabang)->max(DB::raw('CAST(kode_panel_pekerjaan AS UNSIGNED)')) ?? 0;
      $kode = sprintf('P%04d', $lastNum + 1);

      $data = [
        'kode_cabang' => $request->kode_cabang,
        'kode_panel_pekerjaan' => $kode,
        'panel_pekerjaan' => $request->panel_pekerjaan,
        'point' => $request->point,
        'no_panel' => $request->no_panel,
        'harga' => blank($request->harga) ? 0 : str_replace([","], "", $request->harga),
        'is_active' => $request->is_active,
        'created_by' => Auth::user()->username
      ];

      $ok = PanelPekerjaan::create($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil Tambah Data Panel Pekerjaan' : 'Gagal Tambah Data Panel Pekerjaan';
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
    $data = PanelPekerjaan::findOrFail($id);
    $data->harga = number_format($data->harga, 2, '.', ',');
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
    $data = PanelPekerjaan::query()->where('id', $id)->first()->toArray();

    $ok = PanelPekerjaan::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Panel Pekerjaan' : 'Gagal Hapus Data Panel Pekerjaan';
    LogActivity::saveLogActivity($desc, $data);
  }
}