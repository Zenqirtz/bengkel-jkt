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
// use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class ParameterController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Parameter(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Parameter';

    $nama_tabel = Parameter::query()
      ->select('nama_tabel as kode', 'nama_tabel as keterangan')
      ->groupBy('nama_tabel')
      ->orderBy('nama_tabel', 'asc')
      ->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.setting.parameter', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'nama_tabel' => $nama_tabel,
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
    $base = DB::table('parameter as a');

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
    if ($request->filled('kode')) {
      $query->where('a.kode', 'like', '%' . $request->kode . '%');
    }
    if ($request->filled('keterangan')) {
      $query->where('a.keterangan', 'like', '%' . $request->keterangan . '%');
    }
    if ($request->filled('nama_tabel')) {
      if ($request->nama_tabel <> 'all') {
        $query->where('a.nama_tabel', 'like', '%' . $request->nama_tabel . '%');
      }
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('a.id');

    // Ambil data halaman saat ini
    $datas = $query
    ->select([
        'a.id',
        'a.kode',
        'a.keterangan',
        'a.nama_tabel',
        'a.no_urut',
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
        'id'         => $row->id,
        'fake_id'    => ++$fake,
        'kode'       => $row->kode,
        'keterangan' => $row->keterangan,
        'nama_tabel' => $row->nama_tabel,
        'no_urut'    => $row->no_urut,
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
        'kode'       => 'required',
        'keterangan' => 'required',
        'nama_tabel' => 'required',
      ];
  
      $messages = [
        'kode.required' => 'Kode Parameter Wajib diisi',
        'keterangan.required'  => 'Keterangan Wajib diisi',
        'nama_tabel.required'  => 'Nama Tabel  Wajib diisi',
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
        'kode' => $request->kode,
        'keterangan' => $request->keterangan,
        'nama_tabel' => $request->nama_tabel,
        'no_urut' => $request->no_urut,
        'updated_by' => Auth::user()->username
      ];

      if($request->nama_tabel == "other") {
        $data['nama_tabel'] = $request->nama_tabel_lain;
      }

      // update the value
      $ok = Parameter::updateOrCreate(
        ['id' => $dataID],
        $data
      );

      ## Log Activity
      $desc = $ok ? 'Berhasil Ubah Data Parameter' : 'Gagal Ubah Data Parameter';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } else {
      $rules = [
        'kode'       => 'required',
        'keterangan' => 'required',
        'nama_tabel' => 'required',
      ];
  
      $messages = [
        'kode.required' => 'Kode Parameter Wajib diisi',
        'keterangan.required'  => 'Keterangan Wajib diisi',
        'nama_tabel.required'  => 'Nama Tabel  Wajib diisi',
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
        'kode' => $request->kode,
        'keterangan' => $request->keterangan,
        'nama_tabel' => $request->nama_tabel,
        'no_urut' => $request->no_urut,
        'created_by' => Auth::user()->username
      ];

      if($request->nama_tabel == "other") {
        $data['nama_tabel'] = $request->nama_tabel_lain;
      }

      $ok = Parameter::create($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil Tambah Data Parameter' : 'Gagal Tambah Data Parameter';
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
    $data = Parameter::findOrFail($id);
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
    $data = Parameter::query()->where('id', $id)->first()->toArray();

    $ok = Parameter::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Parameter' : 'Gagal Hapus Data Parameter';
    LogActivity::saveLogActivity($desc, $data);
  }
}