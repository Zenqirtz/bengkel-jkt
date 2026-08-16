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
use App\Models\TarifPpn;
use App\Models\Kwitansi;
use App\Models\KirimKwitansi;
use App\Models\Estimasi;
use App\Models\LogActivity;
use Carbon\Carbon;

class InvoiceController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Invoice(): View
  {
    $isList = \Helper::AuthIsPerm("list");
    $isAdd = \Helper::AuthIsPerm("add");
    $isEdit = \Helper::AuthIsPerm("edit");
    $isDel = \Helper::AuthIsPerm("delete");
    if (!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path = request()->path();
    $title = \Helper::getTitleMenu($path) ?? ' Invoice';

    $user_cabang = session('kd_cabang');
    $status_spk = Parameter::query()->where('nama_tabel', 'STATUS_SPK')->orderBy('no_urut', 'asc')->get();
    $status = Parameter::query()->where('nama_tabel', 'STATUS_SPK_KET')->orderBy('no_urut', 'asc')->get();
    $tipe_kwitansi = Parameter::query()->where('nama_tabel', 'TIPE_KWITANSI')->orderBy('no_urut', 'asc')->get();

    $startDate = date("Y-m-d");
    $endDate = date("Y-m-d");
    $cekPPN = TarifPpn::where(function ($q) use ($startDate, $endDate) {
      $q->where('startdate', '<=', $endDate)
        ->where('enddate', '>=', $startDate);
    })->first();

    $ppn_persen = ($cekPPN) ? $cekPPN->ppn : 0;

    // ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.administrasi.invoice', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'status_spk' => $status_spk,
      'status' => $status,
      'tipe_kwitansi' => $tipe_kwitansi,
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
        'c.npwp', // ← TAMBAH
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
        'npwp' => $row->npwp ?? '', // ← TAMBAH
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
    $tipe = $request->tipe;

    if ($tipe == "kirim-invoice") {

      if ($dataID) {
        $res = KirimKwitansi::findOrFail($dataID);

        $rules = [
          'tgl_kirim_kwitansi' => 'required',
        ];

        $messages = [
          'tgl_kirim_kwitansi.required' => 'Tanggal Pengiriman Wajib diisi',
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
          'tanggal' => blank($request->tgl_kirim_kwitansi) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_kirim_kwitansi), 'Asia/Jakarta')->format('Y-m-d'),
          'memo' => $request->memo,
          'updated_by' => Auth::user()->username
        ];

        $result = $res->update($data);

        if ($result) {
          ## Update Kwitansi
          $dataKwitansi = Kwitansi::updateOrCreate(
            [
              'kode_spk' => $request->kode_spk,
              'kode_kwitansi' => $request->kode_kwitansi,
              'kode_cabang' => $user_cabang
            ],
            [
              'memo' => $request->memo,
              'tgl_kirim_kwitansi' => blank($request->tgl_kirim_kwitansi) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_kirim_kwitansi), 'Asia/Jakarta')->format('Y-m-d'),
              'updated_by' => Auth::user()->username
            ]
          );

          ## Update Estimasi
          $dataEstimasi = Estimasi::updateOrCreate(
            [
              'kode_spk' => $request->kode_spk,
              'kode_estimasi' => $request->kode_estimasi,
              'kode_cabang' => $user_cabang
            ],
            [
              'tgl_kirim_kwitansi' => blank($request->tgl_kirim_kwitansi) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_kirim_kwitansi), 'Asia/Jakarta')->format('Y-m-d'),
              'updated_by' => Auth::user()->username
            ]
          );
        }

        ## Log Activity
        $desc = $result ? 'Berhasil Proses Kirim Invoice' : 'Gagal Proses Kirim Invoice';
        LogActivity::saveLogActivity($desc, $data);

        return response()->json([
          'status' => (bool) $result,
          'message' => $desc
        ]);
      } else {
        $rules = [
          'tgl_kirim_kwitansi' => 'required',
        ];

        $messages = [
          'tgl_kirim_kwitansi.required' => 'Tanggal Pengiriman Wajib diisi',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
          return response()->json([
            'status' => false,
            'message' => "Gagal menyimpan data.",
            'errors' => $validator->errors()
          ], 200);
        }

        $penomoran = \Helper::getNomorTransaksi($user_cabang, 'KR');

        $cekspk = Kwitansi::where('kode_kirim_kwitansi', $penomoran)->first();
        if (!empty($cekspk)) {
          return response()->json(['status' => false, 'message' => "Nomor Kirim Kwitansi sudah digunakan"]);
        }

        $data = [
          'kode_cabang' => $user_cabang,
          'kode_kirim_kwitansi' => $penomoran,
          'tanggal' => blank($request->tgl_kirim_kwitansi) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_kirim_kwitansi), 'Asia/Jakarta')->format('Y-m-d'),
          'kode_kwitansi' => $request->kode_kwitansi,
          'kode_spk' => $request->kode_spk,
          'memo' => $request->memo,
          'created_by' => Auth::user()->username
        ];

        $result = KirimKwitansi::create($data);

        if ($result) {
          ## Update Kwitansi
          $dataKwitansi = Kwitansi::updateOrCreate(
            [
              'kode_spk' => $request->kode_spk,
              'kode_kwitansi' => $request->kode_kwitansi,
              'kode_cabang' => $user_cabang
            ],
            [
              'kode_kirim_kwitansi' => $penomoran,
              'memo' => $request->memo,
              'tgl_kirim_kwitansi' => blank($request->tgl_kirim_kwitansi) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_kirim_kwitansi), 'Asia/Jakarta')->format('Y-m-d'),
              'updated_by' => Auth::user()->username
            ]
          );

          ## Update Estimasi
          $dataEstimasi = Estimasi::updateOrCreate(
            [
              'kode_spk' => $request->kode_spk,
              'kode_estimasi' => $request->kode_estimasi,
              'kode_cabang' => $user_cabang
            ],
            [
              'tgl_kirim_kwitansi' => blank($request->tgl_kirim_kwitansi) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_kirim_kwitansi), 'Asia/Jakarta')->format('Y-m-d'),
              'updated_by' => Auth::user()->username
            ]
          );

          ## Update Nomor Kwitansi
          $res = \Helper::updateNomorTransaksi($user_cabang, 'KR', $penomoran);
        }

        ## Log Activity
        $desc = $result ? 'Berhasil Proses Kirim Invoice' : 'Gagal Proses Kirim Invoice';
        LogActivity::saveLogActivity($desc, $data);

        return response()->json([
          'status' => (bool) $result,
          'message' => $desc
        ]);
      }
    } elseif ($tipe == "terbit-invoice") {

      if ($dataID) {
        $res = Kwitansi::findOrFail($dataID);

        $rules = [
          'tgl_kwitansi' => 'required',
          'kode_tipe_kwitansi' => 'required',
          'persen_jasa' => 'required',
          'persen_bahan' => 'required',
        ];

        $messages = [
          'tgl_kwitansi.required' => 'Tanggal Kwitansi Wajib diisi',
          'kode_tipe_kwitansi.required' => 'Tipe Kwitansi Wajib diisi',
          'persen_jasa.required' => 'Persen Bahan Wajib diisi',
          'persen_bahan.required' => 'Persen Jasa Wajib diisi',
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
          'tanggal' => blank($request->tgl_kwitansi) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_kwitansi), 'Asia/Jakarta')->format('Y-m-d'),
          'kode_tipe_kwitansi' => $request->kode_tipe_kwitansi,
          'memo' => $request->memo,
          'total_perbaikan' => blank($request->total_perbaikan) ? 0 : str_replace([","], "", $request->total_perbaikan),
          'total_sparepart' => blank($request->total_sparepart) ? 0 : str_replace([","], "", $request->total_sparepart),
          'total_lain' => blank($request->total_lain) ? 0 : str_replace([","], "", $request->total_lain),
          'total_or_ass' => blank($request->total_or_ass) ? 0 : str_replace([","], "", $request->total_or_ass),
          'grand_total' => blank($request->grand_total) ? 0 : str_replace([","], "", $request->grand_total),
          'ppn' => blank($request->ppn) ? 0 : str_replace([","], "", $request->ppn),
          'pph' => blank($request->pph) ? 0 : str_replace([","], "", $request->pph),
          'prorata' => blank($request->prorata) ? 0 : str_replace([","], "", $request->prorata),
          'salvage' => blank($request->salvage) ? 0 : str_replace([","], "", $request->salvage),
          'penyusutan' => blank($request->penyusutan) ? 0 : str_replace([","], "", $request->penyusutan),
          'discount' => blank($request->discount) ? 0 : str_replace([","], "", $request->discount),
          'persen_jasa' => blank($request->persen_jasa) ? 0 : str_replace([","], "", $request->persen_jasa),
          'persen_bahan' => blank($request->persen_bahan) ? 0 : str_replace([","], "", $request->persen_bahan),
          'updated_by' => Auth::user()->username
        ];

        $result = $res->update($data);

        if ($result) {
          ## Update Estimasi
          $dataEstimasi = Estimasi::updateOrCreate(
            [
              'kode_spk' => $request->kode_spk,
              'kode_estimasi' => $request->kode_estimasi,
              'kode_cabang' => $user_cabang
            ],
            [
              'tgl_kwitansi' => blank($request->tgl_kwitansi) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_kwitansi), 'Asia/Jakarta')->format('Y-m-d'),
              'updated_by' => Auth::user()->username
            ]
          );
        }

        ## Log Activity
        $desc = $result ? 'Berhasil Proses Terbit Invoice' : 'Gagal Proses Terbit Invoice';
        LogActivity::saveLogActivity($desc, $data);

        return response()->json([
          'status' => (bool) $result,
          'message' => $desc
        ]);
      } else {
        $rules = [
          'tgl_kwitansi' => 'required',
          'kode_tipe_kwitansi' => 'required',
          'persen_jasa' => 'required',
          'persen_bahan' => 'required',
        ];

        $messages = [
          'tgl_kwitansi.required' => 'Tanggal Kwitansi Wajib diisi',
          'kode_tipe_kwitansi.required' => 'Tipe Kwitansi Wajib diisi',
          'persen_jasa.required' => 'Persen Bahan Wajib diisi',
          'persen_bahan.required' => 'Persen Jasa Wajib diisi',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
          return response()->json([
            'status' => false,
            'message' => "Gagal menyimpan data.",
            'errors' => $validator->errors()
          ], 200);
        }

        $penomoran = \Helper::getNomorTransaksi($user_cabang, 'KWT');

        $cekspk = Kwitansi::where('kode_kwitansi', $penomoran)->first();
        if (!empty($cekspk)) {
          return response()->json(['status' => false, 'message' => "Nomor Kwitansi sudah digunakan"]);
        }

        $data = [
          'kode_cabang' => $user_cabang,
          'kode_kwitansi' => $penomoran,
          'tanggal' => blank($request->tgl_kwitansi) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_kwitansi), 'Asia/Jakarta')->format('Y-m-d'),
          'kode_estimasi' => $request->kode_estimasi,
          'kode_spk' => $request->kode_spk,
          'kode_tipe_kwitansi' => $request->kode_tipe_kwitansi,
          'memo' => $request->memo,
          'total_perbaikan' => blank($request->total_perbaikan) ? 0 : str_replace([","], "", $request->total_perbaikan),
          'total_sparepart' => blank($request->total_sparepart) ? 0 : str_replace([","], "", $request->total_sparepart),
          'total_lain' => blank($request->total_lain) ? 0 : str_replace([","], "", $request->total_lain),
          'total_or_ass' => blank($request->total_or_ass) ? 0 : str_replace([","], "", $request->total_or_ass),
          'grand_total' => blank($request->grand_total) ? 0 : str_replace([","], "", $request->grand_total),
          'ppn' => blank($request->ppn) ? 0 : str_replace([","], "", $request->ppn),
          'pph' => blank($request->pph) ? 0 : str_replace([","], "", $request->pph),
          'prorata' => blank($request->prorata) ? 0 : str_replace([","], "", $request->prorata),
          'salvage' => blank($request->salvage) ? 0 : str_replace([","], "", $request->salvage),
          'penyusutan' => blank($request->penyusutan) ? 0 : str_replace([","], "", $request->penyusutan),
          'discount' => blank($request->discount) ? 0 : str_replace([","], "", $request->discount),
          'persen_jasa' => blank($request->persen_jasa) ? 0 : str_replace([","], "", $request->persen_jasa),
          'persen_bahan' => blank($request->persen_bahan) ? 0 : str_replace([","], "", $request->persen_bahan),
          'created_by' => Auth::user()->username
        ];

        $result = Kwitansi::create($data);

        if ($result) {
          ## Update Estimasi
          $dataEstimasi = Estimasi::updateOrCreate(
            [
              'kode_spk' => $request->kode_spk,
              'kode_estimasi' => $request->kode_estimasi,
              'kode_cabang' => $user_cabang
            ],
            [
              'kode_kwitansi' => $penomoran,
              'tgl_kwitansi' => blank($request->tgl_kwitansi) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_kwitansi), 'Asia/Jakarta')->format('Y-m-d'),
              'updated_by' => Auth::user()->username
            ]
          );

          ## Update Nomor Kwitansi
          $res = \Helper::updateNomorTransaksi($user_cabang, 'KWT', $penomoran);
        }

        ## Log Activity
        $desc = $result ? 'Berhasil Proses Terbit Invoice' : 'Gagal Proses Terbit Invoice';
        LogActivity::saveLogActivity($desc, $data);

        return response()->json([
          'status' => (bool) $result,
          'message' => $desc
        ]);
      }
    } else {
      $result = false;

      ## Log Activity
      $desc = 'Gagal Proses Terbit Invoice';
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
    // $data = Spk::findOrFail($id);
    $data = DB::table('v_trx_kwitansi')->where('id', $id)->first();

    if (blank($data)) {
      $result = false;
      return response()->json([
        'status' => (bool) $result,
        'message' => 'Estimasi belum dibuat!'
      ]);
    }

    if (blank($data->kode_persetujuan)) {
      $result = false;
      return response()->json([
        'status' => (bool) $result,
        'message' => 'Estimasi belum disetujui!'
      ]);
    }

    // Ambil total_or dari t_spk_master
    // v_trx_kwitansi tidak mengambil total_or dari t_spk_master, padahal datanya ada di sana
    // $total_or = DB::table('t_spk_master')
    //   ->where('kode_spk', $data->kode_spk)
    //   ->value('total_or') ?? 0;

    // $data->total_or_ass = $total_or;
    // $data->total_or_ass_s = $total_or;
    // SELESAI

    $result = true;

    $data->sifat_ppn = blank($data->sifat_ppn) ? '0' : $data->sifat_ppn;
    $data->sparepart_ppn = blank($data->sparepart_ppn) ? '0' : $data->sparepart_ppn;
    $data->lain_ppn = blank($data->lain_ppn) ? '0' : $data->lain_ppn;

    $data->tgl_kwitansi = blank($data->tgl_kwitansi) ? date("d/m/Y") : date("d/m/Y", strtotime($data->tgl_kwitansi));
    $data->tgl_kirim_kwitansi = blank($data->tgl_kirim_kwitansi) ? date("d/m/Y") : date("d/m/Y", strtotime($data->tgl_kirim_kwitansi));

    $data->total_perbaikan = blank($data->total_perbaikan) ? $data->total_perbaikan_s : $data->total_perbaikan;
    $data->total_sparepart = blank($data->total_sparepart) ? $data->total_sparepart_s : $data->total_sparepart;
    $data->total_lain = blank($data->total_lain) ? $data->total_lain_s : $data->total_lain;
    $data->total = number_format($data->total_perbaikan + $data->total_sparepart + $data->total_lain, 2, '.', ',');
    $data->grand_total = blank($data->grand_total) ? number_format($data->total_s, 2, '.', ',') : number_format($data->grand_total, 2, '.', ',');
    $data->ppn = blank($data->ppn) ? number_format($data->ppn_s, 2, '.', ',') : number_format($data->ppn, 2, '.', ',');
    $data->total_or_ass = blank($data->total_or_ass) ? number_format($data->total_or_ass_s, 2, '.', ',') : number_format($data->total_or_ass, 2, '.', ',');
    $data->salvage = blank($data->salvage) ? number_format($data->salvage_s, 2, '.', ',') : number_format($data->salvage, 2, '.', ',');
    $data->prorata = number_format($data->prorata, 2, '.', ',');
    $data->pph = number_format($data->pph, 2, '.', ',');
    $data->penyusutan = number_format($data->penyusutan, 2, '.', ',');
    $data->discount = number_format($data->discount, 2, '.', ',');
    $data->transport = number_format($data->transport, 2, '.', ',');
    $data->total_sparepart = number_format($data->total_sparepart, 2, '.', ',');
    $data->total_lain = number_format($data->total_lain, 2, '.', ',');
    $data->persen_jasa = blank($data->persen_jasa) ? 20 : $data->persen_jasa;
    $data->persen_bahan = blank($data->persen_bahan) ? 80 : $data->persen_bahan;

    $data->total_bahan = number_format($data->total_perbaikan * ($data->persen_bahan / 100), 2, '.', ',');
    $data->total_jasa = number_format($data->total_perbaikan * ($data->persen_jasa / 100), 2, '.', ',');

    return response()->json([
      'status' => (bool) $result,
      'message' => 'Berhasil Terbit Invoice',
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

  public function printInvoice(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $title = 'Cetak Invoice';
    $id = $request->id;

    // id yang masuk adalah id SPK (t_spk_master)
    // v_trx_kwitansi juga pakai id dari t_spk_master
    $data = DB::table('v_trx_kwitansi')->where('id', $id)->first();

    if (blank($data)) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }

    if ($data->kode_pelanggan == "00001") {
      $data->nama_pelanggan = $data->pemilik;
      $data->alamat = $data->alamat_pemilik;
    }

    // Terbilang
    $data->terbilang = \Helper::terbilang_rupiah($data->grand_total);

    $data->persen_jasa = blank($data->persen_jasa) ? 20 : $data->persen_jasa;
    $data->persen_bahan = blank($data->persen_bahan) ? 80 : $data->persen_bahan;

    $data->total_perbaikan = blank($data->total_perbaikan) ? $data->total_perbaikan_s : $data->total_perbaikan;
    $data->total_sparepart = blank($data->total_sparepart) ? $data->total_sparepart_s : $data->total_sparepart;
    $data->total_lain = blank($data->total_lain) ? $data->total_lain_s : $data->total_lain;
    $subtotal = $data->total_perbaikan + $data->total_sparepart + $data->total_lain;

    $data->total_or_ass = blank($data->total_or_ass) ? $data->total_or_ass_s : $data->total_or_ass;
    $data->salvage = blank($data->salvage) ? $data->salvage_s : $data->salvage;
    $data->ppn = blank($data->ppn) ? $data->ppn_s : $data->ppn;
    // $subtotal2 = $data->total_or_ass + $data->pph + $data->penyusutan + $data->salvage;
    $subtotal2 = $data->total_or_ass + $data->pph + $data->ppn + $data->salvage;

    // $data->ppn = blank($data->ppn) ? number_format($data->ppn_s, 0, '.', ',') : number_format($data->ppn, 0, '.', ',');
    // $data->prorata = number_format($data->prorata, 0, '.', ',');
    // $data->discount = number_format($data->discount, 0, '.', ',');
    // $data->transport = number_format($data->transport, 0, '.', ',');

    $data->total_bahan = number_format($data->total_perbaikan * ($data->persen_bahan / 100), 0, '.', ',');
    $data->total_jasa = number_format($data->total_perbaikan * ($data->persen_jasa / 100), 0, '.', ',');
    $data->total_sparepart = number_format($data->total_sparepart, 0, '.', ',');
    $data->total_lain = number_format($data->total_lain, 0, '.', ',');
    $data->subtotal = number_format($subtotal, 0, '.', ',');

    $data->total_or_ass = number_format($data->total_or_ass, 0, '.', ',');
    $data->pph = number_format($data->pph, 0, '.', ',');
    $data->ppn = number_format($data->ppn, 0, '.', ',');
    // $data->penyusutan = number_format($data->penyusutan, 0, '.', ',');
    $data->salvage = number_format($data->salvage, 0, '.', ',');
    $data->subtotal2 = number_format($subtotal2, 0, '.', ',');

    $data->grand_total = blank($data->grand_total) ? number_format($data->total_s, 0, '.', ',') : number_format($data->grand_total, 0, '.', ',');

    $cabang = DB::table('m_cabang')->where('kode_cabang', $data->kode_cabang)->first();
    $cabang->alamat1 = sprintf("%s %s %s", $cabang->alamat1, $cabang->alamat2, $cabang->alamat3);

    $dest = public_path('assets/img/cabang');
    $logo_cabang = $dest . DIRECTORY_SEPARATOR . $cabang->logo_cabang;
    $file_logo = (is_file($logo_cabang)) ? "1" : "0";

    LogActivity::saveLogActivity("Print " . $title);

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.administrasi.invoice-print', [
      'title' => $title,
      'data' => $data,
      'cabang' => $cabang,
      'file_logo'  => $file_logo,
      'pageConfigs' => $pageConfigs,
    ]);
  }
}
