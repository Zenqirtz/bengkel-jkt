<?php

namespace App\Http\Controllers\setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Parameter;
use App\Models\LogActivity;
use App\Models\Coa;
// use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class CoaController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Coa(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Chart of Accounts';

    $status_aktif = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();
    $coa_tipe = Parameter::query()->where('nama_tabel', 'COA_TIPE')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.setting.coa', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'status_aktif' => $status_aktif,
      'coa_tipe' => $coa_tipe,
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
      1 => 'a.id',
      2 => 'a.nama_cabang',
      3 => 'a.alamat1',
      4 => 'a.kode_pos',
      5 => 'a.npwp',
      6 => 'a.telepon',
      7 => 'a.fax',
      8 => 'b.keterangan',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'a.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('m_coa as a')
    ->leftJoin('parameter as b', function ($join) {
      $join->on('b.kode', '=', 'a.active_status')
          ->where('b.nama_tabel', '=', 'STATUS'); // syarat di JOIN
    });

    // Total baris tanpa filter
    $totalData = (clone $base)->count('a.id');

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
    if ($request->filled('acct_cd')) {
      $query->where('a.acct_cd', 'like', '%' . $request->acct_cd . '%');
    }
    if ($request->filled('descs')) {
      $query->where('a.descs', 'like', '%' . $request->descs . '%');
    }
    if ($request->filled('active_status')) {
      if ($request->active_status <> 'all') {
        $query->where('a.active_status', 'like', '%' . $request->active_status . '%');
      }
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('a.id');

    // Ambil data halaman saat ini
    $datas = $query
    ->select([
        'a.id',
        'a.acct_cd',
        'a.descs',
        'a.class_cd',
        'a.ilevel',
        'a.seq_no',
        'a.acct_type',
        'a.active_status',
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
            'id'            => $row->id,
            'fake_id'       => ++$fake,
            'acct_cd'       => $row->acct_cd,
            'descs'         => $row->descs,
            'class_cd'      => $row->class_cd,
            'ilevel'        => $row->ilevel,
            'seq_no'        => $row->seq_no,
            'acct_type'     => $row->acct_type,
            'active_status' => $row->active_status,
            'status_aktif'  => $row->status_aktif,
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
        'acct_cd'       => 'required|max:10|unique:m_coa,acct_cd,'.$dataID,
        'descs'         => 'required',
        'active_status' => 'required',
      ];
  
      $messages = [
        'acct_cd.required' => 'Kode Akun Wajib diisi',
        'acct_cd.unique' => 'Kode Akun sudah digunakan',
        'descs.required'  => 'Nama Akun Wajib diisi',
        'active_status.required'  => 'Status Aktif  Wajib diisi',
      ];
  
      $validator = Validator::make($request->all(), $rules, $messages);
  
      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ]);
      }

      $class_cd = substr($request->acct_cd, 0, 2);

      $data = [
        'acct_cd' => $request->acct_cd,
        'descs' => $request->descs,
        'class_cd' => $class_cd,
        'ilevel' => $request->ilevel,
        'seq_no' => $request->seq_no,
        'acct_type' => $request->acct_type,
        'active_status' => $request->active_status,
        'updated_by' => Auth::user()->username
      ];

      // update the value
      $ok = Coa::updateOrCreate(
        ['id' => $dataID],
        $data
      );

      ## Log Activity
      $desc = $ok ? 'Berhasil Ubah Data COA' : 'Gagal Ubah Data COA';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } else {
      $rules = [
        'acct_cd'       => 'required|max:10|unique:m_coa,acct_cd',
        'descs'         => 'required',
        'active_status' => 'required',
      ];
  
      $messages = [
        'acct_cd.required' => 'Kode Akun Wajib diisi',
        'acct_cd.unique' => 'Kode Akun sudah digunakan',
        'descs.required'  => 'Nama Akun Wajib diisi',
        'active_status.required'  => 'Status Aktif  Wajib diisi',
      ];
  
      $validator = Validator::make($request->all(), $rules, $messages);
  
      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ]);
      }

      // $lastNum = Coa::query()->where('kode_cabang', $request->kode_cabang)->max(DB::raw('CAST(kode_karyawan AS UNSIGNED)')) ?? 0;
      // $kode = sprintf('%05d', $lastNum + 1);
      $class_cd = substr($request->acct_cd, 0, 2);

      $data = [
        'acct_cd' => $request->acct_cd,
        'descs' => $request->descs,
        'class_cd' => $class_cd,
        'ilevel' => $request->ilevel,
        'seq_no' => $request->seq_no,
        'acct_type' => $request->acct_type,
        'active_status' => $request->active_status,
        'created_by' => Auth::user()->username
      ];

      $ok = Coa::create($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil Tambah Data COA' : 'Gagal Tambah Data COA';
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
    $data = Coa::findOrFail($id);
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
    $data = Coa::query()->where('id', $id)->first()->toArray();

    $ok = Coa::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data COA' : 'Gagal Hapus Data COA';
    LogActivity::saveLogActivity($desc, $data);
  }
}