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
use App\Models\Marketing;
use App\Models\Parameter;
use App\Models\LogActivity;
// use Carbon\Carbon;

class MarketingController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Marketing(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Marketing';

    $user_cabang = session('kd_cabang');
    $status_aktif = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.customer-service.marketing', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'status_aktif' => $status_aktif
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
      2 => 'k.nama',
      3 => 'k.no_hp',
      4 => 'b.keterangan',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'k.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('m_karyawan as k')
    ->leftJoin('parameter as b', function ($join) {
      $join->on('b.kode', '=', 'k.status_aktif')
          ->where('b.nama_tabel', '=', 'STATUS'); // syarat di JOIN
    })
    ->where('k.kode_jabatan', '00006')
    ->where('k.kode_cabang', $user_cabang);

    // Total baris tanpa filter
    $totalData = (clone $base)->count('k.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('k.id', 'like', "%{$search}%")
    //           ->orWhere('k.nama_marketing', 'like', "%{$search}%")
    //           ->orWhere('k.no_identitas', 'like', "%{$search}%")
    //           ->orWhere('k.tipe_marketing', 'like', "%{$search}%")
    //           ->orWhere('b.keterangan', 'like', "%{$search}%");
    //     });
    // }
    if ($request->filled('nama_marketing')) {
      $query->where('k.nama', 'like', '%' . $request->nama_marketing . '%');
    }
    if ($request->filled('telepon')) {
      $query->where('k.no_hp', 'like', '%' . $request->telepon . '%');
    }
    if ($request->filled('is_active')) {
      if ($request->is_active <> 'all') {
        $query->where('k.status_aktif', 'like', '%' . $request->is_active . '%');
      }
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('k.id');

    // Ambil data halaman saat ini
    $datas = $query
      ->select([
          'k.id',
          'k.nama',
          'k.no_hp',
          'k.status_aktif as is_active',
          'b.keterangan as status_aktif',
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
            'nama_marketing'  => $row->nama,
            'telepon'         => $row->no_hp,
            'is_active'       => $row->is_active,
            'status_aktif'    => $row->status_aktif,
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
        'nama_marketing' => [
            'required',
            'string',
            'max:50',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_karyawan', 'nama')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang)   // Cek Kolom 2
                    ->where('kode_jabatan', '00006');   // Cek Kolom 3
                })
                ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'is_active' => 'required',
        'telepon' => 'required',
      ];
  
      $messages = [
        'nama_marketing.required' => 'Nama Marketing Wajib diisi',
        'nama_marketing.unique' => 'Nama Marketing sudah digunakan',
        'is_active.required'  => 'Status Aktif Wajib diisi',
        'telepon.required'  => 'Telepon Selular Wajib diisi',
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
        // 'kode_cabang' => $request->kode_cabang,
        'nama' => $request->nama_marketing,
        'no_hp' => $request->telepon,
        // 'kode_jabatan' => '00006',
        'status_aktif' => $request->is_active,
        'updated_by' => Auth::user()->username
      ];

      // update the value
      $ok = Marketing::updateOrCreate(
        ['id' => $dataID],
        $data
      );

      ## Log Activity
      $desc = $ok ? 'Berhasil ubah Data Marketing.' : 'Gagal ubah Data Marketing.';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } else {
      $rules = [
        'nama_marketing' => [
            'required',
            'string',
            'max:50',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_karyawan', 'nama')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang)   // Cek Kolom 2
                    ->where('kode_jabatan', '00006');   // Cek Kolom 3
                })
                ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'is_active' => 'required',
        'telepon' => 'required',
      ];
  
      $messages = [
        'nama_marketing.required' => 'Nama Marketing Wajib diisi',
        'nama_marketing.unique' => 'Nama Marketing sudah digunakan',
        'is_active.required'  => 'Status Aktif Wajib diisi',
        'telepon.required'  => 'Telepon Selular Wajib diisi',
      ];
  
      $validator = Validator::make($request->all(), $rules, $messages);
  
      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ]);
      }

      $lastNum = Marketing::query()->where('kode_cabang', $request->kode_cabang)->max(DB::raw('CAST(kode_karyawan AS UNSIGNED)')) ?? 0;
      $kode = sprintf('%05d', $lastNum + 1);

      $data = [
        'kode_karyawan' => $kode,
        'kode_cabang' => $request->kode_cabang,
        'nama' => $request->nama_marketing,
        'no_hp' => $request->telepon,
        'kode_jabatan' => '00006',
        'status_aktif' => $request->is_active,
        'created_by' => Auth::user()->username
      ];

      $ok = Marketing::create($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil tambah Data Marketing.' : 'Gagal tambah Data Marketing.';
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
    $data = Marketing::findOrFail($id);
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
    $data = Marketing::query()->where('id', $id)->first()->toArray();

    $ok = Marketing::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Marketing.' : 'Gagal Hapus Data Marketing.';
    LogActivity::saveLogActivity($desc, $data);
  }
}