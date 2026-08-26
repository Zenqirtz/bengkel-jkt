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
use App\Models\Spk;
use App\Models\Estimasi;
use App\Models\PointPanel;
use App\Models\Karyawan;
use App\Models\LogActivity;
use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class JadwalTurunLapController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function JadwalTurunLap(): View
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
    $title = Helper::getTitleMenu($path) ?? ' Estimasi';

    $user_cabang = session('kd_cabang');
    $status_spk = Parameter::query()->where('nama_tabel', 'STATUS_SPK')->orderBy('no_urut', 'asc')->get();
    $status = Parameter::query()->where('nama_tabel', 'STATUS_SPK_KET')->orderBy('no_urut', 'asc')->get();
    $pic_menyerahkan = Karyawan::query()->select('kode_karyawan', 'nama')->where('kode_cabang', $user_cabang)->where('status_aktif', 'Y')->where('kode_jabatan', '00008')->orderBy('nama', 'asc')->get();
    $pic_menerima = Karyawan::query()->select('kode_karyawan', 'nama')->where('kode_cabang', $user_cabang)->where('status_aktif', 'Y')->where('kode_jabatan', '00003')->orderBy('nama', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.administrasi.jadwal-turun-lapangan', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'status_spk' => $status_spk,
      'status' => $status,
      'pic_menyerahkan' => $pic_menyerahkan,
      'pic_menerima' => $pic_menerima,
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
        4 => 'd.point',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.id';
      $dir = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('t_point_panel as a')
        ->leftJoin('m_jenis_pekerjaan as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'a.kode_cabang')
            ->on('c.kode_jenis_pekerjaan', '=', 'a.kode_jenis_pekerjaan'); // syarat di JOIN
        })
        ->leftJoin('m_panel_pekerjaan as d', function ($join) {
          $join->on('d.kode_cabang', '=', 'a.kode_cabang')
            ->on('d.kode_panel_pekerjaan', '=', 'a.kode_panel_pekerjaan'); // syarat di JOIN
        })
        ->where('a.kode_cabang', $request->kode_cabang)
        ->where('a.kode_spk', $request->kode_spk);

      $isExist = (clone $base)->exists();

      if (!$isExist) {
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
      }

      // Total baris tanpa filter
      $totalData = (clone $base)->count('a.id');

      // Filtering (search global)
      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('c.jenis_pekerjaan', 'like', "%{$search}%")
            ->orWhere('d.panel_pekerjaan', 'like', "%{$search}%");
        });
      }

      // Hitung setelah filter (tanpa limit/offset)
      $totalFiltered = (clone $query)->count('a.id');

      if (!$isExist) {
        $datas = $query
          ->select([
            'a.id',
            'a.kode_jenis_pekerjaan',
            'a.kode_panel_pekerjaan',
            'c.jenis_pekerjaan',
            'd.panel_pekerjaan',
            'd.point',
            DB::raw("'0' as cek"),
          ])
          ->orderBy($order, $dir)
          // ->offset($start)
          // ->limit($limit)
          ->get();
      } else {
        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'a.id',
            'a.kode_jenis_pekerjaan',
            'a.kode_panel_pekerjaan',
            'c.jenis_pekerjaan',
            'd.panel_pekerjaan',
            'd.point',
            'a.cek',
          ])
          ->orderBy($order, $dir)
          // ->offset($start)
          // ->limit($limit)
          ->get();
      }

      // Susun payload DataTables
      $data = [];
      $fake = $start;
      foreach ($datas as $row) {
        $data[] = [
          'id' => $row->id,
          'fake_id' => ++$fake,
          'kode_jenis_pekerjaan' => $row->kode_jenis_pekerjaan,
          'kode_panel_pekerjaan' => $row->kode_panel_pekerjaan,
          'jenis_pekerjaan' => $row->jenis_pekerjaan,
          'panel_pekerjaan' => $row->panel_pekerjaan,
          'point' => number_format($row->point, 1, '.', ','),
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
      $res = Spk::findOrFail($dataID);

      $rules = [
        'tgl_turun_lapangan' => 'required',
        'tgl_rencana_selesai' => 'required',
        'yang_menyerahkan' => 'required',
        'yang_menerima' => 'required',
        'tgl_terima' => 'required',
      ];

      $messages = [
        'tgl_turun_lapangan.required' => 'Tanggal Turun Lapangan Wajib diisi',
        'tgl_rencana_selesai.required' => 'Tanggal Rencana Selesai Wajib diisi',
        'yang_menyerahkan.required' => 'Yang Menyerahkan Wajib diisi',
        'yang_menerima.required' => 'Yang Menerima Wajib diisi',
        'tgl_terima.required' => 'Tanggal Terima Wajib diisi',
      ];

      $validator = Validator::make($request->all(), $rules, $messages);

      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ], 200);
      }

      $yangMenerimaString = !empty($request->yang_menerima) ? implode('::', $request->yang_menerima) : null;

      $data = [
        'tgl_turun_lapangan' => blank($request->tgl_turun_lapangan) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_turun_lapangan), 'Asia/Jakarta')->format('Y-m-d'),
        'tgl_rencana_selesai' => blank($request->tgl_rencana_selesai) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_rencana_selesai), 'Asia/Jakarta')->format('Y-m-d'),
        'yang_menyerahkan' => $request->yang_menyerahkan,
        'yang_menerima' => $yangMenerimaString,
        'tgl_terima' => blank($request->tgl_terima) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_terima), 'Asia/Jakarta')->format('Y-m-d'),
        'keterangan' => $request->keterangan,
        'jumlah_panel' => $request->jumlah_panel,
        // RAWAT JALAN
        'ada_rawat_jalan' => $request->has('ada_rawat_jalan') ? '1' : '0',
        // END RAWAT JALAN
        'created_by' => Auth::user()->username
      ];

      if (blank($request->kode_turun_lapangan)) {
        $penomoran = Helper::getNomorTransaksi($user_cabang, 'TL');

        $isExist = Spk::where('kode_turun_lapangan', $penomoran)->exists();
        if ($isExist) {
          return response()->json(['status' => false, 'message' => "Nomor Turun Lapangan sudah digunakan"]);
        }

        $data['kode_turun_lapangan'] = $penomoran;
        $data['status_spk'] = '07';
      }

      $result = $res->update($data);

      if ($result) {
        ## INSERT DETAIL POINT PANEL
        PointPanel::where('kode_cabang', $user_cabang)->where('kode_spk', $request->kode_spk)->delete();
        if ($request->pekerjaan) {
          $no = 1;
          foreach ($request->pekerjaan as $item) {
            PointPanel::create([
              'kode_cabang' => $user_cabang,
              'kode_spk' => $request->kode_spk,
              'seq_id' => $no++,
              'kode_jenis_pekerjaan' => $item['jenis'],
              'kode_panel_pekerjaan' => $item['panel'],
              'point' => str_replace([","], "", $item['point']),
              'cek' => isset($item['cek']) ? $item['cek'] : '1',
              'created_by' => Auth::user()->username,
            ]);
          }
        }

        if (blank($request->kode_turun_lapangan)) {
          ## Update Nomor Transaksi
          $res = Helper::updateNomorTransaksi($user_cabang, 'TL', $penomoran);
        }

      }

      ## Log Activity
      $desc = $result ? 'Berhasil Proses Jadwal Turun Lapangan' : 'Gagal Proses Jadwal Turun Lapangan';
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
    // $data = Spk::findOrFail($id);
    $data = DB::table('v_trx_turun_lapangan')->where('id', $id)->first();

    if (!$data || blank($data)) {
      $result = false;
      return response()->json([
        'status' => (bool) $result,
        'message' => 'Estimasi belum dibuat!'
      ]);
    }

    // if(blank($data->kode_estimasi)) {
    //   $result = false;
    //   return response()->json([
    //     'status'  => (bool)$result,
    //     'message' => 'Estimasi belum dibuat!'
    //   ]);
    // }

    $result = true;

    $data->tgl_estimasi = blank($data->tgl_estimasi) ? '' : date("d/m/Y", strtotime($data->tgl_estimasi));
    $data->tgl_turun_lapangan = blank($data->tgl_turun_lapangan) ? '' : date("d/m/Y", strtotime($data->tgl_turun_lapangan));
    $data->tgl_rencana_selesai = blank($data->tgl_rencana_selesai) ? '' : date("d/m/Y", strtotime($data->tgl_rencana_selesai));
    $data->tgl_terima = blank($data->tgl_terima) ? '' : date("d/m/Y", strtotime($data->tgl_terima));

    return response()->json([
      'status' => (bool) $result,
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
