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
use App\Models\InputPembelian;
use App\Models\ReturPembelian;
use App\Models\ReturPembelianDetail;
use App\Models\Spk;
use App\Models\SaldoBahan;
use App\Models\SaldoSparepart;
use App\Models\LogActivity;
use Carbon\Carbon;
use App\Models\Notifikasi;

use App\Helpers\Helpers as Helper;

class ReturPembelianController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function ReturPembelian(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Retur Pembelian';

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

    return view('content.gudang.retur-pembelian', [
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
      $base = DB::table('t_retur_barang_dtl as a')
        ->join('t_retur_barang_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.seq_no', '=', 'a.seq_no'); // syarat di JOIN
        })
        ->leftJoin('m_bahan as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'a.kode_cabang')
            ->on('c.kode_bahan', '=', 'a.kode_barang'); // syarat di JOIN
        })
        ->leftJoin('parameter as d', function ($join) {
          $join->on('d.kode', '=', 'a.kode_satuan')
            ->where('d.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
        })
        ->where('b.kode_cabang', $user_cabang)
        ->where('b.no_retur', $request->no_retur);

      $isExist = (clone $base)->exists();

      if (!$isExist) {
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
          'c.kode_bahan',
          'a.qty',
          'a.kode_satuan',
          'a.no_sparepart',
          'a.harga',
          'a.jumlah',
          'a.cek',
          'c.nama_bahan',
          'd.keterangan as nama_satuan',
        ])
        ->orderBy('a.id', 'asc')
        // ->offset($start)
        // ->limit($limit)
        ->get();

      // Susun payload DataTables
      $data = [];
      $fake = 0; //$start;
      foreach ($datas as $row) {
        if (!$isExist) {
          $cekdata = DB::table('t_retur_barang_dtl as a')
            ->join('t_retur_barang_hdr as b', function ($join) {
              $join->on('b.kode_cabang', '=', 'a.kode_cabang')
                ->on('b.seq_no', '=', 'a.seq_no'); // syarat di JOIN
            })
            ->where('b.kode_cabang', $user_cabang)
            ->where('b.kode_input', $request->kode_input)
            ->where('a.kode_barang', $row->kode_bahan)
            ->first();
        } else {
          $cekdata = null;
        }

        if (!$cekdata) {
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
      }
    } elseif ($request->tipe == "detail-spk") {
      $kode = blank($request->no_retur) ? 'xx' : $request->no_retur;
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
      $base = DB::table('t_retur_barang_dtl as a')
        ->join('t_retur_barang_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.seq_no', '=', 'a.seq_no'); // syarat di JOIN
        })
        ->leftJoin('m_sparepart as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'a.kode_cabang')
            ->on('c.kode_sparepart', '=', 'a.kode_barang'); // syarat di JOIN
        })
        ->leftJoin('parameter as d', function ($join) {
          $join->on('d.kode', '=', 'a.kode_satuan')
            ->where('d.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
        })
        ->where('b.kode_cabang', $user_cabang)
        ->where('b.no_retur', $request->no_retur);

      $isExist = (clone $base)->exists();

      if (!$isExist) {
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
          'c.kode_sparepart as kode_bahan',
          'c.nama_sparepart as nama_bahan',
          'a.no_sparepart',
          'a.kode_satuan',
          'd.keterangan as nama_satuan',
          'a.qty',
          'a.harga',
          'a.jumlah',
          'a.cek',
        ])
        ->orderBy('a.id', 'asc')
        ->get();

      // Susun payload DataTables
      $data = [];
      $fake = 0; //$start;
      foreach ($datas as $row) {
        if (!$isExist) {
          $cekdata = DB::table('t_retur_barang_dtl as a')
            ->join('t_retur_barang_hdr as b', function ($join) {
              $join->on('b.kode_cabang', '=', 'a.kode_cabang')
                ->on('b.seq_no', '=', 'a.seq_no'); // syarat di JOIN
            })
            ->where('b.kode_cabang', $user_cabang)
            ->where('b.kode_input', $request->kode_input)
            ->where('a.kode_barang', $row->kode_bahan)
            ->first();
        } else {
          $cekdata = null;
        }

        if (!$cekdata) {
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
      }
    } elseif ($request->tipe == "total-data") {
      $tahun = date("Y");
      ## Jumlah Input Gudang
      $totalPermintaan = InputPembelian::where('kode_cabang', $user_cabang)
        ->where('status_approve', '1')
        ->whereYear('tanggal', $tahun)
        // ->whereYear('tanggal_permintaan', '>=', '2025')
        ->whereNotIn('kode_input', function ($subquery) use ($user_cabang) {
          $subquery->select('kode_input')
            ->from('t_retur_barang_hdr')
            ->where('kode_cabang', $user_cabang)
            ->whereRaw('LENGTH(kode_input) > 0')
            ->groupBy('kode_input');
        })
        ->count();

      ## Jumlah Pending Retur Gudang
      $totalPurchasePending = ReturPembelian::where('kode_cabang', $user_cabang)
        ->whereYear('tanggal', $tahun)
        ->where('status_approve', '0')
        ->count();

      ## Jumlah Retur Gudang per Tahun
      $totalPurchaseInput = ReturPembelian::where('kode_cabang', $user_cabang)
        ->whereYear('tanggal', $tahun)
        ->count();

      $data['permintaan'] = number_format($totalPermintaan, 0, ".", ",");
      $data['po_pending'] = number_format($totalPurchasePending, 0, ".", ",");
      $data['po'] = number_format($totalPurchaseInput, 0, ".", ",");

      return response()->json($data);
    } elseif ($request->tipe == "input-gudang" || $request->tipe == "input-gudang-all") {
      $columns = [
        1 => 'a.id',
        2 => 'a.tanggal',
        3 => 'a.kode_input',
        4 => 'a.kode_order',
        5 => 'a.kode_spk',
        6 => 'c.keterangan', //'a.tipe_barang',
        7 => 'b.nama_pemasok',
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
        ->where('a.status_approve', '1')
        ->whereYear('a.tanggal', date('Y'))
        ->where('a.kode_cabang', $user_cabang);

      if ($request->tipe == "input-gudang") {
        $base->whereNotIn('kode_input', function ($subquery) use ($user_cabang) {
          $subquery->select('kode_input')
            ->from('t_retur_barang_hdr')
            ->where('kode_cabang', $user_cabang)
            ->whereRaw('LENGTH(kode_input) > 0')
            ->groupBy('kode_input');
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
            ->orWhere('a.kode_input', 'like', "%{$search}%")
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
          'a.kode_input',
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
          'kode_input' => $row->kode_input,
          'kode_spk' => $row->kode_spk,
          'tipe_barang' => $row->tipe_barang,
          'kode_pemasok' => $row->kode_pemasok,
          'nama_pemasok' => $row->nama_pemasok,
        ];
      }
    } elseif ($request->tipe == "retur-pembelian") {
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
      $base = DB::table('t_retur_barang_hdr as a')
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
      if ($request->filled('no_retur')) {
        $query->where('a.no_retur', 'like', '%' . $request->no_retur . '%');
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
          'a.no_retur',
          'a.kode_spk',
          'a.tanggal',
          'a.kode_input',
          'a.tipe as kode_tipe',
          'a.kode_pemasok',
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
          'no_retur' => $row->no_retur,
          'kode_input' => $row->kode_input,
          'kode_spk' => $row->kode_spk,
          'status_approve' => $row->status_approve,
          'keterangan' => ($row->status_approve == '1') ? 'APPROVED' : 'MENUNGGU APPROVAL',
          'tanggal' => blank($row->tanggal) ? '' : date("d/m/Y", strtotime($row->tanggal)),
          'tipe_barang' => $row->tipe_barang,
          'kode_pemasok' => $row->kode_pemasok,
          'nama_pemasok' => $row->nama_pemasok,
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
      $res = ReturPembelian::findOrFail($dataID);

      $rules = [
        // 'kode_order' => 'required',
        'tanggal' => 'required',
        'no_bon' => 'required',
      ];

      $messages = [
        // 'kode_order.required' => 'Nomor Input Wajib diisi',
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
        ReturPembelianDetail::where('kode_cabang', $user_cabang)->where('seq_no', $res->seq_no)->delete();
        if ($request->detail) {
          $no = 1;
          foreach ($request->detail as $item) {
            if (isset($item['cek'])) {
              ReturPembelianDetail::create([
                'kode_cabang' => $user_cabang,
                'seq_no' => $res->seq_no,
                'line_no' => $no++,
                'kode_barang' => $item['bahan'],
                'kode_satuan' => $item['satuan'],
                'qty' => str_replace([","], "", $item['qty']),
                'harga' => str_replace([","], "", $item['harga']),
                'jumlah' => str_replace([","], "", $item['jumlah']),
                'no_sparepart' => isset($item['no_sparepart']) ? $item['no_sparepart'] : null,
                'cek' => isset($item['cek']) ? $item['cek'] : '0',
                'created_by' => Auth::user()->username,
              ]);

              ## UPDATE SALDO STOCK
              if ($request->status_approve == "1") {
                // $qty = str_replace([","], "", $item['qty']);
                // $harga = str_replace([","], "", $item['harga']);
                // $jumlah = str_replace([","], "", $item['jumlah']);

                if ($res->tipe == 'S' || $res->tipe == 'T') {

                  DB::select('CALL up_apl_rekonsiliasi_sparepart(?, ?, ?, ?, ?)', [
                    $user_cabang,
                    $item['bahan'],
                    $tglawal,
                    $tglakhir,
                    Auth::user()->username
                  ]);
                  // $spk = Spk::query()->where('kode_cabang', $user_cabang)->where('kode_spk', $request->kode_spk)->first();

                  // $saldo = SaldoSparepart::query()
                  //   ->where('kode_sparepart', $item['bahan'])
                  //   ->where('kode_input', $request->kode_input)
                  //   ->where('kode_cabang', $user_cabang)
                  //   ->where('tahun', $tahun)
                  //   ->where('bulan', $bulan)
                  //   ->first();

                  // if ($saldo) {
                  //   $tmp['unit_retur'] = $saldo->unit_retur + $qty;
                  //   $tmp['jumlah_retur'] = $saldo->jumlah_retur + $jumlah;
                  //   $tmp['harga_retur'] = ($tmp['unit_retur'] > 0) ? ($tmp['jumlah_retur'] / $tmp['unit_retur']) : 0;
                  //   $tmp['unit_akhir'] = ($saldo->unit_awal + $saldo->unit_tambah) - ($saldo->unit_kurang + $tmp['unit_retur']);
                  //   $tmp['jumlah_akhir'] = ($saldo->jumlah_awal + $saldo->jumlah_tambah) - ($saldo->jumlah_kurang + $tmp['jumlah_retur']);
                  //   $tmp['harga_akhir'] = ($tmp['unit_akhir'] > 0) ? ($tmp['jumlah_akhir'] / $tmp['unit_akhir']) : 0;
                  //   $tmp['updated_by'] = Auth::user()->username;
                  //   SaldoSparepart::where('id', $saldo->id)->update($tmp);
                  // }
                } else {
                  DB::select('CALL up_apl_update_saldo_bahan(?, ?, ?, ?, ?)', [
                    $user_cabang,
                    $item['bahan'],
                    $tglawal,
                    $tglakhir,
                    Auth::user()->username
                  ]);
                  // $saldo = SaldoBahan::query()
                  //   ->where('kode_bahan', $item['bahan'])
                  //   ->where('kode_cabang', $user_cabang)
                  //   ->where('tahun', $tahun)
                  //   ->where('bulan', $bulan)
                  //   ->first();

                  // if ($saldo) {
                  //   $tmp['unit_retur'] = $saldo->unit_retur + $qty;
                  //   $tmp['jumlah_retur'] = $saldo->jumlah_retur + $jumlah;
                  //   $tmp['harga_retur'] = ($tmp['unit_retur'] > 0) ? ($tmp['jumlah_retur'] / $tmp['unit_retur']) : 0;
                  //   $tmp['unit_akhir'] = ($saldo->unit_awal + $saldo->unit_tambah) - ($saldo->unit_kurang + $tmp['unit_retur']);
                  //   $tmp['jumlah_akhir'] = ($saldo->jumlah_awal + $saldo->jumlah_tambah) - ($saldo->jumlah_kurang + $tmp['jumlah_retur']);
                  //   $tmp['harga_akhir'] = ($tmp['unit_akhir'] > 0) ? ($tmp['jumlah_akhir'] / $tmp['unit_akhir']) : 0;
                  //   $tmp['updated_by'] = Auth::user()->username;
                  //   SaldoBahan::where('id', $saldo->id)->update($tmp);
                  // }
                }
              }
            }
          }
        }
      }

      // ## Notifikasi ke Staff (UL01) kalau Retur baru saja di-approve
      // if ($request->status_approve == "1") {
      //   Notifikasi::aggregate(
      //     [
      //       'user_id' => null,
      //       'target_level' => 'UL01',
      //       'kode_cabang' => $res->kode_cabang,
      //       'tipe' => 'operasional',
      //       'url' => url('gudang/retur-pembelian'),
      //       'is_read' => false,
      //     ],
      //     'retur_approved_' . $res->kode_cabang . '_' . now()->format('Ymd'),
      //     'Ada {count} Retur Pembelian Disetujui',
      //     "Retur Pembelian {$res->no_retur} telah disetujui oleh " . Auth::user()->username . "."
      //   );
      // }


      ## Log Activity
      if ($request->status_approve == "1") {
        $desc = $result ? 'Berhasil Approve Retur Gudang' : 'Gagal Approve Retur Gudang';
      } else {
        $desc = $result ? 'Berhasil Ubah Retur Gudang' : 'Gagal Ubah Retur Gudang';
      }
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status' => (bool) $result,
        'message' => $desc
      ]);
    } else {

      $rules = [
        'kode_input' => 'required',
        'tanggal' => 'required',
        'no_bon' => 'required',
      ];

      $messages = [
        'kode_input.required' => 'Nomor Input Wajib diisi',
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

      $tanggalRetur = $request->tanggal;
      $carbonDate = blank($tanggalRetur) ? now() : Carbon::createFromFormat('d/m/Y', trim($tanggalRetur), 'Asia/Jakarta');
      $tanggal = $carbonDate->format('Y-m-d');
      $tahun = $carbonDate->year;
      $tahunShort = now()->format('y');

      ## Nomor Seq No
      $lastNum = ReturPembelian::query()->where('kode_cabang', $user_cabang)->max(DB::raw('CAST(seq_no AS UNSIGNED)')) ?? 0;
      $seq_no = $lastNum + 1;

      $dtInput = InputPembelian::where('kode_input', $request->kode_input)->first();

      ## Nomor Retur Pembelian
      $penomoran = Helper::getNomorTransaksi($user_cabang, 'RT');

      $cekspk = ReturPembelian::where('no_retur', $penomoran)->first();
      if (!empty($cekspk)) {
        return response()->json(['status' => false, 'message' => "Nomor Retur Pembelian sudah digunakan"]);
      }

      $data = [
        'kode_cabang' => $user_cabang,
        'no_retur' => $penomoran,
        'tanggal' => $tanggal,
        'seq_no' => $seq_no,
        'kode_pemasok' => $dtInput->kode_pemasok,
        'tipe' => $dtInput->tipe,
        'kode_input' => $dtInput->kode_input,
        'kode_spk' => $dtInput->kode_spk,
        'tipe_bayar' => $dtInput->tipe_bayar,
        'memo' => $request->memo,
        'no_bon' => $request->no_bon,
        'created_by' => Auth::user()->username
      ];

      $result = ReturPembelian::create($data);

      if ($result) {
        ## INSERT DETAIL ORDER
        ReturPembelianDetail::where('kode_cabang', $user_cabang)->where('seq_no', $seq_no)->delete();
        if ($request->detail) {
          $no = 1;
          foreach ($request->detail as $item) {
            if (isset($item['cek'])) {
              ReturPembelianDetail::create([
                'kode_cabang' => $user_cabang,
                'seq_no' => $seq_no,
                'line_no' => $no++,
                'kode_barang' => $item['bahan'],
                'kode_satuan' => $item['satuan'],
                'qty' => str_replace([","], "", $item['qty']),
                'harga' => str_replace([","], "", $item['harga']),
                'jumlah' => str_replace([","], "", $item['jumlah']),
                'no_sparepart' => isset($item['no_sparepart']) ? $item['no_sparepart'] : null,
                'cek' => isset($item['cek']) ? $item['cek'] : '0',
                'created_by' => Auth::user()->username,
              ]);
            }
          }
        }

        ## Update Nomor Retur Pembelian
        $res = Helper::updateNomorTransaksi($user_cabang, 'RT');
      }

      // ## Notifikasi ke Manager & Supervisor kalau ada Retur baru menunggu approval
      // foreach (['UL02', 'UL03'] as $level) {
      //   Notifikasi::aggregate(
      //     [
      //       'user_id' => null,
      //       'target_level' => $level,
      //       'kode_cabang' => $user_cabang,
      //       'tipe' => 'operasional',
      //       'url' => url('gudang/retur-pembelian'),
      //       'is_read' => false,
      //     ],
      //     'retur_new_' . $level . '_' . $user_cabang . '_' . now()->format('Ymd'),
      //     'Ada {count} Retur Pembelian Baru Menunggu Approval',
      //     "Retur Pembelian {$penomoran} menunggu persetujuan Anda."
      //   );
      // }


      ## Log Activity
      $desc = $result ? 'Berhasil Tambah Retur Pembelian' : 'Gagal Tambah Retur Pembelian';
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
    // $data = ReturPembelian::findOrFail($id);
    if (!$id) {
      return response()->json([]);
    }

    $user_cabang = session('kd_cabang');

    $data = DB::table('t_retur_barang_hdr as a')
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
        'a.no_retur',
        'a.tanggal',
        'a.no_bon',
        'a.kode_spk',
        'a.tipe as kode_tipe_barang',
        'a.tipe_bayar as kode_tipe_bayar',
        'a.kode_pemasok',
        'a.memo',
        'a.status_approve',
        'b.nama_pemasok',
        'c.keterangan as tipe_barang',
        'd.keterangan as tipe_bayar',
      ])
      ->first();

    $data->tanggal = date("d/m/Y", strtotime($data->tanggal));

    $dtInput = DB::table('v_input_gudang')
      ->where('kode_cabang', $data->kode_cabang)
      ->where('kode_input', $data->kode_input)
      ->first();

    if ($dtInput) {
      // $data->id_order = $dtInput->id;
      $data->tanggal_order = date("d/m/Y", strtotime($dtInput->tanggal));
      $data->no_polisi = $dtInput->no_polisi;
      $data->merek_tipe = $dtInput->merek_tipe;
      $data->pemilik = $dtInput->pemilik;
      $data->nama_pelanggan = $dtInput->nama_pelanggan;
      $data->no_polis = $dtInput->no_polis;
      $data->kode_claim = $dtInput->kode_claim;
    }

    $message = ($data->status_approve == '1') ? 'Data Retur sudah di appprove' : 'Berhasil Retur Gudang';

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
    $data = ReturPembelian::query()->where('id', $id)->first()->toArray();

    $ok = ReturPembelian::where('id', $id)->delete();
    if ($ok) {
      ReturPembelianDetail::where('kode_cabang', $data['kode_cabang'])->where('seq_no', $data['seq_no'])->delete();
    }

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Retur Gudang' : 'Gagal Hapus Retur Gudang';
    LogActivity::saveLogActivity($desc, $data);
  }

  public function getDataInputGudang(Request $request): JsonResponse
  {
    $kd_cabang = session('kd_cabang');
    $kode_input = $request->kode_input;

    $data = DB::table('v_input_gudang')
      ->where('id', $kode_input)
      ->first();

    if (blank($data)) {
      $result = false;
      return response()->json([
        'status' => (bool) $result,
        'message' => 'Kode Input Gudang tidak ditemukan'
      ]);
    }

    $data->tanggal = blank($data->tanggal) ? '' : date("d/m/Y", strtotime($data->tanggal));

    $result = true;
    return response()->json([
      'status' => (bool) $result,
      'message' => 'Berhasil Data IG',
      'data' => $data
    ]);
  }

  public function cetakReturPembelian(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Retur Pembelian';

    $id = $request->id;

    $datas = DB::table('t_retur_barang_hdr as a')
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
        'a.no_retur',
        'a.kode_spk',
        'a.tanggal',
        'a.tipe as kode_tipe',
        'a.tipe_bayar',
        'a.kode_pemasok',
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
      // $spk = DB::table('v_spk')->select('kode_spk', 'no_polisi', 'merek_tipe', 'kode_estimasi')->where('kode_spk', $datas->kode_spk)->first();

      $details = DB::table('t_retur_barang_dtl as a')
        ->join('t_retur_barang_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.seq_no', '=', 'a.seq_no'); // syarat di JOIN
        })
        ->leftJoin('m_sparepart as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'a.kode_cabang')
            ->on('c.kode_sparepart', '=', 'a.kode_barang'); // syarat di JOIN
        })
        ->leftJoin('parameter as d', function ($join) {
          $join->on('d.kode', '=', 'a.kode_satuan')
            ->where('d.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
        })
        ->where('b.kode_cabang', $datas->kode_cabang)
        ->where('b.no_retur', $datas->no_retur)
        // ->where('a.cek', '1')
        ->select(['a.line_no', 'a.kode_barang', 'c.nama_sparepart as nama_bahan', 'a.no_sparepart', 'a.kode_satuan', 'd.keterangan as nama_satuan', 'a.qty', 'a.harga', 'a.jumlah'])
        ->orderBy('a.line_no', 'asc')
        ->get();
    } else {
      // $spk = null;

      $details = DB::table('t_retur_barang_dtl as a')
        ->join('t_retur_barang_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.seq_no', '=', 'a.seq_no'); // syarat di JOIN
        })
        ->leftJoin('m_bahan as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'a.kode_cabang')
            ->on('c.kode_bahan', '=', 'a.kode_barang'); // syarat di JOIN
        })
        ->leftJoin('parameter as d', function ($join) {
          $join->on('d.kode', '=', 'a.kode_satuan')
            ->where('d.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
        })
        ->where('b.kode_cabang', $datas->kode_cabang)
        ->where('b.kode_input', $datas->kode_input)
        // ->where('a.cek', '1')
        ->select(['a.line_no', 'a.kode_barang', 'c.nama_bahan', 'a.no_sparepart', 'a.kode_satuan', 'd.keterangan as nama_satuan', 'a.qty', 'a.harga', 'a.jumlah'])
        ->orderBy('a.line_no', 'asc')
        ->get();
    }

    ## Log Activity
    $desc = "Cetak " . $title;
    LogActivity::saveLogActivity($desc);

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.gudang.retur-pembelian-cetak', [
      'title' => $title,
      // 'spk' => $spk,
      'datas' => $datas,
      'details' => $details,
      'pageConfigs' => $pageConfigs,
    ]);
  }
}
