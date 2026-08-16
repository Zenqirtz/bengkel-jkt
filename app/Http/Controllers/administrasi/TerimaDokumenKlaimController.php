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
use App\Models\TerimaDokumenKlaim;
use App\Models\LogActivity;
use Carbon\Carbon;

class TerimaDokumenKlaimController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function TerimaDokumenKlaim(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Tanda Terima Dokumen Klaim';

    $user_cabang = session('kd_cabang');
    $status_spk = Parameter::query()->where('nama_tabel', 'STATUS_SPK')->orderBy('no_urut', 'asc')->get();
    $status = Parameter::query()->where('nama_tabel', 'STATUS_SPK_KET')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.administrasi.terima-dokumen-klaim', [
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

    if ($request->tipe == "spk-dokumen") {
      $dtSPK = DB::table('t_spk_master as a')
        ->join('t_estimasi_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_spk', '=', 'a.kode_spk'); // syarat di JOIN
        })
        ->join('m_mobil as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'a.kode_cabang')
            ->on('c.no_polisi', '=', 'a.no_polisi'); // syarat di JOIN
        })
        ->where('a.kode_cabang', $request->kode_cabang)
        ->where('a.kode_spk', $request->kode_spk)
        ->select([
          'b.kode_estimasi',
          'a.pemilik',
          'a.no_polis',
          'c.nama_distnk',
        ])
        ->first();

      $columns = [
        1 => 'a.id',
        2 => 'c.jenis_pekerjaan',
        3 => 'd.panel_pekerjaan',
        4 => 'd.point',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = 'a.doc_seq_no'; //$columns[$request->input('order.0.column')] ?? 'a.doc_seq_no';
      $dir   = 'asc'; //$request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('t_dokumen_checklist as a')
        ->where('a.kode_cabang', $request->kode_cabang)
        ->where('a.kode_spk', $request->kode_spk);

      $isExist = (clone $base)->exists();

      if (!$isExist) {
        // Base query + LEFT JOIN
        $base = DB::table('m_proses_dokumen as a')
          ->where('a.kode_cabang', $request->kode_cabang);
      }

      // Total baris tanpa filter
      $totalData = (clone $base)->count('a.id');

      // Filtering (search global)
      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('a.doc_desc', 'like', "%{$search}%");
        });
      }

      // Hitung setelah filter (tanpa limit/offset)
      $totalFiltered = (clone $query)->count('a.id');

      if (!$isExist) {
        $datas = $query
          ->select([
            'a.id',
            'a.doc_seq_no',
            'a.doc_desc',
            DB::raw("'' as isi_dokumen"),
            DB::raw("'N' as checklist"),
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
            'a.doc_seq_no',
            'a.doc_desc',
            'a.isi_dokumen',
            'a.checklist',
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
        if (!$isExist) {
          if ($row->doc_seq_no == '1') {
            $row->isi_dokumen = $dtSPK->kode_estimasi;
          } else if ($row->doc_seq_no == '2') {
            $row->isi_dokumen = $dtSPK->no_polis;
          } else if ($row->doc_seq_no == '3') {
            $row->isi_dokumen = $dtSPK->pemilik;
          } else if ($row->doc_seq_no == '4') {
            $row->isi_dokumen = $dtSPK->nama_distnk;
          }
        }

        $data[] = [
          'id'  => $row->id,
          'fake_id' => ++$fake,
          'doc_seq_no' => $row->doc_seq_no,
          'doc_desc' => $row->doc_desc,
          'isi_dokumen' => blank($row->isi_dokumen) ? '' : $row->isi_dokumen,
          'checklist' => $row->checklist,
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
      $res = Spk::findOrFail($dataID);


      $data = [
        'updated_by' => Auth::user()->username
      ];

      $result = $res->update($data);

      if ($result) {
        ## INSERT DETAIL POINT PANEL
        TerimaDokumenKlaim::where('kode_cabang', $user_cabang)->where('kode_spk', $request->kode_spk)->delete();
        if ($request->dokumen) {
          $no = 1;
          foreach ($request->dokumen as $item) {
            TerimaDokumenKlaim::create([
              'kode_cabang'  => $user_cabang,
              'kode_spk'     => $request->kode_spk,
              'doc_seq_no'   => $item['seq_no'],
              'tgl_dokumen'  => date("Y-m-d"),
              'doc_desc'     => $item['desc'],
              'isi_dokumen'  => $item['isi'],
              'checklist'    => isset($item['checklist']) ? $item['checklist'] : 'N',
              'created_by'   => Auth::user()->username,
            ]);
          }
        }
      }

      ## Log Activity
      $desc = $result ? 'Berhasil Proses Tanda Terima Dokumen' : 'Gagal Proses Tanda Terima Dokumen';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$result,
        'message' => $desc
      ]);
    } else {
      $result = false;

      ## Log Activity
      $desc = 'ID SPK tidak ditemukan';
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
    $data = DB::table('v_spk')->where('id', $id)->first();

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
