<?php

namespace App\Http\Controllers\gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Umum;
use App\Models\Parameter;
use App\Models\LogActivity;

use App\Helpers\Helpers as Helper;

class UmumController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Umum(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Data Umum';

    $user_cabang = session('kd_cabang');
    $status_aktif = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();
    $jenis = Parameter::query()->where('nama_tabel', 'JENIS_UMUM')->orderBy('no_urut', 'asc')->get();

    return view('content.gudang.umum', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'status_aktif' => $status_aktif,
      'jenis' => $jenis
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
      1 => 'a.id',
      2 => 'a.nama_barang',
      3 => 'b.keterangan',
      4 => 'a.price',
      5 => 'c.keterangan',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'a.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('m_umum as a')
        ->leftJoin('parameter as b', function ($join) {
          $join->on('b.kode', '=', 'a.kode_jenis')
              ->where('b.nama_tabel', '=', 'JENIS_UMUM');
        })
        ->leftJoin('parameter as c', function ($join) {
          $join->on('c.kode', '=', 'a.is_active')
              ->where('c.nama_tabel', '=', 'STATUS');
        })
        ->where('a.kode_cabang', $user_cabang);

    // Total baris tanpa filter
    $totalData = (clone $base)->count('a.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('a.nama_barang', 'like', "%{$search}%")
    //           ->orWhere('b.keterangan', 'like', "%{$search}%")
    //           ->orWhere('c.keterangan', 'like', "%{$search}%");
    //     });
    // }
    if ($request->filled('nama_barang')) {
      $query->where('a.nama_barang', 'like', '%' . $request->nama_barang . '%');
    }
    if ($request->filled('kode_jenis')) {
      if ($request->kode_jenis <> 'all') {
        $query->where('a.kode_jenis', 'like', '%' . $request->kode_jenis . '%');
      }
    }
    if ($request->filled('is_active')) {
      if ($request->is_active <> 'all') {
        $query->where('a.is_active', 'like', '%' . $request->is_active . '%');
      }
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('a.id');

    // Ambil data halaman saat ini
    $datas = $query
      ->select([
          'a.id',
          'a.nama_barang',
          'a.kode_jenis',
          'b.keterangan as jenis',
          'a.price',
          'a.is_active',
          'c.keterangan as status_aktif',
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
            'id'               => $row->id,
            'fake_id'          => ++$fake,
            'nama_barang'      => $row->nama_barang,
            'kode_jenis'       => $row->kode_jenis,
            'jenis'            => $row->jenis,
            'price'            => number_format($row->price, 0, ".", ","),
            'is_active'        => $row->is_active,
            'status_aktif'     => $row->status_aktif,
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
        'nama_barang' => [
            'required',
            'string',
            'max:50',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_umum', 'nama_barang')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
                })
                ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'kode_jenis' => 'required',
        'is_active' => 'required',
      ];
  
      $messages = [
        'nama_barang.required' => 'Nama Barang Wajib diisi',
        'nama_barang.unique' => 'Nama Barang sudah digunakan',
        'kode_satuan.required' => 'Jenis Wajib diisi',
        'is_active.required'  => 'Status Aktif Wajib diisi',
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
        'nama_barang' => $request->nama_barang,
        'kode_jenis' => $request->kode_jenis,
        // 'price' => blank($request->price) ? 0 : str_replace([","], "", $request->price),
        'is_active' => $request->is_active,
        'updated_by' => Auth::user()->username
      ];

      $ok = Umum::updateOrCreate(
        ['id' => $dataID],
        $data
      );

      ## Log Activity
      $desc = $ok ? 'Berhasil Ubah Data Barang Umum' : 'Gagal Ubah Data Barang Umum';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } else {
      $rules = [
        'nama_barang' => [
            'required',
            'string',
            'max:50',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_umum', 'nama_barang')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
                })
                ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'kode_jenis' => 'required',
        'is_active' => 'required',
      ];
  
      $messages = [
        'nama_barang.required' => 'Nama Barang Wajib diisi',
        'nama_barang.unique' => 'Nama Barang sudah digunakan',
        'kode_satuan.required' => 'Jenis Wajib diisi',
        'is_active.required'  => 'Status Aktif Wajib diisi',
      ];
  
      $validator = Validator::make($request->all(), $rules, $messages);
  
      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ]);
      }

      $lastNum = Umum::query()->where('kode_cabang', $request->kode_cabang)->max(DB::raw('CAST(kode_barang AS UNSIGNED)')) ?? 0;
      $kode = sprintf('%05d', $lastNum + 1);

      $data = [
        'kode_barang' => $kode,
        'kode_cabang' => $request->kode_cabang,
        'nama_barang' => $request->nama_barang,
        'kode_jenis' => $request->kode_jenis,
        // 'price' => blank($request->price) ? 0 : str_replace([","], "", $request->price),
        'is_active' => $request->is_active,
        'created_by' => Auth::user()->username
      ];

      $ok = Umum::create($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil Tambah Data Barang Umum' : 'Gagal Tambah Data Barang Umum';
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
    $data = Umum::findOrFail($id);
    $data->price = number_format($data->price, 0, ".", ",");
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
    $data = Umum::query()->where('id', $id)->first()->toArray();

    $ok = Umum::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Barang Umum' : 'Gagal Hapus Data Barang Umum';
    LogActivity::saveLogActivity($desc, $data);
  }
}