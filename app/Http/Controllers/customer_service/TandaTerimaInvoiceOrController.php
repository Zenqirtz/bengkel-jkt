<?php

namespace App\Http\Controllers\customer_service;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
// use Illuminate\Support\Facades\Validator;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Parameter;
use App\Models\Pemilik;
use App\Models\Pelanggan;
use App\Models\LogActivity;
use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class TandaTerimaInvoiceOrController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function TandaTerimaInvoiceOr(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Tanda Terima Invoice OR';

    $user_cabang = session('kd_cabang');
    $status_spk = Parameter::query()->where('nama_tabel', 'STATUS_SPK')->orderBy('no_urut', 'asc')->get();
    $status = Parameter::query()->where('nama_tabel', 'STATUS_SPK_KET')->orderBy('no_urut', 'asc')->get();
    $pemilik = Pemilik::query()->select('nama_pemilik')->where('kode_cabang', $user_cabang)->groupBy('nama_pemilik')->orderBy('nama_pemilik', 'asc')->get();
    $pelanggan = Pelanggan::query()->select('nama_pelanggan')->where('kode_cabang', $user_cabang)->groupBy('nama_pelanggan')->orderBy('nama_pelanggan', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.customer-service.tanda-terima-invoice-or', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'status_spk' => $status_spk,
      'status' => $status,
      'pemilik' => $pemilik,
      'pelanggan' => $pelanggan,
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

    try {
      $columns = [
        1 => 'k.id',
        2 => 'k.tgl_invoice',
        3 => 'k.no_invoice',
        4 => 'k.tgl_masuk',
        5 => 'k.kode_spk', 
        6 => 'e.keterangan', // status
        7 => 'k.no_polisi',
        8 => 'b.nama_tipe',
        9 => 'k.pemilik',
        10 => 'c.nama_pelanggan',
        11 => 'k.kode_claim',
        12 => 'k.total_or',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'k.tgl_invoice';
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
        ->where('k.kode_cabang', $user_cabang)
        ->where('c.kode_jenis_pelanggan', '00001')
        ->whereNotNull('k.no_invoice');

      // Total baris tanpa filter
      $totalData = (clone $base)->count('k.id');

      // Filtering (search global)
      $query = (clone $base);
      // if ($search = trim((string) $request->input('search.value'))) {
      //     $query->where(function ($q) use ($search) {
      //         $q->where('k.kode_spk', 'like', "%{$search}%")
      //           ->orWhere('k.no_polisi', 'like', "%{$search}%")
      //           ->orWhere('k.pemilik', 'like', "%{$search}%");
      //     });
      // }

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
      if ($request->filled('tgl_invoice_awal')) {
        $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_invoice_awal, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('k.tgl_invoice', '>=', $startDate);
      }
      if ($request->filled('tgl_invoice_akhir')) {
        $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_invoice_akhir, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('k.tgl_invoice', '<=', $endDate);
      }
      if ($request->filled('nama_pelanggan')) {
        // $query->where('c.nama_pelanggan', 'like', '%' . $request->nama_pelanggan . '%');
        if ($request->nama_pelanggan <> 'all') {
          $query->where('c.nama_pelanggan', 'like', '%' . $request->nama_pelanggan . '%');
        }
      }
      if ($request->filled('nama_pemilik')) {
        // $query->where('k.pemilik', 'like', '%' . $request->nama_pemilik . '%');
        if ($request->nama_pemilik <> 'all') {
          $query->where('k.pemilik', 'like', '%' . $request->nama_pemilik . '%');
        }
      }
      if ($request->filled('no_invoice')) {
        $query->where('k.no_invoice', 'like', '%' . $request->no_invoice . '%');
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
          'k.status_spk as kode_status_spk',
          'e.keterangan as status',
          'k.no_polisi',
          'k.kode_tipe',
          'b.nama_tipe',
          'k.pemilik',
          'k.kode_pelanggan',
          'c.nama_pelanggan',
          'k.no_polis',
          'k.kode_claim',
          'k.no_invoice',
          'k.tgl_invoice',
          'k.total_or',
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
          'kode_status_spk' => $row->kode_status_spk,
          'keterangan' => $row->status,
          'no_polisi' => $row->no_polisi,
          'kode_tipe' => $row->kode_tipe,
          'nama_tipe' => $row->nama_tipe,
          'pemilik' => $row->pemilik,
          'kode_pelanggan' => $row->kode_pelanggan,
          'nama_pelanggan' => $row->nama_pelanggan,
          'no_polis' => $row->no_polis,
          'kode_claim' => $row->kode_claim,
          'no_invoice' => $row->no_invoice,
          'total_or' => number_format($row->total_or, 0, ',', '.'),
          'tgl_masuk' => blank($row->tgl_masuk) ? '' : date("d/m/Y", strtotime($row->tgl_masuk)),
          'tgl_invoice' => blank($row->tgl_invoice) ? '' : date("d/m/Y", strtotime($row->tgl_invoice)),
        ];
      }

      // ✅ Always return full DataTables structure, even if no results
      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => intval($totalData),
        'recordsFiltered' => intval($totalFiltered),
        'data' => $data,
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 500);
    }
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
  public function edit($id): JsonResponse
  {
    $data = DB::table('v_spk')->where('id', $id)->first();

    if ($data->kode_jenis_pelanggan == "00002") {
      $result = false;
      return response()->json([
        'status' => (bool) $result,
        'message' => 'Pelanggan PRIBADI tidak ada OR'
      ]);
    }

    if ($data->ada_or != "01") {
      $result = false;
      return response()->json([
        'status' => (bool) $result,
        'message' => 'Invoice OR tidak perlu dibuat'
      ]);
    }

    // $data->tgl_batal = blank($data->tgl_batal) ? '' : date("d/m/Y", strtotime($data->tgl_batal));
    // $data->tgl_keluar = blank($data->tgl_keluar) ? '' : date("d/m/Y", strtotime($data->tgl_keluar));
    // $data->tgl_invoice = blank($data->tgl_invoice) ? '' : date("d/m/Y", strtotime($data->tgl_invoice));
    // $data->nilai_or = number_format($data->nilai_or, 2, ".", ",");
    // $data->total_or = number_format($data->total_or, 2, ".", ",");

    // if(blank($data->no_invoice)) {
    //   $nomor = Helper::getNomorTransaksi($data->kode_cabang, 'OR');
    //   $data->no_invoice = $nomor;
    // }

    $result = true;
    return response()->json([
      'status' => (bool) $result,
      'message' => 'Berhasil Kirim Invoice OR',
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
    // $datas = Spk::where('id', $id)->delete();
  }

  // public function cetakInvoice(Request $request)
  // {
  //   $user_cabang = session('kd_cabang');
  //   $namaCabang = session('nm_cabang');

  //   $title = 'Tanda Terima Invoice OR';

  //   $id = $request->id;

  //   $data = DB::table('v_spk')->where('id', $id)->first();

  //   if(blank($data->no_invoice)) {
  //     $pageConfigs = ['myLayout' => 'blank'];
  //     return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
  //   }

  //   $cabang = DB::table('m_cabang')->where('kode_cabang', $data->kode_cabang)->first();
  //   $cabang->alamat1 = sprintf("%s %s %s", $cabang->alamat1, $cabang->alamat2, $cabang->alamat3);

  //   $dest = public_path('assets/img/cabang');
  //   $logo_cabang = $dest.DIRECTORY_SEPARATOR.$cabang->logo_cabang;
  //   $file_logo = (is_file($logo_cabang)) ? "1" : "0";

  //   $data->terbilang = Helper::terbilang_rupiah($data->total_or);

  //   $data->total_or = number_format($data->total_or, 0, '.', ',');

  //   ## Log Activity
  //   $desc = "Cetak " . $title;
  //   LogActivity::saveLogActivity($desc);

  //   $pageConfigs = ['myLayout' => 'blank'];
  //   return view('content.customer-service.tanda-terima-invoice-or-print', [
  //     'title' => $title,
  //     // 'namaCabang' => $namaCabang,
  //     // 'periodeStr' => $periodeStr,
  //     'data' => $data,
  //     'cabang' => $cabang,
  //     'file_logo' => $file_logo,
  //     'pageConfigs' => $pageConfigs,
  //   ]);

  // }
  public function cetakInvoice(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $title = 'Tanda Terima Invoice OR';

    $ids = (array) $request->input('id', []);

    if (empty($ids)) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }

    $datas = DB::table('v_spk')->whereIn('id', $ids)->get();
    $datas = $datas->filter(fn($d) => !blank($d->no_invoice))->values();

    if ($datas->isEmpty()) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }

    // Group by kode_pelanggan — pelanggan sama = 1 halaman, beda = halaman berbeda
    // $grouped = $datas->groupBy('kode_pelanggan')->values()->map(function ($group) {
    $grouped = $datas->groupBy(function ($item) {
      return strtoupper(trim($item->pemilik));
    })->values()->map(function ($group) {
      $grandTotal = $group->sum('total_or');
      return [
        'pelanggan' => $group->first(),
        'items' => $group,
        'grand_total' => $grandTotal,
        'terbilang' => Helper::terbilang_rupiah($grandTotal),
      ];
    });

    $cabang = DB::table('m_cabang')->where('kode_cabang', $user_cabang)->first();
    $cabang->alamat1 = sprintf("%s %s %s", $cabang->alamat1, $cabang->alamat2, $cabang->alamat3);

    $dest = public_path('assets/img/cabang');
    $logo_cabang = $dest . DIRECTORY_SEPARATOR . $cabang->logo_cabang;
    $file_logo = (is_file($logo_cabang)) ? "1" : "0";

    ## Log Activity
    $desc = "Cetak " . $title;
    LogActivity::saveLogActivity($desc);

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.customer-service.tanda-terima-invoice-or-print', [
      'title' => $title,
      'grouped' => $grouped,
      'cabang' => $cabang,
      'file_logo' => $file_logo,
      'pageConfigs' => $pageConfigs,
    ]);
  }

}
