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
use App\Models\Parameter;
use App\Models\Karyawan;
use App\Models\Spk;
use App\Models\Estimasi;
use App\Models\PointPanel;
use App\Models\LogActivity;
use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class ProgressKerjaController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function ProgressKerja(): View
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
    $title = Helper::getTitleMenu($path) ?? ' Progress Kerja';

    $user_cabang = session('kd_cabang');
    $status_spk = Parameter::query()->where('nama_tabel', 'STATUS_SPK')->orderBy('no_urut', 'asc')->get();
    $status = Parameter::query()->where('nama_tabel', 'STATUS_SPK_KET')->orderBy('no_urut', 'asc')->get();
    // $pekerja_las = Karyawan::query()->select('kode_karyawan', 'nama')->where('kode_cabang', $user_cabang)->where('kode_posisi', '00001')->orderBy('nama', 'asc')->get();
    // $pekerja_dempul = Karyawan::query()->select('kode_karyawan', 'nama')->where('kode_cabang', $user_cabang)->where('kode_posisi', '00002')->orderBy('nama', 'asc')->get();
    $pekerja_las = Karyawan::query()->select('kode_karyawan', 'nama')->where('kode_cabang', $user_cabang)->where('kode_posisi', '00006')->orderBy('nama', 'asc')->get();
    $pekerja_dempul = Karyawan::query()->select('kode_karyawan', 'nama')->where('kode_cabang', $user_cabang)->where('kode_posisi', '00006')->orderBy('nama', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.administrasi.progress-kerja', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'status_spk' => $status_spk,
      'pekerja_las' => $pekerja_las,
      'pekerja_dempul' => $pekerja_dempul,
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
      4 => 'e.keterangan', // status
      5 => 'k.no_polisi',
      6 => 'b.nama_tipe',
      7 => 'k.pemilik',
      8 => 'c.nama_pelanggan',
      9 => 'k.tgl_batal',
      10 => 'k.tgl_turun_lapangan',
      11 => 'k.tgl_finishing2', // ← Tanggal Selesai
      12 => 'k.tgl_keluar',
      13 => 'd.keterangan', // status_spk
      14 => 'k.no_polis',
      15 => 'k.kode_claim',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'k.id';
    $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

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

    // Filter berdasarkan input yang dikirim dari DataTables
    if ($request->filled('kode_spk')) {
      $query->where('k.kode_spk', 'like', '%' . $request->kode_spk . '%');
    }
    if ($request->filled('no_polisi')) {
      $query->where('k.no_polisi', 'like', '%' . $request->no_polisi . '%');
    }
    if ($request->filled('tgl_masuk_awal')) {
      $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_masuk_awal, 'Asia/Jakarta')->format('Y-m-d');
      $query->whereDate('k.tgl_masuk', '>=', $startDate);
    }
    if ($request->filled('tgl_masuk_akhir')) {
      $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_masuk_akhir, 'Asia/Jakarta')->format('Y-m-d');
      $query->whereDate('k.tgl_masuk', '<=', $endDate);
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
    if ($request->filled('status_spk')) {
      if ($request->status_spk <> 'all') {
        $query->where('k.kode_status_spk', 'like', '%' . $request->status_spk . '%');
      }
    }
    if ($request->filled('status')) {
      if ($request->status <> 'all') {
        $query->where('k.status_spk', 'like', '%' . $request->status . '%');
      }
    }

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
        'k.tgl_finishing2', // ← tambah ini
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
        'id' => $row->id,
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
        'tgl_finishing2' => blank($row->tgl_finishing2) ? '' : date("d/m/Y", strtotime($row->tgl_finishing2)), // ← tambah ini
        'tgl_keluar' => blank($row->tgl_keluar) ? '' : date("d/m/Y", strtotime($row->tgl_keluar)),
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
    $user_cabang = session('kd_cabang');
    $dataID = $request->id;

    if ($dataID) {
      $res = Spk::findOrFail($dataID);

      $data = [
        'tgl_bongkar1' => blank($request->tgl_bongkar1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_bongkar1), 'Asia/Jakarta')->format('Y-m-d'),
        'tgl_bongkar2' => blank($request->tgl_bongkar2) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_bongkar2), 'Asia/Jakarta')->format('Y-m-d'),
        'tgl_las1' => blank($request->tgl_las1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_las1), 'Asia/Jakarta')->format('Y-m-d'),
        'tgl_las2' => blank($request->tgl_las2) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_las2), 'Asia/Jakarta')->format('Y-m-d'),
        'tgl_dempul1' => blank($request->tgl_dempul1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_dempul1), 'Asia/Jakarta')->format('Y-m-d'),
        'tgl_dempul2' => blank($request->tgl_dempul2) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_dempul2), 'Asia/Jakarta')->format('Y-m-d'),
        'tgl_mixing1' => blank($request->tgl_mixing1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_mixing1), 'Asia/Jakarta')->format('Y-m-d'),
        'tgl_mixing2' => blank($request->tgl_mixing2) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_mixing2), 'Asia/Jakarta')->format('Y-m-d'),
        'tgl_cat1' => blank($request->tgl_cat1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_cat1), 'Asia/Jakarta')->format('Y-m-d'),
        'tgl_cat2' => blank($request->tgl_cat2) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_cat2), 'Asia/Jakarta')->format('Y-m-d'),
        'tgl_poles1' => blank($request->tgl_poles1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_poles1), 'Asia/Jakarta')->format('Y-m-d'),
        'tgl_poles2' => blank($request->tgl_poles2) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_poles2), 'Asia/Jakarta')->format('Y-m-d'),
        'tgl_finishing1' => blank($request->tgl_finishing1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_finishing1), 'Asia/Jakarta')->format('Y-m-d'),
        'tgl_finishing2' => blank($request->tgl_finishing2) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_finishing2), 'Asia/Jakarta')->format('Y-m-d'),
        'created_by' => Auth::user()->username
      ];

      if ($request->step == "bongkar") {
        $rules = [
          'tgl_bongkar1' => 'required',
        ];

        $messages = [
          'tgl_bongkar1.required' => 'Tanggal Mulai Bongkar Wajib diisi',
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
          'tgl_bongkar1' => blank($request->tgl_bongkar1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_bongkar1), 'Asia/Jakarta')->format('Y-m-d'),
          'updated_by' => Auth::user()->username
        ];

        $result = $res->update($data);
      } elseif ($request->step == "las") {
        $rules = [
          'tgl_las1' => 'required',
        ];

        $messages = [
          'tgl_las1.required' => 'Tanggal Mulai Las Wajib diisi',
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
          'tgl_bongkar2' => blank($request->tgl_las1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_las1), 'Asia/Jakarta')->format('Y-m-d'),
          'tgl_las1' => blank($request->tgl_las1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_las1), 'Asia/Jakarta')->format('Y-m-d'),
          'pekerja_las' => $request->pekerja_las ?? null,
          'upah_las' => (float) str_replace(',', '', $request->upah_las ?? 0),
          'updated_by' => Auth::user()->username
        ];

        $result = $res->update($data);
      } elseif ($request->step == "dempul") {
        $rules = [
          'tgl_dempul1' => 'required',
        ];

        $messages = [
          'tgl_dempul1.required' => 'Tanggal Mulai Dempul Wajib diisi',
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
          'tgl_las2' => blank($request->tgl_dempul1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_dempul1), 'Asia/Jakarta')->format('Y-m-d'),
          'tgl_dempul1' => blank($request->tgl_dempul1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_dempul1), 'Asia/Jakarta')->format('Y-m-d'),
          'pekerja_dempul' => $request->pekerja_dempul ?? null,
          'upah_dempul' => (float) str_replace(',', '', $request->upah_dempul ?? 0),
          'updated_by' => Auth::user()->username
        ];

        $result = $res->update($data);
      } elseif ($request->step == "mixing") {
        $rules = [
          'tgl_mixing1' => 'required',
        ];

        $messages = [
          'tgl_mixing1.required' => 'Tanggal Mulai Mixing Wajib diisi',
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
          'tgl_dempul2' => blank($request->tgl_mixing1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_mixing1), 'Asia/Jakarta')->format('Y-m-d'),
          'tgl_mixing1' => blank($request->tgl_mixing1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_mixing1), 'Asia/Jakarta')->format('Y-m-d'),
          'updated_by' => Auth::user()->username
        ];

        $result = $res->update($data);
      } elseif ($request->step == "cat") {
        $rules = [
          'tgl_cat1' => 'required',
        ];

        $messages = [
          'tgl_cat1.required' => 'Tanggal Mulai Cat Wajib diisi',
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
          'tgl_mixing2' => blank($request->tgl_cat1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_cat1), 'Asia/Jakarta')->format('Y-m-d'),
          'tgl_cat1' => blank($request->tgl_cat1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_cat1), 'Asia/Jakarta')->format('Y-m-d'),
          'updated_by' => Auth::user()->username
        ];

        $result = $res->update($data);
      } elseif ($request->step == "poles") {
        $rules = [
          'tgl_poles1' => 'required',
        ];

        $messages = [
          'tgl_poles1.required' => 'Tanggal Mulai Poles Wajib diisi',
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
          'tgl_cat2' => blank($request->tgl_poles1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_poles1), 'Asia/Jakarta')->format('Y-m-d'),
          'tgl_poles1' => blank($request->tgl_poles1) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_poles1), 'Asia/Jakarta')->format('Y-m-d'),
          'updated_by' => Auth::user()->username
        ];

        $result = $res->update($data);
      } elseif ($request->step == "finishing") {
        $rules = [
          'tgl_finishing2' => 'required',
        ];

        $messages = [
          'tgl_finishing2.required' => 'Tanggal Mulai Finishing Wajib diisi',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
          return response()->json([
            'status' => false,
            'message' => "Gagal menyimpan data.",
            'errors' => $validator->errors()
          ], 200);
        }

        // RAWAT JALAN
        $tgl = blank($request->tgl_finishing2) ? null
          : Carbon::createFromFormat('d/m/Y', trim($request->tgl_finishing2), 'Asia/Jakarta')->format('Y-m-d');
        // END RAWAT JALAN

        $data = [
          'tgl_poles2' => blank($request->tgl_finishing2) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_finishing2), 'Asia/Jakarta')->format('Y-m-d'),
          // RAWAT JALAN
          'tgl_rawat_jalan2' => $res->ada_rawat_jalan == '1' ? $tgl : null,
          // END RAWAT JALAN
          'tgl_finishing1' => blank($request->tgl_finishing2) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_finishing2), 'Asia/Jakarta')->format('Y-m-d'),
          'tgl_finishing2' => blank($request->tgl_finishing2) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_finishing2), 'Asia/Jakarta')->format('Y-m-d'),
          'updated_by' => Auth::user()->username
        ];

        $result = $res->update($data);

      }
      // RAWAT JALAN
      elseif ($request->step == "rawat_jalan") {
        $rules = [
          'tgl_rawat_jalan1' => 'required',
        ];

        $messages = [
          'tgl_rawat_jalan1.required' => 'Tanggal Mulai Rawat Jalan Wajib diisi',
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
          'tgl_poles2' => blank($request->tgl_rawat_jalan1) ? null
            : Carbon::createFromFormat('d/m/Y', trim($request->tgl_rawat_jalan1), 'Asia/Jakarta')->format('Y-m-d'),
          'tgl_rawat_jalan1' => blank($request->tgl_rawat_jalan1) ? null
            : Carbon::createFromFormat('d/m/Y', trim($request->tgl_rawat_jalan1), 'Asia/Jakarta')->format('Y-m-d'),
          'updated_by' => Auth::user()->username
        ];

        $result = $res->update($data);
      }
      // END RAWAT JALAN
      else {
        $result = false;
        $data = [];
      }

      ## Log Activity
      $desc = $result ? 'Berhasil Proses Progress Kerja' : 'Gagal Proses Progress Kerja';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status' => (bool) $result,
        'message' => $desc
      ]);
    } else {
      $result = false;

      ## Log Activity
      $desc = 'ID SPK tidak ditemukan';
      LogActivity::saveLogActivity($desc);

      return response()->json([
        'status' => (bool) $result,
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
    $data = DB::table('v_trx_turun_lapangan')->where('id', $id)->first();

    if (blank($data)) {
      $result = false;
      return response()->json([
        'status' => (bool) $result,
        'message' => 'Estimasi belum dibuat!'
      ]);
    }

    if (blank($data->kode_estimasi)) {
      $result = false;
      return response()->json([
        'status' => (bool) $result,
        'message' => 'Estimasi belum dibuat!'
      ]);
    }

    if (blank($data->kode_turun_lapangan)) {
      $result = false;
      return response()->json([
        'status' => (bool) $result,
        'message' => 'Mobil Belum Turun Lapangan!'
      ]);
    }

    $result = true;

    $data->tgl_estimasi = blank($data->tgl_estimasi) ? '' : date("d/m/Y", strtotime($data->tgl_estimasi));
    $data->tgl_turun_lapangan = blank($data->tgl_turun_lapangan) ? '' : date("d/m/Y", strtotime($data->tgl_turun_lapangan));
    $data->tgl_rencana_selesai = blank($data->tgl_rencana_selesai) ? '' : date("d/m/Y", strtotime($data->tgl_rencana_selesai));
    $data->tgl_terima = blank($data->tgl_terima) ? '' : date("d/m/Y", strtotime($data->tgl_terima));
    // RAWAT JALAN
    $data->total_step = $data->ada_rawat_jalan == '1' ? 8 : 7;
    // END RAWAT JALAN

    if (blank($data->tgl_bongkar1)) {
      $data->step = 'bongkar';
      $data->jum_step = 0;
    } elseif (blank($data->tgl_las1)) {
      $data->step = 'las';
      $data->jum_step = 1;
    } elseif (blank($data->tgl_dempul1)) {
      $data->step = 'dempul';
      $data->jum_step = 2;
    } elseif (blank($data->tgl_mixing1)) {
      $data->step = 'mixing';
      $data->jum_step = 3;
    } elseif (blank($data->tgl_cat1)) {
      $data->step = 'cat';
      $data->jum_step = 4;
    } elseif (blank($data->tgl_poles1)) {
      $data->step = 'poles';
      $data->jum_step = 5;
      // } elseif(blank($data->tgl_finishing2)) {
      //   $data->step = 'finishing';
      //   $data->jum_step = 6;
      // } else {
      //   $data->step = 'finishing';
      //   $data->jum_step = 7;
      // }
      // RAWAT JALAN
    } elseif ($data->ada_rawat_jalan == '1' && !blank($data->tgl_poles1) && blank($data->tgl_rawat_jalan1)) {
      $data->step = 'rawat_jalan';
      $data->jum_step = 6;
    } elseif (blank($data->tgl_finishing2)) {
      $data->step = 'finishing';
      $data->jum_step = $data->ada_rawat_jalan == '1' ? 7 : 6;
    } else {
      $data->step = 'finishing';
      $data->jum_step = $data->ada_rawat_jalan == '1' ? 8 : 7;
    }
    // END RAWAT JALAN

    $data->tgl_bongkar1 = blank($data->tgl_bongkar1) ? date("d/m/Y") : date("d/m/Y", strtotime($data->tgl_bongkar1));
    // $data->tgl_bongkar2 = blank($data->tgl_bongkar2) ? '' : date("d/m/Y", strtotime($data->tgl_bongkar2));
    $data->tgl_las1 = blank($data->tgl_las1) ? date("d/m/Y") : date("d/m/Y", strtotime($data->tgl_las1));
    // $data->tgl_las2 = blank($data->tgl_las2) ? '' : date("d/m/Y", strtotime($data->tgl_las2));
    $data->tgl_dempul1 = blank($data->tgl_dempul1) ? date("d/m/Y") : date("d/m/Y", strtotime($data->tgl_dempul1));
    // $data->tgl_dempul2 = blank($data->tgl_dempul2) ? '' : date("d/m/Y", strtotime($data->tgl_dempul2));
    $data->tgl_mixing1 = blank($data->tgl_mixing1) ? date("d/m/Y") : date("d/m/Y", strtotime($data->tgl_mixing1));
    // $data->tgl_mixing2 = blank($data->tgl_mixing2) ? '' : date("d/m/Y", strtotime($data->tgl_mixing2));
    $data->tgl_cat1 = blank($data->tgl_cat1) ? date("d/m/Y") : date("d/m/Y", strtotime($data->tgl_cat1));
    // $data->tgl_cat2 = blank($data->tgl_cat2) ? '' : date("d/m/Y", strtotime($data->tgl_cat2));
    $data->tgl_poles1 = blank($data->tgl_poles1) ? date("d/m/Y") : date("d/m/Y", strtotime($data->tgl_poles1));
    // $data->tgl_poles2 = blank($data->tgl_poles2) ? '' : date("d/m/Y", strtotime($data->tgl_poles2));
    // $data->tgl_finishing1 = blank($data->tgl_finishing1) ? date("d/m/Y") : date("d/m/Y", strtotime($data->tgl_finishing1));
    $data->tgl_finishing2 = blank($data->tgl_finishing2) ? date("d/m/Y") : date("d/m/Y", strtotime($data->tgl_finishing2));
    // RAWAT JALAN
    $data->tgl_rawat_jalan1 = blank($data->tgl_rawat_jalan1) ? date("d/m/Y") : date("d/m/Y", strtotime($data->tgl_rawat_jalan1));
    // END RAWAT JALAN
    // HARIAN LEPAS
    $data->pekerja_las = $data->pekerja_las ?? '';
    $data->upah_las = (float) ($data->upah_las ?? 0);
    $data->pekerja_dempul = $data->pekerja_dempul ?? '';
    $data->upah_dempul = (float) ($data->upah_dempul ?? 0);
    // END HARIAN LEPAS

    return response()->json([
      'status' => (bool) $result,
      'message' => 'Berhasil Kirim Progress Kerja',
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
    //$datas = Spk::where('id', $id)->delete();
  }

}
