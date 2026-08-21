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
use App\Models\Kewajiban;
use App\Models\LogActivity;
use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class KewajibanController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Kewajiban(): View
  {
    $isList = Helper::AuthIsPerm("list");
    $isAdd = Helper::AuthIsPerm("add");
    $isEdit = Helper::AuthIsPerm("edit");
    $isDel = Helper::AuthIsPerm("delete");
    if(!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path = request()->path();
    $title = Helper::getTitleMenu($path) ?? 'Kewajiban Tertanggung';

    $user_cabang = session('kd_cabang');
    $status_spk = Parameter::query()->where('nama_tabel', 'STATUS_SPK')->orderBy('no_urut', 'asc')->get();
    $status = Parameter::query()->where('nama_tabel', 'STATUS_SPK_KET')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.administrasi.kewajiban', [
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
      $res = Kewajiban::findOrFail($dataID);

      $rules = [
        'ada_or' => 'required',
        'is_free' => 'required',
        // 'keterangan' => 'required',
      ];

      $messages = [
        'ada_or.required' => 'Ada OR Wajib diisi',
        'is_free.required' => 'Free OR Wajib diisi',
        // 'keterangan.required'  => 'Keterangan Wajib diisi',
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
        'cek_polis' => blank($request->cek_polis) ? 'N' : $request->cek_polis,
        'surat_kuasa' => blank($request->surat_kuasa) ? 'N' : $request->surat_kuasa,
        'prorata' => blank($request->prorata) ? 'N' : $request->prorata,
        'pernyataan_puas' => blank($request->pernyataan_puas) ? 'N' : $request->pernyataan_puas,
        'biaya_penyusutan' => blank($request->biaya_penyusutan) ? 'N' : $request->biaya_penyusutan,
        'is_free' => $request->is_free,
        'nilai_free_or' => blank($request->nilai_free_or) ? 0 : str_replace([","], "", $request->nilai_free_or),
        'biaya_komisi' => blank($request->biaya_komisi) ? 0 : str_replace([","], "", $request->biaya_komisi),
        'biaya_estimasi' => blank($request->biaya_estimasi) ? 0 : str_replace([","], "", $request->biaya_estimasi),
        'biaya_pribadi' => blank($request->biaya_pribadi) ? 0 : str_replace([","], "", $request->biaya_pribadi),
        'keterangan' => $request->keterangan,
        'updated_by' => Auth::user()->username
      ];

      $result = $res->update($data);

      if ($result && blank($request->kode_pengiriman)) {
        ## Update Data SPK
        $dataspk = Spk::updateOrCreate(
          [
            'kode_spk'    => $request->kode_spk,
            'kode_cabang' => $user_cabang
          ],
          [
            'ada_or' => $request->ada_or,
            'jumlah_or' => blank($request->jumlah_or) ? 0 : str_replace([","], "", $request->jumlah_or),
            'nilai_or' => blank($request->nilai_or) ? 0 : str_replace([","], "", $request->nilai_or),
            'total_or' => blank($request->total_or) ? 0 : str_replace([","], "", $request->total_or),
            'free' => blank($request->nilai_free_or) ? 0 : str_replace([","], "", $request->nilai_free_or),
            'updated_by' => Auth::user()->username
          ]
        );
      }

      ## Log Activity
      $desc = $result ? 'Berhasil Proses Kewajiban Tertanggung' : 'Gagal Proses Kewajiban Tertanggung';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$result,
        'message' => $desc
      ]);
    } else {
      $rules = [
        'ada_or' => 'required',
        'is_free' => 'required',
        // 'keterangan' => 'required',
      ];

      $messages = [
        'ada_or.required' => 'Ada OR Wajib diisi',
        'is_free.required' => 'Free OR Wajib diisi',
        // 'keterangan.required'  => 'Keterangan Wajib diisi',
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
        'kode_cabang' => $user_cabang,
        'kode_spk' => $request->kode_spk,
        'kode_estimasi' => $request->kode_estimasi,
        'kode_pelanggan' => $request->kode_pelanggan,
        'tgl_kewajiban' => date("Y-m-d"),
        'cek_polis' => blank($request->cek_polis) ? 'N' : $request->cek_polis,
        'surat_kuasa' => blank($request->surat_kuasa) ? 'N' : $request->surat_kuasa,
        'prorata' => blank($request->prorata) ? 'N' : $request->prorata,
        'pernyataan_puas' => blank($request->pernyataan_puas) ? 'N' : $request->pernyataan_puas,
        'biaya_penyusutan' => blank($request->biaya_penyusutan) ? 'N' : $request->biaya_penyusutan,
        'is_free' => $request->is_free,
        'nilai_free_or' => blank($request->nilai_free_or) ? 0 : str_replace([","], "", $request->nilai_free_or),
        'biaya_komisi' => blank($request->biaya_komisi) ? 0 : str_replace([","], "", $request->biaya_komisi),
        'biaya_estimasi' => blank($request->biaya_estimasi) ? 0 : str_replace([","], "", $request->biaya_estimasi),
        'biaya_pribadi' => blank($request->biaya_pribadi) ? 0 : str_replace([","], "", $request->biaya_pribadi),
        'keterangan' => $request->keterangan,
        'created_by' => Auth::user()->username
      ];

      $result = Kewajiban::create($data);

      if ($result) {
        ## Update Data SPK
        $dataspk = Spk::updateOrCreate(
          [
            'kode_spk'    => $request->kode_spk,
            'kode_cabang' => $user_cabang
          ],
          [
            'ada_or' => $request->ada_or,
            'jumlah_or' => blank($request->jumlah_or) ? 0 : str_replace([","], "", $request->jumlah_or),
            'nilai_or' => blank($request->nilai_or) ? 0 : str_replace([","], "", $request->nilai_or),
            'total_or' => blank($request->total_or) ? 0 : str_replace([","], "", $request->total_or),
            'free' => blank($request->nilai_free_or) ? 0 : str_replace([","], "", $request->nilai_free_or),
            'status_spk' => '06',
            'updated_by' => Auth::user()->username
          ]
        );
      }

      ## Log Activity
      $desc = $result ? 'Berhasil Proses Kewajiban Tertanggung' : 'Gagal Proses Kewajiban Tertanggung';
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
    $data = DB::table('v_trx_kewajiban')->where('id', $id)->first();

    if(blank($data)) {
      $result = false;
      return response()->json([
        'status'  => (bool)$result,
        'message' => 'Estimasi belum dibuat!'
      ]);
    }

    if($data->kode_jenis_pelanggan == "00002") { // Non Asuransi
      $result = false;
      return response()->json([
        'status'  => (bool)$result,
        'message' => 'Pelanggan PRIBADI tidak ada Kewajiban Tertanggung!'
      ]);
    }

    $data->ada_or = blank($data->ada_or) ? '02' : $data->ada_or;
    $data->is_free = blank($data->is_free) ? 'T' : $data->is_free;
    $data->jumlah_or = number_format($data->jumlah_or, 0, ".", ",");
    $data->nilai_or = number_format($data->nilai_or, 0, ".", ",");
    $data->total_or = number_format($data->total_or, 0, ".", ",");
    $data->nilai_free_or = number_format($data->nilai_free_or, 0, ".", ",");
    $data->biaya_komisi = number_format($data->biaya_komisi, 0, ".", ",");
    $data->nilai_estimasi = number_format($data->nilai_estimasi, 0, ".", ",");
    $data->biaya_pribadi = number_format($data->biaya_pribadi, 0, ".", ",");
    $data->tgl_estimasi = blank($data->tgl_estimasi) ? '' : date("d/m/Y", strtotime($data->tgl_estimasi));

    $result = true;
    return response()->json([
      'status'  => (bool)$result,
      'message' => 'Berhasil Kirim Kewajiban',
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
