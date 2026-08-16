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
use Illuminate\Validation\Rule;
use App\Models\Bank;
use App\Models\Parameter;
use App\Models\LogActivity;


class BankController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Bank(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Data Bank';

    $status_aktif = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();
    $kategori = Parameter::query()->where('nama_tabel', 'KATEGORI_REKENING')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.keuangan.bank', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'status_aktif' => $status_aktif,
      'kategori' => $kategori,
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
        2 => 'a.no_rekening',
        3 => 'a.nama_bank',
        4 => 'a.lokasi_bank',
        5 => 'b.keterangan',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.id';
      $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('m_bank_fin as a')
        ->leftJoin('parameter as b', function ($join) {
          $join->on('b.kode', '=', 'a.is_active')
              ->where('b.nama_tabel', '=', 'STATUS');
        })
        ->where('a.kode_cabang', $user_cabang);
        // ->leftJoin('parameter as c', function ($join) {
        //   $join->on('c.kode', '=', 'a.kode_kategori')
        //       ->where('c.nama_tabel', '=', 'KATEGORI_REKENING');
        // });

      // Total baris tanpa filter
      $totalData = (clone $base)->count('a.id');

      // Filtering (search global)
      $query = (clone $base);
      // if ($search = trim((string) $request->input('search.value'))) {
      //     $query->where(function ($q) use ($search) {
      //         $q->where('a.nama_bank', 'like', "%{$search}%")
      //           ->orWhere('b.keterangan', 'like', "%{$search}%")
      //           ->orWhere('c.keterangan', 'like', "%{$search}%");
      //     });
      // }
      if ($request->filled('nama_bank')) {
        $query->where('a.nama_bank', 'like', '%' . $request->nama_bank . '%');
      }
      if ($request->filled('no_rekening')) {
        $query->where('a.no_rekening', 'like', '%' . $request->no_rekening . '%');
      }
      if ($request->filled('lokasi_bank')) {
        $query->where('a.lokasi_bank', 'like', '%' . $request->lokasi_bank . '%');
      }
      // if ($request->filled('kode_kategori')) {
      //   if ($request->kode_kategori <> 'all') {
      //     $query->where('a.kode_kategori', 'like', '%' . $request->kode_kategori . '%');
      //   }
      // }
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
            'a.no_rekening',
            'a.nama_bank',
            'a.lokasi_bank',
            'a.is_active',
            'b.keterangan as status',
            // 'c.keterangan as kategori',
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
            'no_rekening' => $row->no_rekening,
            'nama_bank' => $row->nama_bank,
            'lokasi_bank' => $row->lokasi_bank,
            'is_active' => $row->is_active,
            'status' => $row->status,
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
    $user_cabang = session('kd_cabang');
    $dataID = $request->id;

    try {
      if ($dataID) {
        $rules = [
          // 'no_rekening' => 'required|max:20|unique:m_bank_fin,no_rekening,'.$request->id,
          'no_rekening' => [
            'required',
            'max:20',
            Rule::unique('m_bank_fin', 'no_rekening')->where(function ($query) use ($user_cabang) {
                return $query->where('kode_cabang', $user_cabang);
            })
            ->ignore($dataID) // Penting: Abaikan ID sendiri saat update
          ],
          'nama_bank' => 'required',
          'is_active' => 'required',
        ];
    
        $messages = [
          'nama_bank.required' => 'Nama Bank Wajib diisi',
          'is_active.required' => 'Status Wajib diisi',
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
          'nama_bank'     => $request->nama_bank,
          'lokasi_bank'   => $request->lokasi_bank,
          'is_active'     => $request->is_active,
          'updated_by'    => Auth::user()->username,
        ];
  
        // update the value
        $ok = Bank::updateOrCreate(
          ['id' => $dataID],
          $data
        );
  
        ## Log Activity
        $desc = $ok ? 'Berhasil ubah Data Bank.' : 'Gagal ubah Data Bank.';
        LogActivity::saveLogActivity($desc, $data);
    
        return response()->json([
          'status'  => (bool)$ok,
          'message' => $desc
        ]);
  
      } else {
        $rules = [
          // 'no_rekening' => 'required|max:20|unique:m_bank_fin,no_rekening',
          'no_rekening' => [
            'required',
            'max:20',
            Rule::unique('m_bank_fin', 'no_rekening')->where(function ($query) use ($user_cabang) {
                return $query->where('kode_cabang', $user_cabang);
            })
          ],
          'nama_bank' => 'required',
          'is_active' => 'required',
        ];
    
        $messages = [
          'nama_bank.required' => 'Nama Bank Wajib diisi',
          'is_active.required' => 'Status Wajib diisi',
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
  
        $lastNum = Bank::query()->max(DB::raw('CAST(kode_bank AS UNSIGNED)')) ?? 0;
        $kode = sprintf('%05d', $lastNum + 1);
  
        $data = [
          'kode_cabang'  => $user_cabang,
          'kode_bank'    => $kode,
          'no_rekening'  => $request->no_rekening,
          'nama_bank'    => $request->nama_bank,
          'lokasi_bank'  => $request->lokasi_bank,
          'is_active'    => $request->is_active,
          'created_by'   => Auth::user()->username,
        ];
    
        $ok = Bank::create($data);
  
        ## Log Activity
        $desc = $ok ? 'Berhasil tambah Data Bank.' : 'Gagal tambah Data Bank.';
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
    $data = Bank::findOrFail($id);
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
    $data = Bank::query()->select('id','kode_bank','nama_bank')->where('id', $id)->first()->toArray();

    $ok = Bank::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Bank.' : 'Gagal Hapus Data Bank.';
    LogActivity::saveLogActivity($desc, $data);
  }
}
