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
use App\Models\Sparepart;
use App\Models\PemakaianBahan;
use App\Models\PosisiPekerjaan;
use App\Models\LogActivity;

use App\Helpers\Helpers as Helper;

class PemakaianBahanController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function PemakaianBahan(): View
  {
    $isList = Helper::AuthIsPerm("list");
    $isAdd = Helper::AuthIsPerm("add");
    $isEdit = Helper::AuthIsPerm("edit");
    $isDel = Helper::AuthIsPerm("delete");
    if (!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path = request()->path();
    $title = Helper::getTitleMenu($path) ?? 'Data Bahan';

    $user_cabang = session('kd_cabang');
    $pekerjaan = PosisiPekerjaan::query()->select('kode_posisi', 'posisi_pekerjaan')->where('is_active', 'Y')->whereIn('kode_posisi', ['00002', '00003'])->orderBy('seq_no', 'asc')->get();
    // $bahan = Bahan::query()->select('kode_bahan','nama_bahan')->where('kode_cabang', $user_cabang)->where('is_active', 'Y')->orderBy('nama_bahan', 'asc')->get();

    $point_panel = [];
    for ($i = 1; $i <= 20; $i++) {
      $point_panel[$i] = $i;
    }

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.gudang.pemakaian-bahan', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'pekerjaan' => $pekerjaan,
      // 'bahan' => $bahan,
      'point_panel' => $point_panel,
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
      2 => 'c.posisi_pekerjaan',
      3 => 'b.nama_bahan',
      4 => 'a.point_panel',
      5 => 'a.qty',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'a.id';
    $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('m_standarisasi_point_panel as a')
      ->join('m_bahan as b', function ($join) {
        $join->on('b.kode_cabang', '=', 'a.kode_cabang')
          ->on('b.kode_bahan', '=', 'a.kode_bahan');
      })
      ->join('m_posisi_pekerjaan as c', 'c.kode_posisi', '=', 'a.kode_posisi')
      ->where('a.kode_cabang', $user_cabang);

    // Total baris tanpa filter
    $totalData = (clone $base)->count('a.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('b.nama_bahan', 'like', "%{$search}%")
    //           ->orWhere('c.posisi_pekerjaan', 'like', "%{$search}%")
    //           ->orWhere('a.qty', 'like', "%{$search}%")
    //           ->orWhere('a.point_panel', 'like', "%{$search}%");
    //     });
    // }

    // Filter berdasarkan input yang dikirim dari DataTables
    if ($request->filled('kode_posisi')) {
      if ($request->kode_posisi <> 'all') {
        $query->where('a.kode_posisi', 'like', '%' . $request->kode_posisi . '%');
      }
    }
    if ($request->filled('point_panel')) {
      if ($request->point_panel <> 'all') {
        $query->where('a.point_panel', '=', $request->point_panel);
      }
    }
    if ($request->filled('nama_bahan')) {
      $query->where('b.nama_bahan', 'like', '%' . $request->nama_bahan . '%');
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('a.id');

    // Ambil data halaman saat ini
    $datas = $query
      ->select([
        'a.id',
        'a.kode_cabang',
        'a.kode_posisi',
        'a.kode_bahan',
        'a.point_panel',
        'a.qty',
        'b.nama_bahan',
        'c.posisi_pekerjaan',
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
        'kode_cabang' => $row->kode_cabang,
        'kode_posisi' => $row->kode_posisi,
        'kode_bahan' => $row->kode_bahan,
        'point_panel' => $row->point_panel,
        'qty' => number_format($row->qty, 2, ".", ","),
        'nama_bahan' => $row->nama_bahan,
        'posisi_pekerjaan' => $row->posisi_pekerjaan,
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
          // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
          Rule::unique('m_standarisasi_point_panel', 'kode_bahan')
            ->where(function ($query) use ($request) {
              return $query
                ->where('kode_posisi', $request->kode_posisi)
                ->where('point_panel', $request->point_panel)
                ->where('kode_cabang', $request->kode_cabang);
            })
            ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'kode_posisi' => 'required',
        'point_panel' => 'required',
        'qty' => 'required',
      ];

      $messages = [
        'kode_bahan.required' => 'Nama Bahan Wajib diisi',
        'kode_bahan.unique' => 'Nama Bahan sudah digunakan',
        'kode_posisi.required' => 'Pekerjaan diisi',
        'point_panel.required' => 'Point Panel diisi',
        'qty.required' => 'Qty Wajib diisi',
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
        'kode_posisi' => $request->kode_posisi,
        'kode_bahan' => $request->kode_bahan,
        'point_panel' => $request->point_panel,
        'qty' => blank($request->qty) ? 0 : str_replace([","], "", $request->qty),
        'updated_by' => Auth::user()->username
      ];

      $ok = PemakaianBahan::updateOrCreate(
        ['id' => $dataID],
        $data
      );

      ## Log Activity
      $desc = $ok ? 'Berhasil Ubah Data Pemakaian Bahan' : 'Gagal Ubah Data Pemakaian Bahan';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status' => (bool) $ok,
        'message' => $desc
      ]);
    } else {
      $rules = [
        'kode_bahan' => [
          'required',
          // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
          Rule::unique('m_standarisasi_point_panel', 'kode_bahan')
            ->where(function ($query) use ($request) {
              return $query
                ->where('kode_posisi', $request->kode_posisi)
                ->where('point_panel', $request->point_panel)
                ->where('kode_cabang', $request->kode_cabang);
            })
            ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'kode_posisi' => 'required',
        'point_panel' => 'required',
        'qty' => 'required',
      ];

      $messages = [
        'kode_bahan.required' => 'Nama Bahan Wajib diisi',
        'kode_bahan.unique' => 'Nama Bahan sudah digunakan',
        'kode_posisi.required' => 'Pekerjaan diisi',
        'point_panel.required' => 'Point Panel diisi',
        'qty.required' => 'Qty Wajib diisi',
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
        'kode_posisi' => $request->kode_posisi,
        'kode_bahan' => $request->kode_bahan,
        'point_panel' => $request->point_panel,
        'qty' => blank($request->qty) ? 0 : str_replace([","], "", $request->qty),
        'created_by' => Auth::user()->username
      ];

      $ok = PemakaianBahan::create($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil Tambah Data Pemakaian Bahan' : 'Gagal Tambah Data Pemakaian Bahan';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status' => (bool) $ok,
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
  // public function edit($id): JsonResponse
  // {
  //   $data = PemakaianBahan::findOrFail($id);
  //   $data->qty = number_format($data->qty, 2, ".", ",");
  //   return response()->json($data);
  // }
  public function edit($id): JsonResponse
  {
    $data = DB::table('m_standarisasi_point_panel as a')
      ->join('m_posisi_pekerjaan as c', 'c.kode_posisi', '=', 'a.kode_posisi')
      ->join('m_bahan as b', function ($join) {
        $join->on('b.kode_cabang', '=', 'a.kode_cabang')
          ->on('b.kode_bahan', '=', 'a.kode_bahan');
      })
      ->where('a.id', $id)
      ->select([
        'a.id',
        'a.kode_cabang',
        'a.kode_posisi',
        'a.kode_bahan',
        'a.point_panel',
        'a.qty',
        'b.nama_bahan',
        'c.posisi_pekerjaan',
      ])
      ->first();

    if (!$data)
      abort(404);

    $data->qty = number_format($data->qty, 2, ".", ",");

    return response()->json($data);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $data = PemakaianBahan::query()->where('id', $id)->first()?->toArray() ?? [];

    $ok = PemakaianBahan::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Pemakaian Bahan' : 'Gagal Hapus Data Pemakaian Bahan';
    LogActivity::saveLogActivity($desc, $data);
  }

  public function getNamaBahan(Request $request): JsonResponse
  {
    $jenisId = $request->query('jenis_id');

    if (!$jenisId) {
      return response()->json([]);
    }

    $user_cabang = session('kd_cabang');

    // if ($jenisId == "S" || $jenisId == "T") {
    //   $data = Sparepart::where('kode_cabang', $user_cabang)
    //     ->where('is_active', 'Y')
    //     ->select([
    //       'kode_sparepart as kode_bahan',
    //       'nama_sparepart as nama_bahan',
    //     ])
    //     ->orderBy('nama_sparepart', 'asc')
    //     ->get();

    //   return response()->json($data);
    // }

    if ($jenisId == "00003") {
      $jenisId = "00002"; // Cat
    } else {
      $jenisId = "00001"; // Bahan
    }

    $data = Bahan::where('kode_cabang', $user_cabang)
      ->where('kode_group_bahan', $jenisId)
      ->where('is_active', 'Y')
      ->select('kode_bahan', 'nama_bahan')
      ->orderBy('nama_bahan', 'asc')
      ->get();

    return response()->json($data);
  }
}
