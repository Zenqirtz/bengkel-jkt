<?php

namespace App\Http\Controllers\setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\PenomoranTransaksi;
use App\Models\ProfilePerusahaan;
use App\Models\Bank;
use App\Models\Parameter;
use App\Models\LogActivity;
// use Carbon\Carbon;

class PenomoranTransaksiController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function PenomoranTransaksi(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Penomoran Transaksi';

    $user_cabang = session('kd_cabang');

    $modultrs = Parameter::query()->where('nama_tabel', 'MODUL_TRANSAKSI')->orderBy('no_urut', 'asc')->get();
    // $cabang = ProfilePerusahaan::query()->select('kode_cabang')->whereNotNull('kode_cabang')->where('kode_cabang', '<>', '')->orderBy('nourut', 'asc')->get();
    $cabang = ProfilePerusahaan::query()->select('kode_cabang', 'nama_singkat')->where('kode_cabang', $user_cabang)->orderBy('nourut', 'asc')->get();
    $bank = Bank::query()->select('kode_bank', 'nama_bank')->whereNotNull('kode_bank')->where('is_active', 'Y')->orderBy('nama_bank', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.setting.penomoran-transaksi', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'cabang' => $cabang,
      'bank' => $bank,
      'modultrs' => $modultrs,
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
      2 => 'b.keterangan', //modul
      3 => 'd.nama_singkat',
      4 => 'c.nama_bank',
      5 => 'a.autoreset',
      6 => 'a.nourut',
      7 => 'a.contoh',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'a.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('m_penomoran as a')
        ->leftJoin('parameter as b', function ($join) {
          $join->on('b.kode', '=', 'a.modul')
              ->where('b.nama_tabel', '=', 'MODUL_TRANSAKSI'); // syarat di JOIN
        })
        ->leftJoin('m_bank_fin as c', 'c.kode_bank', '=', 'a.bank')
        ->leftJoin('m_cabang as d', 'd.kode_cabang', '=', 'a.kode_cabang')
        ->where('a.kode_cabang', $user_cabang);

    // Total baris tanpa filter
    $totalData = (clone $base)->count('a.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('a.id', 'like', "%{$search}%")
    //           ->orWhere('b.keterangan', 'like', "%{$search}%")
    //           ->orWhere('a.contoh', 'like', "%{$search}%"); // cari di nama posisi juga
    //     });
    // }

    if ($request->filled('modul')) {
      if ($request->modul <> 'all') {
        $query->where('a.modul', 'like', '%' . $request->modul . '%');
      }
    }
    if ($request->filled('autoreset')) {
      if ($request->autoreset <> 'all') {
        $query->where('a.autoreset', 'like', '%' . $request->autoreset . '%');
      }
    }
    if ($request->filled('contoh')) {
      $query->where('a.contoh', 'like', '%' . $request->contoh . '%');
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('a.id');

    // Ambil data halaman saat ini
    $datas = $query
        ->select([
            'a.id',
            // 'a.cabang',
            'a.bank',
            'a.nourut',
            'a.autoreset',
            'a.contoh',
            'b.keterangan as modul',
            'c.nama_bank',
            'd.nama_singkat as cabang',
            'd.nama_cabang',
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
            'id'        => $row->id,
            'fake_id'   => ++$fake,
            'cabang'    => $row->cabang,
            'bank'      => $row->bank,
            'autoreset' => $row->autoreset,
            'nourut'    => $row->nourut,
            'contoh'    => $row->contoh,
            'modul'     => $row->modul,
            'nama_bank' => $row->nama_bank,
            'nama_cabang' => $row->nama_cabang,
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
        'modul' => [
            'required',
            'string',
            'max:5',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_penomoran', 'modul')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
                })
                ->ignore($request->id), // Penting: Abaikan ID sendiri saat update
        ],
        'nourut' => 'required',
        'segmen1' => 'required'
      ];
  
      $messages = [
        'modul.required' => 'Modul Wajib diisi',
        'modul.unique' => 'Modul sudah digunakan',
        'nourut.required' => 'Nomor Urut Wajib diisi',
        'segmen1.required'  => 'Segmen Wajib diisi',
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
        // 'kode_cabang'  => $request->kode_cabang,
        'modul'        => $request->modul,
        'cabang'       => $request->dept,
        'bank'         => $request->bank,
        'digit_cnt'    => $request->digit_cnt,
        'nourut'       => $request->nourut,
        'segmen1'      => $request->segmen1,
        // 'segmen2'   => $request->segmen2,
        // 'segmen3'   => $request->segmen3,
        // 'segmen4'   => $request->segmen4,
        // 'segmen5'   => $request->segmen5,
        // 'segmen6'   => $request->segmen6,
        // 'segmen7'   => $request->segmen7,
        'autoreset'    => $request->autoreset,
        'contoh'       => $request->contoh,
        'updated_by'   => Auth::user()->username
      ];

      // update the value
      $ok = PenomoranTransaksi::updateOrCreate(
        ['id' => $dataID],
        $data
      );

      ## Log Activity
      $desc = $ok ? 'Berhasil Ubah Data Penomoran Transaksi' : 'Gagal Ubah Data Penomoran Transaksi';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);

      // user updated
      return response()->json(['status' => true, 'message' => "Berhasil ubah data"]);
    } else {
      $rules = [
        'modul' => [
            'required',
            'string',
            'max:5',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_penomoran', 'modul')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
                })
                ->ignore($request->id), // Penting: Abaikan ID sendiri saat update
        ],
        'nourut' => 'required',
        'segmen1' => 'required'
      ];
  
      $messages = [
        'modul.required' => 'Modul Wajib diisi',
        'modul.unique' => 'Modul sudah digunakan',
        'nourut.required' => 'Nomor Urut Wajib diisi',
        'segmen1.required'  => 'Segmen Wajib diisi',
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
        'kode_cabang'  => $request->kode_cabang,
        'modul'        => $request->modul,
        'cabang'       => $request->dept,
        'bank'         => $request->bank,
        'digit_cnt'    => $request->digit_cnt,
        'nourut'       => $request->nourut,
        'segmen1'      => $request->segmen1,
        // 'segmen2'   => $request->segmen2,
        // 'segmen3'   => $request->segmen3,
        // 'segmen4'   => $request->segmen4,
        // 'segmen5'   => $request->segmen5,
        // 'segmen6'   => $request->segmen6,
        // 'segmen7'   => $request->segmen7,
        'autoreset'    => $request->autoreset,
        'contoh'       => $request->contoh,
        'created_by'   => Auth::user()->username
      ];

      $ok = PenomoranTransaksi::create($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil Tambah Data Penomoran Transaksi' : 'Gagal Tambah Data Penomoran Transaksi';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);

      // create new one if kode cabang is unique
      // $cek = PenomoranTransaksi::where('kode_cabang', $user_cabang)->where('modul', $request->modul)->first();

      // if (empty($cek)) {
        
      // } else {
      //   // user already exist
      //   return response()->json(['status' => false, 'message' => "Modul Transaksi sudah digunakan"]);
      // }
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
    $data = PenomoranTransaksi::findOrFail($id);
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
    $data = PenomoranTransaksi::query()->where('id', $id)->first()->toArray();

    $ok = PenomoranTransaksi::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Penomoran Transaksi' : 'Gagal Hapus Data Penomoran Transaksi';
    LogActivity::saveLogActivity($desc, $data);
  }
}