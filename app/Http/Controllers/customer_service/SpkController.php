<?php

namespace App\Http\Controllers\customer_service;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Kendaraan;
use App\Models\Spk;
use App\Models\Parameter;
use App\Models\Marketing;
use App\Models\Perantara;
use App\Models\Asuransi;
use App\Models\LogActivity;
use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class SpkController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function SPK(): View
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
    $title = Helper::getTitleMenu($path) ?? 'SPK';

    $user_cabang = session('kd_cabang');
    $kendaraan = Kendaraan::query()->select('id','no_polisi')->where('kode_cabang', $user_cabang)->orderBy('no_polisi', 'asc')->get();
    $status_spk = Parameter::query()->where('nama_tabel', 'STATUS_SPK')->orderBy('no_urut', 'asc')->get();
    $status = Parameter::query()->where('nama_tabel', 'STATUS_SPK_KET')->orderBy('no_urut', 'asc')->get();
    $jenis_polis = Parameter::query()->where('nama_tabel', 'JENIS_POLIS')->orderBy('no_urut', 'asc')->get();
    $jenis_asuransi = Parameter::query()->where('nama_tabel', 'JENIS_PERANTARA')->orderBy('no_urut', 'asc')->get();
    // $marketing = Marketing::query()->select('kode_marketing','nama_marketing')->where('kode_cabang', $user_cabang)->where('is_active', 'Y')->orderBy('nama_marketing', 'asc')->get();
    $marketing = DB::table('v_marketing')->select('kode_marketing','nama_marketing')->where('kode_cabang', $user_cabang)->where('is_active', 'Y')->orderBy('nama_marketing', 'asc')->get();
    $perantara = Perantara::query()->select('kode_perantara','nama_perantara')->where('kode_cabang', $user_cabang)->where('is_active', 'Y')->orderBy('nama_perantara', 'asc')->get();
    $asuransi = Asuransi::query()->select('kode_pelanggan','nama_pelanggan')->where('kode_cabang', $user_cabang)->where('is_active', 'Y')->orderBy('nama_pelanggan', 'asc')->get();

    // $jumspk = $this->getStatusSpk($user_cabang);
    
    $nomorspk = Helper::getNomorTransaksi($user_cabang, 'SPK');
    $tglspk = date("d/m/Y");
    $periode = date("F Y");

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.customer-service.spk', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'kendaraan' => $kendaraan,
      'status_spk' => $status_spk,
      'status' => $status,
      'marketing' => $marketing,
      'perantara' => $perantara,
      'jenis_polis' => $jenis_polis,
      'jenis_asuransi' => $jenis_asuransi,
      'asuransi' => $asuransi,
      'nomorspk' => $nomorspk,
      'tglspk' => $tglspk,
      'periode' => $periode,
      // 'jumspk' => $jumspk,
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

    // $base = DB::table('v_spk as k')
    //     ->where('k.kode_cabang', $user_cabang)
    //     ->whereMonth('tgl_masuk', date('m'))
    //     ->whereYear('tgl_masuk', date('Y'));

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
      $query->where('k.tgl_masuk', '>=', $startDate);
    }
    if ($request->filled('tgl_masuk_akhir')) {
      $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_masuk_akhir, 'Asia/Jakarta')->format('Y-m-d');
      $query->where('k.tgl_masuk', '<=', $endDate);
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

    $tgl_polis_awal  = null;
    $tgl_polis_akhir = null;

    // Jika mengandung " to " → berarti ada 2 tanggal
    $periode = $request->tgl_polis;
    if (str_contains($periode, ' to ')) {

        [$awal, $akhir] = explode(' to ', $periode);

        if (!empty(trim($awal))) {
            $tgl_polis_awal = Carbon::createFromFormat('d/m/Y', trim($awal))->format('Y-m-d');
        }

        if (!empty(trim($akhir))) {
            $tgl_polis_akhir = Carbon::createFromFormat('d/m/Y', trim($akhir))->format('Y-m-d');
        }

    } else {
        // Hanya 1 tanggal → anggap awal dan akhir sama
        if (!empty(trim($periode))) {
            $tgl = Carbon::createFromFormat('d/m/Y', trim($periode))->format('Y-m-d');
            $tgl_polis_awal  = $tgl;
            $tgl_polis_akhir = $tgl;
        }
    }

    if ($dataID) {
      $spk = Spk::findOrFail($dataID);

      $rules = [
        'no_polisi' => 'required',
        'kode_status_spk' => 'required',
        'kode_jenis_pelanggan' => 'required',
      ];
  
      $messages = [
        'no_polisi.required' => 'Nomor Polisi Wajib diisi',
        'kode_status_spk.required' => 'Status SPK Wajib diisi',
        'kode_jenis_pelanggan.required'  => 'Jenis Asuransi Wajib diisi',
      ];
  
      $validator = Validator::make($request->all(), $rules, $messages);
  
      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ], 200);
      }

      $dtKend = DB::table('v_mobil')->where('kode_cabang', $user_cabang)->where('no_polisi', 'like', '%' . $request->no_polisi . '%')->first();

      $data = [
        // 'kode_cabang' => $user_cabang,
        // 'kode_spk' => $request->kode_spk,
        // 'tgl_masuk' => date("Y-m-d H:i:s"),
        'no_polisi' => $dtKend->no_polisi,
        'kode_merek' => $dtKend->kode_merek,
        'kode_tipe' => $dtKend->kode_tipe,
        'pemilik' => $dtKend->nama_pemilik,
        'alamat' => $dtKend->alamat,
        'telepon' => $dtKend->handphone,
        'kode_status_spk' => $request->kode_status_spk,
        'kode_jenis_pelanggan' => $request->kode_jenis_pelanggan,
        'kode_pelanggan' => $request->kode_pelanggan,
        'kode_perantara' => $request->kode_perantara,
        'kode_marketing' => $request->kode_marketing,
        'kode_jenis_polis' => $request->kode_jenis_polis,
        'no_polis' => $request->no_polis,
        'kode_claim' => $request->kode_claim,
        'tertanggung' => $request->tertanggung,
        'catatan_khusus' => $request->catatan_khusus,
        'jenis_perbaikan' => $request->jenis_perbaikan,
        'berlaku_awal_polis' => $tgl_polis_awal,
        'berlaku_akhir_polis' => $tgl_polis_akhir,
        // 'harga_polis' => 0,
        // 'harga_pasar' => 0,
        // 'prorata' => 0,
        // 'jumlah_or' => 0,
        // 'nilai_or' => 0,
        // 'total_or' => 0,
        // 'free' => 0,
        // 'print_invoice' => 0,
        'updated_by' => Auth::user()->username
      ];

      $ok = $spk->update($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil ubah Data SPK' : 'Gagal ubah Data SPK';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } else {

      $rules = [
        'no_polisi' => 'required',
        'kode_status_spk' => 'required',
        'kode_jenis_pelanggan' => 'required',
      ];
  
      $messages = [
        'no_polisi.required' => 'Nomor Polisi Wajib diisi',
        'kode_status_spk.required' => 'Status SPK Wajib diisi',
        'kode_jenis_pelanggan.required'  => 'Jenis Asuransi Wajib diisi',
      ];
  
      $validator = Validator::make($request->all(), $rules, $messages);
  
      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ], 200);
      }

      // $dtKend = DB::table('v_mobil')->where('id', $request->no_polisi)->first();
      $dtKend = DB::table('v_mobil')->where('kode_cabang', $user_cabang)->where('no_polisi', 'like', '%' . $request->no_polisi . '%')->first();
      $nomorspk = Helper::getNomorTransaksi($user_cabang, 'SPK');

      $cekspk = Spk::where('kode_spk', $nomorspk)->first();
      if (!empty($cekspk)) {
        return response()->json(['status' => false, 'message' => "Nomor SPK sudah digunakan"]);
      }

      $data = [
        'kode_cabang' => $user_cabang,
        'kode_spk' => $nomorspk,
        'tgl_masuk' => date("Y-m-d H:i:s"),
        'no_work_order' => $nomorspk,
        'tgl_work_order' => date("Y-m-d H:i:s"),
        'no_polisi' => $dtKend->no_polisi,
        'kode_merek' => $dtKend->kode_merek,
        'kode_tipe' => $dtKend->kode_tipe,
        'pemilik' => $dtKend->nama_pemilik,
        'alamat' => $dtKend->alamat,
        // 'alamat' => sprintf("%s %s", $dtKend->alamat1, $dtKend->alamat2),
        'telepon' => $dtKend->handphone,
        'kode_status_spk' => $request->kode_status_spk,
        'kode_jenis_pelanggan' => $request->kode_jenis_pelanggan,
        'kode_pelanggan' => $request->kode_pelanggan,
        'kode_perantara' => $request->kode_perantara,
        'kode_marketing' => $request->kode_marketing,
        'kode_jenis_polis' => $request->kode_jenis_polis,
        'no_polis' => $request->no_polis,
        'kode_claim' => $request->kode_claim,
        'tertanggung' => $request->tertanggung,
        'catatan_khusus' => $request->catatan_khusus,
        'jenis_perbaikan' => $request->jenis_perbaikan,
        'berlaku_awal_polis' => $tgl_polis_awal,
        'berlaku_akhir_polis' => $tgl_polis_akhir,
        'harga_polis' => 0,
        'harga_pasar' => 0,
        'prorata' => 0,
        'jumlah_or' => 0,
        'nilai_or' => 0,
        'total_or' => 0,
        'free' => 0,
        'print_invoice' => 0,
        'status_spk' => '01',
        'created_by' => Auth::user()->username
      ];

      $ok = Spk::create($data);

      if ($ok) {
        ## Update Nomor SPK
        $res = Helper::updateNomorTransaksi($user_cabang, 'SPK', $nomorspk);
      }

      ## Log Activity
      $desc = $ok ? 'Berhasil tambah Data SPK' : 'Gagal tambah Data SPK';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
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
    $data = Spk::findOrFail($id);

    $asuransi = Asuransi::query()->select('kode_pelanggan','nama_pelanggan')->where('kode_cabang', $data->kode_cabang)->where('kode_pelanggan', $data->kode_pelanggan)->first();
    if($asuransi) {
      $data->nama_pelanggan = $asuransi->nama_pelanggan;
    }
    
    // $data->tgl_stnk_berakhir = $data->tgl_stnk_berakhir ? date("d/m/Y", strtotime($data->tgl_stnk_berakhir)) : '';
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
    $datas = Spk::where('id', $id)->delete();
  }


  public function getStatusSpk($cabang)
  {
    // $data = DB::table('v_spk')
    //   ->select('status', DB::raw('count(1) as jumlah'))
    //   ->where('kode_cabang', $cabang)
    //   // ->whereMonth('tgl_masuk', date('m'))
    //   // ->whereYear('tgl_masuk', date('Y'))
    //   ->groupBy('status')
    //   ->pluck('jumlah', 'status') // Parameter 1: Value, Parameter 2: Key
    //   ->toArray(); // Konversi dari Collection ke Array PHP murni

    // $data['SPK BARU'] = isset($data['SPK BARU']) ? $data['SPK BARU'] : 0;
    // $data['SPK BATAL'] = isset($data['SPK BATAL']) ? $data['SPK BATAL'] : 0;
    // $data['SPK KELUAR'] = isset($data['SPK KELUAR']) ? $data['SPK KELUAR'] : 0;
    // $data['SPK TUTUP'] = isset($data['SPK TUTUP']) ? $data['SPK TUTUP'] : 0;

    $data = DB::table('t_spk_master as a')
      ->leftJoin('parameter as b', function ($join) {
        $join->on('b.kode', '=', 'a.status_spk')
            ->where('b.nama_tabel', '=', 'STATUS_SPK_KET'); // syarat di JOIN
      })
      ->select('a.status_spk', 'b.keterangan', DB::raw('count(1) as jumlah'))
      ->where('a.kode_cabang', $cabang)
      // ->whereMonth('tgl_masuk', date('m'))
      // ->whereYear('tgl_masuk', date('Y'))
      ->groupBy('a.status_spk')
      ->groupBy('b.keterangan')
      ->get()
        ->map(function ($item) {
            return (array) $item; // Paksa ubah object stduse App\Helpers\Helpers as Helper;

Class jadi array murni
        })
      ->toArray(); // Konversi dari Collection ke Array PHP murni

    return $data;
  }

}