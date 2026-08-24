<?php

namespace App\Http\Controllers\administrasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ProfilePerusahaan;
use App\Models\Kendaraan;
use App\Models\Spk;
use App\Models\Parameter;
use App\Models\JenisPekerjaan;
use App\Models\PanelPekerjaan;
use App\Models\Asuransi;
use App\Models\Sparepart;
use App\Models\Estimator;
use App\Models\Surveyor;
use App\Models\KonsepEstimasi;
use App\Models\KonsepEstimasiPerbaikan;
use App\Models\KonsepEstimasiSparepart;
use App\Models\KonsepEstimasiLain;
use App\Models\TarifPpn;
use App\Models\LogActivity;
use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class KonsepEstimasiController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function KonsepEstimasi(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Konsep Estimasi';

    $user_cabang = session('kd_cabang');
    $username = Auth::user()->username;
    $status_spk = Parameter::query()->where('nama_tabel', 'STATUS_SPK')->orderBy('no_urut', 'asc')->get();
    $status = Parameter::query()->where('nama_tabel', 'STATUS_SPK_KET')->orderBy('no_urut', 'asc')->get();
    $asuransi = Asuransi::query()->select('kode_pelanggan', 'nama_pelanggan')->where('kode_cabang', $user_cabang)->where('is_active', 'Y')->orderBy('nama_pelanggan', 'asc')->get();
    $estimator = Estimator::query()->select('kode_estimator', 'nama_estimator')->where('kode_cabang', $user_cabang)->where('is_active', 'Y')->orderBy('nama_estimator', 'asc')->get();
    // $surveyor = Surveyor::query()->select('kode_surveyor','nama_surveyor')->where('kode_cabang', $user_cabang)->where('is_active', 'Y')->orderBy('nama_surveyor', 'asc')->get();
    $sparepart = Sparepart::query()->select('kode_sparepart', 'nama_sparepart')->where('kode_cabang', $user_cabang)->where('is_active', 'Y')->orderBy('nama_sparepart', 'asc')->get();
    $jenis_pekerjaan = JenisPekerjaan::query()->select('kode_jenis_pekerjaan', 'jenis_pekerjaan')->where('kode_cabang', $user_cabang)->where('is_active', 'Y')->orderBy('jenis_pekerjaan', 'asc')->get();
    $panel_pekerjaan = PanelPekerjaan::query()->select('kode_panel_pekerjaan', 'panel_pekerjaan')->where('kode_cabang', $user_cabang)->where('is_active', 'Y')->orderBy('panel_pekerjaan', 'asc')->get();
    $tipe_pekerjaan = Parameter::query()->where('nama_tabel', 'TIPE_PEKERJAAN')->orderBy('no_urut', 'asc')->get();

    $startDate = date("Y-m-d");
    $endDate = date("Y-m-d");
    $cekPPN = TarifPpn::where(function ($q) use ($startDate, $endDate) {
      $q->where('startdate', '<=', $endDate)
        ->where('enddate', '>=', $startDate);
    })->first();

    $ppn_persen = ($cekPPN) ? $cekPPN->ppn : 0;

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.administrasi.konsep-estimasi', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'username' => $username,
      'status_spk' => $status_spk,
      'status' => $status,
      'asuransi' => $asuransi,
      'estimator' => $estimator,
      'sparepart' => $sparepart,
      'jenis_pekerjaan' => $jenis_pekerjaan,
      'panel_pekerjaan' => $panel_pekerjaan,
      'tipe_pekerjaan' => $tipe_pekerjaan,
      'ppn_persen' => $ppn_persen,
      // 'surveyor' => $surveyor,
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

    if ($request->tipe == "konsep-estimasi-perbaikan") {
      $columns = [
        1 => 'a.id',
        2 => 'c.jenis_pekerjaan',
        3 => 'd.panel_pekerjaan',
        4 => 'a.harga',
        5 => 'a.tipe',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.idx';
      $dir   = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('t_konsep_estimasi_dtl1 as a')
        ->join('t_konsep_estimasi_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_konsep_estimasi', '=', 'a.kode_konsep_estimasi'); // syarat di JOIN
        })
        ->leftJoin('m_jenis_pekerjaan as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'a.kode_cabang')
            ->on('c.kode_jenis_pekerjaan', '=', 'a.kode_jenis_pekerjaan'); // syarat di JOIN
        })
        ->leftJoin('m_panel_pekerjaan as d', function ($join) {
          $join->on('d.kode_cabang', '=', 'a.kode_cabang')
            ->on('d.kode_panel_pekerjaan', '=', 'a.kode_panel_pekerjaan'); // syarat di JOIN
        })
        ->where('b.kode_cabang', $request->kode_cabang)
        ->where('b.kode_spk', $request->kode_spk);

      // Total baris tanpa filter
      $totalData = (clone $base)->count('a.id');

      // Filtering (search global)
      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('c.jenis_pekerjaan', 'like', "%{$search}%")
            ->orWhere('d.panel_pekerjaan', 'like', "%{$search}%")
            ->orWhere('a.harga', 'like', "%{$search}%");
        });
      }

      // Hitung setelah filter (tanpa limit/offset)
      $totalFiltered = (clone $query)->count('a.id');

      // Ambil data halaman saat ini
      $datas = $query
        ->select([
          'a.id',
          'a.kode_jenis_pekerjaan',
          'a.kode_panel_pekerjaan',
          'c.jenis_pekerjaan',
          'd.panel_pekerjaan',
          'a.harga',
          'a.tipe',
          'a.created_at',
          'a.created_by',
          'a.updated_at',
          'a.updated_by',
        ])
        ->orderBy($order, $dir)
        // ->offset($start)
        // ->limit($limit)
        ->get();

      // Susun payload DataTables
      $data = [];
      $fake = $start;
      foreach ($datas as $row) {
        $data[] = [
          'id'  => $row->id,
          'fake_id' => ++$fake,
          'kode_jenis_pekerjaan' => $row->kode_jenis_pekerjaan,
          'kode_panel_pekerjaan' => $row->kode_panel_pekerjaan,
          'jenis_pekerjaan' => $row->jenis_pekerjaan,
          'panel_pekerjaan' => $row->panel_pekerjaan,
          'harga' => number_format($row->harga, 2, '.', ','),
          'tipe' => $row->tipe,
          'created_at' => (blank($row->updated_at)) ? date("d/m/Y", strtotime($row->created_at)) : date("d/m/Y", strtotime($row->updated_at)),
          'created_by' => (blank($row->updated_by)) ? $row->created_by : $row->updated_by,
          'cek' => '0',
        ];
      }
    } elseif ($request->tipe == "konsep-estimasi-sparepart") {
      $columns = [
        1 => 'a.id',
        2 => 'a.kode_sparepart',
        3 => 'a.no_sparepart',
        4 => 'a.qty',
        5 => 'a.harga',
        6 => 'a.jumlah',
        7 => 'a.tipe',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.idx';
      $dir   = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('t_konsep_estimasi_dtl2 as a')
        ->join('t_konsep_estimasi_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_konsep_estimasi', '=', 'a.kode_konsep_estimasi'); // syarat di JOIN
        })
        ->leftJoin('m_sparepart as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'a.kode_cabang')
            ->on('c.kode_sparepart', '=', 'a.kode_sparepart'); // syarat di JOIN
        })
        ->where('b.kode_cabang', $request->kode_cabang)
        ->where('b.kode_spk', $request->kode_spk);

      // Total baris tanpa filter
      $totalData = (clone $base)->count('a.id');

      // Filtering (search global)
      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('c.nama_sparepart', 'like', "%{$search}%")
            ->orWhere('a.no_sparepart', 'like', "%{$search}%")
            ->orWhere('a.harga', 'like', "%{$search}%");
        });
      }

      // Hitung setelah filter (tanpa limit/offset)
      $totalFiltered = (clone $query)->count('a.id');

      // Ambil data halaman saat ini
      $datas = $query
        ->select([
          'a.id',
          'a.kode_sparepart',
          'c.nama_sparepart',
          'a.no_sparepart',
          'a.qty',
          'a.harga',
          'a.jumlah',
          'a.tipe',
          'a.created_at',
          'a.created_by',
          'a.updated_at',
          'a.updated_by',
        ])
        ->orderBy($order, $dir)
        // ->offset($start)
        // ->limit($limit)
        ->get();

      // Susun payload DataTables
      $data = [];
      $fake = $start;
      foreach ($datas as $row) {
        $data[] = [
          'id'  => $row->id,
          'fake_id' => ++$fake,
          'kode_sparepart' => $row->kode_sparepart,
          'nama_sparepart' => $row->nama_sparepart,
          'no_sparepart' => $row->no_sparepart,
          'qty' => number_format($row->qty, 0, '.', ','),
          'harga' => number_format($row->harga, 2, '.', ','),
          'jumlah' => number_format($row->jumlah, 2, '.', ','),
          'tipe' => $row->tipe,
          'created_at' => (blank($row->updated_at)) ? date("d/m/Y", strtotime($row->created_at)) : date("d/m/Y", strtotime($row->updated_at)),
          'created_by' => (blank($row->updated_by)) ? $row->created_by : $row->updated_by,
          'cek' => '0',
        ];
      }
    } elseif ($request->tipe == "konsep-estimasi-lain") {
      $columns = [
        1 => 'a.id',
        2 => 'a.memo',
        3 => 'a.harga',
        4 => 'a.tipe',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.idx';
      $dir   = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('t_konsep_estimasi_dtl3 as a')
        ->join('t_konsep_estimasi_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_konsep_estimasi', '=', 'a.kode_konsep_estimasi'); // syarat di JOIN
        })
        ->where('b.kode_cabang', $request->kode_cabang)
        ->where('b.kode_spk', $request->kode_spk);

      // Total baris tanpa filter
      $totalData = (clone $base)->count('a.id');

      // Filtering (search global)
      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('c.memo', 'like', "%{$search}%")
            ->orWhere('a.harga', 'like', "%{$search}%");
        });
      }

      // Hitung setelah filter (tanpa limit/offset)
      $totalFiltered = (clone $query)->count('a.id');

      // Ambil data halaman saat ini
      $datas = $query
        ->select([
          'a.id',
          'a.memo',
          'a.harga',
          'a.tipe',
          'a.created_at',
          'a.created_by',
          'a.updated_at',
          'a.updated_by',
        ])
        ->orderBy($order, $dir)
        // ->offset($start)
        // ->limit($limit)
        ->get();

      // Susun payload DataTables
      $data = [];
      $fake = $start;
      foreach ($datas as $row) {
        $data[] = [
          'id'  => $row->id,
          'fake_id' => ++$fake,
          'memo' => $row->memo,
          'harga' => number_format($row->harga, 2, '.', ','),
          'tipe' => $row->tipe,
          'created_at' => (blank($row->updated_at)) ? date("d/m/Y", strtotime($row->created_at)) : date("d/m/Y", strtotime($row->updated_at)),
          'created_by' => (blank($row->updated_by)) ? $row->created_by : $row->updated_by,
          'cek' => '0',
        ];
      }
    } else {
      $columns = [
        1 => 'k.id',
        2 => 'k.tgl_masuk',
        3 => 'k.kode_spk',
        4 => 'e.keterangan', // status
        5 => 'k.no_polisi',
        6 => 'b.nama_tipe',
        7 => 'k.pemilik',
        8 => 'c.nama_pelanggan',
        9 => 'k.tgl_batal',
        10 => 'k.tgl_turun_lapangan',
        11 => 'k.tgl_finishing1',
        12 => 'k.tgl_keluar',
        13 => 'd.keterangan', // status_spk
        14 => 'k.no_polis',
        15 => 'k.kode_claim',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'k.id';
      $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('t_spk_master as k')
        ->leftJoin('m_tipe_kendaraan as b', function ($join) {
          $join->on('b.kode_tipe', '=', 'k.kode_tipe')
            ->on('b.kode_merek', '=', 'k.kode_merek'); // syarat di JOIN
        })
        ->leftJoin('m_pelanggan_hdr as c', function ($join) {
          $join->on('c.kode_pelanggan', '=', 'k.kode_pelanggan')
            ->on('c.kode_cabang', '=', 'k.kode_cabang'); // syarat di JOIN
        })
        ->leftJoin('parameter as d', function ($join) {
          $join->on('d.kode', '=', 'k.kode_status_spk')
            ->where('d.nama_tabel', '=', 'STATUS_SPK'); // syarat di JOIN
        })
        ->leftJoin('parameter as e', function ($join) {
          $join->on('e.kode', '=', 'k.status_spk')
            ->where('e.nama_tabel', '=', 'STATUS_SPK_KET'); // syarat di JOIN
        })
        ->where('k.kode_cabang', $user_cabang);
      // ->whereMonth('k.tgl_masuk', date('m'))
      // ->whereYear('k.tgl_masuk', date('Y'));

      // Total baris tanpa filter
      $totalData = (clone $base)->count('k.id');

      // Filtering (search global)
      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('k.kode_spk', 'like', "%{$search}%")
            ->orWhere('k.no_polisi', 'like', "%{$search}%")
            ->orWhere('k.pemilik', 'like', "%{$search}%");
        });
      }

      $status = 'all';
      // Filter berdasarkan input yang dikirim dari DataTables
      if ($request->filled('kode_spk')) {
        $status = '';
        $query->where('k.kode_spk', 'like', '%' . $request->kode_spk . '%');
      }
      if ($request->filled('no_polisi')) {
        $status = '';
        $query->where('k.no_polisi', 'like', '%' . $request->no_polisi . '%');
      }
      if ($request->filled('tgl_masuk_awal')) {
        $status = '';
        $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_masuk_awal, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('k.tgl_masuk', '>=', $startDate);
      }
      if ($request->filled('tgl_masuk_akhir')) {
        $status = '';
        $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_masuk_akhir, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('k.tgl_masuk', '<=', $endDate);
      }
      if ($request->filled('nama_pelanggan')) {
        $status = '';
        $query->where('c.nama_pelanggan', 'like', '%' . $request->nama_pelanggan . '%');
      }
      if ($request->filled('nama_pemilik')) {
        $status = '';
        $query->where('k.pemilik', 'like', '%' . $request->nama_pemilik . '%');
      }
      if ($request->filled('no_polis')) {
        $status = '';
        $query->where('k.no_polis', 'like', '%' . $request->no_polis . '%');
      }
      if ($request->filled('kode_claim')) {
        $status = '';
        $query->where('k.kode_claim', 'like', '%' . $request->kode_claim . '%');
      }
      if ($request->filled('status_spk')) {
        if ($request->status_spk <> 'all') {
          $status = '';
          $query->where('k.kode_status_spk', 'like', '%' . $request->status_spk . '%');
        }
      }
      if ($request->filled('status')) {
        if ($request->status <> 'all') {
          $query->where('k.status_spk', 'like', '%' . $request->status . '%');
        } else {
          // $query->whereIn('k.status_spk', ['01', '02']);
        }
      }
      // if ($status == "all") {
      //   $query->whereIn('k.status_spk', ['01', '02']);
      // }

      // Hitung setelah filter (tanpa limit/offset)
      $totalFiltered = (clone $query)->count('k.id');

      // Ambil data halaman saat ini
      $datas = $query
        ->select([
          'k.id',
          'k.kode_cabang',
          'k.tgl_masuk',
          'k.kode_spk',
          'e.keterangan as status',
          'k.no_polisi',
          'k.kode_tipe',
          'b.nama_tipe',
          'k.pemilik',
          'k.kode_pelanggan',
          'c.nama_pelanggan',
          'k.tgl_batal',
          'k.tgl_turun_lapangan',
          'k.tgl_finishing1',
          'k.tgl_keluar',
          'd.keterangan as status_spk',
          'k.no_polis',
          'k.kode_claim',
          'k.status_spk as kode_status_spk',
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
          'id'  => $row->id,
          'fake_id' => ++$fake,
          'kode_cabang' => $row->kode_cabang,
          'kode_spk' => $row->kode_spk,
          'keterangan' => $row->status,
          'no_polisi' => $row->no_polisi,
          'kode_tipe' => $row->kode_tipe,
          'nama_tipe' => $row->nama_tipe,
          'pemilik' => $row->pemilik,
          'kode_pelanggan' => $row->kode_pelanggan,
          'nama_pelanggan' => $row->nama_pelanggan,
          'kode_status_spk' => $row->kode_status_spk,
          'status_spk' => $row->status_spk,
          'no_polis' => $row->no_polis,
          'kode_claim' => $row->kode_claim,
          'tgl_masuk' => blank($row->tgl_masuk) ? '' : date("d/m/Y", strtotime($row->tgl_masuk)),
          'tgl_batal' => blank($row->tgl_batal) ? '' : date("d/m/Y", strtotime($row->tgl_batal)),
          'tgl_turun_lapangan' => blank($row->tgl_turun_lapangan) ? '' : date("d/m/Y", strtotime($row->tgl_turun_lapangan)),
          'tgl_finishing1' => blank($row->tgl_finishing1) ? '' : date("d/m/Y", strtotime($row->tgl_finishing1)),
          'tgl_keluar' => blank($row->tgl_keluar) ? '' : date("d/m/Y", strtotime($row->tgl_keluar)),
        ];
      }
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
    $user_cabang = session('kd_cabang');
    $dataID = $request->konsep_id;

    if ($dataID) {
      $res = KonsepEstimasi::findOrFail($dataID);

      $rules = [
        'kode_pelanggan' => 'required',
        'kode_estimator' => 'required',
        'nama_surveyor' => 'required',
        'tgl_survey' => 'required',
        'lama_pekerjaan' => 'required',
      ];

      $messages = [
        'kode_pelanggan.required' => 'Nama Asuransi Wajib diisi',
        'kode_estimator.required' => 'Nama Estimator Wajib diisi',
        'nama_surveyor.required' => 'Nama Surveyor Wajib diisi',
        'tgl_survey.required' => 'Tanggal Survey Wajib diisi',
        'lama_pekerjaan.required'  => 'Lama Pekerjaan Wajib diisi',
      ];

      $validator = Validator::make($request->all(), $rules, $messages);

      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ], 200);
      }

      $data = [
        'kode_pelanggan' => $request->kode_pelanggan,
        'kode_spk' => $request->kode_spk,
        'tahun' => $request->tahun,
        'kode_estimator' => $request->kode_estimator,
        'lama_pekerjaan' => $request->lama_pekerjaan,
        'nama_surveyor' => $request->nama_surveyor,
        'tgl_survey' => blank($request->tgl_survey) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_survey), 'Asia/Jakarta')->format('Y-m-d'),
        'memo' => $request->memo,
        'total_perbaikan' => blank($request->total_perbaikan) ? 0 : str_replace([","], "", $request->total_perbaikan),
        'total_sparepart' => blank($request->total_sparepart) ? 0 : str_replace([","], "", $request->total_sparepart),
        'total_lain' => blank($request->total_lain) ? 0 : str_replace([","], "", $request->total_lain),
        'total' => blank($request->total) ? 0 : str_replace([","], "", $request->total),
        'updated_by' => Auth::user()->username
      ];

      $result = $res->update($data);

      if ($result) {
        ## INSERT DETAIL PERBAIKAN
        // KonsepEstimasiPerbaikan::where('kode_cabang', $res->kode_cabang)->where('kode_konsep_estimasi', $res->kode_konsep_estimasi)->delete();
        if ($request->pekerjaan) {
          $no = KonsepEstimasiPerbaikan::where('kode_cabang', $res->kode_cabang)->where('kode_konsep_estimasi', $res->kode_konsep_estimasi)->count() + 1;
          $processedIds = [];
          foreach ($request->pekerjaan as $key => $item) {

            $isExist = KonsepEstimasiPerbaikan::where('id', $key)->exists();

            if ($isExist) {
              if ($item['cek'] == "1") {
                $tmpData = [
                  'kode_jenis_pekerjaan'  => $item['jenis'],
                  'kode_panel_pekerjaan'  => $item['panel'],
                  'harga'                 => str_replace([","], "", $item['harga']),
                  'tipe'                  => $item['tipe'],
                  'updated_by'            => Auth::user()->username,
                ];

                KonsepEstimasiPerbaikan::where('id', $key)->update($tmpData);
              }

              $processedIds[] = $key;
            } else {
              $tmpData = [
                'kode_cabang'           => $res->kode_cabang,
                'kode_konsep_estimasi'  => $res->kode_konsep_estimasi,
                'idx'                   => $no++,
                'kode_jenis_pekerjaan'  => $item['jenis'],
                'kode_panel_pekerjaan'  => $item['panel'],
                'harga'                 => str_replace([","], "", $item['harga']),
                'tipe'                  => $item['tipe'],
                'created_by'            => Auth::user()->username,
              ];

              $newRecord = KonsepEstimasiPerbaikan::create($tmpData);

              $processedIds[] = $newRecord->id;
            }

            // KonsepEstimasiPerbaikan::create([
            //   'kode_cabang'           => $res->kode_cabang,
            //   'kode_konsep_estimasi'  => $res->kode_konsep_estimasi,
            //   'idx'                   => $no++,
            //   'kode_jenis_pekerjaan'  => $item['jenis'],
            //   'kode_panel_pekerjaan'  => $item['panel'],
            //   'harga'                 => str_replace([","], "", $item['harga']),
            //   'tipe'                  => $item['tipe'],
            //   'created_by'            => Auth::user()->username,
            // ]);
          }

          // Hapus sisa data yang tidak ada di form
          KonsepEstimasiPerbaikan::where('kode_cabang', $res->kode_cabang)
            ->where('kode_konsep_estimasi', $res->kode_konsep_estimasi)
            ->whereNotIn('id', $processedIds)
            ->delete();
        } else {
          KonsepEstimasiPerbaikan::where('kode_cabang', $res->kode_cabang)->where('kode_konsep_estimasi', $res->kode_konsep_estimasi)->delete();
        }

        ## INSERT DETAIL SPAREPART
        // KonsepEstimasiSparepart::where('kode_cabang', $res->kode_cabang)->where('kode_konsep_estimasi', $res->kode_konsep_estimasi)->delete();
        if ($request->sparepart) {
          $no = KonsepEstimasiSparepart::where('kode_cabang', $res->kode_cabang)->where('kode_konsep_estimasi', $res->kode_konsep_estimasi)->count() + 1;
          $processedIds = [];
          foreach ($request->sparepart as $key => $item) {

            $isExist = KonsepEstimasiSparepart::where('id', $key)->exists();

            if ($isExist) {
              if ($item['cek'] == "1") {
                $tmpData = [
                  'kode_sparepart'        => $item['kode_sparepart'],
                  'no_sparepart'          => $item['no_sparepart'],
                  'qty'                   => str_replace([","], "", $item['qty']),
                  'harga'                 => str_replace([","], "", $item['harga']),
                  'jumlah'                => str_replace([","], "", $item['jumlah']),
                  'up'                    => 0,
                  'tipe'                  => $item['tipe'],
                  'updated_by'            => Auth::user()->username,
                ];

                KonsepEstimasiSparepart::where('id', $key)->update($tmpData);
              }

              $processedIds[] = $key;
            } else {
              $tmpData = [
                'kode_cabang'           => $res->kode_cabang,
                'kode_konsep_estimasi'  => $res->kode_konsep_estimasi,
                'idx'                   => $no++,
                'kode_sparepart'        => $item['kode_sparepart'],
                'no_sparepart'          => $item['no_sparepart'],
                'qty'                   => str_replace([","], "", $item['qty']),
                'harga'                 => str_replace([","], "", $item['harga']),
                'jumlah'                => str_replace([","], "", $item['jumlah']),
                'up'                    => 0,
                'tipe'                  => $item['tipe'],
                'created_by'            => Auth::user()->username,
              ];

              $newRecord = KonsepEstimasiSparepart::create($tmpData);

              $processedIds[] = $newRecord->id;
            }

            // KonsepEstimasiSparepart::create([
            //   'kode_cabang'           => $res->kode_cabang,
            //   'kode_konsep_estimasi'  => $res->kode_konsep_estimasi,
            //   'idx'                   => $no++,
            //   'kode_sparepart'        => $item['kode_sparepart'],
            //   'no_sparepart'          => $item['no_sparepart'],
            //   'qty'                   => str_replace([","], "", $item['qty']),
            //   'harga'                 => str_replace([","], "", $item['harga']),
            //   'jumlah'                => str_replace([","], "", $item['jumlah']),
            //   'up'                    => 0,
            //   'tipe'                  => $item['tipe'],
            //   'created_by'            => Auth::user()->username,
            // ]);
          }

          // Hapus sisa data yang tidak ada di form
          KonsepEstimasiSparepart::where('kode_cabang', $res->kode_cabang)
            ->where('kode_konsep_estimasi', $res->kode_konsep_estimasi)
            ->whereNotIn('id', $processedIds)
            ->delete();
        } else {
          KonsepEstimasiSparepart::where('kode_cabang', $res->kode_cabang)->where('kode_konsep_estimasi', $res->kode_konsep_estimasi)->delete();
        }

        ## INSERT DETAIL LAINNYA
        // KonsepEstimasiLain::where('kode_cabang', $res->kode_cabang)->where('kode_konsep_estimasi', $res->kode_konsep_estimasi)->delete();
        if ($request->lain) {
          $no = KonsepEstimasiLain::where('kode_cabang', $res->kode_cabang)->where('kode_konsep_estimasi', $res->kode_konsep_estimasi)->count() + 1;
          $processedIds = [];
          foreach ($request->lain as $key => $item) {

            $isExist = KonsepEstimasiLain::where('id', $key)->exists();

            if ($isExist) {
              if ($item['cek'] == "1") {
                $tmpData = [
                  'memo'                  => $item['memo'],
                  'harga'                 => str_replace([","], "", $item['harga']),
                  'tipe'                  => $item['tipe'],
                  'updated_by'            => Auth::user()->username,
                ];

                KonsepEstimasiLain::where('id', $key)->update($tmpData);
              }

              $processedIds[] = $key;
            } else {
              $tmpData = [
                'kode_cabang'           => $res->kode_cabang,
                'kode_konsep_estimasi'  => $res->kode_konsep_estimasi,
                'idx'                   => $no++,
                'memo'                  => $item['memo'],
                'harga'                 => str_replace([","], "", $item['harga']),
                'tipe'                  => $item['tipe'],
                'created_by'            => Auth::user()->username,
              ];

              $newRecord = KonsepEstimasiLain::create($tmpData);

              $processedIds[] = $newRecord->id;
            }

            // KonsepEstimasiLain::create([
            //   'kode_cabang'           => $res->kode_cabang,
            //   'kode_konsep_estimasi'  => $res->kode_konsep_estimasi,
            //   'idx'                   => $no++,
            //   'memo'                  => $item['memo'],
            //   'harga'                 => str_replace([","], "", $item['harga']),
            //   'tipe'                  => $item['tipe'],
            //   'created_by'            => Auth::user()->username,
            // ]);
          }
        } else {
          KonsepEstimasiLain::where('kode_cabang', $res->kode_cabang)->where('kode_konsep_estimasi', $res->kode_konsep_estimasi)->delete();
        }
      }

      ## Log Activity
      $desc = $result ? 'Berhasil Ubah Konsep Estimasi' : 'Gagal Ubah Konsep Estimasi';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$result,
        'message' => $desc
      ]);
    } else {

      $rules = [
        'kode_pelanggan' => 'required',
        'kode_estimator' => 'required',
        'nama_surveyor' => 'required',
        'tgl_survey' => 'required',
        'lama_pekerjaan' => 'required',
      ];

      $messages = [
        'kode_pelanggan.required' => 'Nama Asuransi Wajib diisi',
        'kode_estimator.required' => 'Nama Estimator Wajib diisi',
        'nama_surveyor.required' => 'Nama Surveyor Wajib diisi',
        'tgl_survey.required' => 'Tanggal Survey Wajib diisi',
        'lama_pekerjaan.required'  => 'Lama Pekerjaan Wajib diisi',
      ];

      $validator = Validator::make($request->all(), $rules, $messages);

      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ], 200);
      }

      $penomoran = Helper::getNomorTransaksi($user_cabang, 'KE');

      $cekspk = KonsepEstimasi::where('kode_konsep_estimasi', $penomoran)->first();
      if (!empty($cekspk)) {
        return response()->json(['status' => false, 'message' => "Nomor Konsep Estimasi sudah digunakan"]);
      }

      $lastNum = KonsepEstimasi::query()->where('kode_cabang', $user_cabang)->max(DB::raw('CAST(seq_id AS UNSIGNED)')) ?? 0;
      $seq_id = $lastNum + 1;

      $data = [
        'kode_cabang' => $user_cabang,
        'seq_id' => $seq_id,
        'kode_konsep_estimasi' => $penomoran,
        'kode_pelanggan' => $request->kode_pelanggan,
        'tanggal' => date("Y-m-d"),
        'kode_spk' => $request->kode_spk,
        'tahun' => $request->tahun,
        'kode_estimator' => $request->kode_estimator,
        'lama_pekerjaan' => $request->lama_pekerjaan,
        'nama_surveyor' => $request->nama_surveyor,
        'tgl_survey' => blank($request->tgl_survey) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_survey), 'Asia/Jakarta')->format('Y-m-d'),
        'memo' => $request->memo,
        'total_perbaikan' => blank($request->total_perbaikan) ? 0 : str_replace([","], "", $request->total_perbaikan),
        'total_sparepart' => blank($request->total_sparepart) ? 0 : str_replace([","], "", $request->total_sparepart),
        'total_lain' => blank($request->total_lain) ? 0 : str_replace([","], "", $request->total_lain),
        'total' => blank($request->total) ? 0 : str_replace([","], "", $request->total),
        'created_by' => Auth::user()->username
      ];

      $result = KonsepEstimasi::create($data);

      if ($result) {
        ## INSERT DETAIL PERBAIKAN
        KonsepEstimasiPerbaikan::where('kode_cabang', $user_cabang)->where('kode_konsep_estimasi', $penomoran)->delete();
        if ($request->pekerjaan) {
          $no = 1;
          foreach ($request->pekerjaan as $item) {
            KonsepEstimasiPerbaikan::create([
              'kode_cabang'           => $user_cabang,
              'kode_konsep_estimasi'  => $penomoran,
              'idx'                   => $no++,
              'kode_jenis_pekerjaan'  => $item['jenis'],
              'kode_panel_pekerjaan'  => $item['panel'],
              'harga'                 => str_replace([","], "", $item['harga']),
              'tipe'                  => $item['tipe'],
              'created_by'            => Auth::user()->username,
            ]);
          }
        }

        ## INSERT DETAIL SPAREPART
        KonsepEstimasiSparepart::where('kode_cabang', $user_cabang)->where('kode_konsep_estimasi', $penomoran)->delete();
        if ($request->sparepart) {
          $no = 1;
          foreach ($request->sparepart as $item) {
            KonsepEstimasiSparepart::create([
              'kode_cabang'           => $user_cabang,
              'kode_konsep_estimasi'  => $penomoran,
              'idx'                   => $no++,
              'kode_sparepart'        => $item['kode_sparepart'],
              'no_sparepart'          => $item['no_sparepart'],
              'qty'                   => str_replace([","], "", $item['qty']),
              'harga'                 => str_replace([","], "", $item['harga']),
              'jumlah'                => str_replace([","], "", $item['jumlah']),
              'up'                    => 0,
              'tipe'                  => $item['tipe'],
              'created_by'            => Auth::user()->username,
            ]);
          }
        }

        ## INSERT DETAIL LAINNYA
        KonsepEstimasiLain::where('kode_cabang', $user_cabang)->where('kode_konsep_estimasi', $penomoran)->delete();
        if ($request->lain) {
          $no = 1;
          foreach ($request->lain as $item) {
            KonsepEstimasiLain::create([
              'kode_cabang'           => $user_cabang,
              'kode_konsep_estimasi'  => $penomoran,
              'idx'                   => $no++,
              'memo'                  => $item['memo'],
              'harga'                 => str_replace([","], "", $item['harga']),
              'tipe'                  => $item['tipe'],
              'created_by'            => Auth::user()->username,
            ]);
          }
        }


        ## Update Status SPK
        $dataspk = Spk::updateOrCreate(
          ['id' => $request->id],
          [
            'status_spk' => '02',
            'updated_by' => Auth::user()->username
          ]
        );

        ## Update Nomor Konsep Estimasi
        $res = Helper::updateNomorTransaksi($user_cabang, 'KE', $penomoran);
      }

      ## Log Activity
      $desc = $result ? 'Berhasil Tambah Konsep Estimasi' : 'Gagal Tambah Konsep Estimasi';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$result,
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
    $data = DB::table('v_trx_konsep_estimasi')->where('id', $id)->first();

    if (blank($data)) {
      $result = false;
      return response()->json([
        'status'  => (bool)$result,
        'message' => 'Konsep Estimasi belum dibuat!'
      ]);
    }

    $result = true;
    return response()->json([
      'status'  => (bool)$result,
      'message' => 'Berhasil Kirim Konsep Estimasi',
      'data' => $data
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id): JsonResponse
  {
    // $data = Spk::findOrFail($id);
    $data = DB::table('v_spk')->where('id', $id)->first();
    $dataEstimasi = KonsepEstimasi::query()->where('kode_cabang', $data->kode_cabang)->where('kode_spk', $data->kode_spk)->first();

    if ($dataEstimasi) {
      $data->tgl_konsep = blank($dataEstimasi->tanggal) ? '' : date("d/m/Y", strtotime($dataEstimasi->tanggal));
      $data->tgl_survey = blank($dataEstimasi->tgl_survey) ? '' : date("d/m/Y", strtotime($dataEstimasi->tgl_survey));
      $data->nama_surveyor = $dataEstimasi->nama_surveyor;
      $data->kode_pelanggan = $dataEstimasi->kode_pelanggan;
      $data->kode_estimator = $dataEstimasi->kode_estimator;
      $data->lama_pekerjaan = $dataEstimasi->lama_pekerjaan;
      $data->memo = $dataEstimasi->memo;
      $data->konsep_id = $dataEstimasi->id;
    } else {
      $data->tgl_konsep = date("d/m/Y");
      $data->lama_pekerjaan = 0;
      $data->memo = '';
      $data->konsep_id = '';
      $data->nama_surveyor = '';
      $data->tgl_survey = '';
    }


    $data->tgl_batal = blank($data->tgl_batal) ? '' : date("d/m/Y", strtotime($data->tgl_batal));
    $data->tgl_keluar = blank($data->tgl_keluar) ? '' : date("d/m/Y", strtotime($data->tgl_keluar));
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
    //$datas = Spk::where('id', $id)->delete();
  }

  public function cetakKonsepEstimasi(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Cetak Konsep Estimasi';

    $id = $request->id;

    $data = DB::table('v_trx_konsep_estimasi')->where('id', $id)->first();

    if (blank($data)) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }

    $data->tgl_konsep = date("d-M-Y", strtotime($data->tgl_konsep));
    $data->total_perbaikan = number_format($data->total_perbaikan, 0, ".", ",");
    $data->total_sparepart = number_format($data->total_sparepart, 0, ".", ",");
    $data->total_lain = number_format($data->total_lain, 0, ".", ",");
    $data->total = number_format($data->total, 0, ".", ",");

    $cabang = ProfilePerusahaan::where('kode_cabang', $data->kode_cabang)->first();
    $cabang->alamat1 = sprintf("%s %s %s", $cabang->alamat1, $cabang->alamat2, $cabang->alamat3);

    $dest = public_path('assets/img/cabang');
    $logo_cabang = $dest . DIRECTORY_SEPARATOR . $cabang->logo_cabang;
    $file_logo = (is_file($logo_cabang)) ? "1" : "0";

    // $data->terbilang = Helper::terbilang_rupiah($data->total_or);

    ## DATA DETAIL PERBAIKAN
    $data_perbaikan = DB::table('t_konsep_estimasi_dtl1 as a')
      ->join('t_konsep_estimasi_hdr as b', function ($join) {
        $join->on('b.kode_cabang', '=', 'a.kode_cabang')
          ->on('b.kode_konsep_estimasi', '=', 'a.kode_konsep_estimasi'); // syarat di JOIN
      })
      ->leftJoin('m_jenis_pekerjaan as c', function ($join) {
        $join->on('c.kode_cabang', '=', 'a.kode_cabang')
          ->on('c.kode_jenis_pekerjaan', '=', 'a.kode_jenis_pekerjaan'); // syarat di JOIN
      })
      ->leftJoin('m_panel_pekerjaan as d', function ($join) {
        $join->on('d.kode_cabang', '=', 'a.kode_cabang')
          ->on('d.kode_panel_pekerjaan', '=', 'a.kode_panel_pekerjaan'); // syarat di JOIN
      })
      ->where('b.kode_cabang', $data->kode_cabang)
      ->where('b.kode_spk', $data->kode_spk)
      ->select(['a.idx', 'c.jenis_pekerjaan', 'd.panel_pekerjaan', 'a.harga'])
      ->orderBy('a.idx', 'asc')
      ->get();

    ## DATA DETAIL SPAREPART
    $data_sparepart = DB::table('t_konsep_estimasi_dtl2 as a')
      ->join('t_konsep_estimasi_hdr as b', function ($join) {
        $join->on('b.kode_cabang', '=', 'a.kode_cabang')
          ->on('b.kode_konsep_estimasi', '=', 'a.kode_konsep_estimasi'); // syarat di JOIN
      })
      ->leftJoin('m_sparepart as c', function ($join) {
        $join->on('c.kode_cabang', '=', 'a.kode_cabang')
          ->on('c.kode_sparepart', '=', 'a.kode_sparepart'); // syarat di JOIN
      })
      ->where('b.kode_cabang', $data->kode_cabang)
      ->where('b.kode_spk', $data->kode_spk)
      ->select(['a.idx', 'c.nama_sparepart', 'a.no_sparepart', 'a.qty', 'a.harga', 'a.jumlah'])
      ->orderBy('a.idx', 'asc')
      ->get();

    ## DATA DETAIL LAIN
    $data_lain = DB::table('t_konsep_estimasi_dtl3 as a')
      ->join('t_konsep_estimasi_hdr as b', function ($join) {
        $join->on('b.kode_cabang', '=', 'a.kode_cabang')
          ->on('b.kode_konsep_estimasi', '=', 'a.kode_konsep_estimasi'); // syarat di JOIN
      })
      ->where('b.kode_cabang', $data->kode_cabang)
      ->where('b.kode_spk', $data->kode_spk)
      ->select(['a.idx', 'a.memo', 'a.harga'])
      ->orderBy('a.idx', 'asc')
      ->get();

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.administrasi.konsep-estimasi-print', [
      'pageConfigs' => $pageConfigs,
      'title' => $title,
      'cabang' => $cabang,
      'data' => $data,
      'data_perbaikan' => $data_perbaikan,
      'data_sparepart' => $data_sparepart,
      'data_lain' => $data_lain,
      'file_logo' => $file_logo,
    ]);
  }
}
