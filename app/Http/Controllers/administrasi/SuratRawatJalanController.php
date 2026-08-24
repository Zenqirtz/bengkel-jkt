<?php

namespace App\Http\Controllers\administrasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\Parameter;
use App\Models\LogActivity;
use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class SuratRawatJalanController extends Controller
{
  /**
   * Redirect to view.
   */
  public function SuratRawatJalan(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Surat Rawat Jalan';

    $user_cabang = session('kd_cabang');
    $status_spk = Parameter::query()->where('nama_tabel', 'STATUS_SPK')->orderBy('no_urut', 'asc')->get();
    $status = Parameter::query()->where('nama_tabel', 'STATUS_SPK_KET')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    LogActivity::saveLogActivity("View " . $title);

    return view('content.administrasi.surat-rawat-jalan', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'status_spk' => $status_spk,
      'status' => $status,
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
      2 => 'k.tgl_masuk',
      3 => 'k.kode_spk',
      4 => 'e.keterangan',
      5 => 'k.no_polisi',
      6 => 'b.nama_tipe',
      7 => 'k.pemilik',
      8 => 'c.nama_pelanggan',
      9 => 'k.tgl_rawat_jalan1',
      10 => 'k.tgl_rawat_jalan2',
      11 => 'd.keterangan',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'k.id';
    $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    $base = DB::table('t_spk_master as k')
      ->leftJoin('m_tipe_kendaraan as b', function ($join) {
        $join->on('b.kode_tipe', '=', 'k.kode_tipe')
          ->on('b.kode_merek', '=', 'k.kode_merek');
      })
      ->leftJoin('m_merek_kendaraan as mk', function ($join) {
        $join->on('mk.kode_merek', '=', 'k.kode_merek');
      })
      ->leftJoin('m_pelanggan_hdr as c', function ($join) {
        $join->on('c.kode_pelanggan', '=', 'k.kode_pelanggan')
          ->on('c.kode_cabang', '=', 'k.kode_cabang');
      })
      ->leftJoin('parameter as d', function ($join) {
        $join->on('d.kode', '=', 'k.kode_status_spk')
          ->where('d.nama_tabel', '=', 'STATUS_SPK');
      })
      ->leftJoin('parameter as e', function ($join) {
        $join->on('e.kode', '=', 'k.status_spk')
          ->where('e.nama_tabel', '=', 'STATUS_SPK_KET');
      })
      ->where('k.kode_cabang', $user_cabang)
      ->where('k.ada_rawat_jalan', '1'); // hanya rawat jalan

    $totalData = (clone $base)->count('k.id');

    $query = (clone $base);

    if ($search = trim((string) $request->input('search.value'))) {
      $query->where(function ($q) use ($search) {
        $q->where('k.kode_spk', 'like', "%{$search}%")
          ->orWhere('k.no_polisi', 'like', "%{$search}%")
          ->orWhere('k.pemilik', 'like', "%{$search}%");
      });
    }

    if ($request->filled('kode_spk')) {
      $query->where('k.kode_spk', 'like', '%' . $request->kode_spk . '%');
    }
    if ($request->filled('no_polisi')) {
      $query->where('k.no_polisi', 'like', '%' . $request->no_polisi . '%');
    }
    if ($request->filled('tgl_masuk_awal')) {
      $query->whereDate('k.tgl_masuk', '>=', Carbon::createFromFormat('d/m/Y', $request->tgl_masuk_awal, 'Asia/Jakarta')->format('Y-m-d'));
    }
    if ($request->filled('tgl_masuk_akhir')) {
      $query->whereDate('k.tgl_masuk', '<=', Carbon::createFromFormat('d/m/Y', $request->tgl_masuk_akhir, 'Asia/Jakarta')->format('Y-m-d'));
    }
    if ($request->filled('nama_pelanggan')) {
      $query->where('c.nama_pelanggan', 'like', '%' . $request->nama_pelanggan . '%');
    }
    if ($request->filled('nama_pemilik')) {
      $query->where('k.pemilik', 'like', '%' . $request->nama_pemilik . '%');
    }
    if ($request->filled('no_polis')) {
      $query->where('k.no_polis', 'like', '%' . $request->no_polis . '%');
    }
    if ($request->filled('kode_claim')) {
      $query->where('k.kode_claim', 'like', '%' . $request->kode_claim . '%');
    }
    if ($request->filled('status_spk') && $request->status_spk !== 'all') {
      $query->where('k.kode_status_spk', 'like', '%' . $request->status_spk . '%');
    }
    if ($request->filled('status') && $request->status !== 'all') {
      $query->where('k.status_spk', 'like', '%' . $request->status . '%');
    }

    $totalFiltered = (clone $query)->count('k.id');

    $datas = $query
      ->select([
        'k.id',
        'k.kode_cabang',
        'k.tgl_masuk',
        'k.kode_spk',
        'k.no_polisi',
        'k.kode_merek',
        'mk.nama_merek',
        'k.kode_tipe',
        'b.nama_tipe',
        'k.pemilik',
        'k.alamat',
        'k.kode_pelanggan',
        'c.nama_pelanggan',
        'k.no_polis',
        'k.kode_claim',
        'k.tgl_rawat_jalan1',
        'k.tgl_rawat_jalan2',
        'k.status_spk as kode_status_spk',
        'd.keterangan as status_spk',
        'e.keterangan as keterangan',
      ])
      ->orderBy($order, $dir)
      ->offset($start)
      ->limit($limit)
      ->get();

    $data = [];
    $fake = $start;
    foreach ($datas as $row) {
      $data[] = [
        'id' => $row->id,
        'fake_id' => ++$fake,
        'kode_cabang' => $row->kode_cabang,
        'kode_spk' => $row->kode_spk,
        'no_polisi' => $row->no_polisi,
        'nama_merek' => $row->nama_merek,
        'kode_tipe' => $row->kode_tipe,
        'nama_tipe' => $row->nama_tipe,
        'merek_tipe' => trim(($row->nama_merek ?? '') . ' ' . ($row->nama_tipe ?? '')),
        'pemilik' => $row->pemilik,
        'alamat' => $row->alamat,
        'nama_pelanggan' => $row->nama_pelanggan,
        'nama_asuransi' => $row->nama_pelanggan,
        'no_polis' => $row->no_polis,
        'kode_claim' => $row->kode_claim,
        'kode_status_spk' => $row->kode_status_spk,
        'status_spk' => $row->status_spk,
        'keterangan' => $row->keterangan,
        'tgl_masuk' => blank($row->tgl_masuk) ? '' : date("d/m/Y", strtotime($row->tgl_masuk)),
        'tgl_rawat_jalan1' => blank($row->tgl_rawat_jalan1) ? '' : date("d/m/Y", strtotime($row->tgl_rawat_jalan1)),
        'tgl_rawat_jalan2' => blank($row->tgl_rawat_jalan2) ? '' : date("d/m/Y", strtotime($row->tgl_rawat_jalan2)),
      ];
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
    //
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

  //Ambil detail 1 SPK untuk cetak surat rawat jalan
  public function edit($id): JsonResponse
  {
    $data = DB::table('t_spk_master as k')
      ->leftJoin('m_tipe_kendaraan as b', function ($join) {
        $join->on('b.kode_tipe', '=', 'k.kode_tipe')
          ->on('b.kode_merek', '=', 'k.kode_merek');
      })
      ->leftJoin('m_merek_kendaraan as mk', 'mk.kode_merek', '=', 'k.kode_merek')
      ->leftJoin('m_pelanggan_hdr as c', function ($join) {
        $join->on('c.kode_pelanggan', '=', 'k.kode_pelanggan')
          ->on('c.kode_cabang', '=', 'k.kode_cabang');
      })
      ->leftJoin('m_mobil as mm', function ($join) {
        $join->on('mm.no_polisi', '=', 'k.no_polisi')
          ->on('mm.kode_cabang', '=', 'k.kode_cabang');
      })
      ->where('k.id', $id)
      ->select([
        'k.*',
        'b.nama_tipe',
        'mk.nama_merek',
        'c.nama_pelanggan',
        'k.alamat',
        'c.nama_pelanggan',
        'mm.no_rangka',
        'mm.no_mesin',

      ])
      ->first();

    if (blank($data)) {
      return response()->json(['status' => false, 'message' => 'Data tidak ditemukan!']);
    }

    if ($data->ada_rawat_jalan != '1') {
      return response()->json(['status' => false, 'message' => 'SPK ini tidak memiliki rawat jalan!']);
    }

    return response()->json([
      'status' => true,
      'message' => 'OK',
      'data' => $data,
    ]);
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
    //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    //
  }

  //Cetak Surat Rawat Jalan (bisa multi-id via query string).
  public function cetakSurat(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $title = 'Surat Rawat Jalan';

    $ids = (array) $request->input('id', []);

    if (empty($ids)) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }

    $datas = DB::table('t_spk_master as k')
      ->leftJoin('m_tipe_kendaraan as b', function ($join) {
        $join->on('b.kode_tipe', '=', 'k.kode_tipe')
          ->on('b.kode_merek', '=', 'k.kode_merek');
      })
      ->leftJoin('m_merek_kendaraan as mk', 'mk.kode_merek', '=', 'k.kode_merek')
      ->leftJoin('m_pelanggan_hdr as c', function ($join) {
        $join->on('c.kode_pelanggan', '=', 'k.kode_pelanggan')
          ->on('c.kode_cabang', '=', 'k.kode_cabang');
      })
      ->leftJoin('m_mobil as mm', function ($join) {
        $join->on('mm.no_polisi', '=', 'k.no_polisi')
          ->on('mm.kode_cabang', '=', 'k.kode_cabang');
      })
      ->whereIn('k.id', $ids)
      ->where('k.ada_rawat_jalan', '1')
      ->select([
        'k.*',
        'b.nama_tipe',
        'mk.nama_merek',
        'c.nama_pelanggan',
        'k.alamat',
        'k.alamat',
        'mm.no_rangka',
        'mm.no_mesin',
      ])
      ->get();

    if ($datas->isEmpty()) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }

    $cabang = DB::table('m_cabang')->where('kode_cabang', $user_cabang)->first();
    $cabang->alamat_lengkap = sprintf("%s %s %s", $cabang->alamat1, $cabang->alamat2, $cabang->alamat3);

    $dest = public_path('assets/img/cabang');
    $file_logo = is_file($dest . DIRECTORY_SEPARATOR . $cabang->logo_cabang) ? "1" : "0";

    ## Log Activity
    LogActivity::saveLogActivity("Cetak " . $title);

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.administrasi.surat-rawat-jalan-print', [
      'title' => $title,
      'datas' => $datas,
      'cabang' => $cabang,
      'file_logo' => $file_logo,
      'pageConfigs' => $pageConfigs,
    ]);
  }
}
