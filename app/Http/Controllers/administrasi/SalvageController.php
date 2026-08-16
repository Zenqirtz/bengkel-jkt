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
use App\Models\Salvage;
use App\Models\SalvageDetail;
use App\Models\LogActivity;
use Carbon\Carbon;

class SalvageController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Salvage(): View
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
    $title = \Helper::getTitleMenu($path) ?? ' Salvage';

    $user_cabang = session('kd_cabang');
    $status_spk = Parameter::query()->where('nama_tabel', 'STATUS_SPK')->orderBy('no_urut', 'asc')->get();
    $status = Parameter::query()->where('nama_tabel', 'STATUS_SPK_KET')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.administrasi.salvage', [
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

    if ($request->tipe == "salvage") {
      $columns = [
        1 => 'a.id',
        2 => 'c.jenis_pekerjaan',
        3 => 'd.panel_pekerjaan',
        4 => 'd.point',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'b.id';
      $dir   = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('t_salvage_hdr as a')
        ->join('t_salvage_dtl as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.no_salvage', '=', 'a.no_salvage'); // syarat di JOIN
        })
        ->join('m_sparepart as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'b.kode_cabang')
            ->on('c.kode_sparepart', '=', 'b.kode_sparepart'); // syarat di JOIN
        })
        ->where('a.kode_cabang', $request->kode_cabang)
        ->where('a.kode_spk', $request->kode_spk);

      $isExist = (clone $base)->exists();

      if (!$isExist) {
        // Base query + LEFT JOIN
        $base = DB::table('t_estimasi_hdr as a')
          ->join('t_estimasi_dtl2 as b', function ($join) {
            $join->on('b.kode_cabang', '=', 'a.kode_cabang')
              ->on('b.kode_estimasi', '=', 'a.kode_estimasi'); // syarat di JOIN
          })
          ->leftJoin('m_sparepart as c', function ($join) {
            $join->on('c.kode_cabang', '=', 'b.kode_cabang')
              ->on('c.kode_sparepart', '=', 'b.kode_sparepart'); // syarat di JOIN
          })
          ->where('a.kode_cabang', $request->kode_cabang)
          ->where('a.kode_spk', $request->kode_spk);
      }

      // Total baris tanpa filter
      $totalData = (clone $base)->count('b.id');

      // Filtering (search global)
      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('c.nama_sparepart', 'like', "%{$search}%")
            ->orWhere('b.kode_sparepart', 'like', "%{$search}%");
        });
      }

      // Hitung setelah filter (tanpa limit/offset)
      $totalFiltered = (clone $query)->count('b.id');

      $datas = $query
        ->select([
          'b.id',
          'b.kode_sparepart',
          'c.nama_sparepart',
          'b.qty',
          'b.cek',
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
          'cek' => $row->cek,
          'qty' => number_format($row->qty, 0, '.', ','),
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
        ->leftJoin('t_salvage_hdr as f', function ($join) {
          $join->on('f.kode_spk', '=', 'k.kode_spk')
            ->on('f.kode_cabang', '=', 'k.kode_cabang'); // syarat di JOIN
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
          'f.tgl_kirim',
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
          'tgl_kirim' => blank($row->tgl_kirim) ? '' : date("d/m/Y", strtotime($row->tgl_kirim)),
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
      $res = Salvage::findOrFail($dataID);

      // $rules = [
      //   'tgl_turun_lapangan' => 'required',
      //   'tgl_rencana_selesai' => 'required',
      //   'yang_menyerahkan' => 'required',
      //   'yang_menerima' => 'required',
      //   'tgl_terima' => 'required',
      // ];

      // $messages = [
      //   'tgl_turun_lapangan.required' => 'Tanggal Turun Lapangan Wajib diisi',
      //   'tgl_rencana_selesai.required' => 'Tanggal Rencana Selesai Wajib diisi',
      //   'yang_menyerahkan.required'  => 'Yang Menyerahkan Wajib diisi',
      //   'yang_menerima.required'  => 'Yang Menerima Wajib diisi',
      //   'tgl_terima.required'  => 'Tanggal Terima Wajib diisi',
      // ];

      // $validator = Validator::make($request->all(), $rules, $messages);

      // if ($validator->fails()) {
      //   return response()->json([
      //     'status' => false,
      //     'message' => "Gagal menyimpan data.",
      //     'errors' => $validator->errors()
      //   ], 200);
      // }

      $data = [
        // 'kode_spk' => $request->kode_spk,
        // 'no_polisi' => $request->no_polisi,
        // 'no_polis' => $request->no_polis,
        // 'kode_merek' => $request->kode_merek,
        // 'kode_tipe' => $request->kode_tipe,
        // 'kode_pelanggan' => $request->kode_pelanggan,
        'updated_by' => Auth::user()->username
      ];

      $result = $res->update($data);

      if ($result) {
        ## INSERT DETAIL SALVAGE
        SalvageDetail::where('kode_cabang', $user_cabang)->where('no_salvage', $res->no_salvage)->delete();
        if ($request->salvage) {
          $no = 1;
          foreach ($request->salvage as $item) {
            SalvageDetail::create([
              'kode_cabang'    => $user_cabang,
              'no_salvage'     => $res->no_salvage,
              'line_no'        => $no++,
              'kode_sparepart' => $item['sparepart'],
              'qty'            => blank($item['qty']) ? 0 : str_replace([","], "", $item['qty']),
              'cek'            => isset($item['ada']) ? '1' : '0',
              'created_by'     => Auth::user()->username,
              'updated_by'     => Auth::user()->username,
            ]);
          }
        }
      }

      ## Log Activity
      $desc = $result ? 'Berhasil Ubah Salvage' : 'Gagal Ubah Salvage';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$result,
        'message' => $desc
      ]);
    } else {
      $penomoran = \Helper::getNomorTransaksi($user_cabang, 'SVG');

      $cekspk = Salvage::where('no_salvage', $penomoran)->first();
      if (!empty($cekspk)) {
        return response()->json(['status' => false, 'message' => "Nomor Salvage sudah digunakan"]);
      }

      $data = [
        'kode_cabang' => $user_cabang,
        'no_salvage' => $penomoran,
        'tanggal' => date("Y-m-d"),
        'kode_spk' => $request->kode_spk,
        'no_polisi' => $request->no_polisi,
        'no_polis' => $request->no_polis,
        'kode_merek' => $request->kode_merek,
        'kode_tipe' => $request->kode_tipe,
        'kode_pelanggan' => $request->kode_pelanggan,
        'created_by' => Auth::user()->username
      ];

      $result = Salvage::create($data);

      if ($result) {
        ## INSERT DETAIL SALVAGE
        SalvageDetail::where('kode_cabang', $user_cabang)->where('no_salvage', $penomoran)->delete();
        if ($request->salvage) {
          $no = 1;
          foreach ($request->salvage as $item) {
            SalvageDetail::create([
              'kode_cabang'    => $user_cabang,
              'no_salvage'     => $penomoran,
              'line_no'        => $no++,
              'kode_sparepart' => $item['sparepart'],
              'qty'            => blank($item['qty']) ? 0 : str_replace([","], "", $item['qty']),
              'cek'            => isset($item['ada']) ? '1' : '0',
              'created_by'     => Auth::user()->username,
              'updated_by'     => Auth::user()->username,
            ]);
          }
        }

        ## Update Nomor Estimasi
        $res = \Helper::updateNomorTransaksi($user_cabang, 'SVG', $penomoran);
      }

      ## Log Activity
      $desc = $result ? 'Berhasil Tambah Salvage' : 'Gagal Tambah Salvage';
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
    $data = DB::table('v_trx_salvage')->where('id', $id)->first();

    if (blank($data)) {
      $result = false;
      return response()->json([
        'status'  => (bool)$result,
        'message' => 'Estimasi belum dibuat!'
      ]);
    }

    $dataPart = DB::table('t_estimasi_dtl2 as a')
      ->join('t_estimasi_hdr as b', function ($join) {
        $join->on('b.kode_cabang', '=', 'a.kode_cabang')
          ->on('b.kode_estimasi', '=', 'a.kode_estimasi'); // syarat di JOIN
      })
      ->where('b.kode_cabang', $data->kode_cabang)
      ->where('b.kode_spk', $data->kode_spk)
      ->first();

    if (blank($dataPart)) {
      $result = false;
      return response()->json([
        'status'  => (bool)$result,
        'message' => 'Tidak ada pergantian Sparepart di Estimasi!'
      ]);
    }

    $result = true;

    $data->tgl_salvage = blank($data->tgl_salvage) ? date("d/m/Y") : date("d/m/Y", strtotime($data->tgl_salvage));
    // $data->tgl_turun_lapangan = blank($data->tgl_turun_lapangan) ? '' : date("d/m/Y", strtotime($data->tgl_turun_lapangan));
    // $data->tgl_rencana_selesai = blank($data->tgl_rencana_selesai) ? '' : date("d/m/Y", strtotime($data->tgl_rencana_selesai));
    // $data->tgl_terima = blank($data->tgl_terima) ? '' : date("d/m/Y", strtotime($data->tgl_terima));

    return response()->json([
      'status'  => (bool)$result,
      'message' => 'Berhasil Kirim Salvage',
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

  public function cetakSalvage(Request $request)
  {
    // $user_cabang = session('kd_cabang');
    $title = 'Cetak Salvage';
    $id = $request->id;

    $data = DB::table('v_trx_salvage')->where('id', $id)->first();

    if (blank($data)) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }

    if ($data->no_salvage) {
      $data_detail = DB::table('t_salvage_hdr as a')
        ->join('t_salvage_dtl as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.no_salvage', '=', 'a.no_salvage'); // syarat di JOIN
        })
        ->join('m_sparepart as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'b.kode_cabang')
            ->on('c.kode_sparepart', '=', 'b.kode_sparepart'); // syarat di JOIN
        })
        ->where('a.kode_cabang', $data->kode_cabang)
        ->where('a.kode_spk', $data->kode_spk)
        ->select([
          'b.line_no',
          'b.kode_sparepart',
          'c.nama_sparepart',
          'b.qty',
          'b.cek',
        ])
        ->orderBy('b.line_no', 'asc')
        ->get();
    } else {
      $data_detail = DB::table('t_estimasi_hdr as a')
        ->join('t_estimasi_dtl2 as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_estimasi', '=', 'a.kode_estimasi'); // syarat di JOIN
        })
        ->leftJoin('m_sparepart as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'b.kode_cabang')
            ->on('c.kode_sparepart', '=', 'b.kode_sparepart'); // syarat di JOIN
        })
        ->where('a.kode_cabang', $data->kode_cabang)
        ->where('a.kode_spk', $data->kode_spk)
        ->select([
          'b.idx as line_no',
          'b.kode_sparepart',
          'c.nama_sparepart',
          'b.qty',
          DB::raw('null as cek'),
        ])
        ->orderBy('b.idx', 'asc')
        ->get();
    }

    $data->tgl_salvage = (blank($data->tgl_salvage)) ? "-" : date("d-M-Y", strtotime($data->tgl_salvage));

    $cabang = DB::table('m_cabang')->where('kode_cabang', $data->kode_cabang)->first();

    LogActivity::saveLogActivity($title);

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.administrasi.salvage-print', [
      'title' => $title,
      'data' => $data,
      'data_detail' => $data_detail,
      'cabang' => $cabang,
      'pageConfigs' => $pageConfigs,
    ]);
  }
}
