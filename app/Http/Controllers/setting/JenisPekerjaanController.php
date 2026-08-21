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
use App\Models\JenisPekerjaan;
use App\Models\Parameter;
use App\Models\LogActivity;
// use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class JenisPekerjaanController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function JenisPekerjaan(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Jenis Pekerjaan';

    $user_cabang = session('kd_cabang');
    $status_aktif = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.setting.jenis-pekerjaan', [
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
      2 => 'k.jenis_pekerjaan',
      3 => 'b.keterangan',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'k.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('m_jenis_pekerjaan as k')
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
    //           ->orWhere('k.jenis_pekerjaan', 'like', "%{$search}%")
    //           ->orWhere('b.keterangan', 'like', "%{$search}%"); // cari di nama posisi juga
    //     });
    // }
    if ($request->filled('jenis_pekerjaan')) {
      $query->where('k.jenis_pekerjaan', 'like', '%' . $request->jenis_pekerjaan . '%');
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
          'k.jenis_pekerjaan',
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
            'jenis_pekerjaan' => $row->jenis_pekerjaan,
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
        'jenis_pekerjaan' => [
          'required',
          'string',
          'max:50',
          // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
          Rule::unique('m_jenis_pekerjaan', 'jenis_pekerjaan')
              ->where(function ($query) use ($request) {
                return $query
                  ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
              })
              ->ignore($request->id), // Penting: Abaikan ID sendiri saat update
        ],
        'is_active' => 'required'
      ];
  
      $messages = [
        'jenis_pekerjaan.required' => 'Jenis Pekerjaan Wajib diisi',
        'jenis_pekerjaan.unique' => 'Jenis Pekerjaan sudah digunakan',
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
        'jenis_pekerjaan' => $request->jenis_pekerjaan,
        'is_active' => $request->is_active,
        'updated_by' => Auth::user()->username
      ];

      // update the value
      $ok = JenisPekerjaan::updateOrCreate(
        ['id' => $dataID],
        $data
      );

      ## Log Activity
      $desc = $ok ? 'Berhasil Ubah Data Jenis Pekerjaan' : 'Gagal Ubah Data Jenis Pekerjaan';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } else {
      $rules = [
        'jenis_pekerjaan' => [
          'required',
          'string',
          'max:50',
          // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
          Rule::unique('m_jenis_pekerjaan', 'jenis_pekerjaan')
              ->where(function ($query) use ($request) {
                return $query
                  ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
              })
              ->ignore($request->id), // Penting: Abaikan ID sendiri saat update
        ],
        'is_active' => 'required'
      ];
  
      $messages = [
        'jenis_pekerjaan.required' => 'Jenis Pekerjaan Wajib diisi',
        'jenis_pekerjaan.unique' => 'Jenis Pekerjaan sudah digunakan',
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

      $lastNum = JenisPekerjaan::query()->where('kode_cabang', $request->kode_cabang)->max(DB::raw('CAST(kode_jenis_pekerjaan AS UNSIGNED)')) ?? 0;
      $kode = sprintf('%05d', $lastNum + 1);

      $data = [
        'kode_cabang' => $request->kode_cabang,
        'kode_jenis_pekerjaan' => $kode,
        'jenis_pekerjaan' => $request->jenis_pekerjaan,
        'is_active' => $request->is_active,
        'created_by' => Auth::user()->username
      ];

      $ok = JenisPekerjaan::create($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil Tambah Data Jenis Pekerjaan' : 'Gagal Tambah Data Jenis Pekerjaan';
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
    $data = JenisPekerjaan::findOrFail($id);
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
    $data = JenisPekerjaan::query()->where('id', $id)->first()->toArray();

    $ok = JenisPekerjaan::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Jenis Pekerjaan' : 'Gagal Hapus Data Jenis Pekerjaan';
    LogActivity::saveLogActivity($desc, $data);
  }
}