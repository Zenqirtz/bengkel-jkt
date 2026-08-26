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
use App\Models\Spk;
use App\Models\Parameter;
use App\Models\LogActivity;
use App\Models\SaldoBahan;
use App\Models\SaldoSparepart;
use App\Models\SaldoBahanAdjust;
use App\Models\SaldoSparepartAdjust;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel; // EXPORT EXCEL
use App\Exports\StockOpnameExport;    // EXPORT EXCEL

use App\Helpers\Helpers as Helper;

class StockOpnameController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function StockOpname(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Stock Opname';

    $user_cabang = session('kd_cabang');

    $months = Helper::listMonths();
    $years = Helper::listYears();
    $tipe_barang = Parameter::query()->where('nama_tabel', 'TIPE_BARANG')->orderBy('no_urut', 'asc')->get();

    $datafilter = session('datafilter');
    if (empty($datafilter)) {
      $nama_barang = '';
      $datafilter['tipe_barang'] = '';
      $datafilter['tgl_awal'] = '';
      $datafilter['bulan'] = date("m");
      $datafilter['tahun'] = date("Y");
    } else {
      $tmp = Parameter::query()->where('kode', $datafilter['tipe'])->where('nama_tabel', 'TIPE_BARANG')->orderBy('no_urut', 'asc')->first();
      $nama_barang = $tmp->keterangan;
    }

    return view('content.gudang.stock-opname', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'months' => $months,
      'years' => $years,
      'user_cabang' => $user_cabang,
      'tipe_barang' => $tipe_barang,
      'nama_barang' => $nama_barang,
      'datafilter' => $datafilter,
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
    $totalData = 0;
    $totalFiltered = 0;
    $data = [];

    if ($request->filled('tipe')) {
      $bulan = (int) $request->bulan;
      $tahun = $request->tahun;

      if ($request->tipe == "P") {
        $columns = [
          1 => 'b.nama_bahan',
          2 => 'c.keterangan',
          3 => 'a.unit_awal',
          4 => 'a.harga_awal',
          5 => 'a.jumlah_awal',
          6 => 'a.unit_tambah',
          7 => 'a.harga_tambah',
          8 => 'a.jumlah_tambah',
          9 => 'a.unit_kurang',
          10 => 'a.harga_kurang',
          11 => 'a.jumlah_kurang',
          12 => 'a.unit_retur',
          13 => 'a.harga_retur',
          14 => 'a.jumlah_retur',
          15 => 'a.unit_akhir',
          16 => 'a.harga_akhir',
          17 => 'a.jumlah_akhir',
        ];

        $limit = (int) $request->input('length', 10);
        $start = (int) $request->input('start', 0);
        $order = $columns[$request->input('order.0.column')] ?? 'a.id';
        $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        // Base query + LEFT JOIN
        $base = DB::table('t_saldo_bahan as a')
          ->leftJoin('m_bahan as b', function ($join) {
            $join->on('b.kode_cabang', '=', 'a.kode_cabang')
              ->on('b.kode_bahan', '=', 'a.kode_bahan'); // syarat di JOIN
          })
          ->leftJoin('parameter as c', function ($join) {
            $join->on('c.kode', '=', 'b.kode_satuan')
              ->where('c.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
          })
          ->where('a.bulan', (int) $bulan)
          ->where('a.tahun', $tahun)
          ->where('a.kode_group_bahan', '00001')
          ->where('a.kode_cabang', $user_cabang)
          // saldo akhir = 0 tidak perlu ditampilkan
          ->where(function ($q) {
            $q->where('a.unit_akhir', '!=', 0)
              ->orWhere('a.harga_akhir', '!=', 0)
              ->orWhere('a.jumlah_akhir', '!=', 0);
          }); // ← tambah ini


        // Total baris tanpa filter
        $totalData = (clone $base)->count('a.id');

        // Filtering (search global)
        $query = (clone $base);
        if ($search = trim((string) $request->input('search.value'))) {
          $query->where(function ($q) use ($search) {
            $q->where('b.nama_bahan', 'like', "%{$search}%")
              ->orWhere('c.keterangan', 'like', "%{$search}%");
          });
        }

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('a.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'a.id',
            'b.nama_bahan',
            'c.keterangan as satuan',
            'a.unit_awal',
            'a.harga_awal',
            'a.jumlah_awal',
            'a.unit_tambah',
            'a.harga_tambah',
            'a.jumlah_tambah',
            'a.unit_kurang',
            'a.harga_kurang',
            'a.jumlah_kurang',
            'a.unit_retur',
            'a.harga_retur',
            'a.jumlah_retur',
            'a.unit_adjust',
            'a.harga_adjust',
            'a.jumlah_adjust',
            'a.unit_akhir',
            'a.harga_akhir',
            'a.jumlah_akhir',
            'a.unit_so',
            'a.harga_so',
            'a.jumlah_so',
            'a.unit_selisih',
            'a.jumlah_selisih',
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
            'nama_bahan' => $row->nama_bahan,
            'satuan' => $row->satuan,
            'unit_awal' => number_format($row->unit_awal, 2, '.', ','),
            'harga_awal' => number_format($row->harga_awal, 2, '.', ','),
            'jumlah_awal' => number_format($row->jumlah_awal, 2, '.', ','),
            'unit_tambah' => number_format($row->unit_tambah, 2, '.', ','),
            'harga_tambah' => number_format($row->harga_tambah, 2, '.', ','),
            'jumlah_tambah' => number_format($row->jumlah_tambah, 2, '.', ','),
            'unit_kurang' => number_format($row->unit_kurang, 2, '.', ','),
            'harga_kurang' => number_format($row->harga_kurang, 2, '.', ','),
            'jumlah_kurang' => number_format($row->jumlah_kurang, 2, '.', ','),
            'unit_retur' => number_format($row->unit_retur, 2, '.', ','),
            'harga_retur' => number_format($row->harga_retur, 2, '.', ','),
            'jumlah_retur' => number_format($row->jumlah_retur, 2, '.', ','),
            'unit_adjust' => number_format($row->unit_adjust, 2, '.', ','),
            'harga_adjust' => number_format($row->harga_adjust, 2, '.', ','),
            'jumlah_adjust' => number_format($row->jumlah_adjust, 2, '.', ','),
            'unit_akhir' => number_format($row->unit_akhir, 2, '.', ','),
            'harga_akhir' => number_format($row->harga_akhir, 2, '.', ','),
            'jumlah_akhir' => number_format($row->jumlah_akhir, 2, '.', ','),
            'unit_so' => number_format($row->unit_so, 2, '.', ','),
            'harga_so' => number_format($row->harga_so, 2, '.', ','),
            'jumlah_so' => number_format($row->jumlah_so, 2, '.', ','),
            'unit_selisih' => number_format($row->unit_selisih, 2, '.', ','),
            'jumlah_selisih' => number_format($row->jumlah_selisih, 2, '.', ','),
          ];
        }
      } elseif ($request->tipe == "C") {
        $columns = [
          1 => 'b.nama_bahan',
          2 => 'c.keterangan',
          3 => 'a.unit_awal',
          4 => 'a.harga_awal',
          5 => 'a.jumlah_awal',
          6 => 'a.unit_tambah',
          7 => 'a.harga_tambah',
          8 => 'a.jumlah_tambah',
          9 => 'a.unit_kurang',
          10 => 'a.harga_kurang',
          11 => 'a.jumlah_kurang',
          12 => 'a.unit_retur',
          13 => 'a.harga_retur',
          14 => 'a.jumlah_retur',
          15 => 'a.unit_akhir',
          16 => 'a.harga_akhir',
          17 => 'a.jumlah_akhir',
        ];

        $limit = (int) $request->input('length', 10);
        $start = (int) $request->input('start', 0);
        $order = $columns[$request->input('order.0.column')] ?? 'a.id';
        $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        // Base query + LEFT JOIN
        $base = DB::table('t_saldo_bahan as a')
          ->leftJoin('m_bahan as b', function ($join) {
            $join->on('b.kode_cabang', '=', 'a.kode_cabang')
              ->on('b.kode_bahan', '=', 'a.kode_bahan'); // syarat di JOIN
          })
          ->leftJoin('parameter as c', function ($join) {
            $join->on('c.kode', '=', 'b.kode_satuan')
              ->where('c.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
          })
          ->where('a.bulan', (int) $bulan)
          ->where('a.tahun', $tahun)
          ->where('a.kode_group_bahan', '00002')
          ->where('a.kode_cabang', $user_cabang)
          // saldo akhir = 0 tidak perlu ditampilkan
          ->where(function ($q) {
            $q->where('a.unit_akhir', '!=', 0)
              ->orWhere('a.harga_akhir', '!=', 0)
              ->orWhere('a.jumlah_akhir', '!=', 0);
          }); // ← tambah ini

        // Total baris tanpa filter
        $totalData = (clone $base)->count('a.id');

        // Filtering (search global)
        $query = (clone $base);
        if ($search = trim((string) $request->input('search.value'))) {
          $query->where(function ($q) use ($search) {
            $q->where('b.nama_bahan', 'like', "%{$search}%")
              ->orWhere('c.keterangan', 'like', "%{$search}%");
          });
        }

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('a.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'a.id',
            'b.nama_bahan',
            'c.keterangan as satuan',
            'a.unit_awal',
            'a.harga_awal',
            'a.jumlah_awal',
            'a.unit_tambah',
            'a.harga_tambah',
            'a.jumlah_tambah',
            'a.unit_kurang',
            'a.harga_kurang',
            'a.jumlah_kurang',
            'a.unit_retur',
            'a.harga_retur',
            'a.jumlah_retur',
            'a.unit_adjust',
            'a.harga_adjust',
            'a.jumlah_adjust',
            'a.unit_akhir',
            'a.harga_akhir',
            'a.jumlah_akhir',
            'a.unit_so',
            'a.harga_so',
            'a.jumlah_so',
            'a.unit_selisih',
            'a.jumlah_selisih',
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
            'nama_bahan' => $row->nama_bahan,
            'satuan' => $row->satuan,
            'unit_awal' => number_format($row->unit_awal, 2, '.', ','),
            'harga_awal' => number_format($row->harga_awal, 2, '.', ','),
            'jumlah_awal' => number_format($row->jumlah_awal, 2, '.', ','),
            'unit_tambah' => number_format($row->unit_tambah, 2, '.', ','),
            'harga_tambah' => number_format($row->harga_tambah, 2, '.', ','),
            'jumlah_tambah' => number_format($row->jumlah_tambah, 2, '.', ','),
            'unit_kurang' => number_format($row->unit_kurang, 2, '.', ','),
            'harga_kurang' => number_format($row->harga_kurang, 2, '.', ','),
            'jumlah_kurang' => number_format($row->jumlah_kurang, 2, '.', ','),
            'unit_retur' => number_format($row->unit_retur, 2, '.', ','),
            'harga_retur' => number_format($row->harga_retur, 2, '.', ','),
            'jumlah_retur' => number_format($row->jumlah_retur, 2, '.', ','),
            'unit_adjust' => number_format($row->unit_adjust, 2, '.', ','),
            'harga_adjust' => number_format($row->harga_adjust, 2, '.', ','),
            'jumlah_adjust' => number_format($row->jumlah_adjust, 2, '.', ','),
            'unit_akhir' => number_format($row->unit_akhir, 2, '.', ','),
            'harga_akhir' => number_format($row->harga_akhir, 2, '.', ','),
            'jumlah_akhir' => number_format($row->jumlah_akhir, 2, '.', ','),
            'unit_so' => number_format($row->unit_so, 2, '.', ','),
            'harga_so' => number_format($row->harga_so, 2, '.', ','),
            'jumlah_so' => number_format($row->jumlah_so, 2, '.', ','),
            'unit_selisih' => number_format($row->unit_selisih, 2, '.', ','),
            'jumlah_selisih' => number_format($row->jumlah_selisih, 2, '.', ','),
          ];
        }
      } elseif ($request->tipe == "S") {
        $columns = [
          1 => 'a.bulan',
          2 => 'a.tahun',
          3 => 'e.nama_merek',
          4 => 'd.nama_tipe',
          5 => 'a.kode_input',
          6 => 'b.nama_sparepart',
          7 => 'c.keterangan',
          8 => 'a.unit_awal',
          9 => 'a.harga_awal',
          10 => 'a.jumlah_awal',
          11 => 'a.unit_tambah',
          12 => 'a.harga_tambah',
          13 => 'a.jumlah_tambah',
          14 => 'a.unit_kurang',
          15 => 'a.harga_kurang',
          16 => 'a.jumlah_kurang',
          17 => 'a.unit_retur',
          18 => 'a.harga_retur',
          19 => 'a.jumlah_retur',
          20 => 'a.unit_akhir',
          21 => 'a.harga_akhir',
          22 => 'a.jumlah_akhir',
        ];

        $limit = (int) $request->input('length', 10);
        $start = (int) $request->input('start', 0);
        $order = $columns[$request->input('order.0.column')] ?? 'a.id';
        $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        // Base query + LEFT JOIN
        $base = DB::table('t_saldo_sparepart as a')
          ->leftJoin('m_sparepart as b', function ($join) {
            $join->on('b.kode_cabang', '=', 'a.kode_cabang')
              ->on('b.kode_sparepart', '=', 'a.kode_sparepart'); // syarat di JOIN
          })
          ->leftJoin('parameter as c', function ($join) {
            $join->on('c.kode', '=', 'b.kode_satuan')
              ->where('c.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
          })
          ->leftJoin('m_tipe_kendaraan as d', function ($join) {
            $join->on('d.kode_merek', '=', 'a.kode_merek')
              ->on('d.kode_tipe', '=', 'a.kode_tipe'); // syarat di JOIN
          })
          ->leftJoin('m_merek_kendaraan as e', 'e.kode_merek', '=', 'a.kode_merek')
          ->where('a.periode_bulan', (int) $bulan)
          ->where('a.periode_tahun', $tahun)
          ->where('a.kode_cabang', $user_cabang)
          // saldo akhir = 0 tidak perlu ditampilkan
          ->where(function ($q) {
            $q->where('a.unit_akhir', '!=', 0)
              ->orWhere('a.harga_akhir', '!=', 0)
              ->orWhere('a.jumlah_akhir', '!=', 0);
          }); // ← tambah ini

        // Total baris tanpa filter
        $totalData = (clone $base)->count('a.id');

        // Filtering (search global)
        $query = (clone $base);
        if ($search = trim((string) $request->input('search.value'))) {
          $query->where(function ($q) use ($search) {
            $q->where('b.nama_sparepart', 'like', "%{$search}%")
              ->orWhere('e.nama_merek', 'like', "%{$search}%")
              ->orWhere('d.nama_tipe', 'like', "%{$search}%")
              ->orWhere('a.kode_input', 'like', "%{$search}%")
              ->orWhere('c.keterangan', 'like', "%{$search}%");
          });
        }

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('a.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'a.id',
            'a.bulan',
            'a.tahun',
            'e.nama_merek',
            'd.nama_tipe',
            'a.kode_input',
            'b.nama_sparepart',
            'c.keterangan as satuan',
            'a.unit_awal',
            'a.harga_awal',
            'a.jumlah_awal',
            'a.unit_tambah',
            'a.harga_tambah',
            'a.jumlah_tambah',
            'a.unit_kurang',
            'a.harga_kurang',
            'a.jumlah_kurang',
            'a.unit_retur',
            'a.harga_retur',
            'a.jumlah_retur',
            'a.unit_adjust',
            'a.harga_adjust',
            'a.jumlah_adjust',
            'a.unit_akhir',
            'a.harga_akhir',
            'a.jumlah_akhir',
            'a.unit_so',
            'a.harga_so',
            'a.jumlah_so',
            'a.unit_selisih',
            'a.jumlah_selisih',
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
            'bulan' => $row->bulan,
            'tahun' => $row->tahun,
            'nama_merek' => $row->nama_merek,
            'nama_tipe' => $row->nama_tipe,
            'kode_input' => $row->kode_input,
            'nama_sparepart' => $row->nama_sparepart,
            'satuan' => $row->satuan,
            'unit_awal' => number_format($row->unit_awal, 2, '.', ','),
            'harga_awal' => number_format($row->harga_awal, 2, '.', ','),
            'jumlah_awal' => number_format($row->jumlah_awal, 2, '.', ','),
            'unit_tambah' => number_format($row->unit_tambah, 2, '.', ','),
            'harga_tambah' => number_format($row->harga_tambah, 2, '.', ','),
            'jumlah_tambah' => number_format($row->jumlah_tambah, 2, '.', ','),
            'unit_kurang' => number_format($row->unit_kurang, 2, '.', ','),
            'harga_kurang' => number_format($row->harga_kurang, 2, '.', ','),
            'jumlah_kurang' => number_format($row->jumlah_kurang, 2, '.', ','),
            'unit_retur' => number_format($row->unit_retur, 2, '.', ','),
            'harga_retur' => number_format($row->harga_retur, 2, '.', ','),
            'jumlah_retur' => number_format($row->jumlah_retur, 2, '.', ','),
            'unit_adjust' => number_format($row->unit_adjust, 2, '.', ','),
            'harga_adjust' => number_format($row->harga_adjust, 2, '.', ','),
            'jumlah_adjust' => number_format($row->jumlah_adjust, 2, '.', ','),
            'unit_akhir' => number_format($row->unit_akhir, 2, '.', ','),
            'harga_akhir' => number_format($row->harga_akhir, 2, '.', ','),
            'jumlah_akhir' => number_format($row->jumlah_akhir, 2, '.', ','),
            'unit_so' => number_format($row->unit_so, 2, '.', ','),
            'harga_so' => number_format($row->harga_so, 2, '.', ','),
            'jumlah_so' => number_format($row->jumlah_so, 2, '.', ','),
            'unit_selisih' => number_format($row->unit_selisih, 2, '.', ','),
            'jumlah_selisih' => number_format($row->jumlah_selisih, 2, '.', ','),
          ];
        }
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
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $dataID = $request->id;
    if ($dataID) {
      $unit_adjust = str_replace([","], "", $request->unit_adjust);

      if($unit_adjust <= 0) {
        return response()->json([
          'status' => false,
          'message' => "Qty Adjust harus lebih besar dari 0"
        ], 200);
      }

      if($request->tipe == "S" || $request->tipe == "T") {
        $res = SaldoSparepart::findOrFail($dataID);

        $data = [
          'unit_adjust' => str_replace([","], "", $request->unit_adjust),
          'harga_adjust' => str_replace([","], "", $request->harga_adjust),
          'jumlah_adjust' => str_replace([","], "", $request->jumlah_adjust),
          // 'unit_akhir' => str_replace([","], "", $request->unit_adjust),
          // 'harga_akhir' => str_replace([","], "", $request->harga_adjust),
          // 'jumlah_akhir' => str_replace([","], "", $request->jumlah_adjust),
          'updated_by' => Auth::user()->username
        ];

        $result = $res->update($data);

        if($result) {
          $data2 = [
            'kode_cabang' => $res->kode_cabang,
            'tanggal' => date("Y-m-d H:i:s"),
            'bulan' => $res->bulan,
            'tahun' => $res->tahun,
            'kode_merek' => $res->kode_merek,
            'kode_tipe' => $res->kode_tipe,
            'kode_input' => $res->kode_input,
            'kode_sparepart' => $res->kode_sparepart,
            'unit_adjust' => str_replace([","], "", $request->unit_adjust),
            'harga_adjust' => str_replace([","], "", $request->harga_adjust),
            'jumlah_adjust' => str_replace([","], "", $request->jumlah_adjust),
            'created_by' => Auth::user()->username,
            'updated_by' => Auth::user()->username
          ];

          SaldoSparepartAdjust::create($data2);
        }

        ## Log Activity
        $desc = $result ? 'Berhasil Adjust Saldo Sparepart' : 'Gagal Adjust Saldo Sparepart';
        LogActivity::saveLogActivity($desc, $data);

        return response()->json([
          'status'  => (bool)$result,
          'message' => $desc
        ]);

      } else {
        $res = SaldoBahan::findOrFail($dataID);

        $data = [
          'unit_adjust' => str_replace([","], "", $request->unit_adjust),
          'harga_adjust' => str_replace([","], "", $request->harga_adjust),
          'jumlah_adjust' => str_replace([","], "", $request->jumlah_adjust),
          // 'unit_akhir' => str_replace([","], "", $request->unit_adjust),
          // 'harga_akhir' => str_replace([","], "", $request->harga_adjust),
          // 'jumlah_akhir' => str_replace([","], "", $request->jumlah_adjust),
          'updated_by' => Auth::user()->username
        ];

        $result = $res->update($data);

        if($result) {
          $data2 = [
            'kode_cabang' => $res->kode_cabang,
            'tanggal' => date("Y-m-d H:i:s"),
            'bulan' => $res->bulan,
            'tahun' => $res->tahun,
            'kode_bahan' => $res->kode_bahan,
            'kode_group_bahan' => $res->kode_group_bahan,
            'unit_adjust' => str_replace([","], "", $request->unit_adjust),
            'harga_adjust' => str_replace([","], "", $request->harga_adjust),
            'jumlah_adjust' => str_replace([","], "", $request->jumlah_adjust),
            'created_by' => Auth::user()->username,
            'updated_by' => Auth::user()->username
          ];

          SaldoBahanAdjust::create($data2);
        }

        ## Log Activity
        $desc = $result ? 'Berhasil Adjust Saldo Bahan' : 'Gagal Adjust Saldo Bahan';
        LogActivity::saveLogActivity($desc, $data);

        return response()->json([
          'status'  => (bool)$result,
          'message' => $desc
        ]);
      }
    } else {
      $validatedData = $request->validate(
        [
          'tipe' => 'required',
          'bulan' => 'required',
          'tahun' => 'required',
        ],
        [ // custom messages
          'tipe.required' => 'Tipe Barang diisi.',
          'bulan.required' => 'Periode Bulan wajib diisi.',
          'tahun.required' => 'Periode Tahun wajib diisi.',
        ]
      );
  
      $dataArray['tipe'] = $request->tipe;
      $dataArray['bulan'] = $request->bulan;
      $dataArray['tahun'] = $request->tahun;
  
      ## Log Activity
      $desc = "View Stock Opname";
      LogActivity::saveLogActivity($desc, $dataArray);
  
      return redirect('gudang/stock-opname')->with('datafilter', $dataArray);
    }
  }

  /**
   * Export data to Excel.
   */
  public function exportExcel(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    // Siapkan struktur data cabang
    $cabangData = [
      'kode' => $user_cabang,
      'nama' => $namaCabang
    ];
    // -----------------------------------------------------

    $filters = $request->all();

    // --- TAMBAHAN: Format String Periode ---

    $months = Helper::listMonths();
    $periodeStr = $months[$request->bulan] . ' ' . $request->tahun;
    // ---------------------------------------

    if ($request->tipe == "P") {
      $fileName = 'Stock_Saldo_Bahan_' . date('Ymd_His') . '.xlsx';
    } elseif ($request->tipe == "C") {
      $fileName = 'Stock_Saldo_Cat_' . date('Ymd_His') . '.xlsx';
    } elseif ($request->tipe == "S") {
      $fileName = 'Stock_Saldo_Sparepart_' . date('Ymd_His') . '.xlsx';
    }

    ## Log Activity
    $desc = "Export Stock Opname";
    LogActivity::saveLogActivity($desc, $filters);

    return Excel::download(new StockOpnameExport($filters, $cabangData, $periodeStr), $fileName);
  }

  public function konsolidasiSaldo(Request $request)
  {

    $user_cabang = session('kd_cabang');
    try {

      $rules = [
        'tipe' => 'required',
        'bulan' => 'required',
        'tahun' => 'required',
      ];
  
      $messages = [
        'tipe.required' => 'Tipe Barang Wajib diisi',
        'bulan.required' => 'Periode Bulan Wajib diisi',
        'tahun.required'  => 'Periode Tahun Wajib diisi',
      ];
  
      $validator = Validator::make($request->all(), $rules, $messages);
  
      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ], 200);
      }
      
      if($request->tipe == "P" || $request->tipe == "C") {
        $bahan = ($request->tipe == "P") ? "00001" : "00002";

        $cek = SaldoBahan::where('kode_cabang', $user_cabang)
        ->where('kode_group_bahan', $bahan)
        ->where('bulan', $request->bulan)
        ->where('tahun', $request->tahun)
        ->count();

        if($cek <= 0) {
          return response()->json([
            'status' => false,
            'message' => "Saldo Stock bahan belum terbentuk!"
          ], 200);
        }

        
        $tglawal = sprintf("%04d-%02d-01", $request->tahun, $request->bulan);
        $tglakhir = sprintf("%04d-%02d-%02d", $request->tahun, $request->bulan, date("t"));
        
        $results = DB::select('CALL up_apl_rekonsiliasi_bahan(?, ?, ?, ?)', [
          $user_cabang, $tglawal, $tglakhir, Auth::user()->username
        ]);

        $data = $results[0];

        $status = ($data->status == "SUCCESS") ? true : false;
        LogActivity::saveLogActivity($data->message, (array)$data);
  
        return response()->json([
          'status'  => $status,
          'message' => $data->message
        ]);

      } elseif($request->tipe == "S") {

        $cek = SaldoSparepart::where('kode_cabang', $user_cabang)
        ->where('bulan', $request->bulan)
        ->where('tahun', $request->tahun)
        ->count();

        if($cek <= 0) {
          return response()->json([
            'status' => false,
            'message' => "Saldo Stock sparepart belum terbentuk!"
          ], 200);
        }

        $tglawal = sprintf("%04d-%02d-01", $request->tahun, $request->bulan);
        $tglakhir = sprintf("%04d-%02d-%02d", $request->tahun, $request->bulan, date("t"));

        $results = DB::select('CALL up_apl_rekonsiliasi_sparepart(?, ?, ?, ?)', [
          $user_cabang, $tglawal, $tglakhir, Auth::user()->username
        ]);

        $data = $results[0];

        $status = ($data->status == "SUCCESS") ? true : false;
        LogActivity::saveLogActivity($data->message, (array)$data);
  
        return response()->json([
          'status'  => $status,
          'message' => $data->message
        ]);

      } else {
        return response()->json([
          'status' => false,
          'message' => "Tutup Buku Gagal: Tipe barang tidak ada"
        ], 200);
      }

    } catch (\Exception $e) {
      // Tangkap error jika terjadi masalah saat insert ke database
      return response()->json([
          'status' => false,
          'message' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 200);
    }
  }

  public function getDataSaldo(Request $request): JsonResponse
  {
    $kd_cabang = session('kd_cabang');
    $tipe = $request->tipe;
    $kode = $request->kode;

    if($tipe == "S" || $tipe == "T") {
      // $data = SaldoSparepart::find($kode);

      $data = DB::table('t_saldo_sparepart as a')
      ->leftJoin('m_sparepart as b', function ($join) {
      $join->on('b.kode_cabang', '=', 'a.kode_cabang')
        ->on('b.kode_sparepart', '=', 'a.kode_sparepart'); // syarat di JOIN
      })
      ->leftJoin('parameter as c', function ($join) {
      $join->on('c.kode', '=', 'b.kode_satuan')
        ->where('c.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
      })
      ->leftJoin('m_tipe_kendaraan as d', function ($join) {
      $join->on('d.kode_merek', '=', 'a.kode_merek')
        ->on('d.kode_tipe', '=', 'a.kode_tipe'); // syarat di JOIN
      })
      ->leftJoin('m_merek_kendaraan as e', 'e.kode_merek', '=', 'a.kode_merek')
      ->where('a.id', $kode)
      ->select([
        'a.id',
        'a.kode_cabang',
        'a.kode_input',
        'a.kode_sparepart as kode_bahan',
        'a.bulan',
        'a.tahun',
        'e.nama_merek',
	      'd.nama_tipe',
        'b.nama_sparepart as nama_bahan',
        'c.keterangan as satuan',
        'a.unit_adjust',
        'a.harga_adjust',
        'a.jumlah_adjust',
        'a.unit_akhir',
        'a.harga_akhir',
        'a.jumlah_akhir',
      ])
      ->first();

      if(!$data || blank($data)) {
        $result = false;
        return response()->json([
          'status'  => (bool)$result,
          'message' => 'Data Saldo Sparepart tidak ditemukan'
        ]);
      }

      $dataAdjust = SaldoSparepartAdjust::where('bulan', $data->bulan)
      ->where('tahun', $data->tahun)
      ->where('kode_cabang', $data->kode_cabang)
      ->where('kode_input', $data->kode_input)
      ->where('kode_sparepart', $data->kode_bahan)
      ->orderBy('tanggal', 'desc')
      ->first();
      
      if($dataAdjust) {
        $data->unit_adjust = number_format($dataAdjust->unit_adjust, 2, '.', ',');
        $data->harga_adjust = number_format($dataAdjust->harga_adjust, 2, '.', ',');
        $data->jumlah_adjust = number_format($dataAdjust->jumlah_adjust, 2, '.', ',');
      } else {
        $data->unit_adjust = number_format($data->unit_akhir, 2, '.', ',');
        $data->harga_adjust = number_format($data->harga_akhir, 2, '.', ',');
        $data->jumlah_adjust = number_format($data->jumlah_akhir, 2, '.', ',');
      }

      $data->unit_akhir = number_format($data->unit_akhir, 2, '.', ',');
      $data->harga_akhir = number_format($data->harga_akhir, 2, '.', ',');
      $data->jumlah_akhir = number_format($data->jumlah_akhir, 2, '.', ',');

    } else {
      // $data = SaldoBahan::find($kode);
      $data = DB::table('t_saldo_bahan as a')
      ->leftJoin('m_bahan as b', function ($join) {
        $join->on('b.kode_cabang', '=', 'a.kode_cabang')
          ->on('b.kode_bahan', '=', 'a.kode_bahan'); // syarat di JOIN
      })
      ->leftJoin('parameter as c', function ($join) {
        $join->on('c.kode', '=', 'b.kode_satuan')
          ->where('c.nama_tabel', '=', 'SATUAN'); // syarat di JOIN
      })
      ->where('a.id', $kode)
      ->select([
        'a.id',
        'a.kode_cabang',
        'a.kode_bahan',
        'a.bulan',
        'a.tahun',
        'b.nama_bahan',
        'c.keterangan as satuan',
        'a.unit_adjust',
        'a.harga_adjust',
        'a.jumlah_adjust',
        'a.unit_akhir',
        'a.harga_akhir',
        'a.jumlah_akhir',
      ])
      ->first();

      if(!$data || blank($data)) {
        $result = false;
        return response()->json([
          'status'  => (bool)$result,
          'message' => 'Data Saldo Bahan tidak ditemukan'
        ]);
      }

      $dataAdjust = SaldoBahanAdjust::where('bulan', $data->bulan)
      ->where('tahun', $data->tahun)
      ->where('kode_cabang', $data->kode_cabang)
      ->where('kode_bahan', $data->kode_bahan)
      ->orderBy('tanggal', 'desc')
      ->first();
      
      if($dataAdjust) {
        $data->unit_adjust = number_format($dataAdjust->unit_adjust, 2, '.', ',');
        $data->harga_adjust = number_format($dataAdjust->harga_adjust, 2, '.', ',');
        $data->jumlah_adjust = number_format($dataAdjust->jumlah_adjust, 2, '.', ',');
      } else {
        $data->unit_adjust = number_format($data->unit_akhir, 2, '.', ',');
        $data->harga_adjust = number_format($data->harga_akhir, 2, '.', ',');
        $data->jumlah_adjust = number_format($data->jumlah_akhir, 2, '.', ',');
      }

      $data->unit_akhir = number_format($data->unit_akhir, 2, '.', ',');
      $data->harga_akhir = number_format($data->harga_akhir, 2, '.', ',');
      $data->jumlah_akhir = number_format($data->jumlah_akhir, 2, '.', ',');
    }

    $result = true;
    return response()->json([
      'status'  => (bool)$result,
      'message' => 'Berhasil Data Saldo',
      'data' => $data
    ]);
  }

}
