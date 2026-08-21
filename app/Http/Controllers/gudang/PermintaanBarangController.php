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
use App\Models\Spk;
use App\Models\PermintaanBarang;
use App\Models\PermintaanBarangDetail;
use App\Models\PosisiPekerjaan;
use App\Models\LogActivity;
use Carbon\Carbon;
use App\Models\Notifikasi;

use App\Helpers\Helpers as Helper;

class PermintaanBarangController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function PermintaanBarang(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Permintaan Barang';

    $user_cabang = session('kd_cabang');
    $tipe_barang = Parameter::query()->select('kode', 'keterangan')->where('nama_tabel', 'TIPE_BARANG')->where('kode', '=', 'S')->orderBy('no_urut', 'asc')->get();
    $bagian = PosisiPekerjaan::query()->select('kode_posisi', 'posisi_pekerjaan')->where('is_active', 'Y')->orderBy('seq_no', 'asc')->get();
    $satuan = Parameter::query()->where('nama_tabel', 'SATUAN')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.gudang.permintaan-barang', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'tipe_barang' => $tipe_barang,
      'bagian' => $bagian,
      'satuan' => $satuan,
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
      if ($request->tipe == "detail") {
        $columns = [
          1 => 'a.id',
          2 => 'c.nama_bahan',
          3 => 'd.keterangan',
          4 => 'a.qty',
          5 => 'a.harga',
          6 => 'a.jumlah',
        ];

        $limit = (int) $request->input('length', 10);
        $start = (int) $request->input('start', 0);
        $order = $columns[$request->input('order.0.column')] ?? 'a.seq_no';
        $dir = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';

        // Base query + LEFT JOIN
        $base = DB::table('t_permintaan_barang_dtl as a')
          ->join('t_permintaan_barang_hdr as b', function ($join) {
            $join->on('b.kode_cabang', '=', 'a.kode_cabang')
              ->on('b.seq_no', '=', 'a.seq_no'); // syarat di JOIN
          })
          ->leftJoin('m_bahan as c', function ($join) {
            $join->on('c.kode_cabang', '=', 'a.kode_cabang')
              ->on('c.kode_bahan', '=', 'a.kode_barang'); // syarat di JOIN
          })
          ->leftJoin('parameter as d', function ($join) {
            $join->on('d.kode', '=', 'c.kode_satuan')
              ->where('d.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
          })
          ->where('b.kode_cabang', $request->kode_cabang)
          ->where('b.kode_permintaan', $request->kode_permintaan);

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
            'b.tipe_barang',
            'a.kode_barang as kode_bahan',
            'a.qty',
            'c.kode_satuan',
            'a.harga',
            'a.tipe', // <-- tambah
            'c.nama_bahan',
            'd.keterangan as nama_satuan',
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
            'id' => $row->id,
            'fake_id' => ++$fake,
            'tipe_barang' => $row->tipe_barang,
            'kode_bahan' => $row->kode_bahan,
            'nama_bahan' => $row->nama_bahan,
            'kode_satuan' => $row->kode_satuan,
            'nama_satuan' => $row->nama_satuan,
            'no_sparepart' => '',
            'qty' => number_format($row->qty, 0, '.', ','),
            'harga' => number_format($row->harga, 0, '.', ','),
            'tipe' => $row->tipe, // <-- tambah
          ];
        }
      } elseif ($request->tipe == "detail-sparepart") {
        $columns = [
          1 => 'a.id',
          2 => 'c.nama_bahan',
          3 => 'd.keterangan',
          4 => 'a.qty',
          5 => 'a.harga',
          6 => 'a.jumlah',
        ];

        $limit = (int) $request->input('length', 10);
        $start = (int) $request->input('start', 0);
        $order = $columns[$request->input('order.0.column')] ?? 'a.seq_no';
        $dir = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';

        // Base query + LEFT JOIN
        $base = DB::table('t_permintaan_barang_dtl as a')
          ->join('t_permintaan_barang_hdr as b', function ($join) {
            $join->on('b.kode_cabang', '=', 'a.kode_cabang')
              ->on('b.seq_no', '=', 'a.seq_no'); // syarat di JOIN
          })
          ->leftJoin('m_sparepart as c', function ($join) {
            $join->on('c.kode_cabang', '=', 'a.kode_cabang')
              ->on('c.kode_sparepart', '=', 'a.kode_barang'); // syarat di JOIN
          })
          ->leftJoin('parameter as d', function ($join) {
            $join->on('d.kode', '=', 'c.kode_satuan')
              ->where('d.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
          })
          ->where('b.kode_cabang', $request->kode_cabang)
          ->where('b.kode_permintaan', $request->kode_permintaan);

        // Total baris tanpa filter
        $totalData = (clone $base)->count('a.id');

        // Filtering (search global)
        $query = (clone $base);
        if ($search = trim((string) $request->input('search.value'))) {
          $query->where(function ($q) use ($search) {
            $q->where('a.kode_order', 'like', "%{$search}%")
              ->orWhere('c.nama_sparepart', 'like', "%{$search}%");
          });
        }

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('a.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'a.id',
            'b.tipe_barang',
            'a.kode_barang',
            'a.qty',
            'c.kode_satuan',
            'a.no_sparepart',
            'a.harga',
            'a.tipe', // <-- tambah
            'c.nama_sparepart',
            'd.keterangan as nama_satuan',
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
            'id' => $row->id,
            'fake_id' => ++$fake,
            'tipe_barang' => $row->tipe_barang,
            'kode_bahan' => $row->kode_barang,
            'nama_bahan' => $row->nama_sparepart,
            'kode_satuan' => $row->kode_satuan,
            'nama_satuan' => $row->nama_satuan,
            'no_sparepart' => $row->no_sparepart,
            'qty' => number_format($row->qty, 0, '.', ','),
            'harga' => number_format($row->harga, 0, '.', ','),
            'tipe' => $row->tipe, // <-- tambah
          ];
        }
      } elseif ($request->tipe == "estimasi-sparepart") {
        $columns = [
          1 => 'a.id',
          2 => 'c.nama_bahan',
          3 => 'd.keterangan',
          4 => 'a.qty',
          5 => 'a.harga',
          6 => 'a.jumlah',
        ];

        $limit = (int) $request->input('length', 10);
        $start = (int) $request->input('start', 0);
        $order = $columns[$request->input('order.0.column')] ?? 'a.idx';
        $dir = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';

        // Base query + LEFT JOIN
        $base = DB::table('t_estimasi_dtl2 as a')
          ->join('t_estimasi_hdr as b', function ($join) {
            $join->on('b.kode_cabang', '=', 'a.kode_cabang')
              ->on('b.kode_estimasi', '=', 'a.kode_estimasi'); // syarat di JOIN
          })
          ->leftJoin('m_sparepart as c', function ($join) {
            $join->on('c.kode_cabang', '=', 'a.kode_cabang')
              ->on('c.kode_sparepart', '=', 'a.kode_sparepart'); // syarat di JOIN
          })
          ->leftJoin('parameter as d', function ($join) {
            $join->on('d.kode', '=', 'c.kode_satuan')
              ->where('d.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
          })
          ->where('b.kode_cabang', $request->kode_cabang)
          ->where('b.kode_spk', $request->kode_spk);

        // Total baris tanpa filter
        $totalData = (clone $base)->count('a.id');

        // Filtering (search global)
        $query = (clone $base);
        // if ($search = trim((string) $request->input('search.value'))) {
        //     $query->where(function ($q) use ($search) {
        //         $q->where('a.kode_order', 'like', "%{$search}%")
        //           ->orWhere('c.nama_sparepart', 'like', "%{$search}%");
        //     });
        // }

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('a.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'a.id',
            'a.kode_sparepart',
            'a.no_sparepart',
            'a.qty',
            'c.kode_satuan',
            'a.harga',
            'a.tipe',                 // <-- tambah
            'c.nama_sparepart',
            'c.nama_sparepart',
            'd.keterangan as nama_satuan',
          ])
          ->orderBy($order, $dir)
          // ->offset($start)
          // ->limit($limit)
          ->get();

        // Susun payload DataTables
        $data = [];
        $fake = $start;
        foreach ($datas as $row) {
          $cekdata = DB::table('t_permintaan_barang_dtl as a')
            ->join('t_permintaan_barang_hdr as b', function ($join) {
              $join->on('b.kode_cabang', '=', 'a.kode_cabang')
                ->on('b.seq_no', '=', 'a.seq_no'); // syarat di JOIN
            })
            ->where('b.kode_cabang', $request->kode_cabang)
            ->where('b.kode_spk', $request->kode_spk)
            ->where('a.kode_barang', $row->kode_sparepart)
            ->first();

          $cek = ($cekdata) ? '1' : '0';

          $data[] = [
            'id' => sprintf("%s_%s", $row->id, date("His")),
            'fake_id' => ++$fake,
            'tipe_barang' => 'S',
            'cek' => $cek,
            'kode_bahan' => $row->kode_sparepart,
            'nama_bahan' => $row->nama_sparepart,
            'kode_satuan' => $row->kode_satuan,
            'nama_satuan' => $row->nama_satuan,
            'no_sparepart' => $row->no_sparepart,
            'qty' => number_format($row->qty, 0, '.', ','),
            'harga' => number_format($row->harga, 0, '.', ','),
            'tipe' => $row->tipe,     // <-- tambah
          ];
        }
      } elseif ($request->tipe == "spk-baru" || $request->tipe == "spk-baru-all") {
        $columns = [
          1 => 'k.id',
          2 => 'k.tgl_masuk',
          3 => 'k.kode_spk',
          4 => 'e.keterangan', // status
          5 => 'k.no_polisi',
          6 => 'b.nama_tipe',
          7 => 'k.pemilik',
          8 => 'c.nama_pelanggan',
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
          ->where('k.kode_cabang', $user_cabang)
          // ->whereYear('k.tgl_masuk', date('Y'))
          ->whereYear('k.tgl_masuk', '>=', '2025')
          ->whereNotIn('k.status_spk', ['01', '02', '11']);
        // ->whereNotIn('k.kode_spk', function ($subquery) use ($user_cabang) {
        //   $subquery->select('kode_spk')
        //            ->from('t_permintaan_barang_hdr')
        //            ->where('kode_cabang', $user_cabang)
        //            ->groupBy('kode_spk');
        // });

        if ($request->tipe == "spk-baru") {
          $base->whereNotIn('k.kode_spk', function ($subquery) use ($user_cabang) {
            $subquery->select('kode_spk')
              ->from('t_permintaan_barang_hdr')
              ->where('kode_cabang', $user_cabang)
              ->groupBy('kode_spk');
          });
        }

        // Total baris tanpa filter
        $totalData = (clone $base)->count('k.id');

        // Filtering (search global)
        $query = (clone $base);
        if ($search = trim((string) $request->input('search.value'))) {
          $query->where(function ($q) use ($search) {
            $q->where('k.kode_spk', 'like', "%{$search}%")
              ->orWhere('k.no_polisi', 'like', "%{$search}%")
              ->orWhere('b.nama_tipe', 'like', "%{$search}%")
              ->orWhere('c.nama_pelanggan', 'like', "%{$search}%")
              ->orWhere('k.no_polis', 'like', "%{$search}%")
              ->orWhere('k.kode_claim', 'like', "%{$search}%")
              ->orWhere('k.pemilik', 'like', "%{$search}%");
          });
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
          ];
        }
      } elseif ($request->tipe == "total-data") {
        $bulan = date("m");
        $tahun = date("Y");
        ## Jumlah SPK Baru
        $totalSpk = Spk::where('kode_cabang', $user_cabang)
          // ->whereYear('tgl_masuk', $tahun)
          ->whereYear('tgl_masuk', '>=', '2025')
          ->whereNotIn('status_spk', ['01', '02', '11'])
          ->whereNotIn('kode_spk', function ($subquery) use ($user_cabang) {
            $subquery->select('kode_spk')
              ->from('t_permintaan_barang_hdr')
              ->where('kode_cabang', $user_cabang)
              ->groupBy('kode_spk');
          })
          ->count();

        ## Jumlah Permintaan per Bulan
        $totalPermintaanBln = PermintaanBarang::where('tipe_barang', 'S')
          ->where('kode_cabang', $user_cabang)
          ->whereMonth('tanggal_permintaan', $bulan)
          ->whereYear('tanggal_permintaan', $tahun)
          ->count();

        ## Jumlah Permintaan per Tahun
        $totalPermintaanThn = PermintaanBarang::where('tipe_barang', 'S')
          ->where('kode_cabang', $user_cabang)
          ->whereYear('tanggal_permintaan', $tahun)
          ->count();

        $data['spk'] = number_format($totalSpk, 0, ".", ",");
        $data['permintaan_bulan'] = number_format($totalPermintaanBln, 0, ".", ",");
        $data['permintaan_tahun'] = number_format($totalPermintaanThn, 0, ".", ",");

        return response()->json($data);

      } elseif ($request->tipe == "permintaan") {
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
        $base = DB::table('t_permintaan_barang_hdr as a')
          ->Join('t_spk_master as b', function ($join) {
            $join->on('b.kode_cabang', '=', 'a.kode_cabang')
              ->on('b.kode_spk', '=', 'a.kode_spk'); // syarat di JOIN
          })
          ->leftJoin('m_tipe_kendaraan as d', function ($join) {
            $join->on('b.kode_tipe', '=', 'd.kode_tipe')
              ->on('b.kode_merek', '=', 'd.kode_merek'); // syarat di JOIN
          })
          ->leftJoin('m_merek_kendaraan as e', 'd.kode_merek', '=', 'e.kode_merek')
          ->leftJoin('m_pelanggan_hdr as f', function ($join) {
            $join->on('b.kode_cabang', '=', 'f.kode_cabang')
              ->on('b.kode_pelanggan', '=', 'f.kode_pelanggan'); // syarat di JOIN
          })
          ->leftJoin('parameter as g', function ($join) {
            $join->on('g.kode', '=', 'a.tipe_barang')
              ->where('g.nama_tabel', '=', 'TIPE_BARANG'); // syarat di JOIN
          })
          ->leftJoin('m_posisi_pekerjaan as h', 'h.kode_posisi', '=', 'a.kode_bagian')
          ->where('a.tipe_barang', 'S')
          ->where('a.kode_cabang', $user_cabang);

        // $base = DB::table('v_trx_permintaan_barang as a')
        // ->where('a.kode_cabang', $user_cabang);

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
        if ($request->filled('kode_permintaan')) {
          $query->where('a.kode_permintaan', 'like', '%' . $request->kode_permintaan . '%');
        }
        if ($request->filled('kode_spk')) {
          $query->where('a.kode_spk', 'like', '%' . $request->kode_spk . '%');
        }
        if ($request->filled('tanggal_awal')) {
          $startDate = Carbon::createFromFormat('d/m/Y', $request->tanggal_awal, 'Asia/Jakarta')->format('Y-m-d');
          $query->whereDate('a.tanggal_permintaan', '>=', $startDate);
        }
        if ($request->filled('tanggal_akhir')) {
          $endDate = Carbon::createFromFormat('d/m/Y', $request->tanggal_akhir, 'Asia/Jakarta')->format('Y-m-d');
          $query->whereDate('a.tanggal_permintaan', '<=', $endDate);
        }
        if ($request->filled('nama_pemilik')) {
          $query->where('b.pemilik', 'like', '%' . $request->nama_pemilik . '%');
        }
        if ($request->filled('nama_pelanggan')) {
          $query->where('f.nama_pelanggan', 'like', '%' . $request->nama_pelanggan . '%');
        }
        if ($request->filled('no_polisi')) {
          $query->where('b.no_polisi', 'like', '%' . $request->no_polisi . '%');
        }
        if ($request->filled('tipe_kendaraan')) {
          $query->where(function ($q) use ($request) {
            $q->where('e.nama_merek', 'like', '%' . $request->tipe_kendaraan . '%')
              ->orWhere('d.nama_tipe', 'like', '%' . $request->tipe_kendaraan . '%');
          });
        }
        if ($request->filled('tipe_barang')) {
          if ($request->tipe_barang <> 'all') {
            $query->where('a.tipe_barang', 'like', '%' . $request->tipe_barang . '%');
          }
        }
        if ($request->filled('kode_bagian')) {
          if ($request->kode_bagian <> 'all') {
            $query->where('a.kode_bagian', 'like', '%' . $request->kode_bagian . '%');
          }
        }

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('a.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'a.id',
            'a.tanggal_permintaan',
            'a.kode_cabang',
            'a.kode_permintaan',
            'g.keterangan as tipe_barang',
            'h.posisi_pekerjaan',
            'a.kode_spk',
            'b.no_polisi',
            'e.nama_merek',
            'd.nama_tipe',
            'b.pemilik',
            'f.nama_pelanggan',

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
            'tanggal_permintaan' => blank($row->tanggal_permintaan) ? '' : date("d/m/Y", strtotime($row->tanggal_permintaan)),
            'kode_cabang' => $row->kode_cabang,
            'kode_permintaan' => $row->kode_permintaan,
            'tipe_barang' => $row->tipe_barang,
            'posisi_pekerjaan' => $row->posisi_pekerjaan,
            'kode_spk' => $row->kode_spk,
            'no_polisi' => $row->no_polisi,
            'merek_tipe' => sprintf("%s %s", $row->nama_merek, $row->nama_tipe), // $row->merek_tipe,
            'pemilik' => $row->pemilik,
            'nama_pelanggan' => $row->nama_pelanggan,
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
    } catch (\Exception $e) {
      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage(),
      ]);
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
    try {
      $user_cabang = session('kd_cabang');
      $dataID = $request->id;

      $cek = 0;
      if ($request->detail) {
        foreach ($request->detail as $key => $item) {

          if ($request->tipe_barang == "S") {
            if (isset($item['cek'])) {
              $cek++;
            }
          } else {
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

      if ($dataID) {
        $res = PermintaanBarang::findOrFail($dataID);

        $rules = [
          // 'kode_spk' => 'required',
          'tanggal_permintaan' => 'required',
          // 'tipe_barang' => 'required',
          'kode_bagian' => 'required',
        ];

        $messages = [
          // 'kode_spk.required' => 'Nomor SPK Wajib diisi',
          'tanggal_permintaan.required' => 'Tanggal Permintaan Wajib diisi',
          // 'tipe_barang.required' => 'Tipe Barang Wajib diisi',
          'kode_bagian.required' => 'Bagian Wajib diisi',
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
          'tanggal_permintaan' => blank($request->tanggal_permintaan) ? null : Carbon::createFromFormat('d/m/Y', $request->tanggal_permintaan, 'Asia/Jakarta')->format('Y-m-d'),
          // 'tipe_barang' => $request->tipe_barang,
          'kode_bagian' => $request->kode_bagian,
          // 'jenis_permintaan' => 'K',
          // 'pengulangan' => 'N',
          // 'sudah_terpakai' => 'N',
          'updated_by' => Auth::user()->username
        ];

        // if($request->tipe_barang == "U") {
        //   $data['kode_spk'] = 'UMUM';
        // }

        $result = $res->update($data);

        if ($result) {
          ## INSERT DETAIL PERMINTAAN
          // PermintaanBarangDetail::where('kode_cabang', $res->kode_cabang)->where('kode_order', $res->kode_order)->delete();
          if ($request->detail) {
            $no = 1;
            $dataDet = [];
            foreach ($request->detail as $key => $item) {

              $updt = PermintaanBarangDetail::find($key);

              if ($request->tipe_barang == "S") {
                if (isset($item['cek'])) {
                  $tmp = [
                    'no_urut' => $no++,
                    'kode_barang' => $item['bahan'],
                    'qty' => str_replace([","], "", $item['qty']),
                    // 'harga'           => 0,
                    // 'kode_analisa'    => 'K',
                    'no_sparepart' => isset($item['no_sparepart']) ? $item['no_sparepart'] : null,
                    'tipe' => isset($item['tipe']) ? $item['tipe'] : null, // tambahkan ini
                    'updated_by' => Auth::user()->username,
                  ];

                  $updt->update($tmp);
                } else {
                  $tmp = $item;
                  PermintaanBarangDetail::where('id', $key)->delete();
                }
              } else {
                if ($updt) {
                  $tmp = [
                    'kode_barang' => $item['bahan'],
                    'qty' => str_replace([","], "", $item['qty']),
                    // 'harga'           => 0,
                    // 'kode_analisa'    => 'K',
                    'no_sparepart' => isset($item['no_sparepart']) ? $item['no_sparepart'] : null,
                    'tipe' => isset($item['tipe']) ? $item['tipe'] : null, // tambahkan ini
                    'updated_by' => Auth::user()->username,
                  ];

                  $updt->update($tmp);
                } else {
                  $tmp = [
                    'kode_cabang' => $user_cabang,
                    'seq_no' => $res->seq_no,
                    'no_urut' => $no++,
                    'kode_barang' => $item['bahan'],
                    'qty' => str_replace([","], "", $item['qty']),
                    'harga' => 0,
                    'kode_analisa' => 'K',
                    'no_sparepart' => isset($item['no_sparepart']) ? $item['no_sparepart'] : null,
                    'tipe' => isset($item['tipe']) ? $item['tipe'] : null, // tambahkan ini
                    'created_by' => Auth::user()->username,
                  ];

                  PermintaanBarangDetail::create($tmp);
                }
              }
              $dataDet[] = $tmp;
            }

            $data2['DETAIL'] = $dataDet;
          }
        }

        $data2['HEADER'] = $data;

        ## Log Activity
        $desc = $result ? 'Berhasil Ubah Permintaan Barang' : 'Gagal Ubah Permintaan Barang';
        LogActivity::saveLogActivity($desc, $data2);

        return response()->json([
          'status' => (bool) $result,
          'message' => $desc
        ]);
      } else {

        $rules = [
          'kode_spk' => 'required',
          'tanggal_permintaan' => 'required',
          'tipe_barang' => 'required',
          'kode_bagian' => 'required',
        ];

        $messages = [
          'kode_spk.required' => 'Nomor SPK Wajib diisi',
          'tanggal_permintaan.required' => 'Tanggal Permintaan Wajib diisi',
          'tipe_barang.required' => 'Tipe Barang Wajib diisi',
          'kode_bagian.required' => 'Bagian Wajib diisi',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
          return response()->json([
            'status' => false,
            'message' => "Gagal menyimpan data.",
            'errors' => $validator->errors()
          ], 200);
        }

        $tanggalInput = $request->tanggal_permintaan;
        $carbonDate = blank($tanggalInput) ? now() : Carbon::createFromFormat('d/m/Y', trim($tanggalInput), 'Asia/Jakarta');
        $tanggal = $carbonDate->format('Y-m-d');
        $tahun = $carbonDate->year;
        $tahunShort = now()->format('y');

        ## Nomor Permintaan
        $lastNum = PermintaanBarang::query()->max(DB::raw('CAST(seq_no AS UNSIGNED)')) ?? 0;
        $seq_no = $lastNum + 1;

        ## Nomor Permintaan Pembelian
        $penomoran = Helper::getNomorTransaksi($user_cabang, 'PB');

        $cekspk = PermintaanBarang::where('kode_permintaan', $penomoran)->first();
        if (!empty($cekspk)) {
          return response()->json(['status' => false, 'message' => "Nomor Permintaan sudah digunakan"]);
        }

        $data = [
          'kode_cabang' => $user_cabang,
          'kode_permintaan' => $penomoran,
          'seq_no' => $seq_no,
          'tanggal_permintaan' => $tanggal,
          'kode_spk' => $request->kode_spk,
          'tipe_barang' => $request->tipe_barang,
          'kode_bagian' => $request->kode_bagian,
          'jenis_permintaan' => 'K',
          'pengulangan' => 'N',
          'sudah_terpakai' => 'N',
          'created_by' => Auth::user()->username
        ];

        if ($request->tipe_barang == "U") {
          $data['kode_spk'] = 'UMUM';
        }

        $result = PermintaanBarang::create($data);

        if ($result) {
          ## INSERT DETAIL PERMINTAAN
          // PermintaanBarangDetail::where('kode_cabang', $user_cabang)->where('seq_no', $seq_no)->delete();
          if ($request->detail) {
            $no = 1;
            $dataDet = [];
            $tmp = [];
            foreach ($request->detail as $key => $item) {

              if ($request->tipe_barang == "S") {
                if (isset($item['cek'])) {
                  $tmp = [
                    'kode_cabang' => $user_cabang,
                    'seq_no' => $seq_no,
                    'no_urut' => $no++,
                    'kode_barang' => $item['bahan'],
                    'qty' => str_replace([","], "", $item['qty']),
                    // 'harga'           => str_replace([","], "", $item['harga']),
                    'harga' => 0,
                    'kode_analisa' => 'K',
                    'no_sparepart' => isset($item['no_sparepart']) ? $item['no_sparepart'] : null,
                    'tipe' => isset($item['tipe']) ? $item['tipe'] : null, // tambahkan ini
                    'created_by' => Auth::user()->username,
                  ];

                  PermintaanBarangDetail::create($tmp);
                }
              } else {
                $tmp = [
                  'kode_cabang' => $user_cabang,
                  'seq_no' => $seq_no,
                  'no_urut' => $no++,
                  'kode_barang' => $item['bahan'],
                  'qty' => str_replace([","], "", $item['qty']),
                  // 'harga'           => str_replace([","], "", $item['harga']),
                  'harga' => 0,
                  'kode_analisa' => 'K',
                  'no_sparepart' => isset($item['no_sparepart']) ? $item['no_sparepart'] : null,
                  'tipe' => isset($item['tipe']) ? $item['tipe'] : null, // tambahkan ini
                  'created_by' => Auth::user()->username,
                ];

                PermintaanBarangDetail::create($tmp);
              }
              $dataDet[] = $tmp;
            }

            $data2['DETAIL'] = $dataDet;
          }

          ## Update Nomor Permintaan Barang
          $res = Helper::updateNomorTransaksi($user_cabang, 'PB');
        }

        $data2['HEADER'] = $data;


        // // TAMBAHKAN NOTIFIKASI DI SINI
        // Notifikasi::aggregate(
        //   [
        //     'user_id' => null,
        //     'target_level' => 'UL01',
        //     'kode_cabang' => $user_cabang,
        //     'tipe' => 'operasional',
        //     'url' => url('gudang/permintaan-barang'),
        //     'is_read' => false,
        //   ],
        //   'permintaan_barang_' . $user_cabang . '_' . now()->format('Ymd'),
        //   'Ada {count} Permintaan Barang Baru',
        //   "Permintaan barang {$penomoran} (SPK: {$request->kode_spk}) telah diajukan."
        // );

        ## Log Activity
        $desc = $result ? 'Berhasil Tambah Permintaan Barang' : 'Gagal Tambah Permintaan Barang';
        LogActivity::saveLogActivity($desc, $data2);

        return response()->json([
          'status' => (bool) $result,
          'message' => $desc
        ]);
      }
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 500);

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
    $data = DB::table('v_trx_permintaan_barang')->where('id', $id)->first();

    if (blank($data)) {
      $result = false;
      return response()->json([
        'status' => (bool) $result,
        'message' => 'Kode Permintaan tidak ditemukan'
      ]);
    }

    // Tambahan: ambil no_polis & kode_claim dari t_spk_master
    $spk = DB::table('t_spk_master')
      ->where('kode_cabang', $data->kode_cabang)
      ->where('kode_spk', $data->kode_spk)
      ->select('no_polis', 'kode_claim')
      ->first();

    // ✅ TAMBAHKAN INI
    $data->no_polis = $spk->no_polis ?? null;
    $data->kode_claim = $spk->kode_claim ?? null;

    $data->tanggal_permintaan = blank($data->tanggal_permintaan) ? '' : date("d/m/Y", strtotime($data->tanggal_permintaan));

    // $data->total = number_format($data->total, 2, '.', ',');

    $result = true;
    return response()->json([
      'status' => (bool) $result,
      'message' => 'Berhasil Permintaan Barang',
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
    $data = PermintaanBarang::query()->where('id', $id)->first()->toArray();

    $ok = PermintaanBarang::where('id', $id)->delete();
    if ($ok) {
      PermintaanBarangDetail::where('kode_cabang', $data['kode_cabang'])->where('seq_no', $data['seq_no'])->delete();
    }

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Permintaan Barang' : 'Gagal Hapus Permintaan Barang';
    LogActivity::saveLogActivity($desc, $data);
  }

  public function getDataSPK(Request $request): JsonResponse
  {
    $kd_cabang = session('kd_cabang');
    $kode_spk = $request->kode_spk;
    $kode_permintaan = $request->kode_permintaan;

    if (blank($kode_permintaan)) { // Ambil Data SPK

      $data = DB::table('v_spk')
        ->select('id', 'kode_cabang', 'kode_spk', 'no_polisi', 'merek_tipe', 'pemilik', 'nama_pelanggan', 'kode_estimasi', 'no_polis', 'kode_claim')
        ->where('id', $kode_spk)
        // ->where('kode_spk', $kode_spk)
        // ->where('kode_cabang', $kd_cabang)
        ->first();

      if (blank($data)) {
        $result = false;
        return response()->json([
          'status' => (bool) $result,
          'message' => 'Kode SPK tidak ditemukan'
        ]);
      }

      if (blank($data->kode_estimasi)) {
        $result = false;
        return response()->json([
          'status' => (bool) $result,
          'message' => 'Estimasi belum dibuat'
        ]);
      }

      $result = true;
      return response()->json([
        'status' => (bool) $result,
        'message' => 'Berhasil Data SPK',
        'data' => $data
      ]);

    } else { // Ambil Data Permintaan

      $res = PermintaanBarang::find($kode_permintaan);

      if (blank($res)) {
        $result = false;
        return response()->json([
          'status' => (bool) $result,
          'message' => 'Kode Permintaan tidak ditemukan'
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

      $result = true;
      return response()->json([
        'status' => (bool) $result,
        'message' => 'Berhasil Data SPK',
        'data' => $data
      ]);

    }
  }

  public function getDataBagian(Request $request): JsonResponse
  {
    $tipeBarang = $request->query('tipe_barang');

    if ($tipeBarang == "S" || $tipeBarang == "T") {
      $data = PosisiPekerjaan::query()
        ->select('kode_posisi', 'posisi_pekerjaan')
        ->where('is_active', 'Y')
        ->where('kode_posisi', '00006')
        ->orderBy('seq_no', 'asc')
        ->get();
    } else {
      $data = PosisiPekerjaan::query()
        ->select('kode_posisi', 'posisi_pekerjaan')
        ->where('is_active', 'Y')
        ->whereNotIn('kode_posisi', ['00006', '00009'])
        ->orderBy('seq_no', 'asc')
        ->get();
    }

    return response()->json($data);
  }

  public function cetakPermintaanBarang(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Permintaan Barang';

    $id = $request->id;

    $data = DB::table('v_trx_permintaan_barang')->where('id', $id)->first();

    if (blank($data)) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }

    $cabang = DB::table('m_cabang')->where('kode_cabang', $data->kode_cabang)->first();
    $cabang->alamat1 = sprintf("%s %s %s", $cabang->alamat1, $cabang->alamat2, $cabang->alamat3);


    ## DATA DETAIL
    if ($data->kode_tipe_barang == "S") {
      $details = DB::table('t_permintaan_barang_dtl as a')
        ->join('t_permintaan_barang_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.seq_no', '=', 'a.seq_no'); // syarat di JOIN
        })
        ->leftJoin('m_sparepart as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'a.kode_cabang')
            ->on('c.kode_sparepart', '=', 'a.kode_barang'); // syarat di JOIN
        })
        ->leftJoin('parameter as d', function ($join) {
          $join->on('d.kode', '=', 'c.kode_satuan')
            ->where('d.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
        })
        ->where('b.kode_cabang', $data->kode_cabang)
        ->where('b.kode_permintaan', $data->kode_permintaan)
        ->select([
          'a.id',
          'b.tipe_barang',
          'a.kode_barang',
          'a.qty',
          'c.kode_satuan',
          'a.harga',
          'a.no_sparepart',
          'a.tipe',           // ← tambahkan ini
          'c.nama_sparepart as nama_bahan',
          'd.keterangan as nama_satuan',
        ])
        ->orderBy('a.no_urut', 'asc')
        ->get();
    } else {
      $details = $base = DB::table('t_permintaan_barang_dtl as a')
        ->join('t_permintaan_barang_hdr as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.seq_no', '=', 'a.seq_no'); // syarat di JOIN
        })
        ->leftJoin('m_bahan as c', function ($join) {
          $join->on('c.kode_cabang', '=', 'a.kode_cabang')
            ->on('c.kode_bahan', '=', 'a.kode_barang'); // syarat di JOIN
        })
        ->leftJoin('parameter as d', function ($join) {
          $join->on('d.kode', '=', 'c.kode_satuan')
            ->where('d.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
        })
        ->where('b.kode_cabang', $data->kode_cabang)
        ->where('b.kode_permintaan', $data->kode_permintaan)
        ->select([
          'a.id',
          'b.tipe_barang',
          'a.kode_barang',
          'a.qty',
          'c.kode_satuan',
          'a.harga',
          'a.no_sparepart',
          'a.tipe',           // ← tambahkan ini
          'c.nama_bahan',
          'd.keterangan as nama_satuan',
        ])
        ->orderBy('a.no_urut', 'asc')
        ->get();
    }

    ## Log Activity
    // $desc = "Cetak " . $title;
    // LogActivity::saveLogActivity($desc);

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.gudang.permintaan-barang-cetak', [
      'title' => $title,
      'cabang' => $cabang,
      'data' => $data,
      'data_detail' => $details,
      'pageConfigs' => $pageConfigs,
    ]);

  }

}
