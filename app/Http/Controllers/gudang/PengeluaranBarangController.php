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
use App\Models\PengeluaranBarang;
use App\Models\PengeluaranBarangDetail;
use App\Models\Spk;
use App\Models\SaldoBahan;
use App\Models\SaldoSparepart;
use App\Models\LogActivity;
use Carbon\Carbon;
use App\Models\Notifikasi;

class PengeluaranBarangController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function PengeluaranBarang(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Purchase Order';

    $user_cabang = session('kd_cabang');
    $tipe_barang = Parameter::query()->where('nama_tabel', 'TIPE_BARANG')->orderBy('no_urut', 'asc')->get();
    $satuan = Parameter::query()->where('nama_tabel', 'SATUAN')->orderBy('no_urut', 'asc')->get();

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

    return view('content.gudang.pengeluaran-barang', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'ppn_persen' => $ppn_persen,
      'satuan' => $satuan,
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

    if ($request->tipe == "detail-pengeluaran") {
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
      if ($request->tipe_barang == "S" || $request->tipe_barang == "T") {
        $base = DB::table('t_pengeluaran_barang_dtl as a')
          ->join('t_pengeluaran_barang_hdr as b', function ($join) {
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
          ->where('b.kode_pengeluaran', $request->kode_pengeluaran);

        // Total baris tanpa filter
        $totalData = (clone $base)->count('a.id');

        // Filtering (search global)
        $query = (clone $base);
        if ($search = trim((string) $request->input('search.value'))) {
          $query->where(function ($q) use ($search) {
            $q->where('a.kode_barang', 'like', "%{$search}%")
              ->orWhere('c.nama_sparepart', 'like', "%{$search}%");
          });
        }

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('a.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'a.id',
            'a.kode_barang as kode_bahan',
            'c.nama_sparepart as nama_bahan',
            'a.no_sparepart',
            'a.kode_satuan',
            'd.keterangan as nama_satuan',
            'a.qty',
            'a.harga',
            'a.jumlah',
          ])
          ->orderBy('a.no_urut', 'asc')
          ->get();
      } else {
        $base = DB::table('t_pengeluaran_barang_dtl as a')
          ->join('t_pengeluaran_barang_hdr as b', function ($join) {
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
          ->where('b.kode_pengeluaran', $request->kode_pengeluaran);

        // Total baris tanpa filter
        $totalData = (clone $base)->count('a.id');

        // Filtering (search global)
        $query = (clone $base);
        if ($search = trim((string) $request->input('search.value'))) {
          $query->where(function ($q) use ($search) {
            $q->where('a.kode_barang', 'like', "%{$search}%")
              ->orWhere('c.nama_bahan', 'like', "%{$search}%");
          });
        }

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('a.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'a.id',
            'a.kode_barang as kode_bahan',
            'c.nama_bahan',
            'a.no_sparepart',
            'a.kode_satuan',
            'd.keterangan as nama_satuan',
            'a.qty',
            'a.harga',
            'a.jumlah',
          ])
          ->orderBy('a.no_urut', 'asc')
          ->get();
      }

      // Susun payload DataTables
      $data = [];
      $fake = 0; //$start;
      foreach ($datas as $row) {
        $data[] = [
          'id' => $row->id,
          'fake_id' => ++$fake,
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
    } elseif ($request->tipe == "detail-input-gudang") {
      // $kode = blank($request->kode_input) ? 'xx' : $request->kode_permintaan;

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
        ->where('a.cek', '1')
        ->where('b.kode_cabang', $user_cabang)
        ->where('b.kode_input', $request->kode_input);

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
          'b.tipe',
        ])
        ->orderBy('seq_no', 'asc')
        ->get();

      // Susun payload DataTables
      $data = [];
      $fake = 0; //$start;
      foreach ($datas as $row) {
        $data[] = [
          'id' => $row->id,
          'fake_id' => ++$fake,
          'cek' => $row->cek,
          'tipe' => $row->tipe,
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
    } elseif ($request->tipe == "cari-input-gudang") {
      $columns = [
        1 => 'a.id',
        2 => 'a.tanggal',
        3 => 'a.kode_input',
        4 => 'c.keterangan', //'a.tipe_barang',
        5 => 'b.nama_pemasok',
        6 => 'a.kode_spk',
        7 => 'd.no_polisi',
        8 => 'd.merek_tipe',
        9 => 'd.pemilik',
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
        ->leftJoin('v_spk as d', function ($join) {
          $join->on('d.kode_cabang', '=', 'a.kode_cabang')
            ->on('d.kode_spk', '=', 'a.kode_spk'); // syarat di JOIN
        })
        ->where('a.tipe', 'S')
        ->where('a.kode_cabang', $user_cabang)
        ->whereYear('a.tanggal', date('Y'))
        ->whereNotIn('a.kode_input', function ($subquery) use ($user_cabang) {
          $subquery->select('kode_input')
            ->from('t_pengeluaran_barang_hdr')
            ->where('kode_cabang', $user_cabang)
            ->whereRaw('LENGTH(kode_input) > 0')
            ->groupBy('kode_input');
        });

      // Total baris tanpa filter
      $totalData = (clone $base)->count('a.id');

      // Filtering (search global)
      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('a.kode_input', 'like', "%{$search}%")
            ->orWhere('a.kode_spk', 'like', "%{$search}%")
            ->orWhere('b.nama_pemasok', 'like', "%{$search}%")
            ->orWhere('c.keterangan', 'like', "%{$search}%")
            ->orWhere('d.no_polisi', 'like', "%{$search}%")
            ->orWhere('d.pemilik', 'like', "%{$search}%")
            ->orWhere('d.merek_tipe', 'like', "%{$search}%");
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
          'a.kode_input',
          'c.keterangan as tipe_barang',
          'a.kode_spk',
          'd.no_polisi',
          'd.merek_tipe',
          'd.pemilik',
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
          'kode_input' => $row->kode_input,
          'tipe_barang' => $row->tipe_barang,
          'kode_spk' => $row->kode_spk,
          'no_polisi' => $row->no_polisi,
          'merek_tipe' => $row->merek_tipe,
          'pemilik' => $row->pemilik,
          'nama_pemasok' => $row->nama_pemasok,
        ];
      }
    } elseif ($request->tipe == "cari-spk") {
      $columns = [
        1 => 'a.id',
        2 => 'a.tanggal',
        3 => 'a.kode_input',
        4 => 'c.keterangan', //'a.tipe_barang',
        5 => 'b.nama_pemasok',
        6 => 'a.kode_spk',
        7 => 'd.no_polisi',
        8 => 'd.merek_tipe',
        9 => 'd.pemilik',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.id';
      $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('v_spk as a')
        ->whereNotIn('a.kode_status', ['01', '02', '09', '10', '11'])
        ->where('a.kode_cabang', $user_cabang)
        ->whereYear('a.tgl_masuk', date('Y'));
      // ->whereNotIn('a.kode_spk', function ($subquery) use ($user_cabang) {
      //   $subquery->select('kode_spk')
      //            ->from('t_pengeluaran_barang_hdr')
      //            ->where('kode_cabang', $user_cabang)
      //            ->whereRaw('LENGTH(kode_spk) > 0')
      //            ->groupBy('kode_spk');
      // });

      // Total baris tanpa filter
      $totalData = (clone $base)->count('a.id');

      // Filtering (search global)
      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('a.kode_spk', 'like', "%{$search}%")
            ->orWhere('a.no_polisi', 'like', "%{$search}%")
            ->orWhere('a.pemilik', 'like', "%{$search}%")
            ->orWhere('a.merek_tipe', 'like', "%{$search}%");
        });
      }

      // Hitung setelah filter (tanpa limit/offset)
      $totalFiltered = (clone $query)->count('a.id');

      // Ambil data halaman saat ini
      $datas = $query
        ->select([
          'a.id',
          'a.tgl_masuk',
          'a.kode_cabang',
          'a.kode_spk',
          'a.no_polisi',
          'a.merek_tipe',
          'a.pemilik',
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
          'tanggal' => blank($row->tgl_masuk) ? '' : date("d/m/Y", strtotime($row->tgl_masuk)),
          'kode_cabang' => $row->kode_cabang,
          'kode_input' => '',
          'tipe_barang' => '',
          'kode_spk' => $row->kode_spk,
          'no_polisi' => $row->no_polisi,
          'merek_tipe' => $row->merek_tipe,
          'pemilik' => $row->pemilik,
          'nama_pemasok' => '',
        ];
      }
    } elseif ($request->tipe == "total-data") {
      $bulan = date("m");
      $tahun = date("Y");

      ## Jumlah Menunggu Approval
      $totalPengeluaranPending = PengeluaranBarang::where('kode_cabang', $user_cabang)
        ->where('status_approve', '0')
        ->count();

      ## Jumlah Pengeluaran per Bulan
      $totalPengeluaranBulan = PengeluaranBarang::where('kode_cabang', $user_cabang)
        ->whereMonth('tgl_pengeluaran', $bulan)
        ->whereYear('tgl_pengeluaran', $tahun)
        ->count();

      ## Jumlah Pengeluaran per Tahun
      $totalPengeluaranTahun = PengeluaranBarang::where('kode_cabang', $user_cabang)
        ->whereYear('tgl_pengeluaran', $tahun)
        ->count();

      $data['pb_pending'] = number_format($totalPengeluaranPending, 0, ".", ",");
      $data['pb_bulan'] = number_format($totalPengeluaranBulan, 0, ".", ",");
      $data['pb_tahun'] = number_format($totalPengeluaranTahun, 0, ".", ",");

      return response()->json($data);
    } elseif ($request->tipe == "pengeluaran-barang") {
      $columns = [
        1 => 'a.id',
        2 => 'a.tgl_pengeluaran',
        3 => 'a.kode_pengeluaran',
        4 => 'a.status_approve',
        5 => 'a.kode_input',
        6 => 'a.kode_spk',
        7 => 'c.keterangan', //a.tipe_barang
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.id';
      $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      // Base query + LEFT JOIN
      $base = DB::table('t_pengeluaran_barang_hdr as a')
        ->leftJoin('t_input_gudang_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_input', '=', 'a.kode_input'); // syarat di JOIN
        })
        ->leftJoin('t_spk_master as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'a.kode_cabang')
            ->on('c.kode_spk', '=', 'a.kode_spk'); // syarat di JOIN
        })
        ->leftJoin('m_pemasok as d', function ($join) {
          $join->on('d.kode_cabang', '=', 'b.kode_cabang')
            ->on('d.kode_pemasok', '=', 'b.kode_pemasok'); // syarat di JOIN
        })
        ->leftJoin('parameter as e', function ($join) {
          $join->on('e.kode', '=', 'a.tipe')
            ->where('e.nama_tabel', '=', 'TIPE_BARANG'); // syarat di JOIN
        })
        ->where('a.kode_cabang', $user_cabang);

      // Total baris tanpa filter
      $totalData = (clone $base)->count('a.id');

      // Filtering (search global)
      $query = (clone $base);
      // if ($search = trim((string) $request->input('search.value'))) {
      //     $query->where(function ($q) use ($search) {
      //         $q->where('a.kode_order', 'like', "%{$search}%")
      //           ->orWhere('a.no_po', 'like', "%{$search}%")
      //           ->orWhere('b.kode_pemasok', 'like', "%{$search}%");
      //     });
      // }

      // Filter berdasarkan input yang dikirim dari DataTables
      if ($request->filled('kode_pengeluaran')) {
        $query->where('a.kode_pengeluaran', 'like', '%' . $request->kode_pengeluaran . '%');
      }
      if ($request->filled('kode_input')) {
        $query->where('a.kode_input', 'like', '%' . $request->kode_input . '%');
      }
      if ($request->filled('kode_spk')) {
        $query->where('a.kode_spk', 'like', '%' . $request->kode_spk . '%');
      }
      if ($request->filled('tanggal_awal')) {
        $startDate = Carbon::createFromFormat('d/m/Y', $request->tanggal_awal, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('a.tgl_pengeluaran', '>=', $startDate);
      }
      if ($request->filled('tanggal_akhir')) {
        $endDate = Carbon::createFromFormat('d/m/Y', $request->tanggal_akhir, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('a.tgl_pengeluaran', '<=', $endDate);
      }
      if ($request->filled('nama_pemasok')) {
        $query->where('d.nama_pemasok', 'like', '%' . $request->nama_pemasok . '%');
      }
      if ($request->filled('nama_pemilik')) {
        $query->where('c.pemilik', 'like', '%' . $request->nama_pemilik . '%');
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
          'a.kode_pengeluaran',
          'a.kode_spk',
          'a.tgl_pengeluaran',
          'a.kode_input',
          'a.tipe as kode_tipe',
          'e.keterangan as tipe_barang',
          'a.status_approve',
          'd.nama_pemasok',
          'c.pemilik',
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
          'kode_pengeluaran' => $row->kode_pengeluaran,
          'kode_input' => $row->kode_input,
          'kode_spk' => $row->kode_spk,
          'status_approve' => $row->status_approve,
          'keterangan' => ($row->status_approve == '1') ? 'APPROVED' : 'MENUNGGU APPROVAL',
          'tgl_pengeluaran' => blank($row->tgl_pengeluaran) ? '' : date("d/m/Y", strtotime($row->tgl_pengeluaran)),
          'kode_tipe' => $row->kode_tipe,
          'tipe_barang' => $row->tipe_barang,
          'nama_pemasok' => $row->nama_pemasok,
          'pemilik' => $row->pemilik,
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
    $bulan = (int) date('m');
    $tahun = (int) date('Y');
    $tglawal = date("Y-m-01");
    $tglakhir = date("Y-m-d");
    $user_cabang = session('kd_cabang');
    $dataID = $request->id;

    $cek = 0;
    if ($request->detail) {
      foreach ($request->detail as $key => $item) {
        $cek++;

        if ($request->tipe_barang == "S") {

          $sparepart = Sparepart::select('nama_sparepart')->where('kode_cabang', $user_cabang)->where('kode_sparepart', $item['bahan'])->first();
          $nama_barang = ($sparepart) ? $sparepart->nama_sparepart : '';

          $cekSaldo = SaldoSparepart::select('id', 'unit_akhir')
            ->where('kode_cabang', $user_cabang)
            ->where('kode_input', $request->kode_input)
            ->where('kode_sparepart', $item['bahan'])
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

          if ($cekSaldo) {
            $qty = round($item['qty'], 2);
            $unit_akhir = ($cekSaldo) ? round($cekSaldo->unit_akhir, 2) : 0;

            if ($qty > $unit_akhir) {
              return response()->json([
                'status' => false,
                'message' => "Qty melebihi jumlah stok, <br> Nama Barang : " . $nama_barang
              ], 200);
            }
          } else {
            return response()->json([
              'status' => false,
              'message' => "Saldo Sparepart tidak tersedia, <br> Nama Barang : " . $nama_barang
            ], 200);
          }
        } else {
          $kode_group_bahan = ($request->tipe_barang == "P") ? "00001" : "00002";

          $bahan = Bahan::select('nama_bahan')->where('kode_cabang', $user_cabang)->where('kode_bahan', $item['bahan'])->first();
          $nama_barang = ($bahan) ? $bahan->nama_bahan : '';

          $cekSaldo = SaldoBahan::select('id', 'unit_akhir')
            ->where('kode_cabang', $user_cabang)
            ->where('kode_group_bahan', $kode_group_bahan)
            ->where('kode_bahan', $item['bahan'])
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

          if ($cekSaldo) {
            $qty = round($item['qty'], 2);
            $unit_akhir = ($cekSaldo) ? round($cekSaldo->unit_akhir, 2) : 0;

            if ($qty > $unit_akhir) {
              return response()->json([
                'status' => false,
                'message' => "Qty melebihi jumlah stok, <br> Nama Barang : " . $nama_barang
              ], 200);
            }
          } else {
            return response()->json([
              'status' => false,
              'message' => "Saldo Bahan tidak tersedia, <br> Nama Barang : " . $nama_barang
            ], 200);
          }
        }
      }
    }

    if ($cek == 0) {
      return response()->json([
        'status' => false,
        'message' => "Data detail barang tidak boleh kosong."
      ], 200);
    }

    if ($dataID) {
      $res = PengeluaranBarang::findOrFail($dataID);

      $rules = [
        'tanggal' => 'required',
        // 'no_bon' => 'required',
        // 'kode_input' => 'required',
        'tipe_barang' => 'required',
      ];

      $messages = [
        'tanggal.required' => 'Tanggal Wajib diisi',
        // 'no_bon.required' => 'Nomor Bon Wajib diisi',
        // 'kode_input.required'  => 'Nomor Wajib diisi',
        'tipe_barang.required' => 'Tipe barang Wajib diisi',
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

      $data = [
        'tgl_pengeluaran' => $tanggal,
        'tipe' => $request->tipe_barang,
        // 'no_bon' => $request->no_bon,
        'memo' => $request->memo,
        'updated_by' => Auth::user()->username
      ];

      if ($request->tipe_barang == "S") {
        $data['kode_input'] = $request->kode_input;
        $data['kode_spk'] = $request->kode_spk;
      } else {
        $data['kode_spk'] = $request->kode_input;
      }

      if ($request->status_approve == "1") {
        $data['status_approve'] = $request->status_approve;
        $data['tgl_approve'] = date("Y-m-d H:i:s");
        $data['user_approve'] = Auth::user()->username;
      }

      $result = $res->update($data);

      if ($result) {
        ## INSERT DETAIL ORDER
        // PengeluaranBarangDetail::where('kode_cabang', $user_cabang)->where('seq_no', $res->seq_no)->delete();
        if ($request->detail) {
          $no = PengeluaranBarangDetail::where('kode_cabang', $user_cabang)->where('seq_no', $res->seq_no)->count() + 1;
          $processedIds = [];
          foreach ($request->detail as $key => $item) {

            $isExist = PengeluaranBarangDetail::where('id', $key)->exists();

            if ($isExist) {
              if ($item['cek'] == "1") {
                $tmpData = [
                  'kode_barang'   => $item['bahan'],
                  'kode_satuan'   => $item['satuan'],
                  'qty'           => str_replace([","], "", $item['qty']),
                  'harga'         => str_replace([","], "", $item['harga']),
                  'jumlah'        => str_replace([","], "", $item['jumlah']),
                  'no_sparepart'  => isset($item['no_sparepart']) ? $item['no_sparepart'] : null,
                  'updated_by'    => Auth::user()->username,
                ];

                PengeluaranBarangDetail::where('id', $key)->update($tmpData);
              }

              $processedIds[] = $key;
            } else {
              $tmpData = [
                'kode_cabang'   => $user_cabang,
                'seq_no'        => $res->seq_no,
                'no_urut'       => $no++,
                'kode_barang'   => $item['bahan'],
                'kode_satuan'   => $item['satuan'],
                'qty'           => str_replace([","], "", $item['qty']),
                'harga'         => str_replace([","], "", $item['harga']),
                'jumlah'        => str_replace([","], "", $item['jumlah']),
                'no_sparepart'  => isset($item['no_sparepart']) ? $item['no_sparepart'] : null,
                'created_by'    => Auth::user()->username,
              ];

              $newRecord = PengeluaranBarangDetail::create($tmpData);

              $processedIds[] = $newRecord->id;
            }

            ## UPDATE SALDO STOCK
            if ($request->status_approve == "1") {
              // $qty = str_replace([","], "", $item['qty']);
              // $harga = str_replace([","], "", $item['harga']);
              // $jumlah = str_replace([","], "", $item['jumlah']);

              if ($request->tipe_barang == 'S' || $request->tipe_barang == 'T') {

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
                //   $tmp['unit_kurang'] = $saldo->unit_kurang + $qty;
                //   $tmp['jumlah_kurang'] = $saldo->jumlah_kurang + $jumlah;
                //   $tmp['harga_kurang'] = ($tmp['unit_kurang'] > 0) ? ($tmp['jumlah_kurang'] / $tmp['unit_kurang']) : 0;
                //   $tmp['unit_akhir'] = ($saldo->unit_awal + $saldo->unit_tambah) - ($tmp['unit_kurang'] + $saldo->unit_retur);
                //   $tmp['jumlah_akhir'] = ($saldo->jumlah_awal + $saldo->jumlah_tambah) - ($tmp['jumlah_kurang'] + $saldo->jumlah_retur);
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
                //   $tmp['unit_kurang'] = $saldo->unit_kurang + $qty;
                //   $tmp['jumlah_kurang'] = $saldo->jumlah_kurang + $jumlah;
                //   $tmp['harga_kurang'] = ($tmp['unit_kurang'] > 0) ? ($tmp['jumlah_kurang'] / $tmp['unit_kurang']) : 0;
                //   $tmp['unit_akhir'] = ($saldo->unit_awal + $saldo->unit_tambah) - ($tmp['unit_kurang'] + $saldo->unit_retur);
                //   $tmp['jumlah_akhir'] = ($saldo->jumlah_awal + $saldo->jumlah_tambah) - ($tmp['jumlah_kurang'] + $saldo->jumlah_retur);
                //   $tmp['harga_akhir'] = ($tmp['unit_akhir'] > 0) ? ($tmp['jumlah_akhir'] / $tmp['unit_akhir']) : 0;
                //   $tmp['updated_by'] = Auth::user()->username;
                //   SaldoBahan::where('id', $saldo->id)->update($tmp);
                // }
              }
            }
          }
        } else {
          PengeluaranBarangDetail::where('kode_cabang', $user_cabang)->where('seq_no', $res->seq_no)->delete();
        }
      }

      // ## Notifikasi ke Staff (UL01) kalau Pengeluaran baru saja di-approve
      // if ($request->status_approve == "1") {
      //   Notifikasi::aggregate(
      //     [
      //       'user_id' => null,
      //       'target_level' => 'UL01',
      //       'kode_cabang' => $res->kode_cabang,
      //       'tipe' => 'operasional',
      //       'url' => url('gudang/pengeluaran-barang'),
      //       'is_read' => false,
      //     ],
      //     'pengeluaran_approved_' . $res->kode_cabang . '_' . now()->format('Ymd'),
      //     'Ada {count} Pengeluaran Barang Disetujui',
      //     "Pengeluaran Barang {$res->kode_pengeluaran} telah disetujui oleh " . Auth::user()->username . "."
      //   );
      // }

      ## Log Activity
      if ($request->status_approve == "1") {
        $desc = $result ? 'Berhasil Approve Pengeluaran Barang' : 'Gagal Approve Pengeluaran Barang';
      } else {
        $desc = $result ? 'Berhasil Ubah Pengeluaran Barang' : 'Gagal Ubah Pengeluaran Barang';
      }
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status' => (bool) $result,
        'message' => $desc
      ]);
    } else {

      $rules = [
        'tanggal' => 'required',
        // 'no_bon' => 'required',
        'kode_input' => 'required',
        'tipe_barang' => 'required',
      ];

      $messages = [
        'tanggal.required' => 'Tanggal Wajib diisi',
        // 'no_bon.required' => 'Nomor Bon Wajib diisi',
        'kode_input.required' => 'Nomor Wajib diisi',
        'tipe_barang.required' => 'Tipe barang Wajib diisi',
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
      // $tahun = $carbonDate->year;
      // $tahunShort = now()->format('y');

      ## Nomor Pengeluaran
      $lastNum = PengeluaranBarang::query()->where('kode_cabang', $user_cabang)->max(DB::raw('CAST(seq_no AS UNSIGNED)')) ?? 0;
      $seq_no = $lastNum + 1;

      ## Nomor Pengeluaran Barang
      $penomoran = \Helper::getNomorTransaksi($user_cabang, 'KB');

      $cekspk = PengeluaranBarang::where('kode_pengeluaran', $penomoran)->first();
      if (!empty($cekspk)) {
        return response()->json(['status' => false, 'message' => "Nomor Pengeluaran Barang sudah digunakan"]);
      }

      $data = [
        'kode_cabang' => $user_cabang,
        'kode_pengeluaran' => $penomoran,
        'seq_no' => $seq_no,
        'tgl_pengeluaran' => $tanggal,
        'tipe' => $request->tipe_barang,
        // 'no_bon' => $request->no_bon,
        'memo' => $request->memo,
        'created_by' => Auth::user()->username
      ];

      if ($request->tipe_barang == "S") {
        $data['kode_input'] = $request->kode_input;
        $data['kode_spk'] = $request->kode_spk;
      } else {
        $data['kode_spk'] = $request->kode_input;
      }

      $result = PengeluaranBarang::create($data);

      if ($result) {
        ## INSERT DETAIL ORDER
        PengeluaranBarangDetail::where('kode_cabang', $user_cabang)->where('seq_no', $seq_no)->delete();
        if ($request->detail) {
          $no = 1;
          foreach ($request->detail as $item) {
            PengeluaranBarangDetail::create([
              'kode_cabang' => $user_cabang,
              'seq_no' => $seq_no,
              'no_urut' => $no++,
              'kode_barang' => $item['bahan'],
              'kode_satuan' => $item['satuan'],
              'qty' => str_replace([","], "", $item['qty']),
              'harga' => str_replace([","], "", $item['harga']),
              'jumlah' => str_replace([","], "", $item['jumlah']),
              'no_sparepart' => isset($item['no_sparepart']) ? $item['no_sparepart'] : null,
              'created_by' => Auth::user()->username,
            ]);
          }
        }

        ## Update Nomor Pengeluaran Barang
        $res = \Helper::updateNomorTransaksi($user_cabang, 'KB');
      }

      // ## Notifikasi ke Manager & Supervisor kalau ada Pengeluaran baru menunggu approval
      // foreach (['UL02', 'UL03'] as $level) {
      //   Notifikasi::aggregate(
      //     [
      //       'user_id' => null,
      //       'target_level' => $level,
      //       'kode_cabang' => $user_cabang,
      //       'tipe' => 'operasional',
      //       'url' => url('gudang/pengeluaran-barang'),
      //       'is_read' => false,
      //     ],
      //     'pengeluaran_new_' . $level . '_' . $user_cabang . '_' . now()->format('Ymd'),
      //     'Ada {count} Pengeluaran Barang Baru Menunggu Approval',
      //     "Pengeluaran Barang {$penomoran} menunggu persetujuan Anda."
      //   );
      // }

      ## Log Activity
      $desc = $result ? 'Berhasil Tambah Pengeluaran Barang' : 'Gagal Tambah Pengeluaran Barang';
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
  public function show($id): JsonResponse
  {
    if (!$id) {
      return response()->json([]);
    }

    $user_cabang = session('kd_cabang');

    $data = DB::table('t_order_hdr as a')
      ->leftJoin('m_pemasok as b', function ($join) {
        $join->on('b.kode_cabang', '=', 'a.kode_cabang')
          ->on('b.kode_pemasok', '=', 'a.kode_pemasok'); // syarat di JOIN
      })
      ->leftJoin('parameter as c', function ($join) {
        $join->on('c.kode', '=', 'a.tipe_barang')
          ->where('c.nama_tabel', '=', 'TIPE_BARANG'); // syarat di JOIN
      })
      ->leftJoin('parameter as d', function ($join) {
        $join->on('d.kode', '=', 'a.tipe_bayar')
          ->where('d.nama_tabel', '=', 'TIPE_BAYAR'); // syarat di JOIN
      })
      ->where('a.kode_cabang', $user_cabang)
      ->where('a.id', $id)
      ->select([
        'a.id',
        'a.kode_cabang',
        'a.kode_order',
        'a.tanggal',
        'a.no_po',
        'a.kode_spk',
        'a.sifat_ppn',
        'a.tipe_barang',
        'a.tipe_bayar',
        'a.kode_pemasok',
        'a.ppn',
        'a.total',
        'b.nama_pemasok',
        'c.keterangan as nama_tipe_barang',
        'd.keterangan as nama_tipe_bayar',
      ])
      ->first();

    if ($data) {
      $data->tanggal = date("d/m/Y", strtotime($data->tanggal));
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
    $data = PengeluaranBarang::findOrFail($id);

    $data->tanggal = blank($data->tanggal) ? date("d/m/Y") : date("d/m/Y", strtotime($data->tanggal));
    // $data->ppn = number_format($data->ppn, 2, '.', ',');
    // $data->total = number_format($data->total, 2, '.', ',');

    $data2 = DB::table('v_spk')
      ->select('id', 'kode_cabang', 'kode_spk', 'no_polisi', 'merek_tipe', 'pemilik', 'nama_pelanggan', 'kode_estimasi', 'no_polis', 'kode_claim')
      ->where('kode_spk', $data->kode_spk)
      ->where('kode_cabang', $data->kode_cabang)
      ->first();

    $data->no_polisi = $data2->no_polisi;
    $data->merek_tipe = $data2->merek_tipe;
    $data->pemilik = $data2->pemilik;
    $data->nama_pelanggan = $data2->nama_pelanggan;
    $data->no_polis = $data2->no_polis;
    $data->kode_claim = $data2->kode_claim;

    $message = ($data->status_approve == '1') ? 'Data Pengeluaran sudah di appprove' : 'Berhasil Pengeluaran Barang';

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
    $data = PengeluaranBarang::query()->where('id', $id)->first()->toArray();

    $ok = PengeluaranBarang::where('id', $id)->delete();
    if ($ok) {
      PengeluaranBarangDetail::where('kode_cabang', $data['kode_cabang'])->where('seq_no', $data['seq_no'])->delete();
    }

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Pengeluaran Barang' : 'Gagal Hapus Pengeluaran Barang';
    LogActivity::saveLogActivity($desc, $data);
  }

  public function getDataSPK(Request $request): JsonResponse
  {
    $kd_cabang = session('kd_cabang');
    $tipe = $request->tipe;
    $kode = $request->kode;

    if ($tipe == "S") {
      $res = InputPembelian::find($kode);

      if (blank($res)) {
        $result = false;
        return response()->json([
          'status' => (bool) $result,
          'message' => 'Kode Input Gudang tidak ditemukan'
        ]);
      }

      $data = DB::table('v_spk')
        ->select('id', 'kode_cabang', 'kode_spk', 'no_polisi', 'merek_tipe', 'pemilik', 'nama_pelanggan', 'kode_estimasi', 'no_polis', 'kode_claim')
        ->where('kode_spk', $res->kode_spk)
        ->where('kode_cabang', $res->kode_cabang)
        ->first();

      if (blank($data)) {
        $result = false;
        return response()->json([
          'status' => (bool) $result,
          'message' => 'Kode SPK tidak ditemukan'
        ]);
      }

      $data->kode_input = $res->kode_input;
      $data->tipe_barang = $tipe;
    } else {
      $data = DB::table('v_spk')
        ->select('id', 'kode_cabang', 'kode_spk', 'no_polisi', 'merek_tipe', 'pemilik', 'nama_pelanggan', 'kode_estimasi', 'no_polis', 'kode_claim')
        ->where('id', $kode)
        ->first();

      if (blank($data)) {
        $result = false;
        return response()->json([
          'status' => (bool) $result,
          'message' => 'Kode SPK tidak ditemukan'
        ]);
      }

      $data->kode_input = $data->kode_spk;
      $data->tipe_barang = $tipe;
    }

    $result = true;
    return response()->json([
      'status' => (bool) $result,
      'message' => 'Berhasil Data SPK',
      'data' => $data
    ]);
  }

  public function getDataPermintaan(Request $request): JsonResponse
  {
    $jenisId = $request->query('jenis_id');
    $kode = $request->query('kode');
    $tipe = $request->query('tipe');
    $user_cabang = session('kd_cabang');

    if ($jenisId != "S") {
      return response()->json([]);
    }

    if ($tipe == "detail") {
      $data = DB::table('t_permintaan_barang_hdr')
        ->select('kode_permintaan')
        ->where('kode_cabang', $user_cabang)
        ->where('tipe_barang', 'S')
        ->whereYear('tanggal_permintaan', date('Y'))
        // Subquery NOT IN dimulai di sini
        // ->whereNotIn('kode_permintaan', function($query) use ($user_cabang, $kode) {
        //     $query->select('kode_permintaan')
        //           ->from('t_order_hdr')
        //           ->where('kode_cabang', $user_cabang)
        //           ->where('kode_permintaan', '!=', $kode)
        //           ->distinct(); // Sesuai query asli 'select distinct'
        // })
        ->where(function ($query) use ($kode, $user_cabang) {
          // Kondisi A: Kode permintaan spesifik
          $query->where('kode_permintaan', $kode)
            // Kondisi B: Subquery (IN)
            ->orWhereIn('kode_permintaan', function ($subquery) use ($user_cabang) {
              $subquery->select('kode_permintaan')
                ->from('v_trx_permintaan_pending')
                ->where('kode_cabang', $user_cabang);
            });
        })
        ->groupBy('kode_permintaan')
        ->orderBy('tanggal_permintaan', 'desc')
        ->get();
    } elseif ($tipe == "header") {
      $data = DB::table('t_permintaan_barang_hdr')
        ->select('kode_spk')
        ->where('kode_cabang', $user_cabang)
        ->where('kode_permintaan', $kode)
        ->first();
    } else {
      $data = [];
    }

    return response()->json($data);
  }

  public function cetakPengeluaranBarang(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Pengeluaran Barang';

    $id = $request->id;

    $datas = DB::table('t_pengeluaran_barang_hdr as a')
      ->leftJoin('t_input_gudang_hdr as b', function ($join) {
        $join->on('b.kode_cabang', '=', 'a.kode_cabang')
          ->on('b.kode_input', '=', 'a.kode_input'); // syarat di JOIN
      })
      ->leftJoin('t_spk_master as c', function ($join) {
        $join->on('c.kode_cabang', '=', 'a.kode_cabang')
          ->on('c.kode_spk', '=', 'a.kode_spk'); // syarat di JOIN
      })
      ->leftJoin('m_pemasok as d', function ($join) {
        $join->on('d.kode_cabang', '=', 'b.kode_cabang')
          ->on('d.kode_pemasok', '=', 'b.kode_pemasok'); // syarat di JOIN
      })
      ->leftJoin('parameter as e', function ($join) {
        $join->on('e.kode', '=', 'a.tipe')
          ->where('e.nama_tabel', '=', 'TIPE_BARANG'); // syarat di JOIN
      })
      ->leftJoin('m_cabang as f', 'f.kode_cabang', '=', 'a.kode_cabang')
      ->where('a.id', $id)
      ->select([
        'a.id',
        'a.kode_cabang',
        'a.kode_pengeluaran',
        'a.kode_spk',
        'a.tgl_pengeluaran',
        'a.kode_input',
        'a.tipe as kode_tipe',
        'e.keterangan as tipe_barang',
        'a.status_approve',
        'a.no_bon',
        'd.nama_pemasok',
        'c.pemilik',
        'f.nama_cabang',
        'f.alamat1',
        'f.alamat2',
        'f.alamat3',
        'f.telepon',
        'f.fax',
      ])
      ->first();

    if (blank($datas)) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }

    $datas->tanggal = date("d-M-Y", strtotime($datas->tgl_pengeluaran));
    $datas->alamat1 = sprintf("%s %s %s", $datas->alamat1, $datas->alamat2, $datas->alamat3);

    $spk = DB::table('v_spk')->select('kode_spk', 'no_polisi', 'merek_tipe')->where('kode_spk', $datas->kode_spk)->first();

    ## DATA DETAIL
    if ($datas->kode_tipe == "S") {

      $details = DB::table('t_pengeluaran_barang_dtl as a')
        ->join('t_pengeluaran_barang_hdr as b', function ($join) {
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
        ->where('b.kode_pengeluaran', $datas->kode_pengeluaran)
        ->select([
          'a.no_urut',
          'a.kode_barang',
          'c.nama_sparepart as nama_barang',
          'a.no_sparepart',
          'a.kode_satuan',
          'd.keterangan as nama_satuan',
          'a.qty',
          'a.harga',
          'a.jumlah'
        ])
        ->orderBy('a.no_urut', 'asc')
        ->get();
    } else {

      ## DATA DETAIL
      $details = DB::table('t_pengeluaran_barang_dtl as a')
        ->join('t_pengeluaran_barang_hdr as b', function ($join) {
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
        ->where('b.kode_pengeluaran', $datas->kode_pengeluaran)
        ->select([
          'a.no_urut',
          'a.kode_barang',
          'c.nama_bahan as nama_barang',
          'a.no_sparepart',
          'a.kode_satuan',
          'd.keterangan as nama_satuan',
          'a.qty',
          'a.harga',
          'a.jumlah'
        ])
        ->orderBy('a.no_urut', 'asc')
        ->get();
    }

    ## Log Activity
    $desc = "Cetak " . $title;
    LogActivity::saveLogActivity($desc);

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.gudang.pengeluaran-barang-cetak', [
      'title' => $title,
      'spk' => $spk,
      'datas' => $datas,
      'details' => $details,
      'pageConfigs' => $pageConfigs,
    ]);
  }
}
