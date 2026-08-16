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
use App\Models\Bahan;
use App\Models\Parameter;
use App\Models\LogActivity;

class BahanController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Bahan(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Data Bahan';

    $user_cabang = session('kd_cabang');
    $status_aktif = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();
    $group_bahan = Parameter::query()->where('nama_tabel', 'GROUP_BAHAN')->orderBy('no_urut', 'asc')->get();
    $satuan = Parameter::query()->where('nama_tabel', 'SATUAN')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.gudang.bahan', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'status_aktif' => $status_aktif,
      'group_bahan' => $group_bahan,
      'satuan' => $satuan
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
      1 => 'b.id',
      2 => 'b.nama_bahan',
      3 => 'gb.keterangan',
      4 => 'sb.keterangan',
      5 => 'sp.keterangan',
      // 6 => 'b.harga',
      // 7 => 'b.konversi',
      // 8 => 'b.harga_konversi',
      6 => 'st.keterangan',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'b.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('m_bahan as b')
    ->leftJoin('parameter as gb', function ($join) {
      $join->on('gb.kode', '=', 'b.kode_group_bahan')
          ->where('gb.nama_tabel', '=', 'GROUP_BAHAN');
    })
    ->leftJoin('parameter as sb', function ($join) {
      $join->on('sb.kode', '=', 'b.kode_satuan')
          ->where('sb.nama_tabel', '=', 'SATUAN');
    })
    ->leftJoin('parameter as sp', function ($join) {
      $join->on('sp.kode', '=', 'b.kode_satuan2')
          ->where('sp.nama_tabel', '=', 'SATUAN');
    })
    ->leftJoin('parameter as st', function ($join) {
      $join->on('st.kode', '=', 'b.is_active')
          ->where('st.nama_tabel', '=', 'STATUS');
    })
    ->where('b.kode_cabang', $user_cabang);

    // Total baris tanpa filter
    $totalData = (clone $base)->count('b.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('b.id', 'like', "%{$search}%")
    //           ->orWhere('b.nama_bahan', 'like', "%{$search}%")
    //           ->orWhere('gb.keterangan', 'like', "%{$search}%")
    //           ->orWhere('sb.keterangan', 'like', "%{$search}%")
    //           ->orWhere('sp.keterangan', 'like', "%{$search}%")
    //           ->orWhere('st.keterangan', 'like', "%{$search}%");
    //     });
    // }
    if ($request->filled('nama_bahan')) {
      $query->where('b.nama_bahan', 'like', '%' . $request->nama_bahan . '%');
    }
    if ($request->filled('kode_group_bahan')) {
      if ($request->kode_group_bahan <> 'all') {
        $query->where('b.kode_group_bahan', 'like', '%' . $request->kode_group_bahan . '%');
      }
    }
    if ($request->filled('kode_satuan')) {
      if ($request->kode_satuan <> 'all') {
        $query->where('b.kode_satuan', 'like', '%' . $request->kode_satuan . '%');
      }
    }
    if ($request->filled('kode_satuan2')) {
      if ($request->kode_satuan2 <> 'all') {
        $query->where('b.kode_satuan2', 'like', '%' . $request->kode_satuan2 . '%');
      }
    }
    if ($request->filled('is_active')) {
      if ($request->is_active <> 'all') {
        $query->where('b.is_active', 'like', '%' . $request->is_active . '%');
      }
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('b.id');

    // Ambil data halaman saat ini
    $datas = $query
      ->select([
          'b.id',
          'b.nama_bahan',
          'b.kode_group_bahan',
          'gb.keterangan as group_bahan',
          'b.kode_satuan',
          'sb.keterangan as satuan_beli',
          'b.kode_satuan2',
          'sp.keterangan as satuan_pakai',
          'b.harga',
          'b.konversi',
          'b.harga_konversi',
          'b.is_active',
          'st.keterangan as status_aktif',
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
            'id'              => $row->id,
            'fake_id'         => ++$fake,
            'nama_bahan'      => $row->nama_bahan,
            'kode_group_bahan' => $row->kode_group_bahan,
            'group_bahan'     => $row->group_bahan,
            'kode_satuan'     => $row->kode_satuan,
            'satuan_beli'     => $row->satuan_beli,
            'kode_satuan2'    => $row->kode_satuan2,
            'satuan_pakai'    => $row->satuan_pakai,
            'harga'           => number_format($row->harga, 2, ".", ","),
            'konversi'        => number_format($row->konversi, 2, ".", ","),
            'harga_konversi'  => number_format($row->harga_konversi, 2, ".", ","),
            'is_active'       => $row->is_active,
            'status_aktif'    => $row->status_aktif,
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
        'nama_bahan' => [
            'required',
            'string',
            'max:50',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_bahan', 'nama_bahan')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
                })
                ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'kode_group_bahan' => 'required',
        'is_active' => 'required',
      ];
  
      $messages = [
        'nama_bahan.required' => 'Nama Bahan Wajib diisi',
        'nama_bahan.unique' => 'Nama Bahan sudah digunakan',
        'kode_group_bahan.required' => 'Group Bahan Wajib diisi',
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
        'nama_bahan' => $request->nama_bahan,
        'kode_group_bahan' => $request->kode_group_bahan,
        'kode_satuan' => $request->kode_satuan,
        'kode_satuan2' => $request->kode_satuan2,
        // 'harga' => blank($request->harga) ? 0 : str_replace([","], "", $request->harga),
        // 'konversi' => blank($request->konversi) ? 0 : str_replace([","], "", $request->konversi),
        // 'harga_konversi' => blank($request->harga_konversi) ? 0 : str_replace([","], "", $request->harga_konversi),
        'is_active' => $request->is_active,
        'updated_by' => Auth::user()->username
      ];

      $ok = Bahan::updateOrCreate(
        ['id' => $dataID],
        $data
      );

      ## Log Activity
      $desc = $ok ? 'Berhasil Ubah Data Bahan' : 'Gagal Ubah Data Bahan';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } else {
      $rules = [
        'nama_bahan' => [
            'required',
            'string',
            'max:50',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_bahan', 'nama_bahan')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
                })
                ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'kode_group_bahan' => 'required',
        'is_active' => 'required',
      ];
  
      $messages = [
        'nama_bahan.required' => 'Nama Bahan Wajib diisi',
        'nama_bahan.unique' => 'Nama Bahan sudah digunakan',
        'kode_group_bahan.required' => 'Group Bahan Wajib diisi',
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

      $lastNum = Bahan::query()->where('kode_cabang', $request->kode_cabang)->max(DB::raw('CAST(SUBSTRING(kode_bahan, 2) AS UNSIGNED)')) ?? 0;
      $kode = sprintf('B%04d', $lastNum + 1);

      $data = [
        'kode_bahan' => $kode,
        'kode_cabang' => $request->kode_cabang,
        'nama_bahan' => $request->nama_bahan,
        'kode_group_bahan' => $request->kode_group_bahan,
        'kode_satuan' => $request->kode_satuan,
        'kode_satuan2' => $request->kode_satuan2,
        // 'harga' => blank($request->harga) ? 0 : str_replace([","], "", $request->harga),
        // 'konversi' => blank($request->konversi) ? 0 : str_replace([","], "", $request->konversi),
        // 'harga_konversi' => blank($request->harga_konversi) ? 0 : str_replace([","], "", $request->harga_konversi),
        'is_active' => $request->is_active,
        'created_by' => Auth::user()->username
      ];

      $ok = Bahan::create($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil Tambah Data Bahan' : 'Gagal Tambah Data Bahan';
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
    $data = Bahan::findOrFail($id);
    $data->harga = number_format($data->harga, 2, ".", ","); 
    $data->konversi = number_format($data->konversi, 2, ".", ",");
    $data->harga_konversi = number_format($data->harga_konversi, 2, ".", ",");
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
    $data = Bahan::query()->where('id', $id)->first()->toArray();

    $ok = Bahan::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Bahan' : 'Gagal Hapus Data Bahan';
    LogActivity::saveLogActivity($desc, $data);
  }
}