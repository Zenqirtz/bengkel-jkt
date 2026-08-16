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
use App\Models\TipeKendaraan;
use App\Models\MerekKendaraan;
use App\Models\JenisKendaraan;
use App\Models\Parameter;
use App\Models\LogActivity;

class TipeKendaraanController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function TipeKendaraan(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Tipe Kendaraan';

    $status_aktif = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();
    $merek_kendaraan = MerekKendaraan::query()->where('is_active', 'Y')->orderBy('nama_merek', 'asc')->get();
    $jenis_kendaraan = JenisKendaraan::query()->where('is_active', 'Y')->orderBy('nama_jenis', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.setting.tipe-kendaraan', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'status_aktif' => $status_aktif,
      'merek_kendaraan' => $merek_kendaraan,
      'jenis_kendaraan' => $jenis_kendaraan,
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
      1 => 'k.id',
      2 => 'm.nama_merek',
      3 => 'j.nama_jenis',
      4 => 'k.nama_tipe',
      5 => 'b.keterangan',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'k.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('m_tipe_kendaraan as k')
        ->leftJoin('m_merek_kendaraan as m', 'm.kode_merek', '=', 'k.kode_merek')
        ->leftJoin('m_jenis_kendaraan as j', 'j.kode_jenis', '=', 'k.kode_jenis')
        ->leftJoin('parameter as b', function ($join) {
          $join->on('b.kode', '=', 'k.is_active')
              ->where('b.nama_tabel', '=', 'STATUS');
        });

    // Total baris tanpa filter
    $totalData = (clone $base)->count('k.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('k.id', 'like', "%{$search}%")
    //           ->orWhere('m.nama_merek', 'like', "%{$search}%")
    //           ->orWhere('j.nama_jenis', 'like', "%{$search}%")
    //           ->orWhere('k.nama_tipe', 'like', "%{$search}%")
    //           ->orWhere('b.keterangan', 'like', "%{$search}%");
    //     });
    // }
    if ($request->filled('nama_tipe')) {
      $query->where('k.nama_tipe', 'like', '%' . $request->nama_tipe . '%');
    }
    if ($request->filled('kode_jenis')) {
      if ($request->kode_jenis <> 'all') {
        $query->where('k.kode_jenis', 'like', '%' . $request->kode_jenis . '%');
      }
    }
    if ($request->filled('kode_merek')) {
      if ($request->kode_merek <> 'all') {
        $query->where('k.kode_merek', 'like', '%' . $request->kode_merek . '%');
      }
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
          'k.kode_merek',
          'k.kode_jenis',
          'k.nama_tipe',
          'k.is_active',
          'm.nama_merek',
          'j.nama_jenis',
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
            'kode_merek' => $row->kode_merek,
            'kode_jenis' => $row->kode_jenis,
            'nama_merek' => $row->nama_merek,
            'nama_jenis' => $row->nama_jenis,
            'nama_tipe' => $row->nama_tipe,
            'is_active' => $row->is_active,
            'status' => $row->status,
        ];
    }

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
      $rules = [
        'nama_tipe' => 'required|string|max:30|unique:m_tipe_kendaraan,nama_tipe,'.$request->id,
        'is_active'  => 'required',
        'kode_merek'  => 'required',
        'kode_jenis'  => 'required',
      ];
  
      $messages = [
        'nama_tipe.required' => 'Tipe Kendaraan Wajib diisi',
        'nama_tipe.unique' => 'Tipe Kendaraan sudah digunakan',
        'is_active.required'  => 'Status Aktif Wajib diisi',
        'kode_merek.required'  => 'Merek Kendaraan Wajib diisi',
        'kode_jenis.required'  => 'Jenis Kendaraan Wajib diisi',
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
        'kode_merek' => $request->kode_merek,
        'kode_jenis' => $request->kode_jenis,
        'nama_tipe' => $request->nama_tipe,
        'is_active' => $request->is_active,
        'updated_by' => Auth::user()->username
      ];

      $ok = TipeKendaraan::updateOrCreate(
        ['id' => $dataID],
        $data
      );

      ## Log Activity
      $desc = $ok ? 'Berhasil Ubah Data Tipe Kendaraan' : 'Gagal Ubah Data Tipe Kendaraan';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } else {
      $rules = [
        'nama_tipe' => 'required|string|max:30|unique:m_tipe_kendaraan,nama_tipe',
        'is_active'  => 'required',
        'kode_merek'  => 'required',
        'kode_jenis'  => 'required',
      ];
  
      $messages = [
        'nama_tipe.required' => 'Tipe Kendaraan Wajib diisi',
        'nama_tipe.unique' => 'Tipe Kendaraan sudah digunakan',
        'is_active.required'  => 'Status Aktif Wajib diisi',
        'kode_merek.required'  => 'Merek Kendaraan Wajib diisi',
        'kode_jenis.required'  => 'Jenis Kendaraan Wajib diisi',
      ];
  
      $validator = Validator::make($request->all(), $rules, $messages);
  
      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ]);
      }

      $lastNum = TipeKendaraan::query()->max(DB::raw('CAST(kode_tipe AS UNSIGNED)')) ?? 0;
      $kode = sprintf('%05d', $lastNum + 1);

      $data = [
        'kode_tipe' => $kode,
        'kode_merek' => $request->kode_merek,
        'kode_jenis' => $request->kode_jenis,
        'nama_tipe' => $request->nama_tipe,
        'is_active' => $request->is_active,
        'created_by' => Auth::user()->username
      ];

      $ok = TipeKendaraan::create($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil Tambah Data Tipe Kendaraan' : 'Gagal Tambah Data Tipe Kendaraan';
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
    $data = TipeKendaraan::findOrFail($id);
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
    $data = TipeKendaraan::query()->where('id', $id)->first()->toArray();

    $ok = TipeKendaraan::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Tipe Kendaraan' : 'Gagal Hapus Data Tipe Kendaraan';
    LogActivity::saveLogActivity($desc, $data);
  }
}
