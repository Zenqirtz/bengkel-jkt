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
use App\Models\Sparepart;
use App\Models\Parameter;
use App\Models\LogActivity;

use App\Helpers\Helpers as Helper;

class SparepartController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Sparepart(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Data Sparepart';

    $user_cabang = session('kd_cabang');
    $status_aktif = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();
    $satuan = Parameter::query()->where('nama_tabel', 'SATUAN')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.gudang.sparepart', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'status_aktif' => $status_aktif,
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
      1 => 's.id',
      2 => 's.nama_sparepart',
      3 => 'sa.keterangan',
      // 4 => 's.price',
      4 => 'st.keterangan',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 's.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('m_sparepart as s')
    ->leftJoin('parameter as sa', function ($join) {
      $join->on('sa.kode', '=', 's.kode_satuan')
          ->where('sa.nama_tabel', '=', 'SATUAN');
    })
    ->leftJoin('parameter as st', function ($join) {
      $join->on('st.kode', '=', 's.is_active')
          ->where('st.nama_tabel', '=', 'STATUS');
    })
    ->where('s.kode_cabang', $user_cabang);

    // Total baris tanpa filter
    $totalData = (clone $base)->count('s.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('s.id', 'like', "%{$search}%")
    //           ->orWhere('s.nama_sparepart', 'like', "%{$search}%")
    //           ->orWhere('sa.keterangan', 'like', "%{$search}%")
    //           ->orWhere('st.keterangan', 'like', "%{$search}%");
    //     });
    // }
    if ($request->filled('nama_sparepart')) {
      $query->where('s.nama_sparepart', 'like', '%' . $request->nama_sparepart . '%');
    }
    if ($request->filled('kode_satuan')) {
      if ($request->kode_satuan <> 'all') {
        $query->where('s.kode_satuan', 'like', '%' . $request->kode_satuan . '%');
      }
    }
    if ($request->filled('is_active')) {
      if ($request->is_active <> 'all') {
        $query->where('s.is_active', 'like', '%' . $request->is_active . '%');
      }
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('s.id');

    // Ambil data halaman saat ini
    $datas = $query
      ->select([
          's.id',
          's.nama_sparepart',
          's.kode_satuan',
          'sa.keterangan as satuan',
          's.price',
          's.is_active',
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
            'id'               => $row->id,
            'fake_id'          => ++$fake,
            'nama_sparepart'   => $row->nama_sparepart,
            'kode_satuan'      => $row->kode_satuan,
            'satuan'           => $row->satuan,
            'price'            => number_format($row->price, 2, ".", ","),
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
        'nama_sparepart' => [
            'required',
            'string',
            'max:50',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_sparepart', 'nama_sparepart')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
                })
                ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        // 'kode_satuan' => 'required',
        'is_active' => 'required',
      ];
  
      $messages = [
        'nama_sparepart.required' => 'Nama Sparepart Wajib diisi',
        'nama_sparepart.unique' => 'Nama Sparepart sudah digunakan',
        // 'kode_satuan.required' => 'Satuan Wajib diisi',
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
        'nama_sparepart' => $request->nama_sparepart,
        'kode_satuan' => $request->kode_satuan,
        // 'price' => blank($request->price) ? 0 : str_replace([","], "", $request->price),
        'is_active' => $request->is_active,
        'updated_by' => Auth::user()->username
      ];

      $ok = Sparepart::updateOrCreate(
        ['id' => $dataID],
        $data
      );

      ## Log Activity
      $desc = $ok ? 'Berhasil Ubah Data Sparepart' : 'Gagal Ubah Data Sparepart';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } else {
      $rules = [
        'nama_sparepart' => [
            'required',
            'string',
            'max:50',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_sparepart', 'nama_sparepart')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
                })
                ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        // 'kode_satuan' => 'required',
        'is_active' => 'required',
      ];
  
      $messages = [
        'nama_sparepart.required' => 'Nama Sparepart Wajib diisi',
        'nama_sparepart.unique' => 'Nama Sparepart sudah digunakan',
        // 'kode_satuan.required' => 'Satuan Wajib diisi',
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

      $lastNum = Sparepart::query()->where('kode_cabang', $request->kode_cabang)->max(DB::raw('CAST(kode_sparepart AS UNSIGNED)')) ?? 0;
      $kode = sprintf('%05d', $lastNum + 1);

      $data = [
        'kode_sparepart' => $kode,
        'kode_cabang' => $request->kode_cabang,
        'nama_sparepart' => $request->nama_sparepart,
        'kode_satuan' => $request->kode_satuan,
        // 'price' => blank($request->price) ? 0 : str_replace([","], "", $request->price),
        'is_active' => $request->is_active,
        'created_by' => Auth::user()->username
      ];

      $ok = Sparepart::create($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil Tambah Data Sparepart' : 'Gagal Tambah Data Sparepart';
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
    $data = Sparepart::findOrFail($id);
    $data->price = number_format($data->price, 2, ".", ","); 
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
    $data = Sparepart::query()->where('id', $id)->first()?->toArray() ?? [];

    $ok = Sparepart::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Sparepart' : 'Gagal Hapus Data Sparepart';
    LogActivity::saveLogActivity($desc, $data);
  }
}