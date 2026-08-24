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
use App\Models\TarifPpn;
use App\Models\Spk;
use App\Models\Parameter;
use App\Models\JenisPekerjaan;
use App\Models\PanelPekerjaan;
use App\Models\Asuransi;
use App\Models\Sparepart;
use App\Models\Surveyor;
use App\Models\Estimasi;
use App\Models\EstimasiPerbaikan;
use App\Models\EstimasiSparepart;
use App\Models\EstimasiLain;
use App\Models\LogActivity;
use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class EstimasiDisetujuiController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function EstimasiDisetujui(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Estimasi Disetujui';

    $user_cabang = session('kd_cabang');
    $status_spk = Parameter::query()->where('nama_tabel', 'STATUS_SPK')->orderBy('no_urut', 'asc')->get();
    $status = Parameter::query()->where('nama_tabel', 'STATUS_SPK_KET')->orderBy('no_urut', 'asc')->get();
    $asuransi = Asuransi::query()->select('kode_pelanggan', 'nama_pelanggan')->where('kode_cabang', $user_cabang)->where('is_active', 'Y')->orderBy('nama_pelanggan', 'asc')->get();
    $surveyor = Surveyor::query()->select('kode_surveyor', 'nama_surveyor')->where('kode_cabang', $user_cabang)->where('is_active', 'Y')->orderBy('nama_surveyor', 'asc')->get();
    $sparepart = Sparepart::query()->select('kode_sparepart', 'nama_sparepart')->where('kode_cabang', $user_cabang)->where('is_active', 'Y')->orderBy('nama_sparepart', 'asc')->get();
    $jenis_pekerjaan = JenisPekerjaan::query()->select('kode_jenis_pekerjaan', 'jenis_pekerjaan')->where('kode_cabang', $user_cabang)->where('is_active', 'Y')->orderBy('jenis_pekerjaan', 'asc')->get();
    $panel_pekerjaan = PanelPekerjaan::query()->select('kode_panel_pekerjaan', 'panel_pekerjaan')->where('kode_cabang', $user_cabang)->where('is_active', 'Y')->orderBy('panel_pekerjaan', 'asc')->get();
    $tipe_pekerjaan = Parameter::query()->where('nama_tabel', 'TIPE_PEKERJAAN')->orderBy('no_urut', 'asc')->get();

    // $ppn_persen = 10;
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

    return view('content.administrasi.estimasi-disetujui', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'status_spk' => $status_spk,
      'status' => $status,
      'asuransi' => $asuransi,
      'surveyor' => $surveyor,
      'sparepart' => $sparepart,
      'jenis_pekerjaan' => $jenis_pekerjaan,
      'panel_pekerjaan' => $panel_pekerjaan,
      'tipe_pekerjaan' => $tipe_pekerjaan,
      'ppn_persen' => $ppn_persen,
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

    if ($request->tipe == "estimasi-perbaikan") {
      $columns = [
        1 => 'a.id',
        2 => 'c.jenis_pekerjaan',
        3 => 'd.panel_pekerjaan',
        4 => 'a.harga',
        5 => 'a.tipe',
        6 => 'a.harga_s',
        7 => 'a.cek',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.idx';
      $dir   = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('t_estimasi_dtl1 as a')
        ->join('t_estimasi_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_estimasi', '=', 'a.kode_estimasi'); // syarat di JOIN
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
          'a.harga_s',
          'a.cek',
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
          'harga_s' => ($row->harga_s > 0) ? number_format($row->harga_s, 2, '.', ',') : number_format($row->harga, 2, '.', ','),
          'cek' => $row->cek,
        ];
      }
    } elseif ($request->tipe == "estimasi-sparepart") {
      $columns = [
        1 => 'a.id',
        2 => 'a.kode_sparepart',
        3 => 'a.no_sparepart',
        4 => 'a.qty',
        5 => 'a.harga',
        6 => 'a.jumlah',
        7 => 'a.tipe',
        8 => 'a.jumlah_s',
        9 => 'a.cek',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.idx';
      $dir   = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('t_estimasi_dtl2 as a')
        ->join('t_estimasi_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_estimasi', '=', 'a.kode_estimasi'); // syarat di JOIN
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
          'a.jumlah_s',
          'a.cek',
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
          'jumlah_s' => ($row->jumlah_s > 0) ? number_format($row->jumlah_s, 2, '.', ',') : number_format($row->jumlah, 2, '.', ','),
          'cek' => $row->cek,
        ];
      }
    } elseif ($request->tipe == "estimasi-lain") {
      $columns = [
        1 => 'a.id',
        2 => 'a.memo',
        3 => 'a.harga',
        4 => 'a.tipe',
        5 => 'a.harga_s',
        6 => 'a.cek',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.idx';
      $dir   = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('t_estimasi_dtl3 as a')
        ->join('t_estimasi_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_estimasi', '=', 'a.kode_estimasi'); // syarat di JOIN
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
          'a.harga_s',
          'a.cek',
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
          'harga_s' => ($row->harga_s > 0) ? number_format($row->harga_s, 2, '.', ',') : number_format($row->harga, 2, '.', ','),
          'cek' => $row->cek,
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
      //   $query->whereIn('k.status_spk', ['05','06']);
      // }

      // Hitung setelah filter (tanpa limit/offset)
      $totalFiltered = (clone $query)->count('k.id');

      // Ambil data halaman saat ini
      $datas = $query
        ->select([
          'k.id',
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
    $dataID = $request->id;

    if ($dataID) {
      $res = Estimasi::findOrFail($dataID);

      $rules = [
        'kode_claim' => 'required',
        'tgl_persetujuan' => 'required',
        'disetujui_oleh' => 'required',
      ];

      $messages = [
        'kode_claim.required' => 'Nomor Klaim Wajib diisi',
        'tgl_persetujuan.required'  => 'Tanggal Persetujuan Wajib diisi',
        'disetujui_oleh.required'  => 'Disetujui Oleh Wajib diisi',
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
        'kode_claim' => $request->kode_claim,
        'total_perbaikan_s' => blank($request->total_perbaikan_s) ? 0 : str_replace([","], "", $request->total_perbaikan_s),
        'total_sparepart_s' => blank($request->total_sparepart_s) ? 0 : str_replace([","], "", $request->total_sparepart_s),
        'total_lain_s' => blank($request->total_lain_s) ? 0 : str_replace([","], "", $request->total_lain_s),
        'total_s' => blank($request->total_s) ? 0 : str_replace([","], "", $request->total_s),
        'ppn_s' => blank($request->ppn_s) ? 0 : str_replace([","], "", $request->ppn_s),
        'salvage' => blank($request->salvage) ? 0 : str_replace([","], "", $request->salvage),
        // 'persen_jasa' => blank($request->persen_jasa) ? 0 : str_replace([","], "", $request->persen_jasa),
        // 'persen_bahan' => blank($request->persen_bahan) ? 0 : str_replace([","], "", $request->persen_bahan),
        'memo' => $request->memo,
        'disetujui_oleh' => $request->disetujui_oleh,
        // 'total_or_ass' => 0,
        // 'penyusutan_sparepart' => 0,
        // 'prorata' => 0,
        // 'pph' => 0,
        'tgl_persetujuan' => blank($request->tgl_persetujuan) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_persetujuan), 'Asia/Jakarta')->format('Y-m-d'),
        'sifat_ppn' => $request->sifat_ppn,
        'sparepart_ppn' => $request->sparepart_ppn,
        'lain_ppn' => $request->lain_ppn,
        'updated_by' => Auth::user()->username
      ];

      if (blank($request->kode_persetujuan)) {
        $penomoran = Helper::getNomorTransaksi($user_cabang, 'ED');

        $isExist = Estimasi::where('kode_persetujuan', $penomoran)->exists();
        if ($isExist) {
          return response()->json(['status' => false, 'message' => "Nomor Turun Lapangan sudah digunakan"]);
        }

        $data['kode_persetujuan'] = $penomoran;
      }

      $result = $res->update($data);

      if ($result) {
        ## UPDATE DETAIL PERBAIKAN
        if ($request->pekerjaan) {
          foreach ($request->pekerjaan as $key => $item) {
            EstimasiPerbaikan::where('id', $key)
              ->update([
                'harga_s'    => str_replace([","], "", $item['harga']),
                'cek'        => isset($item['cek']) ? $item['cek'] : '0',
                'updated_by' => Auth::user()->username,
              ]);
          }
        }

        ## INSERT DETAIL SPAREPART
        if ($request->sparepart) {
          foreach ($request->sparepart as $key => $item) {
            EstimasiSparepart::where('id', $key)
              ->update([
                'jumlah_s'   => str_replace([","], "", $item['harga']),
                'cek'        => isset($item['cek']) ? $item['cek'] : '0',
                'updated_by' => Auth::user()->username,
              ]);
          }
        }

        ## INSERT DETAIL LAINNYA
        if ($request->lain) {
          foreach ($request->lain as $key => $item) {
            EstimasiLain::where('id', $key)
              ->update([
                'harga_s'    => str_replace([","], "", $item['harga']),
                'cek'        => isset($item['cek']) ? $item['cek'] : '0',
                'updated_by' => Auth::user()->username,
              ]);
          }
        }

        ## Update Status SPK
        $dataspk = Spk::updateOrCreate(
          [
            'kode_spk' => $res->kode_spk,
            'kode_cabang' => $user_cabang
          ],
          [
            'kode_claim' => $request->kode_claim,
            'status_spk' => '05',
            'updated_by' => Auth::user()->username
          ]
        );

        if (blank($request->kode_persetujuan)) {
          ## Update Nomor Transaksi
          $res = Helper::updateNomorTransaksi($user_cabang, 'ED', $penomoran);
        }
      }

      ## Log Activity
      $desc = $result ? 'Berhasil Proses Estimasi Disetujui' : 'Gagal Proses Estimasi Disetujui';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$result,
        'message' => $desc
      ]);
    } else {
      $result = false;

      ## Log Activity
      $desc = 'ID Estimasi tidak ditemukan';
      LogActivity::saveLogActivity($desc);

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
    // $data = Spk::findOrFail($id);
    $data = DB::table('v_trx_estimasi_disetujui')->where('id', $id)->first();

    if (blank($data)) {
      $result = false;
      return response()->json([
        'status'  => (bool)$result,
        'message' => 'Estimasi belum dibuat!'
      ]);
    }

    if($data->kode_jenis_pelanggan != "00002" && blank($data->kewajiban_id)) {
      $result = false;
      return response()->json([
        'status'  => (bool)$result,
        'message' => 'Kewajiban Tertanggung belum dibuat!'
      ]);
    }

    // $data->persen_jasa = blank($data->persen_jasa) ? '20' : $data->persen_jasa;
    // $data->persen_bahan = blank($data->persen_bahan) ? '80' : $data->persen_bahan;
    $data->sifat_ppn = blank($data->sifat_ppn) ? '0' : $data->sifat_ppn;
    $data->sparepart_ppn = blank($data->sparepart_ppn) ? '0' : $data->sparepart_ppn;
    $data->lain_ppn = blank($data->lain_ppn) ? '0' : $data->lain_ppn;
    $data->tgl_persetujuan = blank($data->tgl_persetujuan) ? date("d/m/Y") : date("d/m/Y", strtotime($data->tgl_persetujuan));
    $data->tgl_estimasi = blank($data->tgl_estimasi) ? date("d/m/Y") : date("d/m/Y", strtotime($data->tgl_estimasi));

    $data->total_perbaikan = number_format($data->total_perbaikan, 2, '.', ',');
    $data->total_sparepart = number_format($data->total_sparepart, 2, '.', ',');
    $data->total_lain = number_format($data->total_lain, 2, '.', ',');
    $data->total = number_format($data->total, 2, '.', ',');

    $data->total_kwitansi = number_format($data->total_perbaikan_s + $data->total_sparepart_s + $data->total_lain_s, 2, '.', ',');
    $data->total_perbaikan_s = number_format($data->total_perbaikan_s, 2, '.', ',');
    $data->total_sparepart_s = number_format($data->total_sparepart_s, 2, '.', ',');
    $data->total_lain_s = number_format($data->total_lain_s, 2, '.', ',');
    $data->total_or = number_format($data->total_or, 2, '.', ',');
    $data->ppn_s = number_format($data->ppn_s, 2, '.', ',');
    $data->total_s = number_format($data->total_s, 2, '.', ',');

    $result = true;
    return response()->json([
      'status'  => (bool)$result,
      'message' => 'Berhasil Kirim Estimasi',
      'data' => $data
    ]);
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
}
