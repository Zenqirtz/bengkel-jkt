<?php

namespace App\Http\Controllers\gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Cat;
use App\Models\Bahan;
use App\Models\LogActivity;

use App\Helpers\Helpers as Helper;

class CatController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Cat(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Data Cat';

    $user_cabang = session('kd_cabang');

    // Ambil list bahan cat dengan filter kode_group_bahan = '00002'
    $list_bahan = Bahan::query()
        ->where('kode_cabang', $user_cabang)
        ->where('kode_group_bahan', '00002')
        ->where('is_active', 'Y')
        ->orderBy('nama_bahan', 'asc')
        ->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.gudang.cat', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'list_bahan' => $list_bahan
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
      1 => 'c.id',
      2 => 'b.nama_bahan',
      3 => 'c.jenis',
      4 => 'c.rasio',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'c.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query with LEFT JOIN to bahan table
    $base = DB::table('m_rasio_cat as c')
        ->leftJoin('m_bahan as b', function($join) use ($user_cabang) {
          $join->on('b.kode_bahan', '=', 'c.kode_bahan')
               ->where('b.kode_cabang', '=', $user_cabang);
        })
        ->where('c.kode_cabang', $user_cabang);

    // Total baris tanpa filter
    $totalData = (clone $base)->count('c.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('c.kode_bahan', 'like', "%{$search}%")
    //           ->orWhere('b.nama_bahan', 'like', "%{$search}%")
    //           ->orWhere('c.jenis', 'like', "%{$search}%")
    //           ->orWhere('c.rasio', 'like', "%{$search}%");
    //     });
    // }
    if ($request->filled('nama_bahan')) {
      $query->where('b.nama_bahan', 'like', '%' . $request->nama_bahan . '%');
    }
    if ($request->filled('jenis')) {
      $query->where('c.jenis', 'like', '%' . $request->jenis . '%');
    }
    if ($request->filled('rasio')) {
      $query->where('c.rasio', 'like', '%' . $request->rasio . '%');
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('c.id');

    // Ambil data halaman saat ini
    $datas = $query
      ->select([
          'c.id',
          'c.kode_bahan',
          'b.nama_bahan',
          'c.jenis',
          'c.rasio',
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
            'kode_bahan'  => $row->kode_bahan,
            'nama_bahan'  => $row->nama_bahan ?? $row->kode_bahan,
            'jenis'       => $row->jenis,
            'rasio'       => number_format($row->rasio, 2, ".", ","),
        ];
    }

    // Always return full DataTables structure, even if no results
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
        'kode_bahan' => [
            'required',
            'string',
            'max:50',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_rasio_cat', 'kode_bahan')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
                })
                ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'rasio' => 'required',
      ];
  
      $messages = [
        'kode_bahan.required' => 'Nama Cat Wajib diisi',
        'kode_bahan.unique' => 'Nama Cat sudah digunakan',
        'rasio.required'  => 'Rasio Wajib diisi',
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
        'kode_bahan' => $request->kode_bahan,
        'jenis' => $request->jenis,
        'rasio' => $request->rasio,
        'updated_by' => Auth::user()->username
      ];

      $ok = Cat::updateOrCreate(
        ['id' => $dataID],
        $data
      );

      ## Log Activity
      $desc = $ok ? 'Berhasil Ubah Data Rasio Cat' : 'Gagal Ubah Data Rasio Cat';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } else {
      $rules = [
        'kode_bahan' => [
            'required',
            'string',
            'max:50',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_rasio_cat', 'kode_bahan')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
                })
                ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'rasio' => 'required',
      ];
  
      $messages = [
        'kode_bahan.required' => 'Nama Cat Wajib diisi',
        'kode_bahan.unique' => 'Nama Cat sudah digunakan',
        'rasio.required'  => 'Rasio Wajib diisi',
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
        'kode_bahan' => $request->kode_bahan,
        'jenis' => $request->jenis,
        'rasio' => $request->rasio,
        'created_by' => Auth::user()->username
      ];

      $ok = Cat::create($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil Tambah Data Rasio Cat' : 'Gagal Tambah Data Rasio Cat';
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
    $data = Cat::findOrFail($id);
    $data->rasio = number_format($data->rasio, 2, ".", ",");
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
    $data = Cat::query()->where('id', $id)->first()?->toArray() ?? [];

    $ok = Cat::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Rasio Cat' : 'Gagal Hapus Data Rasio Cat';
    LogActivity::saveLogActivity($desc, $data);
  }
}