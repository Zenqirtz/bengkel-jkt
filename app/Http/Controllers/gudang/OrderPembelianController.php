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
use App\Models\Umum;
use App\Models\PermintaanBarang;
use App\Models\OrderPembelian;
use App\Models\OrderPembelianDetail;
use App\Models\OrderPembelianDetail2;
use App\Models\LogActivity;
use Carbon\Carbon;
use App\Models\Notifikasi;

class OrderPembelianController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function OrderPembelian(): View
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
    $tipe_bayar = Parameter::query()->where('nama_tabel', 'TIPE_BAYAR')->orderBy('no_urut', 'asc')->get();
    $satuan = Parameter::query()->where('nama_tabel', 'SATUAN')->orderBy('no_urut', 'asc')->get();
    $pemasok = Pemasok::query()->select('kode_pemasok', 'nama_pemasok')->where('kode_cabang', $user_cabang)->orderBy('nama_pemasok', 'asc')->get();
    // $bahan = Bahan::query()->select('kode_bahan','nama_bahan','kode_group_bahan')->where('kode_cabang', $user_cabang)->orderBy('nama_bahan', 'asc')->get();

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

    return view('content.gudang.order-pembelian', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'ppn_persen' => $ppn_persen,
      'satuan' => $satuan,
      'tipe_barang' => $tipe_barang,
      'tipe_bayar' => $tipe_bayar,
      'pemasok' => $pemasok,
      // 'bahan' => $bahan,
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
        ->where('b.kode_cabang', $request->kode_cabang)
        ->where('b.kode_order', $request->kode_order);

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
          'kode_bahan' => $row->kode_bahan,
          'nama_bahan' => $row->nama_bahan,
          'kode_satuan' => $row->kode_satuan,
          'nama_satuan' => $row->nama_satuan,
          'no_sparepart' => '',
          'qty' => number_format($row->qty, 0, '.', ','),
          'harga' => number_format($row->harga, 0, '.', ','),
          'jumlah' => number_format($row->jumlah, 0, '.', ','),
        ];
      }
    } elseif ($request->tipe == "detail-order" || $request->tipe == "detail-order-batal") {
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
      } else {
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

        // Total baris tanpa filter
        $totalData = (clone $base)->count('a.id');

        // Filtering (search global)
        $query = (clone $base);
        if ($search = trim((string) $request->input('search.value'))) {
          $query->where(function ($q) use ($search) {
            $q->where('a.kode_bahan', 'like', "%{$search}%")
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
            'c.nama_bahan',
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
          'cek' => $row->cek,
          'qty' => number_format($row->qty, 0, '.', ','),
          'harga' => number_format($row->harga, 0, '.', ','),
          'jumlah' => number_format($row->jumlah, 0, '.', ','),
        ];
      }
    } elseif ($request->tipe == "detail-permintaan-bahan") {
      $kode = blank($request->kode_permintaan) ? 'xx' : $request->kode_permintaan;

      // Base query + LEFT JOIN
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

      $isExist = (clone $base)->exists();

      if ($isExist) {
        // Total baris tanpa filter
        $totalData = (clone $base)->count('a.id');

        // Filtering (search global)
        $query = (clone $base);
        // if ($search = trim((string) $request->input('search.value'))) {
        //     $query->where(function ($q) use ($search) {
        //         $q->where('a.kode_bahan', 'like', "%{$search}%")
        //           ->orWhere('c.nama_sparepart', 'like', "%{$search}%");
        //     });
        // }

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('a.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'a.id',
            'a.kode_bahan',
            'c.nama_bahan',
            'a.no_sparepart',
            'a.kode_satuan',
            'd.keterangan as nama_satuan',
            'a.qty',
            'a.harga',
            'a.jumlah',
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
      } else {
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
          ->where('b.kode_cabang', $user_cabang)
          ->where('b.kode_permintaan', $kode);

        // Total baris tanpa filter
        $totalData = (clone $base)->count('a.id');

        // Filtering (search global)
        $query = (clone $base);
        // if ($search = trim((string) $request->input('search.value'))) {
        //     $query->where(function ($q) use ($search) {
        //         $q->where('c.kode_sparepart', 'like', "%{$search}%")
        //           ->orWhere('c.nama_sparepart', 'like', "%{$search}%");
        //     });
        // }

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('a.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'a.id',
            'a.kode_barang as kode_bahan',
            'c.nama_bahan',
            'a.no_sparepart',
            'd.kode as kode_satuan',
            'd.keterangan as nama_satuan',
            'a.qty',
            'a.harga',
            DB::raw('(a.qty * a.harga) AS jumlah'),
          ])
          ->orderBy('a.no_urut', 'asc')
          ->get();

        // Susun payload DataTables
        $data = [];
        $fake = 0; //$start;
        foreach ($datas as $row) {
          $cekdata = DB::table('t_order_dtl1 as a')
            ->join('t_order_hdr as b', function ($join) {
              $join->on('b.kode_cabang', '=', 'a.kode_cabang')
                ->on('b.kode_order', '=', 'a.kode_order'); // syarat di JOIN
            })
            ->where('b.kode_cabang', $user_cabang)
            ->where('b.kode_permintaan', $kode)
            ->where('a.kode_bahan', $row->kode_bahan)
            ->first();

          if (!$cekdata) {
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
        }
      }
    } elseif ($request->tipe == "detail-permintaan-part") {
      $kode = blank($request->kode_permintaan) ? 'xx' : $request->kode_permintaan;

      // Base query + LEFT JOIN
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

      $isExist = (clone $base)->exists();

      if ($isExist) {
        // Total baris tanpa filter
        $totalData = (clone $base)->count('a.id');

        // Filtering (search global)
        $query = (clone $base);
        // if ($search = trim((string) $request->input('search.value'))) {
        //     $query->where(function ($q) use ($search) {
        //         $q->where('a.kode_bahan', 'like', "%{$search}%")
        //           ->orWhere('c.nama_sparepart', 'like', "%{$search}%");
        //     });
        // }

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
      } else {
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
          ->where('b.kode_cabang', $user_cabang)
          ->where('b.kode_permintaan', $kode);

        // Total baris tanpa filter
        $totalData = (clone $base)->count('a.id');

        // Filtering (search global)
        $query = (clone $base);
        // if ($search = trim((string) $request->input('search.value'))) {
        //     $query->where(function ($q) use ($search) {
        //         $q->where('c.kode_sparepart', 'like', "%{$search}%")
        //           ->orWhere('c.nama_sparepart', 'like', "%{$search}%");
        //     });
        // }

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('a.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'a.id',
            'a.kode_barang as kode_bahan',
            'c.nama_sparepart as nama_bahan',
            'a.no_sparepart',
            'd.kode as kode_satuan',
            'd.keterangan as nama_satuan',
            'a.qty',
            'a.harga',
            DB::raw('(a.qty * a.harga) AS jumlah'),
          ])
          ->orderBy('a.no_urut', 'asc')
          ->get();

        // Susun payload DataTables
        $data = [];
        $fake = 0; //$start;
        foreach ($datas as $row) {
          $cekdata = DB::table('t_order_dtl1 as a')
            ->join('t_order_hdr as b', function ($join) {
              $join->on('b.kode_cabang', '=', 'a.kode_cabang')
                ->on('b.kode_order', '=', 'a.kode_order'); // syarat di JOIN
            })
            ->where('a.cek', '1')
            ->where('b.kode_cabang', $user_cabang)
            ->where('b.kode_permintaan', $kode)
            ->where('a.kode_bahan', $row->kode_bahan)
            ->first();

          if (!$cekdata) {
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
        }
      }
    } elseif ($request->tipe == "permintaan-baru" || $request->tipe == "permintaan-baru-all") {
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
        ->where('a.kode_cabang', $user_cabang)
        ->where('a.tipe_barang', 'S')
        ->whereYear('a.tanggal_permintaan', date('Y'));
      // ->whereYear('a.tanggal_permintaan', '>=', '2025')
      // ->whereNotIn('a.kode_permintaan', function ($subquery) use ($user_cabang) {
      //   $subquery->select('kode_permintaan')
      //            ->from('t_order_hdr')
      //            ->where('kode_cabang', $user_cabang)
      //            ->whereRaw('LENGTH(kode_permintaan) > 0')
      //            ->groupBy('kode_permintaan');
      // });

      if ($request->tipe == "permintaan-baru") {
        $base->whereNotIn('a.kode_permintaan', function ($subquery) use ($user_cabang) {
          $subquery->select('kode_permintaan')
            ->from('t_order_hdr')
            ->where('kode_cabang', $user_cabang)
            ->whereRaw('LENGTH(kode_permintaan) > 0')
            ->groupBy('kode_permintaan');
        });
      }

      // Total baris tanpa filter
      $totalData = (clone $base)->count('a.id');

      // Filtering (search global)
      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('a.kode_permintaan', 'like', "%{$search}%")
            ->orWhere('a.kode_spk', 'like', "%{$search}%")
            ->orWhere('b.no_polisi', 'like', "%{$search}%")
            ->orWhere('g.keterangan', 'like', "%{$search}%")
            ->orWhere('h.posisi_pekerjaan', 'like', "%{$search}%")
            ->orWhere('b.pemilik', 'like', "%{$search}%")
            ->orWhere('d.nama_tipe', 'like', "%{$search}%")
            ->orWhere('e.nama_merek', 'like', "%{$search}%");
        });
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
    } elseif ($request->tipe == "total-data") {
      $tahun = date("Y");
      ## Jumlah SPK Baru
      $totalPermintaan = PermintaanBarang::where('kode_cabang', $user_cabang)
        ->where('tipe_barang', 'S')
        ->whereYear('tanggal_permintaan', $tahun)
        // ->whereYear('tanggal_permintaan', '>=', '2025')
        ->whereNotIn('kode_permintaan', function ($subquery) use ($user_cabang) {
          $subquery->select('kode_permintaan')
            ->from('t_order_hdr')
            ->where('kode_cabang', $user_cabang)
            ->whereRaw('LENGTH(kode_permintaan) > 0')
            ->groupBy('kode_permintaan');
        })
        ->count();

      ## Jumlah Pending Purchase Order
      $totalPurchasePending = OrderPembelian::where('kode_cabang', $user_cabang)
        ->whereYear('tanggal', $tahun)
        ->where('status_approve', '0')
        ->count();

      ## Jumlah PO per Tahun
      $totalPurchaseOrder = OrderPembelian::where('kode_cabang', $user_cabang)
        ->whereYear('tanggal', $tahun)
        ->count();

      $data['permintaan'] = number_format($totalPermintaan, 0, ".", ",");
      $data['po_pending'] = number_format($totalPurchasePending, 0, ".", ",");
      $data['po'] = number_format($totalPurchaseOrder, 0, ".", ",");

      return response()->json($data);
    } elseif ($request->tipe == "order-pembelian") {
      $columns = [
        1 => 'a.id',
        2 => 'a.tanggal',
        3 => 'a.kode_order',
        4 => 'a.status_approve',
        5 => 'a.kode_permintaan',
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
      $base = DB::table('t_order_hdr as a')
        ->leftJoin('m_pemasok as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_pemasok', '=', 'a.kode_pemasok'); // syarat di JOIN
        })
        ->leftJoin('parameter as c', function ($join) {
          $join->on('c.kode', '=', 'a.tipe_barang')
            ->where('c.nama_tabel', '=', 'TIPE_BARANG'); // syarat di JOIN
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
      if ($request->filled('kode_order')) {
        $query->where('a.kode_order', 'like', '%' . $request->kode_order . '%');
      }
      if ($request->filled('kode_permintaan')) {
        $query->where('a.kode_permintaan', 'like', '%' . $request->kode_permintaan . '%');
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
          $query->where('a.tipe_barang', 'like', '%' . $request->tipe_barang . '%');
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
          'a.kode_order',
          'a.kode_spk',
          'a.tanggal',
          'a.kode_permintaan',
          'a.tipe_barang as kode_tipe',
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
      $dtStatus = ['0' => 'MENUNGGU APPROVAL', '1' => 'APPROVED', '2' => 'ADA PEMBATALAN'];
      foreach ($datas as $row) {
        $data[] = [
          'id' => $row->id,
          'fake_id' => ++$fake,
          'kode_cabang' => $row->kode_cabang,
          'kode_order' => $row->kode_order,
          'kode_spk' => $row->kode_spk,
          'status_approve' => $row->status_approve,
          'keterangan' => $dtStatus[$row->status_approve],
          // 'keterangan' => ($row->status_approve == '1') ? 'APPROVED' : 'MENUNGGU APPROVAL',
          'tanggal' => blank($row->tanggal) ? '' : date("d/m/Y", strtotime($row->tanggal)),
          'kode_permintaan' => $row->kode_permintaan,
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
    $user_cabang = session('kd_cabang');
    $dataID = $request->id;

    $cek = 0;
    $cek2 = 0;
    if ($request->detail) {
      foreach ($request->detail as $key => $item) {
        $cek++;

        if ($request->status_approve == "2" && isset($item['cek'])) {
          $cek2++;
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
      $res = OrderPembelian::findOrFail($dataID);

      if ($request->status_approve == "2") {
        if ($cek2 == 0) {
          return response()->json([
            'status' => false,
            'message' => "Data detail barang pembatalan belum dipilih."
          ], 200);
        }

        $rules = [
          'memo_batal' => 'required',
        ];

        $messages = [
          'memo_batal.required' => 'Alasan Batal Wajib diisi',
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
          'status_approve' => $request->status_approve,
          'memo_batal' => $request->memo_batal,
          'tgl_batal' => date("Y-m-d H:i:s"),
          'user_batal' => Auth::user()->username,
          'updated_by' => Auth::user()->username
        ];

        $result = $res->update($data);

        if ($result) {
          ## UPDATE DETAIL ORDER
          if ($request->detail) {
            $no = 1;
            foreach ($request->detail as $key => $item) {
              if (isset($item['cek'])) {
                $tmpdata = [
                  'cek' => '2',
                  'updated_by' => Auth::user()->username
                ];
              } else {
                $tmpdata = [
                  'cek' => '1'
                ];
              }

              $ok = OrderPembelianDetail::updateOrCreate(
                ['id' => $key],
                $tmpdata
              );
            }
          }
        }

        ## Log Activity
        $desc = $result ? 'Berhasil Pembatalan Order Pembelian' : 'Gagal Pembatalan Order Pembelian';
        LogActivity::saveLogActivity($desc, $data);

        return response()->json([
          'status' => (bool) $result,
          'message' => $desc
        ]);
      } else {
        $rules = [
          'tanggal' => 'required',
          'tipe_barang' => 'required',
          'tipe_bayar' => 'required',
          'kode_pemasok' => 'required',
        ];

        $messages = [
          'tanggal.required' => 'Tanggal Wajib diisi',
          'tipe_barang.required' => 'Tipe Barang Wajib diisi',
          'tipe_bayar.required' => 'Pembayaran Wajib diisi',
          'kode_pemasok.required' => 'Nama Supplier Wajib diisi',
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
          'tipe_barang' => $request->tipe_barang,
          'tipe_bayar' => $request->tipe_bayar,
          'kode_pemasok' => $request->kode_pemasok,
          'memo' => $request->memo,
          'sifat_ppn' => $request->sifat_ppn,
          'ppn' => str_replace([","], "", $request->ppn),
          'total' => str_replace([","], "", $request->total_detail),
          'updated_by' => Auth::user()->username
        ];

        if ($request->tipe_barang == "S") {
          $data['kode_spk'] = $request->kode_spk;
          $data['kode_permintaan'] = $request->kode_permintaan;
        }

        if ($request->status_approve == "1") {
          $data['status_approve'] = $request->status_approve;
          $data['tgl_approve'] = date("Y-m-d H:i:s");
          $data['user_approve'] = Auth::user()->username;
        }

        $result = $res->update($data);

        if ($result) {
          ## INSERT DETAIL ORDER
          OrderPembelianDetail::where('kode_cabang', $res->kode_cabang)->where('kode_order', $res->kode_order)->delete();
          if ($request->detail) {
            $no = 1;
            foreach ($request->detail as $item) {
              OrderPembelianDetail::create([
                'kode_cabang' => $res->kode_cabang,
                'kode_order' => $res->kode_order,
                'seq_no' => $no++,
                'kode_bahan' => $item['bahan'],
                'kode_satuan' => $item['satuan'],
                'qty' => str_replace([","], "", $item['qty']),
                'harga' => str_replace([","], "", $item['harga']),
                'jumlah' => str_replace([","], "", $item['jumlah']),
                'memo' => $request->tipe_bayar,
                'kode_spk' => ($request->tipe_barang == "S") ? $request->kode_spk : null,
                'no_sparepart' => isset($item['no_sparepart']) ? $item['no_sparepart'] : null,
                'created_by' => Auth::user()->username,
              ]);
            }
          }
        }

        // ## Notifikasi ke Staff (UL01) kalau PO baru saja di-approve
        // if ($request->status_approve == "1") {
        //   Notifikasi::aggregate(
        //     [
        //       'user_id' => null,
        //       'target_level' => 'UL01',
        //       'kode_cabang' => $res->kode_cabang,
        //       'tipe' => 'operasional',
        //       'url' => url('gudang/order-pembelian'),
        //       'is_read' => false,
        //     ],
        //     'po_approved_' . $res->kode_cabang . '_' . now()->format('Ymd'),
        //     'Ada {count} Purchase Order Disetujui',
        //     "Purchase Order {$res->kode_order} telah disetujui oleh " . Auth::user()->username . "."
        //   );
        // }

        ## Log Activity
        if ($request->status_approve == "1") {
          $desc = $result ? 'Berhasil Approve Order Pembelian' : 'Gagal Approve Order Pembelian';
        } else {
          $desc = $result ? 'Berhasil Ubah Order Pembelian' : 'Gagal Ubah Order Pembelian';
        }
        LogActivity::saveLogActivity($desc, $data);

        return response()->json([
          'status' => (bool) $result,
          'message' => $desc
        ]);
      }
    } else {

      $rules = [
        'tanggal' => 'required',
        'tipe_barang' => 'required',
        'tipe_bayar' => 'required',
        'kode_pemasok' => 'required',
      ];

      $messages = [
        'tanggal.required' => 'Tanggal Wajib diisi',
        'tipe_barang.required' => 'Tipe Barang Wajib diisi',
        'tipe_bayar.required' => 'Pembayaran Wajib diisi',
        'kode_pemasok.required' => 'Nama Supplier Wajib diisi',
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

      ## Nomor Order Pembelian
      $penomoran = \Helper::getNomorTransaksi($user_cabang, 'PO');

      $cekspk = OrderPembelian::where('kode_order', $penomoran)->first();
      if (!empty($cekspk)) {
        return response()->json(['status' => false, 'message' => "Nomor Order sudah digunakan"]);
      }

      $data = [
        'kode_cabang' => $user_cabang,
        'kode_order' => $penomoran,
        'tanggal' => $tanggal,
        'tipe_barang' => $request->tipe_barang,
        // 'no_po' => $no_po,
        'no_po' => $penomoran,
        'tipe_bayar' => $request->tipe_bayar,
        'kode_pemasok' => $request->kode_pemasok,
        'memo' => $request->memo,
        'batal' => 'N',
        'sifat_ppn' => $request->sifat_ppn,
        'ppn' => str_replace([","], "", $request->ppn),
        'total' => str_replace([","], "", $request->total_detail),
        'created_by' => Auth::user()->username
      ];

      if ($request->tipe_barang == "S") {
        $data['kode_spk'] = $request->kode_spk;
        $data['kode_permintaan'] = $request->kode_permintaan;
      }

      $result = OrderPembelian::create($data);

      if ($result) {
        ## INSERT DETAIL ORDER
        OrderPembelianDetail::where('kode_cabang', $user_cabang)->where('kode_order', $penomoran)->delete();
        if ($request->detail) {
          $no = 1;
          foreach ($request->detail as $item) {
            OrderPembelianDetail::create([
              'kode_cabang' => $user_cabang,
              'kode_order' => $penomoran,
              'seq_no' => $no++,
              'kode_bahan' => $item['bahan'],
              'kode_satuan' => $item['satuan'],
              'qty' => str_replace([","], "", $item['qty']),
              'harga' => str_replace([","], "", $item['harga']),
              'jumlah' => str_replace([","], "", $item['jumlah']),
              'memo' => $request->tipe_bayar,
              'kode_spk' => ($request->tipe_barang == "S") ? $request->kode_spk : null,
              'no_sparepart' => isset($item['no_sparepart']) ? $item['no_sparepart'] : null,
              'created_by' => Auth::user()->username,
            ]);
          }
        }

        // ## Notifikasi ke Manager & Supervisor kalau ada PO baru menunggu approval
        // foreach (['UL02', 'UL03'] as $level) {
        //   Notifikasi::aggregate(
        //     [
        //       'user_id' => null,
        //       'target_level' => $level,
        //       'kode_cabang' => $user_cabang,
        //       'tipe' => 'operasional',
        //       'url' => url('gudang/order-pembelian'),
        //       'is_read' => false,
        //     ],
        //     'po_new_' . $level . '_' . $user_cabang . '_' . now()->format('Ymd'),
        //     'Ada {count} Purchase Order Baru Menunggu Approval',
        //     "Purchase Order {$penomoran} menunggu persetujuan Anda."
        //   );
        // }

        ## Update Nomor Order Pembelian
        $res = \Helper::updateNomorTransaksi($user_cabang, 'PO');
      }

      ## Log Activity
      $desc = $result ? 'Berhasil Tambah Order Pembelian' : 'Gagal Tambah Order Pembelian';
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
    $data = OrderPembelian::findOrFail($id);

    $data->tanggal = blank($data->tanggal) ? date("d/m/Y") : date("d/m/Y", strtotime($data->tanggal));
    // $data->ppn = number_format($data->ppn, 2, '.', ',');
    $data->total = number_format($data->total, 2, '.', ',');

    if ($data->tipe_barang == 'S') {
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
    }

    $message = ($data->status_approve == '1') ? 'Data PO sudah di appprove' : 'Berhasil Order Pembelian';

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
    $data = OrderPembelian::query()->where('id', $id)->first()->toArray();

    $ok = OrderPembelian::where('id', $id)->delete();
    if ($ok) {
      OrderPembelianDetail::where('kode_cabang', $data['kode_cabang'])->where('kode_order', $data['kode_order'])->delete();
      OrderPembelianDetail2::where('kode_cabang', $data['kode_cabang'])->where('kode_order', $data['kode_order'])->delete();
    }

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Order Pembelian' : 'Gagal Hapus Order Pembelian';
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

  public function getDataSPK(Request $request): JsonResponse
  {
    $kd_cabang = session('kd_cabang');
    $kode_permintaan = $request->kode_permintaan;

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

    $data->kode_permintaan = $res->kode_permintaan;
    $data->tipe_barang = $res->tipe_barang;

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

  public function cetakOrderPembelian(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Purchase Order';

    $id = $request->id;

    $datas = DB::table('t_order_hdr as a')
      ->leftJoin('m_pemasok as b', function ($join) {
        $join->on('b.kode_cabang', '=', 'a.kode_cabang')
          ->on('b.kode_pemasok', '=', 'a.kode_pemasok'); // syarat di JOIN
      })
      ->leftJoin('parameter as c', function ($join) {
        $join->on('c.kode', '=', 'a.tipe_barang')
          ->where('c.nama_tabel', '=', 'TIPE_BARANG'); // syarat di JOIN
      })
      ->leftJoin('m_cabang as d', 'd.kode_cabang', '=', 'a.kode_cabang')
      ->leftJoin('t_spk_master as e', function ($join) {
        $join->on('e.kode_cabang', '=', 'a.kode_cabang')
          ->on('e.kode_spk', '=', 'a.kode_spk'); // syarat di JOIN
      })
      ->leftJoin('parameter as f', function ($join) {
        $join->on('f.kode', '=', 'a.tipe_bayar')
          ->where('f.nama_tabel', '=', 'TIPE_BAYAR'); // syarat di JOIN
      })
      ->where('a.id', $id)
      ->select([
        'a.id',
        'a.kode_cabang',
        'a.kode_order',
        'a.kode_spk',
        'a.tanggal',
        'a.no_po',
        'a.tipe_barang as kode_tipe',
        'a.tipe_bayar as kode_bayar',
        'a.kode_pemasok',
        'a.ppn',
        'a.total',
        'b.nama_pemasok',
        'c.keterangan as tipe_barang',
        'd.nama_cabang',
        'd.alamat1',
        'd.alamat2',
        'd.alamat3',
        'd.telepon',
        'd.fax',
        'e.kode_spk',
        'f.keterangan as tipe_bayar',
      ])
      ->first();

    if (blank($datas)) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }

    $datas->tanggal = date("d F Y", strtotime($datas->tanggal));
    $datas->alamat1 = sprintf("%s %s %s", $datas->alamat1, $datas->alamat2, $datas->alamat3);


    if ($datas->kode_tipe == "S") {
      $spk = DB::table('v_spk')->select('kode_spk', 'no_polisi', 'merek_tipe')->where('kode_spk', $datas->kode_spk)->first();

      ## DATA DETAIL
      $details = DB::table('t_order_dtl1 as a')
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
        ->where('b.kode_cabang', $datas->kode_cabang)
        ->where('b.kode_order', $datas->kode_order)
        ->select(['a.seq_no', 'a.kode_bahan', 'c.nama_sparepart as nama_bahan', 'a.no_sparepart', 'a.kode_satuan', 'd.keterangan as nama_satuan', 'a.qty', 'a.harga', 'a.jumlah'])
        ->orderBy('seq_no', 'asc')
        ->get();
    } else {
      $spk = null;

      ## DATA DETAIL
      $details = DB::table('t_order_dtl1 as a')
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
        ->where('b.kode_cabang', $datas->kode_cabang)
        ->where('b.kode_order', $datas->kode_order)
        ->select(['a.seq_no', 'a.kode_bahan', 'c.nama_bahan', 'a.no_sparepart', 'a.kode_satuan', 'd.keterangan as nama_satuan', 'a.qty', 'a.harga', 'a.jumlah'])
        ->orderBy('seq_no', 'asc')
        ->get();
    }

    $startDate = date("Y-m-d");
    $endDate = date("Y-m-d");
    $cekPPN = TarifPpn::where(function ($q) use ($startDate, $endDate) {
      $q->where('startdate', '<=', $endDate)
        ->where('enddate', '>=', $startDate);
    })->first();

    $ppn_persen = ($cekPPN) ? $cekPPN->ppn : 0;

    ## Log Activity
    $desc = "Cetak " . $title;
    LogActivity::saveLogActivity($desc);

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.gudang.order-pembelian-cetak', [
      'title' => $title,
      'spk' => $spk,
      'datas' => $datas,
      'ppn_persen' => $ppn_persen,
      'details' => $details,
      'pageConfigs' => $pageConfigs,
    ]);
  }

  public function cariBahan(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $search = $request->q;
    $tipe = $request->tipe_barang;

    if (empty($search)) {
      return response()->json([]);
    }

    if ($tipe == "S" || $tipe == "T") {
      $data = Sparepart::select([
        'kode_sparepart as kode_bahan',
        'nama_sparepart as nama_bahan',
      ])
        ->where('kode_cabang', $user_cabang)
        ->where('is_active', 'Y')
        ->where('nama_sparepart', 'LIKE', "%{$search}%")
        ->orderBy('nama_sparepart', 'asc')
        ->limit(10)
        ->get();
    } elseif ($tipe == "U") {
      $data = Umum::select([
        'kode_barang as kode_bahan',
        'nama_barang as nama_bahan',
      ])
        ->where('kode_cabang', $user_cabang)
        ->where('is_active', 'Y')
        ->where('nama_barang', 'LIKE', "%{$search}%")
        ->orderBy('nama_barang', 'asc')
        ->limit(10)
        ->get();
    } else {
      if ($tipe == "P") {
        $tipe = "00001";
      } elseif ($tipe == "C") {
        $tipe = "00002";
      }

      $data = Bahan::select('kode_bahan', 'nama_bahan')
        ->where('kode_cabang', $user_cabang)
        ->where('kode_group_bahan', $tipe)
        ->where('is_active', 'Y')
        ->where('nama_bahan', 'LIKE', "%{$search}%")
        ->orderBy('nama_bahan', 'asc')
        ->limit(10)
        ->get();
    }

    return response()->json($data);
  }
}
