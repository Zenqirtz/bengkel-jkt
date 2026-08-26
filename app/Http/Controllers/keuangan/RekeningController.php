<?php

namespace App\Http\Controllers\keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Rekening;
use App\Models\Bank;
use App\Models\LogActivity;


use App\Helpers\Helpers as Helper;

class RekeningController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Rekening(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Data Rekening';

    $bank = Bank::query()->select('kode_bank', 'nama_bank')->where('is_active', 'Y')->orderBy('nama_bank', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.keuangan.rekening', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'bank' => $bank,
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

    try {
      $columns = [
        1 => 'a.id',
        2 => 'b.nama_bank',
        3 => 'a.no_rekening',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.id';
      $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('m_bank_rekening as a')
      ->leftJoin('m_bank_fin as b', 'b.kode_bank', '=', 'a.kode_bank')
      ->where('b.is_active', 'Y')
      ->where('a.kode_cabang', $user_cabang);

      // Total baris tanpa filter
      $totalData = (clone $base)->count('a.id');

      // Filtering (search global)
      $query = (clone $base);
      // if ($search = trim((string) $request->input('search.value'))) {
      //     $query->where(function ($q) use ($search) {
      //         $q->where('b.nama_bank', 'like', "%{$search}%")
      //           ->orWhere('a.no_rekening', 'like', "%{$search}%");
      //     });
      // }
      if ($request->filled('no_rekening')) {
        $query->where('a.no_rekening', 'like', '%' . $request->no_rekening . '%');
      }
      if ($request->filled('kode_bank')) {
        if ($request->kode_bank <> 'all') {
          $query->where('a.kode_bank', 'like', '%' . $request->kode_bank . '%');
        }
      }

      // Hitung setelah filter (tanpa limit/offset)
      $totalFiltered = (clone $query)->count('a.id');

      // Ambil data halaman saat ini
      $datas = $query
        ->select([
            'a.id',
            'b.nama_bank',
            'a.no_rekening',
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
              'nama_bank' => $row->nama_bank,
              'no_rekening' => $row->no_rekening,
          ];
      }

      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => intval($totalData),
        'recordsFiltered' => intval($totalFiltered),
        'data' => $data,
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage(),
      ]);
    }
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
    try {
      $user_cabang = session('kd_cabang');
      $dataID = $request->id;

      if ($dataID) {
        $rules = [
          'no_rekening' => 'required|max:20|unique:m_bank_rekening,no_rekening,'.$request->id,
          'kode_bank' => 'required',
        ];

        $messages = [
          'kode_bank.required' => 'Kategori Wajib diisi',
          'no_rekening.required' => 'Nomor Rekening Wajib diisi',
          'no_rekening.unique' => 'Nomor Rekening sudah digunakan',
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
          'no_rekening'   => $request->no_rekening,
          'kode_bank'     => $request->kode_bank,
          'updated_by'    => Auth::user()->username,
        ];

        // update the value
        $ok = Rekening::updateOrCreate(
          ['id' => $dataID],
          $data
        );

        ## Log Activity
        $desc = $ok ? 'Berhasil ubah Data Rekening.' : 'Gagal ubah Data Rekening.';
        LogActivity::saveLogActivity($desc, $data);

        return response()->json([
          'status'  => (bool)$ok,
          'message' => $desc
        ]);

      } else {
        $rules = [
          'no_rekening' => 'required|max:20|unique:m_bank_rekening,no_rekening,'.$request->id,
          'kode_bank' => 'required',
        ];

        $messages = [
          'kode_bank.required' => 'Kategori Wajib diisi',
          'no_rekening.required' => 'Nomor Rekening Wajib diisi',
          'no_rekening.unique' => 'Nomor Rekening sudah digunakan',
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
          'kode_cabang'   => $user_cabang,
          'no_rekening'   => $request->no_rekening,
          'kode_bank'     => $request->kode_bank,
          'created_by'    => Auth::user()->username,
        ];

        $ok = Rekening::create($data);

        ## Log Activity
        $desc = $ok ? 'Berhasil tambah Data Rekening.' : 'Gagal tambah Data Rekening.';
        LogActivity::saveLogActivity($desc, $data);

        return response()->json([
          'status'  => (bool)$ok,
          'message' => $desc
        ]);
      }
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 200);
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
    $data = Rekening::findOrFail($id);
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
    $data = Rekening::query()->select('id','kode_cabang','kode_bank','no_rekening')->where('id', $id)->first()?->toArray() ?? [];

    $ok = Rekening::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Rekening.' : 'Gagal Hapus Data Rekening.';
    LogActivity::saveLogActivity($desc, $data);
  }
}
