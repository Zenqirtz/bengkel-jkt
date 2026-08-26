<?php

namespace App\Http\Controllers\gudang;

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
use App\Models\Pemasok;
use App\Models\Bahan;
use App\Models\Sparepart;
use App\Models\OrderPembelian;
use App\Models\InputPembelian;
use App\Models\InputPembelianDetail;
use App\Models\Spk;
use App\Models\SaldoBahan;
use App\Models\SaldoSparepart;
use App\Models\LogActivity;
use Carbon\Carbon;
use App\Models\Notifikasi;

use App\Helpers\Helpers as Helper;

class InputPembelianController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function InputPembelian(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Input Pembelian';

    $user_cabang = session('kd_cabang');
    $tipe_barang = Parameter::query()->where('nama_tabel', 'TIPE_BARANG')->orderBy('no_urut', 'asc')->get();

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

    return view('content.gudang.input-pembelian', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'ppn_persen' => $ppn_persen,
      'tipe_barang' => $tipe_barang,
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

    if ($request->tipe == "detail") {
      // $columns = [
      //   1 => 'a.id',
      //   2 => 'c.nama_bahan',
      //   3 => 'd.keterangan',
      //   4 => 'a.qty',
      //   5 => 'a.harga',
      //   6 => 'a.jumlah',
      // ];

      // $limit = (int) $request->input('length', 10);
      // $start = (int) $request->input('start', 0);
      // $order = $columns[$request->input('order.0.column')] ?? 'a.seq_no';
      // $dir   = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('t_input_gudang_dtl as a')
        ->join('t_input_gudang_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_input', '=', 'a.kode_input'); // syarat di JOIN
        })
        ->leftJoin('m_bahan as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'a.kode_cabang')
            ->on('c.kode_bahan', '=', 'a.kode_bahan'); // syarat di JOIN
        })
        ->leftJoin('parameter as d', function ($join) {
          $join->on('d.kode', '=', 'a.kode_satuan')
            ->where('d.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
        })
        ->where('b.kode_cabang', $user_cabang)
        ->where('b.kode_input', $request->kode_input);

      $isExist = (clone $base)->exists();

      if (!$isExist) {
        $base = DB::table('t_order_dtl1 as a')
          ->join('t_order_hdr as b', function ($join) {
            $join->on('b.kode_cabang', '=', 'a.kode_cabang')
              ->on('b.kode_order', '=', 'a.kode_order'); // syarat di JOIN
          })
          ->leftJoin('m_bahan as c', function ($join) {
            $join->on('c.kode_cabang', '=', 'a.kode_cabang')
              ->on('c.kode_bahan', '=', 'a.kode_bahan'); // syarat di JOIN
          })
          ->leftJoin('parameter as d', function ($join) {
            $join->on('d.kode', '=', 'a.kode_satuan')
              ->where('d.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
          })
          ->where('b.kode_cabang', $user_cabang)
          ->where('b.kode_order', $request->kode_order);
      }

      // Total baris tanpa filter
      $totalData = (clone $base)->count('a.id');

      // Filtering (search global)
      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('a.kode_order', 'like', "%{$search}%")
            ->orWhere('c.nama_bahan', 'like', "%{$search}%");
        });
      }

      // Hitung setelah filter (tanpa limit/offset)
      $totalFiltered = (clone $query)->count('a.id');

      // Ambil data halaman saat ini
      $datas = $query
        ->select([
          'a.id',
          'a.kode_bahan',
          'a.qty',
          'a.kode_satuan',
          'a.harga',
          'a.jumlah',
          'a.cek',
          'c.nama_bahan',
          'd.keterangan as nama_satuan',
        ])
        ->orderBy('seq_no', 'asc')
        // ->offset($start)
        // ->limit($limit)
        ->get();

      // Susun payload DataTables
      $data = [];
      $fake = 0; //$start;
      foreach ($datas as $row) {
        $data[] = [
          'id' => $row->id,
          'fake_id' => ++$fake,
          'kode_bahan' => $row->kode_bahan,
          'nama_bahan' => $row->nama_bahan,
          'kode_satuan' => $row->kode_satuan,
          'nama_satuan' => $row->nama_satuan,
          'no_sparepart' => '',
          'cek' => $row->cek,
          'qty' => number_format($row->qty, 0, '.', ','),
          'harga' => number_format($row->harga, 0, '.', ','),
          'jumlah' => number_format($row->jumlah, 0, '.', ','),
        ];
      }
    } elseif ($request->tipe == "detail-spk") {
      // $columns = [
      //   1 => 'a.id',
      //   2 => 'c.nama_bahan',
      //   3 => 'd.keterangan',
      //   4 => 'a.qty',
      //   5 => 'a.harga',
      //   6 => 'a.jumlah',
      // ];

      // $limit = (int) $request->input('length', 10);
      // $start = (int) $request->input('start', 0);
      // $order = $columns[$request->input('order.0.column')] ?? 'a.seq_no';
      // $dir   = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('t_input_gudang_dtl as a')
        ->join('t_input_gudang_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_input', '=', 'a.kode_input'); // syarat di JOIN
        })
        ->leftJoin('m_sparepart as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'a.kode_cabang')
            ->on('c.kode_sparepart', '=', 'a.kode_bahan'); // syarat di JOIN
        })
        ->leftJoin('parameter as d', function ($join) {
          $join->on('d.kode', '=', 'a.kode_satuan')
            ->where('d.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
        })
        ->where('b.kode_cabang', $user_cabang)
        ->where('b.kode_input', $request->kode_input);

      $isExist = (clone $base)->exists();

      if (!$isExist) {
        $base = DB::table('t_order_dtl1 as a')
          ->join('t_order_hdr as b', function ($join) {
            $join->on('b.kode_cabang', '=', 'a.kode_cabang')
              ->on('b.kode_order', '=', 'a.kode_order'); // syarat di JOIN
          })
          ->leftJoin('m_sparepart as c', function ($join) {
            $join->on('c.kode_cabang', '=', 'a.kode_cabang')
              ->on('c.kode_sparepart', '=', 'a.kode_bahan'); // syarat di JOIN
          })
          ->leftJoin('parameter as d', function ($join) {
            $join->on('d.kode', '=', 'a.kode_satuan')
              ->where('d.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
          })
          ->where('b.kode_cabang', $user_cabang)
          ->where('b.kode_order', $request->kode_order);
      }

      // Total baris tanpa filter
      $totalData = (clone $base)->count('a.id');

      // Filtering (search global)
      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('a.kode_bahan', 'like', "%{$search}%")
            ->orWhere('c.nama_sparepart', 'like', "%{$search}%");
        });
      }

      // Hitung setelah filter (tanpa limit/offset)
      $totalFiltered = (clone $query)->count('a.id');

      // Ambil data halaman saat ini
      $datas = $query
        ->select([
          'a.id',
          'a.kode_bahan',
          'c.nama_sparepart as nama_bahan',
          'a.no_sparepart',
          'a.kode_satuan',
          'd.keterangan as nama_satuan',
          'a.qty',
          'a.harga',
          'a.jumlah',
          'a.cek',
        ])
        ->orderBy('seq_no', 'asc')
        ->get();

      // Susun payload DataTables
      $data = [];
      $fake = 0; //$start;
      foreach ($datas as $row) {
        // $cekdata = DB::table('t_input_gudang_dtl as a')
        //   ->join('t_input_gudang_hdr as b', function ($join) {
        //     $join->on('b.kode_cabang', '=', 'a.kode_cabang')
        //         ->on('b.kode_input', '=', 'a.kode_input'); // syarat di JOIN
        //   })
        //   ->where('b.kode_cabang', $user_cabang)
        //   ->where('b.kode_input', $kode)
        //   ->where('a.kode_bahan', $row->kode_bahan)
        //   ->first();

        $data[] = [
          'id' => $row->id,
          'fake_id' => ++$fake,
          'cek' => $row->cek,
          'no_sparepart' => $row->no_sparepart,
          'kode_bahan' => $row->kode_bahan,
          'nama_bahan' => $row->nama_bahan,
          'kode_satuan' => $row->kode_satuan,
          'nama_satuan' => $row->nama_satuan,
          'qty' => number_format($row->qty, 0, '.', ','),
          'harga' => number_format($row->harga, 0, '.', ','),
          'jumlah' => number_format($row->jumlah, 0, '.', ','),
        ];
      }
    } elseif ($request->tipe == "total-data") {
      $tahun = date("Y");
      ## Jumlah PO
      $totalPermintaan = OrderPembelian::where('kode_cabang', $user_cabang)
        ->where('status_approve', '1')
        ->whereYear('tanggal', $tahun)
        // ->whereYear('tanggal_permintaan', '>=', '2025')
        ->whereNotIn('kode_order', function ($subquery) use ($user_cabang) {
          $subquery->select('kode_order')
            ->from('t_input_gudang_hdr')
            ->where('kode_cabang', $user_cabang)
            ->whereRaw('LENGTH(kode_order) > 0')
            ->groupBy('kode_order');
        })
        ->count();

      ## Jumlah Pending Input Gudang
      $totalPurchasePending = InputPembelian::where('kode_cabang', $user_cabang)
        ->whereYear('tanggal', $tahun)
        ->where('status_approve', '0')
        ->count();

      ## Jumlah Input Gudang per Tahun
      $totalPurchaseOrder = InputPembelian::where('kode_cabang', $user_cabang)
        ->whereYear('tanggal', $tahun)
        ->count();

      $data['permintaan'] = number_format($totalPermintaan, 0, ".", ",");
      $data['po_pending'] = number_format($totalPurchasePending, 0, ".", ",");
      $data['po'] = number_format($totalPurchaseOrder, 0, ".", ",");

      return response()->json($data);
    } elseif ($request->tipe == "purchase-baru" || $request->tipe == "purchase-baru-all") {
      $columns = [
        1 => 'a.id',
        2 => 'a.tanggal_permintaan',
        3 => 'a.kode_permintaan',
        4 => 'g.keterangan', //'a.tipe_barang',
        5 => 'h.posisi_pekerjaan',
        6 => 'a.kode_spk',
        7 => 'b.no_polisi',
        8 => 'e.nama_merek',
        9 => 'b.pemilik',
        10 => 'f.nama_pelanggan',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.id';
      $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('t_order_hdr as a')
        ->leftJoin('m_pemasok as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_pemasok', '=', 'a.kode_pemasok'); // syarat di JOIN
        })
        ->leftJoin('parameter as c', function ($join) {
          $join->on('c.kode', '=', 'a.tipe_barang')
            ->where('c.nama_tabel', '=', 'TIPE_BARANG'); // syarat di JOIN
        })
        ->where('a.status_approve', '1')
        ->whereYear('a.tanggal', date('Y'))
        ->where('a.kode_cabang', $user_cabang);

      if ($request->tipe == "purchase-baru") {
        $base->whereNotIn('kode_order', function ($subquery) use ($user_cabang) {
          $subquery->select('kode_order')
            ->from('t_input_gudang_hdr')
            ->where('kode_cabang', $user_cabang)
            ->whereRaw('LENGTH(kode_order) > 0')
            ->groupBy('kode_order');
        });
      }

      // Total baris tanpa filter
      $totalData = (clone $base)->count('a.id');

      // Filtering (search global)
      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('a.kode_order', 'like', "%{$search}%")
            ->orWhere('a.kode_spk', 'like', "%{$search}%")
            ->orWhere('a.kode_permintaan', 'like', "%{$search}%")
            ->orWhere('c.keterangan', 'like', "%{$search}%")
            ->orWhere('b.nama_pemasok', 'like', "%{$search}%");
        });
      }

      // Hitung setelah filter (tanpa limit/offset)
      $totalFiltered = (clone $query)->count('a.id');

      // Ambil data halaman saat ini
      $datas = $query
        ->select([
          'a.id',
          'a.tanggal',
          'a.kode_cabang',
          'a.kode_order',
          'a.kode_permintaan',
          'a.kode_spk',
          'c.keterangan as tipe_barang',
          'a.kode_pemasok',
          'b.nama_pemasok',

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
          'tanggal' => blank($row->tanggal) ? '' : date("d/m/Y", strtotime($row->tanggal)),
          'kode_cabang' => $row->kode_cabang,
          'kode_order' => $row->kode_order,
          'kode_permintaan' => $row->kode_permintaan,
          'kode_spk' => $row->kode_spk,
          'tipe_barang' => $row->tipe_barang,
          'kode_pemasok' => $row->kode_pemasok,
          'nama_pemasok' => $row->nama_pemasok,
        ];
      }
    } elseif ($request->tipe == "input-pembelian") {
      $columns = [
        1 => 'a.id',
        2 => 'a.tanggal',
        3 => 'a.kode_input',
        4 => 'a.status_approve',
        5 => 'a.kode_order',
        6 => 'a.kode_spk',
        7 => 'c.keterangan', //a.tipe_barang
        8 => 'b.nama_pemasok',
        9 => 'a.total',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.id';
      $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('t_input_gudang_hdr as a')
        ->leftJoin('m_pemasok as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_pemasok', '=', 'a.kode_pemasok'); // syarat di JOIN
        })
        ->leftJoin('parameter as c', function ($join) {
          $join->on('c.kode', '=', 'a.tipe')
            ->where('c.nama_tabel', '=', 'TIPE_BARANG'); // syarat di JOIN
        })
        ->where('a.kode_cabang', $user_cabang);

      // Total baris tanpa filter
      $totalData = (clone $base)->count('a.id');

      // Filtering (search global)
      $query = (clone $base);
      // if ($search = trim((string) $request->input('search.value'))) {
      //     $query->where(function ($q) use ($search) {
      //         $q->where('a.kode_input', 'like', "%{$search}%")
      //           ->orWhere('a.no_po', 'like', "%{$search}%")
      //           ->orWhere('b.nama_pemasok', 'like', "%{$search}%");
      //     });
      // }

      // Filter berdasarkan input yang dikirim dari DataTables
      if ($request->filled('kode_order')) {
        $query->where('a.kode_order', 'like', '%' . $request->kode_order . '%');
      }
      if ($request->filled('kode_input')) {
        $query->where('a.kode_input', 'like', '%' . $request->kode_input . '%');
      }
      if ($request->filled('kode_spk')) {
        $query->where('a.kode_spk', 'like', '%' . $request->kode_spk . '%');
      }
      if ($request->filled('tanggal_awal')) {
        $startDate = Carbon::createFromFormat('d/m/Y', $request->tanggal_awal, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('a.tanggal', '>=', $startDate);
      }
      if ($request->filled('tanggal_akhir')) {
        $endDate = Carbon::createFromFormat('d/m/Y', $request->tanggal_akhir, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('a.tanggal', '<=', $endDate);
      }
      if ($request->filled('nama_pemasok')) {
        $query->where('b.nama_pemasok', 'like', '%' . $request->nama_pemasok . '%');
      }
      if ($request->filled('tipe_barang')) {
        if ($request->tipe_barang <> 'all') {
          $query->where('a.tipe', 'like', '%' . $request->tipe_barang . '%');
        }
      }
      if ($request->filled('status_approve')) {
        if ($request->status_approve <> 'all') {
          $query->where('a.status_approve', '=', $request->status_approve);
        }
      }

      // Hitung setelah filter (tanpa limit/offset)
      $totalFiltered = (clone $query)->count('a.id');

      // Ambil data halaman saat ini
      $datas = $query
        ->select([
          'a.id',
          'a.kode_cabang',
          'a.kode_input',
          'a.kode_spk',
          'a.tanggal',
          'a.kode_order',
          'a.tipe as kode_tipe',
          'a.kode_pemasok',
          'a.total',
          'a.status_approve',
          'b.nama_pemasok',
          'c.keterangan as tipe_barang',
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
          'kode_input' => $row->kode_input,
          'kode_order' => $row->kode_order,
          'kode_spk' => $row->kode_spk,
          'status_approve' => $row->status_approve,
          'keterangan' => ($row->status_approve == '1') ? 'APPROVED' : 'MENUNGGU APPROVAL',
          'tanggal' => blank($row->tanggal) ? '' : date("d/m/Y", strtotime($row->tanggal)),
          'tipe_barang' => $row->tipe_barang,
          'kode_pemasok' => $row->kode_pemasok,
          'nama_pemasok' => $row->nama_pemasok,
          'total' => number_format($row->total, 0, ".", ","),
        ];
      }
    } else {
      $totalData = 0;
      $totalFiltered = 0;
      $data = [];
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
    $tahun = (int) date("Y");
    $bulan = (int) date("m");
    $tglawal = date("Y-m-01");
    $tglakhir = date("Y-m-d");
    $user_cabang = session('kd_cabang');
    $dataID = $request->id;

    ## Cek Detail Barang
    $cek = 0;
    if ($request->detail) {
      foreach ($request->detail as $key => $item) {
        if (isset($item['cek'])) {
          $cek++;
        }
      }
    }

    if ($cek == 0) {
      return response()->json([
        'status' => false,
        'message' => "Data detail barang tidak boleh kosong."
      ], 200);
    }

    ## Cek Posisi Saldo
    if ($request->tipe == 'S' || $request->tipe == 'T') {
      $ceksaldo = SaldoSparepart::query()
        ->where('kode_cabang', $user_cabang)
        ->where('tahun', $tahun)
        ->where('bulan', $bulan)
        ->count();
    } else {
      $ceksaldo = SaldoBahan::query()
        ->where('kode_cabang', $user_cabang)
        ->where('tahun', $tahun)
        ->where('bulan', $bulan)
        ->count();
    }

    if ($ceksaldo == 0) {
      return response()->json([
        'status' => false,
        'message' => sprintf("Saldo Posisi %s %s belum tergenerate.", date("F"), $tahun)
      ], 200);
    }

    if ($dataID) {
      $res = InputPembelian::findOrFail($dataID);

      $rules = [
        // 'kode_order' => 'required',
        'tanggal' => 'required',
        'no_bon' => 'required',
      ];

      $messages = [
        // 'kode_order.required' => 'Nomor Order Wajib diisi',
        'tanggal.required' => 'Tanggal Wajib diisi',
        'no_bon.required' => 'Nomor Bon Wajib diisi',
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
        'tanggal' => Carbon::createFromFormat('d/m/Y', $request->tanggal, 'Asia/Jakarta')->format('Y-m-d'),
        'memo' => $request->memo,
        'no_bon' => $request->no_bon,
        'ppn' => str_replace([","], "", $request->ppn),
        'diskon' => str_replace([","], "", $request->diskon),
        'total' => str_replace([","], "", $request->total_detail),
        'updated_by' => Auth::user()->username
      ];

      if ($request->status_approve == "1") {
        $data['status_approve'] = $request->status_approve;
        $data['tgl_approve'] = date("Y-m-d H:i:s");
        $data['user_approve'] = Auth::user()->username;
      }

      $result = $res->update($data);

      if ($result) {
        ## INSERT DETAIL ORDER
        InputPembelianDetail::where('kode_cabang', $user_cabang)->where('kode_input', $request->kode_input)->delete();
        if ($request->detail) {
          $no = 1;
          foreach ($request->detail as $item) {
            if (isset($item['cek'])) {
              InputPembelianDetail::create([
                'kode_cabang' => $user_cabang,
                'kode_input' => $request->kode_input,
                'seq_no' => $no++,
                'kode_bahan' => $item['bahan'],
                'kode_satuan' => $item['satuan'],
                'diskon' => 0,
                'harga_diskon' => 0,
                'ppn' => 0,
                'qty' => str_replace([","], "", $item['qty']),
                'harga' => str_replace([","], "", $item['harga']),
                'jumlah' => str_replace([","], "", $item['jumlah']),
                'jumlah_sebelum' => str_replace([","], "", $item['jumlah']),
                'memo' => $res->tipe_bayar,
                'no_sparepart' => isset($item['no_sparepart']) ? $item['no_sparepart'] : null,
                'cek' => isset($item['cek']) ? $item['cek'] : '0',
                'created_by' => Auth::user()->username,
              ]);

              ## UPDATE SALDO STOCK
              if ($request->status_approve == "1") {
                $qty = str_replace([","], "", $item['qty']);
                $harga = str_replace([","], "", $item['harga']);
                $jumlah = str_replace([","], "", $item['jumlah']);

                if ($res->tipe == 'S' || $res->tipe == 'T') {
                  $spk = Spk::query()->where('kode_cabang', $user_cabang)->where('kode_spk', $request->kode_spk)->first();

                  $saldo = SaldoSparepart::query()
                    ->where('kode_sparepart', $item['bahan'])
                    ->where('kode_input', $request->kode_input)
                    ->where('kode_cabang', $user_cabang)
                    ->where('tahun', $tahun)
                    ->where('bulan', $bulan)
                    ->first();

                  if ($saldo) {
                    DB::select('CALL up_apl_rekonsiliasi_sparepart(?, ?, ?, ?, ?)', [
                      $user_cabang,
                      $item['bahan'],
                      $tglawal,
                      $tglakhir,
                      Auth::user()->username
                    ]);

                    // $tmp['unit_tambah'] = $saldo->unit_tambah + $qty;
                    // $tmp['jumlah_tambah'] = $saldo->jumlah_tambah + $jumlah;
                    // $tmp['harga_tambah'] = ($tmp['unit_tambah'] > 0) ? ($tmp['jumlah_tambah'] / $tmp['unit_tambah']) : 0;
                    // $tmp['unit_akhir'] = ($saldo->unit_awal + $tmp['unit_tambah']) - ($saldo->unit_kurang + $saldo->unit_retur);
                    // $tmp['jumlah_akhir'] = ($saldo->jumlah_awal + $tmp['jumlah_tambah']) - ($saldo->jumlah_kurang + $saldo->jumlah_retur);
                    // $tmp['harga_akhir'] = ($tmp['unit_akhir'] > 0) ? ($tmp['jumlah_akhir'] / $tmp['unit_akhir']) : 0;
                    // $tmp['updated_by'] = Auth::user()->username;
                    // SaldoSparepart::where('id', $saldo->id)->update($tmp);
                  } else {
                    $tmp['kode_cabang'] = $user_cabang;
                    $tmp['periode_bulan'] = $bulan;
                    $tmp['periode_tahun'] = $tahun;
                    $tmp['bulan'] = $bulan;
                    $tmp['tahun'] = $tahun;
                    $tmp['kode_merek'] = $spk->kode_merek;
                    $tmp['kode_tipe'] = $spk->kode_tipe;
                    $tmp['kode_input'] = $request->kode_input;
                    $tmp['kode_sparepart'] = $item['bahan'];
                    $tmp['unit_awal'] = 0;
                    $tmp['harga_awal'] = 0;
                    $tmp['jumlah_awal'] = 0;
                    $tmp['unit_tambah'] = $qty;
                    $tmp['harga_tambah'] = $harga;
                    $tmp['jumlah_tambah'] = $jumlah;
                    $tmp['unit_kurang'] = 0;
                    $tmp['harga_kurang'] = 0;
                    $tmp['jumlah_kurang'] = 0;
                    $tmp['unit_retur'] = 0;
                    $tmp['harga_retur'] = 0;
                    $tmp['jumlah_retur'] = 0;
                    $tmp['unit_adjust'] = 0;
                    $tmp['harga_adjust'] = 0;
                    $tmp['jumlah_adjust'] = 0;
                    $tmp['unit_so'] = 0;
                    $tmp['harga_so'] = 0;
                    $tmp['jumlah_so'] = 0;
                    $tmp['unit_selisih'] = 0;
                    $tmp['jumlah_selisih'] = 0;
                    $tmp['unit_akhir'] = $qty;
                    $tmp['harga_akhir'] = $harga;
                    $tmp['jumlah_akhir'] = $jumlah;
                    $tmp['created_by'] = Auth::user()->username;
                    SaldoSparepart::create($tmp);
                  }
                } else {
                  $saldo = SaldoBahan::query()
                    ->where('kode_bahan', $item['bahan'])
                    ->where('kode_cabang', $user_cabang)
                    ->where('tahun', $tahun)
                    ->where('bulan', $bulan)
                    ->first();

                  if ($saldo) {
                    DB::select('CALL up_apl_update_saldo_bahan(?, ?, ?, ?, ?)', [
                      $user_cabang,
                      $item['bahan'],
                      $tglawal,
                      $tglakhir,
                      Auth::user()->username
                    ]);
                    // $tmp['unit_tambah'] = $saldo->unit_tambah + $qty;
                    // $tmp['jumlah_tambah'] = $saldo->jumlah_tambah + $jumlah;
                    // $tmp['harga_tambah'] = ($tmp['unit_tambah'] > 0) ? ($tmp['jumlah_tambah'] / $tmp['unit_tambah']) : 0;
                    // $tmp['unit_akhir'] = ($saldo->unit_awal + $tmp['unit_tambah']) - ($saldo->unit_kurang + $saldo->unit_retur);
                    // $tmp['jumlah_akhir'] = ($saldo->jumlah_awal + $tmp['jumlah_tambah']) - ($saldo->jumlah_kurang + $saldo->jumlah_retur);
                    // $tmp['harga_akhir'] = ($tmp['unit_akhir'] > 0) ? ($tmp['jumlah_akhir'] / $tmp['unit_akhir']) : 0;
                    // $tmp['updated_by'] = Auth::user()->username;
                    // SaldoBahan::where('id', $saldo->id)->update($tmp);
                  } else {
                    $tmp['kode_cabang'] = $user_cabang;
                    $tmp['bulan'] = $bulan;
                    $tmp['tahun'] = $tahun;
                    $tmp['kode_bahan'] = $item['bahan'];
                    $tmp['kode_group_bahan'] = ($res->tipe == 'P') ? '00001' : '00002';
                    $tmp['unit_awal'] = 0;
                    $tmp['harga_awal'] = 0;
                    $tmp['jumlah_awal'] = 0;
                    $tmp['unit_tambah'] = $qty;
                    $tmp['harga_tambah'] = $harga;
                    $tmp['jumlah_tambah'] = $jumlah;
                    $tmp['unit_kurang'] = 0;
                    $tmp['harga_kurang'] = 0;
                    $tmp['jumlah_kurang'] = 0;
                    $tmp['unit_retur'] = 0;
                    $tmp['harga_retur'] = 0;
                    $tmp['jumlah_retur'] = 0;
                    $tmp['unit_adjust'] = 0;
                    $tmp['harga_adjust'] = 0;
                    $tmp['jumlah_adjust'] = 0;
                    $tmp['unit_so'] = 0;
                    $tmp['harga_so'] = 0;
                    $tmp['jumlah_so'] = 0;
                    $tmp['unit_selisih'] = 0;
                    $tmp['jumlah_selisih'] = 0;
                    $tmp['unit_akhir'] = $qty;
                    $tmp['harga_akhir'] = $harga;
                    $tmp['jumlah_akhir'] = $jumlah;
                    $tmp['created_by'] = Auth::user()->username;
                    SaldoBahan::create($tmp);
                  }
                }
              }
            }
          }
        }
      }

      // ## Notifikasi ke Staff (UL01) kalau IG baru saja di-approve
      // if ($request->status_approve == "1") {
      //   Notifikasi::aggregate(
      //     [
      //       'user_id' => null,
      //       'target_level' => 'UL01',
      //       'kode_cabang' => $res->kode_cabang,
      //       'tipe' => 'operasional',
      //       'url' => url('gudang/input-pembelian'),
      //       'is_read' => false,
      //     ],
      //     'ig_approved_' . $res->kode_cabang . '_' . now()->format('Ymd'),
      //     'Ada {count} Input Gudang Disetujui',
      //     "Input Gudang {$res->kode_input} telah disetujui oleh " . Auth::user()->username . "."
      //   );
      // }

      ## Log Activity
      if ($request->status_approve == "1") {
        $desc = $result ? 'Berhasil Approve Input Gudang' : 'Gagal Approve Input Gudang';
      } else {
        $desc = $result ? 'Berhasil Ubah Input Gudang' : 'Gagal Ubah Input Gudang';
      }
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status' => (bool) $result,
        'message' => $desc
      ]);
    } else {

      $rules = [
        'kode_order' => 'required',
        'tanggal' => 'required',
        'no_bon' => 'required',
      ];

      $messages = [
        'kode_order.required' => 'Nomor Order Wajib diisi',
        'tanggal.required' => 'Tanggal Wajib diisi',
        'no_bon.required' => 'Nomor Bon Wajib diisi',
      ];

      $validator = Validator::make($request->all(), $rules, $messages);

      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ], 200);
      }

      $tanggalInput = $request->tanggal;
      $carbonDate = blank($tanggalInput) ? now() : Carbon::createFromFormat('d/m/Y', trim($tanggalInput), 'Asia/Jakarta');
      $tanggal = $carbonDate->format('Y-m-d');
      $tahun = $carbonDate->year;
      $tahunShort = now()->format('y');

      ## Nomor PO
      // $lastNum = OrderPembelian::query()->where('kode_cabang', $user_cabang)->whereYear('tanggal', $tahun)->max(DB::raw('CAST(RIGHT(no_po, 5) AS UNSIGNED)')) ?? 0;
      // $no_po = sprintf('%02d%05d', $tahunShort, $lastNum + 1);

      $dtOrder = OrderPembelian::where('kode_order', $request->kode_order)->first();

      if ($dtOrder->tipe_barang == "S") {
        $jenis = 'SP';
      } elseif ($dtOrder->tipe_barang == "P") {
        $jenis = 'BN';
      } elseif ($dtOrder->tipe_barang == "C") {
        $jenis = 'CT';
      } elseif ($dtOrder->tipe_barang == "U") {
        $jenis = 'UM';
      }

      ## Nomor Input Pembelian
      $penomoran = Helper::getNomorTransaksi($user_cabang, $jenis);

      $cekspk = InputPembelian::where('kode_input', $penomoran)->first();
      if (!empty($cekspk)) {
        return response()->json(['status' => false, 'message' => "Nomor Input Pembelian sudah digunakan"]);
      }

      $data = [
        'kode_cabang' => $user_cabang,
        'kode_input' => $penomoran,
        'no_input' => $penomoran,
        'tanggal' => $tanggal,
        'kode_order' => $dtOrder->kode_order,
        'no_po' => $dtOrder->no_po,
        'kode_pemasok' => $dtOrder->kode_pemasok,
        'tipe' => $dtOrder->tipe_barang,
        'sifat_ppn' => $dtOrder->sifat_ppn,
        'kode_spk' => $dtOrder->kode_spk,
        'tipe_bayar' => $dtOrder->tipe_bayar,
        'memo' => $request->memo,
        'no_bon' => $request->no_bon,
        'is_bayar' => 'N',
        'ppn' => str_replace([","], "", $request->ppn),
        'diskon' => str_replace([","], "", $request->diskon),
        'total' => str_replace([","], "", $request->total_detail),
        'created_by' => Auth::user()->username
      ];

      $result = InputPembelian::create($data);

      if ($result) {
        ## INSERT DETAIL ORDER
        InputPembelianDetail::where('kode_cabang', $user_cabang)->where('kode_input', $penomoran)->delete();
        if ($request->detail) {
          $no = 1;
          foreach ($request->detail as $item) {
            if (isset($item['cek'])) {
              InputPembelianDetail::create([
                'kode_cabang' => $user_cabang,
                'kode_input' => $penomoran,
                'seq_no' => $no++,
                'kode_bahan' => $item['bahan'],
                'kode_satuan' => $item['satuan'],
                'diskon' => 0,
                'harga_diskon' => 0,
                'ppn' => 0,
                'qty' => str_replace([","], "", $item['qty']),
                'harga' => str_replace([","], "", $item['harga']),
                'jumlah' => str_replace([","], "", $item['jumlah']),
                'jumlah_sebelum' => str_replace([","], "", $item['jumlah']),
                'memo' => $dtOrder->tipe_bayar,
                'no_sparepart' => isset($item['no_sparepart']) ? $item['no_sparepart'] : null,
                'cek' => isset($item['cek']) ? $item['cek'] : '0',
                'created_by' => Auth::user()->username,
              ]);
            }
          }
        }

        ## Update Nomor Input Pembelian
        $res = Helper::updateNomorTransaksi($user_cabang, $jenis);
      }


      // ## Notifikasi ke Manager & Supervisor kalau ada IG baru menunggu approval
      // foreach (['UL02', 'UL03'] as $level) {
      //   Notifikasi::aggregate(
      //     [
      //       'user_id' => null,
      //       'target_level' => $level,
      //       'kode_cabang' => $user_cabang,
      //       'tipe' => 'operasional',
      //       'url' => url('gudang/input-pembelian'),
      //       'is_read' => false,
      //     ],
      //     'ig_new_' . $level . '_' . $user_cabang . '_' . now()->format('Ymd'),
      //     'Ada {count} Input Gudang Baru Menunggu Approval',
      //     "Input Gudang {$penomoran} menunggu persetujuan Anda."
      //   );
      // }

      ## Log Activity
      $desc = $result ? 'Berhasil Tambah Input Pembelian' : 'Gagal Tambah Input Pembelian';
      LogActivity::saveLogActivity($desc, $data);

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
    if (!$id) {
      return response()->json([]);
    }

    $user_cabang = session('kd_cabang');

    $data = DB::table('t_input_gudang_hdr as a')
      ->leftJoin('m_pemasok as b', function ($join) {
        $join->on('b.kode_cabang', '=', 'a.kode_cabang')
          ->on('b.kode_pemasok', '=', 'a.kode_pemasok'); // syarat di JOIN
      })
      ->leftJoin('parameter as c', function ($join) {
        $join->on('c.kode', '=', 'a.tipe')
          ->where('c.nama_tabel', '=', 'TIPE_BARANG'); // syarat di JOIN
      })
      ->leftJoin('parameter as d', function ($join) {
        $join->on('d.kode', '=', 'a.tipe_bayar')
          ->where('d.nama_tabel', '=', 'TIPE_BAYAR'); // syarat di JOIN
      })
      ->leftJoin('t_order_hdr as e', function ($join) {
        $join->on('e.kode_cabang', '=', 'a.kode_cabang')
          ->on('e.kode_order', '=', 'a.kode_order'); // syarat di JOIN
      })
      ->where('a.kode_cabang', $user_cabang)
      ->where('a.id', $id)
      ->select([
        'a.id',
        'a.kode_cabang',
        'a.kode_input',
        'a.tanggal as tgl_input',
        'a.kode_order',
        'e.tanggal as tgl_order',
        'a.no_po',
        'a.kode_spk',
        'a.sifat_ppn',
        'a.tipe as tipe_barang',
        'a.tipe_bayar',
        'a.kode_pemasok',
        'a.ppn',
        'a.diskon',
        'a.total',
        'b.nama_pemasok',
        'c.keterangan as nama_tipe_barang',
        'd.keterangan as nama_tipe_bayar',
      ])
      ->first();

    if ($data) {
      $data->tgl_input = date("d/m/Y", strtotime($data->tgl_input));
      $data->tgl_order = date("d/m/Y", strtotime($data->tgl_order));
    }

    return response()->json($data);
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id): JsonResponse
  {
    // $data = InputPembelian::findOrFail($id);
    if (!$id) {
      return response()->json([]);
    }

    $user_cabang = session('kd_cabang');

    $data = DB::table('t_input_gudang_hdr as a')
      ->leftJoin('m_pemasok as b', function ($join) {
        $join->on('b.kode_cabang', '=', 'a.kode_cabang')
          ->on('b.kode_pemasok', '=', 'a.kode_pemasok'); // syarat di JOIN
      })
      ->leftJoin('parameter as c', function ($join) {
        $join->on('c.kode', '=', 'a.tipe')
          ->where('c.nama_tabel', '=', 'TIPE_BARANG'); // syarat di JOIN
      })
      ->leftJoin('parameter as d', function ($join) {
        $join->on('d.kode', '=', 'a.tipe_bayar')
          ->where('d.nama_tabel', '=', 'TIPE_BAYAR'); // syarat di JOIN
      })
      ->where('a.id', $id)
      ->select([
        'a.id',
        'a.kode_cabang',
        'a.kode_input',
        'a.kode_order',
        'a.tanggal',
        'a.no_po',
        'a.no_bon',
        'a.kode_spk',
        'a.sifat_ppn',
        'a.tipe as kode_tipe_barang',
        'a.tipe_bayar as kode_tipe_bayar',
        'a.kode_pemasok',
        'a.memo',
        'a.ppn',
        'a.diskon',
        'a.total',
        'a.status_approve',
        'b.nama_pemasok',
        'c.keterangan as tipe_barang',
        'd.keterangan as tipe_bayar',
      ])
      ->first();

    $data->tanggal = date("d/m/Y", strtotime($data->tanggal));
    $data->diskon = number_format($data->diskon, 2, '.', ',');

    $dtOrder = DB::table('v_purchase_order')
      ->where('kode_cabang', $data->kode_cabang)
      ->where('kode_order', $data->kode_order)
      ->first();

    if ($dtOrder) {
      // $data->id_order = $dtOrder->id;
      $data->tanggal_order = date("d/m/Y", strtotime($dtOrder->tanggal));
      $data->no_polisi = $dtOrder->no_polisi;
      $data->merek_tipe = $dtOrder->merek_tipe;
      $data->pemilik = $dtOrder->pemilik;
      $data->nama_pelanggan = $dtOrder->nama_pelanggan;
      $data->no_polis = $dtOrder->no_polis;
      $data->kode_claim = $dtOrder->kode_claim;
    }

    $message = ($data->status_approve == '1') ? 'Data IG sudah di appprove' : 'Berhasil Input Gudang';

    $result = true;
    return response()->json([
      'status' => (bool) $result,
      'message' => $message,
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
    $data = InputPembelian::query()->where('id', $id)->first()?->toArray() ?? [];

    $ok = InputPembelian::where('id', $id)->delete();
    if ($ok) {
      InputPembelianDetail::where('kode_cabang', $data['kode_cabang'])->where('kode_input', $data['kode_input'])->delete();
    }

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Input Gudang' : 'Gagal Hapus Input Gudang';
    LogActivity::saveLogActivity($desc, $data);
  }

  // public function getDataSPK(Request $request): JsonResponse
  // {
  //   $jenisId = $request->query('jenis_id');
  //   $kodeSPK = $request->query('spk');

  //   if ($jenisId != "S") {
  //       return response()->json([]);
  //   }

  //   $user_cabang = session('kd_cabang');
  //   $data = DB::table('v_trx_spk_sparepart')
  //     ->select('kode_spk')
  //     ->where('kode_cabang', $user_cabang)
  //     // Subquery NOT IN dimulai di sini
  //     ->whereNotIn('kode_spk', function($query) use ($user_cabang, $kodeSPK) {
  //         $query->select('kode_spk')
  //               ->from('t_order_hdr')
  //               ->where('kode_cabang', $user_cabang)
  //               ->where('kode_spk', '!=', $kodeSPK)
  //               ->distinct(); // Sesuai query asli 'select distinct'
  //     })
  //     ->groupBy('kode_spk')
  //     ->orderBy('tgl_masuk', 'desc')
  //     ->get();

  //   return response()->json($data);
  // }

  public function getDataOrder(Request $request): JsonResponse
  {
    $kd_cabang = session('kd_cabang');
    $kode_order = $request->kode_order;

    $data = DB::table('v_purchase_order')
      ->where('id', $kode_order)
      ->first();

    if (blank($data)) {
      $result = false;
      return response()->json([
        'status' => (bool) $result,
        'message' => 'Kode Purchase Order tidak ditemukan'
      ]);
    }

    $data->tanggal = blank($data->tanggal) ? '' : date("d/m/Y", strtotime($data->tanggal));

    $result = true;
    return response()->json([
      'status' => (bool) $result,
      'message' => 'Berhasil Data PO',
      'data' => $data
    ]);
  }

  public function cetakInputPembelian(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Input Pembelian';

    $id = $request->id;

    $datas = DB::table('t_input_gudang_hdr as a')
      ->leftJoin('m_pemasok as b', function ($join) {
        $join->on('b.kode_cabang', '=', 'a.kode_cabang')
          ->on('b.kode_pemasok', '=', 'a.kode_pemasok'); // syarat di JOIN
      })
      ->leftJoin('parameter as c', function ($join) {
        $join->on('c.kode', '=', 'a.tipe')
          ->where('c.nama_tabel', '=', 'TIPE_BARANG'); // syarat di JOIN
      })
      ->leftJoin('m_cabang as d', 'd.kode_cabang', '=', 'a.kode_cabang')
      ->where('a.id', $id)
      ->select([
        'a.id',
        'a.kode_cabang',
        'a.kode_input',
        'a.kode_order',
        'a.kode_spk',
        'a.tanggal',
        'a.no_po',
        'a.tipe as kode_tipe',
        'a.tipe_bayar',
        'a.kode_pemasok',
        'a.ppn',
        'a.diskon',
        'a.total',
        'b.nama_pemasok',
        'c.keterangan as tipe_barang',
        'd.nama_cabang',
        'd.alamat1',
        'd.alamat2',
        'd.alamat3',
        'd.telepon',
        'd.fax',
      ])
      ->first();

    if (blank($datas)) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }

    $datas->tanggal = date("d-M-Y", strtotime($datas->tanggal));
    $datas->alamat1 = sprintf("%s %s %s", $datas->alamat1, $datas->alamat2, $datas->alamat3);

    ## DATA DETAIL
    if ($datas->kode_tipe == "S") {
      $spk = DB::table('v_spk')->select('kode_spk', 'no_polisi', 'merek_tipe', 'kode_estimasi')->where('kode_spk', $datas->kode_spk)->first();

      $details = DB::table('t_input_gudang_dtl as a')
        ->join('t_input_gudang_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_input', '=', 'a.kode_input'); // syarat di JOIN
        })
        ->leftJoin('m_sparepart as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'a.kode_cabang')
            ->on('c.kode_sparepart', '=', 'a.kode_bahan'); // syarat di JOIN
        })
        ->leftJoin('parameter as d', function ($join) {
          $join->on('d.kode', '=', 'a.kode_satuan')
            ->where('d.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
        })
        ->where('b.kode_cabang', $datas->kode_cabang)
        ->where('b.kode_input', $datas->kode_input)
        ->where('a.cek', '1')
        ->select(['a.seq_no', 'a.kode_bahan', 'c.nama_sparepart as nama_bahan', 'a.no_sparepart', 'a.kode_satuan', 'd.keterangan as nama_satuan', 'a.qty', 'a.harga', 'a.jumlah'])
        ->orderBy('seq_no', 'asc')
        ->get();
    } else {
      $spk = null;

      $details = DB::table('t_input_gudang_dtl as a')
        ->join('t_input_gudang_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_input', '=', 'a.kode_input'); // syarat di JOIN
        })
        ->leftJoin('m_bahan as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'a.kode_cabang')
            ->on('c.kode_bahan', '=', 'a.kode_bahan'); // syarat di JOIN
        })
        ->leftJoin('parameter as d', function ($join) {
          $join->on('d.kode', '=', 'a.kode_satuan')
            ->where('d.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
        })
        ->where('b.kode_cabang', $datas->kode_cabang)
        ->where('b.kode_input', $datas->kode_input)
        ->where('a.cek', '1')
        ->select(['a.seq_no', 'a.kode_bahan', 'c.nama_bahan', 'a.no_sparepart', 'a.kode_satuan', 'd.keterangan as nama_satuan', 'a.qty', 'a.harga', 'a.jumlah'])
        ->orderBy('seq_no', 'asc')
        ->get();
    }


    // $details = DB::table('t_input_gudang_dtl as a')
    //   ->join('t_input_gudang_hdr as b', function ($join) {
    //     $join->on('b.kode_cabang', '=', 'a.kode_cabang')
    //         ->on('b.kode_input', '=', 'a.kode_input'); // syarat di JOIN
    //   })
    //   // ->leftJoin('m_bahan as c', function ($join) {
    //   //   $join->on('c.kode_cabang', '=', 'a.kode_cabang')
    //   //       ->on('c.kode_bahan', '=', 'a.kode_bahan'); // syarat di JOIN
    //   // })
    //   ->leftJoin('m_sparepart as c', function ($join) {
    //     $join->on('c.kode_cabang', '=', 'a.kode_cabang')
    //         ->on('c.kode_sparepart', '=', 'a.kode_bahan'); // syarat di JOIN
    //   })
    //   ->leftJoin('parameter as d', function ($join) {
    //     $join->on('d.kode', '=', 'a.kode_satuan')
    //         ->where('d.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
    //   })
    //   ->where('b.kode_cabang', $datas->kode_cabang)
    //   ->where('b.kode_input', $datas->kode_input)
    //   ->where('a.cek', '1')
    //   // ->select(['a.seq_no','a.kode_bahan','c.nama_bahan','a.no_sparepart','a.kode_satuan','d.keterangan as nama_satuan','a.qty','a.harga','a.jumlah'])
    //   ->select(['a.seq_no','a.kode_bahan','c.nama_sparepart as nama_bahan','a.no_sparepart','a.kode_satuan','d.keterangan as nama_satuan','a.qty','a.harga','a.jumlah'])
    //   ->orderBy('seq_no', 'asc')
    //   ->get();

    ## Log Activity
    $desc = "Cetak " . $title;
    LogActivity::saveLogActivity($desc);

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.gudang.input-pembelian-cetak', [
      'title' => $title,
      'spk' => $spk,
      'datas' => $datas,
      'details' => $details,
      'pageConfigs' => $pageConfigs,
    ]);
  }
}
