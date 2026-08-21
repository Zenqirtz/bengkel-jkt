<?php

namespace App\Http\Controllers\keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Parameter;
use App\Models\InputPembelian;
use App\Models\InputGudangFoto;
use App\Models\LogActivity;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;

use App\Helpers\Helpers as Helper;

class FotoBonController extends Controller
{
  /**
   * Redirect to view.
   */
  public function FotoBon(): View
  {
    $isList = Helper::AuthIsPerm("list");
    $isAdd = Helper::AuthIsPerm("add");
    $isEdit = Helper::AuthIsPerm("edit");
    $isDel = Helper::AuthIsPerm("delete");

    if (!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $user_cabang = session('kd_cabang');
    $path = request()->path();
    $title = Helper::getTitleMenu($path) ?? 'Upload Foto Bon';

    $tipe_barang = Parameter::query()
      ->where('nama_tabel', 'TIPE_BARANG')
      ->orderBy('no_urut', 'asc')
      ->get();

    // Log Activity
    LogActivity::saveLogActivity("View " . $title);

    return view('content.keuangan.foto-bon', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'tipe_barang' => $tipe_barang,
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

    if ($request->tipe === 'input-gudang') {

      $columns = [
        1 => 'a.id',
        2 => 'a.tanggal',
        3 => 'a.kode_input',
        4 => 'c.keterangan',  // tipe barang
        5 => 'a.kode_order',
        6 => 'a.kode_spk',
        7 => 'b.nama_pemasok',
        8 => 'a.total',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.id';
      $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      $base = DB::table('t_input_gudang_hdr as a')
        ->leftJoin('m_pemasok as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_pemasok', '=', 'a.kode_pemasok');
        })
        ->leftJoin('parameter as c', function ($join) {
          $join->on('c.kode', '=', 'a.tipe')
            ->where('c.nama_tabel', '=', 'TIPE_BARANG');
        })
        ->where('a.kode_cabang', $user_cabang)
        ->where('a.status_approve', '1'); // hanya yang sudah approved

      $totalData = (clone $base)->count('a.id');

      $query = clone $base;

      // Filter dari form
      if ($request->filled('kode_input')) {
        $query->where('a.kode_input', 'like', '%' . $request->kode_input . '%');
      }
      if ($request->filled('kode_order')) {
        $query->where('a.kode_order', 'like', '%' . $request->kode_order . '%');
      }
      if ($request->filled('kode_spk')) {
        $query->where('a.kode_spk', 'like', '%' . $request->kode_spk . '%');
      }
      if ($request->filled('nama_pemasok')) {
        $query->where('b.nama_pemasok', 'like', '%' . $request->nama_pemasok . '%');
      }
      if ($request->filled('tanggal_awal')) {
        $query->whereDate(
          'a.tanggal',
          '>=',
          Carbon::createFromFormat('d/m/Y', $request->tanggal_awal, 'Asia/Jakarta')->format('Y-m-d')
        );
      }
      if ($request->filled('tanggal_akhir')) {
        $query->whereDate(
          'a.tanggal',
          '<=',
          Carbon::createFromFormat('d/m/Y', $request->tanggal_akhir, 'Asia/Jakarta')->format('Y-m-d')
        );
      }
      if ($request->filled('tipe_barang') && $request->tipe_barang !== 'all') {
        $query->where('a.tipe', $request->tipe_barang);
      }

      $totalFiltered = (clone $query)->count('a.id');

      $datas = $query
        ->select([
          'a.id',
          'a.kode_cabang',
          'a.kode_input',
          'a.kode_order',
          'a.kode_spk',
          'a.tanggal',
          'a.tipe as kode_tipe',
          'a.kode_pemasok',
          'a.total',
          'b.nama_pemasok',
          'c.keterangan as tipe_barang',
        ])
        ->orderBy($order, $dir)
        ->offset($start)
        ->limit($limit)
        ->get();

      $data = [];
      $fake = $start;
      foreach ($datas as $row) {
        $cekFoto = InputGudangFoto::where('kode_cabang', $row->kode_cabang)
          ->where('kode_input', $row->kode_input)
          ->count();

        $data[] = [
          'id' => $row->id,
          'fake_id' => ++$fake,
          'kode_cabang' => $row->kode_cabang,
          'kode_input' => $row->kode_input,
          'kode_order' => $row->kode_order,
          'kode_spk' => $row->kode_spk,
          'tipe_barang' => $row->tipe_barang,
          'nama_pemasok' => $row->nama_pemasok,
          'total' => number_format($row->total, 0, '.', ','),
          'photo' => ($cekFoto > 0) ? '1' : '0',
          'tanggal' => blank($row->tanggal) ? '' : date('d/m/Y', strtotime($row->tanggal)),
        ];
      }

    } else {
      $totalData = 0;
      $totalFiltered = 0;
      $data = [];
    }

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
    $user_cabang = session('kd_cabang');
    $dataID = $request->id;
    $res = InputPembelian::find($dataID);

    if (!$res) {
      return response()->json(['status' => false, 'message' => 'ID Input Gudang tidak ditemukan']);
    }

    $rules = [
      'photo' => 'required|array',
      'photo.*' => 'image|mimes:jpg,jpeg,png',
    ];

    $messages = [
      'photo.required' => 'File Foto wajib diisi.',
      'photo.*.image' => 'File harus berupa gambar.',
      'photo.*.mimes' => 'Format foto harus jpg, jpeg, atau png.',
    ];

    $validator = Validator::make($request->all(), $rules, $messages);
    if ($validator->fails()) {
      return response()->json([
        'status' => false,
        'message' => 'Gagal menyimpan data.',
        'errors' => $validator->errors(),
      ]);
    }

    try {
      $ok = false;
      $desc = '';

      foreach ($request->file('photo') as $file) {
        $filename = $file->getClientOriginalName();
        $fileSize = $file->getSize();

        // Kompresi jika > 70 KB
        if ($fileSize > 70000) {
          $img = Image::make($file->getRealPath());
          $img->fit(640, 480);
          $photoBinary = (string) $img->encode('jpg', 70);
          $fileSize = strlen($photoBinary);
        } else {
          $photoBinary = file_get_contents($file->getRealPath());
        }

        $lastNum = InputGudangFoto::query()
          ->where('kode_cabang', $res->kode_cabang)
          ->where('kode_input', $res->kode_input)
          ->max(DB::raw('CAST(no_urut AS UNSIGNED)')) ?? 0;

        $data = [
          'kode_cabang' => $res->kode_cabang,
          'kode_input' => $res->kode_input,
          'no_urut' => $lastNum + 1,
          'nama_file' => $filename,
          'photo_bon' => $photoBinary,
          'ukuran' => $fileSize,
          'created_by' => Auth::user()->username,
        ];

        $ok = InputGudangFoto::create($data);

        $desc = $ok ? 'Berhasil upload foto bon' : 'Gagal upload foto bon';
        unset($data['photo_bon']);
        LogActivity::saveLogActivity($desc, $data);

        if (!$ok)
          break;
      }

      return response()->json(['status' => (bool) $ok, 'message' => $desc]);

    } catch (\Exception $e) {
      return response()->json(['status' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
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
    $user_cabang = session('kd_cabang');

    $data = DB::table('t_input_gudang_hdr as a')
      ->leftJoin('m_pemasok as b', function ($join) {
        $join->on('b.kode_cabang', '=', 'a.kode_cabang')
          ->on('b.kode_pemasok', '=', 'a.kode_pemasok');
      })
      ->leftJoin('parameter as c', function ($join) {
        $join->on('c.kode', '=', 'a.tipe')
          ->where('c.nama_tabel', '=', 'TIPE_BARANG');
      })
      ->where('a.id', $id)
      ->select([
        'a.id',
        'a.kode_cabang',
        'a.kode_input',
        'a.kode_order',
        'a.kode_spk',
        'a.tanggal',
        'a.no_bon',
        'a.tipe as kode_tipe',
        'a.kode_pemasok',
        'a.total',
        'b.nama_pemasok',
        'c.keterangan as tipe_barang',
      ])
      ->first();

    if ($data) {
      $data->tanggal = blank($data->tanggal) ? '' : date('d/m/Y', strtotime($data->tanggal));
      $data->total = number_format($data->total, 0, '.', ',');
    }

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
    $data = InputGudangFoto::query()
      ->select('id', 'kode_cabang', 'kode_input', 'no_urut', 'nama_file', 'ukuran', 'created_at', 'created_by')
      ->where('id', $id)
      ->first()
      ->toArray();

    $ok = InputGudangFoto::where('id', $id)->delete();
    $desc = $ok ? 'Berhasil Hapus Foto Bon' : 'Gagal Hapus Foto Bon';
    LogActivity::saveLogActivity($desc, $data);
  }

  /**
   * Ambil semua foto bon berdasarkan kode_cabang + kode_input  (untuk galeri)
   */
  public function getFotoBon(Request $request): JsonResponse
  {
    $kdCabang = $request->query('kode_cabang');
    $kdInput = $request->query('kode_input');

    if (!$kdCabang || !$kdInput) {
      return response()->json([]);
    }

    $photos = InputGudangFoto::where('kode_cabang', $kdCabang)
      ->where('kode_input', $kdInput)
      ->orderBy('no_urut', 'asc')
      ->get();

    $data = $photos->map(fn($item) => [
      'id' => $item->id,
      'no_urut' => $item->no_urut,
      'nama_file' => $item->nama_file,
      'photo_bon_base64' => $item->photo_bon ? base64_encode($item->photo_bon) : null,
    ]);

    return response()->json($data);
  }
}
