<?php

namespace App\Http\Controllers\customer_service;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Perantara;
use App\Models\Parameter;
use App\Models\LogActivity;
// use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class PerantaraController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Perantara(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Perantara';

    $user_cabang = session('kd_cabang');
    $status_aktif = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();
    $status_perantara = Parameter::query()->where('nama_tabel', 'STATUS_PERANTARA')->orderBy('no_urut', 'asc')->get();
    $jenis_perantara = Parameter::query()->where('nama_tabel', 'JENIS_PERANTARA')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.customer-service.perantara', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'status_perantara' => $status_perantara,
      'status_aktif' => $status_aktif,
      'jenis_perantara' => $jenis_perantara,
    ]);
  }

  /**
   * Display a listing of the resource.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(Request $request): JsonResponse
  {
    $user_cabang = session('kd_cabang');
    
    $columns = [
      1 => 'k.id',
      2 => 'k.nama_perantara',
      3 => 'c.keterangan',
      4 => 'd.keterangan',
      5 => 'k.telepon',
      6 => 'b.keterangan',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'k.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('m_perantara_hdr as k')
        ->leftJoin('parameter as b', function ($join) {
          $join->on('b.kode', '=', 'k.is_active')
              ->where('b.nama_tabel', '=', 'STATUS'); // syarat di JOIN
        })
        ->leftJoin('parameter as c', function ($join) {
          $join->on('c.kode', '=', 'k.kode_jenis_perantara')
              ->where('c.nama_tabel', '=', 'JENIS_PERANTARA'); // syarat di JOIN
        })
        ->leftJoin('parameter as d', function ($join) {
          $join->on('d.kode', '=', 'k.status')
              ->where('d.nama_tabel', '=', 'STATUS_PERANTARA'); // syarat di JOIN
        })
        ->where('k.kode_cabang', $user_cabang);

    // Total baris tanpa filter
    $totalData = (clone $base)->count('k.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('k.id', 'like', "%{$search}%")
    //           ->orWhere('k.nama_perantara', 'like', "%{$search}%")
    //           ->orWhere('k.telepon', 'like', "%{$search}%")
    //           ->orWhere('c.keterangan', 'like', "%{$search}%")
    //           ->orWhere('b.keterangan', 'like', "%{$search}%");
    //     });
    // }
    if ($request->filled('nama_perantara')) {
      $query->where('k.nama_perantara', 'like', '%' . $request->nama_perantara . '%');
    }
    if ($request->filled('telepon')) {
      $query->where('k.telepon', 'like', '%' . $request->telepon . '%');
    }
    if ($request->filled('kode_jenis_perantara')) {
      if ($request->kode_jenis_perantara <> 'all') {
        $query->where('k.kode_jenis_perantara', 'like', '%' . $request->kode_jenis_perantara . '%');
      }
    }
    if ($request->filled('status')) {
      if ($request->status <> 'all') {
        $query->where('k.status', 'like', '%' . $request->status . '%');
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
          'k.nama_perantara',
          'k.kode_jenis_perantara',
          'k.telepon',
          'k.is_active',
          'b.keterangan as status_aktif',
          'c.keterangan as jenis_perantara',
          'd.keterangan as status_perantara',
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
            'nama_perantara'  => $row->nama_perantara,
            'kode_jenis_perantara' => $row->kode_jenis_perantara,
            'telepon'         => $row->telepon,
            'is_active'       => $row->is_active,
            'status_aktif'    => $row->status_aktif,
            'jenis_perantara' => $row->jenis_perantara,
            'status_perantara' => $row->status_perantara,
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
        'nama_perantara' => [
            'required',
            'string',
            'max:50',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_perantara_hdr', 'nama_perantara')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
                })
                ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'kode_jenis_perantara' => 'required',
        'is_active' => 'required',
        'telepon' => ['required', 'max:15'],
      ];
  
      $messages = [
        'nama_perantara.required' => 'Nama Perantara Wajib diisi',
        'nama_perantara.unique' => 'Nama Perantara sudah digunakan',
        'kode_jenis_perantara.required' => 'Jenis Pemilik Wajib diisi',
        'telepon.required'  => 'Telepon Selular Wajib diisi',
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

      // update the value
      $data = [
        'kode_cabang' => $request->kode_cabang,
        'nama_perantara' => $request->nama_perantara,
        'kode_jenis_perantara' => $request->kode_jenis_perantara,
        'telepon' => $request->telepon,
        'is_active' => $request->is_active,
        'status' => $request->status,
        'updated_by' => Auth::user()->username
      ];

      $ok = Perantara::updateOrCreate(
        ['id' => $dataID],
        $data
      );

      ## Log Activity
      $desc = $ok ? 'Berhasil ubah Data Perantara.' : 'Gagal ubah Data Perantara.';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);

    } else {
      $rules = [
        'nama_perantara' => [
            'required',
            'string',
            'max:50',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_perantara_hdr', 'nama_perantara')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
                })
                ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'kode_jenis_perantara' => 'required',
        'is_active' => 'required',
        'telepon' => ['required', 'max:15'],
      ];
  
      $messages = [
        'nama_perantara.required' => 'Nama Perantara Wajib diisi',
        'nama_perantara.unique' => 'Nama Perantara sudah digunakan',
        'kode_jenis_perantara.required' => 'Jenis Pemilik Wajib diisi',
        'telepon.required'  => 'Telepon Selular Wajib diisi',
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

      $lastNum = Perantara::query()->where('kode_cabang', $request->kode_cabang)->max(DB::raw('CAST(SUBSTRING(kode_perantara, 2) AS UNSIGNED)')) ?? 0;
      $kode = sprintf('P%05d', $lastNum + 1);

      $data = [
        'kode_perantara' => $kode,
        'kode_cabang' => $request->kode_cabang,
        'nama_perantara' => $request->nama_perantara,
        'kode_jenis_perantara' => $request->kode_jenis_perantara,
        'telepon' => $request->telepon,
        'is_active' => $request->is_active,
        'status' => $request->status,
        'created_by' => Auth::user()->username
      ];

      $ok = Perantara::create($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil tambah Data Perantara.' : 'Gagal tambah Data Perantara.';
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
    $data = Perantara::findOrFail($id);
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
    $data = Perantara::query()->where('id', $id)->first()?->toArray() ?? [];

    $ok = Perantara::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Perantara.' : 'Gagal Hapus Data Perantara.';
    LogActivity::saveLogActivity($desc, $data);
  }
}