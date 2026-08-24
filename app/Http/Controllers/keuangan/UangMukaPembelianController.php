<?php

namespace App\Http\Controllers\keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Bank;
use App\Models\Parameter;
use App\Models\UangMukaPembelian;
use App\Models\LogActivity;
use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class UangMukaPembelianController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function UangMukaPembelian(): View
  {
    $isList = Helper::AuthIsPerm("list");
    $isAdd = Helper::AuthIsPerm("add");
    $isEdit = Helper::AuthIsPerm("edit");
    $isDel = Helper::AuthIsPerm("delete");

    if (!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', compact('pageConfigs'));
    }

    $user_cabang = session('kd_cabang');
    $path = request()->path();
    $title = Helper::getTitleMenu($path) ?? 'Uang Muka Pembelian';

    // Ambil semua bank aktif untuk dropdown Keluar Kas/Bank
    $bank = Bank::query()
      ->select('kode_bank', 'nama_bank', 'no_rekening')
      ->where('kode_cabang', $user_cabang)
      ->where('is_active', 'Y')
      ->orderBy('nama_bank', 'asc')
      ->get();

    $bankRekening = $bank->map(fn($b) => [
      'kode_bank' => $b->kode_bank,
      'nama_bank' => $b->nama_bank,
      'no_rekening' => $b->no_rekening,
    ])->values();

    // Ambil jenis pengeluaran dari tabel parameter
    $jenisPengeluaran = Parameter::query()
      ->select('kode', 'keterangan')
      ->where('nama_tabel', 'JENIS_PENGELUARAN_UMB')
      ->orderBy('no_urut', 'asc')
      ->get();

    LogActivity::saveLogActivity("View {$title}");

    return view('content.keuangan.uang-muka-pembelian', compact(
      'title',
      'isList',
      'isAdd',
      'isEdit',
      'isDel',
      'bank',
      'bankRekening',
      'jenisPengeluaran'
    ));
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request): JsonResponse
  {
    $user_cabang = session('kd_cabang');

    if ($request->filled('rekening') && $request->filled('kode_bank')) {
      $data = DB::table('m_bank_fin')
        ->select('no_rekening')
        ->where('kode_cabang', $user_cabang)
        ->where('kode_bank', $request->kode_bank)
        ->where('is_active', 'Y')
        ->get();
      return response()->json($data);
    }

    try {
      $columns = [
        1 => 'a.id',
        2 => 'a.tanggal_transaksi',
        3 => 'a.no_transaksi',
        4 => 'a.jenis_pengeluaran',
        5 => 'a.nama',
        6 => 'b.nama_bank',
        7 => 'a.no_rekening',
        8 => 'a.nilai',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.id';
      $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      $base = DB::table('t_uang_muka_pembelian as a')
        // ->leftJoin('m_bank_fin as b', 'b.kode_bank', '=', 'a.kode_bank')
        ->leftJoin('m_bank_fin as b', function ($join) {
          $join->on('b.kode_bank', '=', 'a.kode_bank')
            ->on('b.kode_cabang', '=', 'a.kode_cabang'); // syarat di JOIN
        })
        ->where('a.kode_cabang', $user_cabang);

      $totalData = (clone $base)->count('a.id');
      $query = clone $base;

      if ($request->filled('no_transaksi')) {
        $query->where('a.no_transaksi', 'like', '%' . $request->no_transaksi . '%');
      }
      if ($request->filled('nama')) {
        $query->where('a.nama', 'like', '%' . $request->nama . '%');
      }
      if ($request->filled('jenis_pengeluaran') && $request->jenis_pengeluaran !== 'all') {
        $query->where('a.jenis_pengeluaran', $request->jenis_pengeluaran);
      }
      if ($request->filled('kode_bank') && $request->kode_bank !== 'all') {
        $query->where('a.kode_bank', $request->kode_bank);
      }
      if ($request->filled('tanggal_awal')) {
        $startDate = Carbon::createFromFormat('d/m/Y', $request->tanggal_awal, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('a.tanggal_transaksi', '>=', $startDate);
      }
      if ($request->filled('tanggal_akhir')) {
        $endDate = Carbon::createFromFormat('d/m/Y', $request->tanggal_akhir, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('a.tanggal_transaksi', '<=', $endDate);
      }

      $totalFiltered = (clone $query)->count('a.id');

      $datas = $query
        ->select([
          'a.id',
          'a.no_transaksi',
          'a.tanggal_transaksi',
          'a.jenis_pengeluaran',
          'a.nama',
          'a.kode_bank',
          'b.nama_bank',
          'a.no_rekening',
          'a.nilai',
        ])
        ->orderBy($order, $dir)
        ->offset($start)
        ->limit($limit)
        ->get();

      $data = [];
      $fake = $start;
      foreach ($datas as $row) {
        $data[] = [
          'id' => $row->id,
          'fake_id' => ++$fake,
          'no_transaksi' => $row->no_transaksi,
          'tanggal_transaksi' => blank($row->tanggal_transaksi) ? '' : date('d/m/Y', strtotime($row->tanggal_transaksi)),
          'jenis_pengeluaran' => $row->jenis_pengeluaran,
          'nama' => $row->nama,
          'kode_bank' => $row->kode_bank,
          'nama_bank' => $row->nama_bank,
          'no_rekening' => $row->no_rekening,
          'nilai' => number_format($row->nilai, 0, '.', ','),
        ];
      }

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
  public function store(Request $request): JsonResponse
  {
    try {
      $user_cabang = session('kd_cabang');
      $dataID = $request->id;

      $rules = [
        'tanggal_transaksi' => 'required',
        'jenis_pengeluaran' => 'required',
        'nama' => 'required|string|max:100',
        'kode_bank' => 'required',
        'no_rekening' => 'required',
        'nilai' => 'required',
      ];

      $messages = [
        'tanggal_transaksi.required' => 'Tanggal wajib diisi',
        'jenis_pengeluaran.required' => 'Jenis Pengeluaran wajib dipilih',
        'nama.required' => 'Nama wajib diisi',
        'kode_bank.required' => 'Keluar Kas/Bank wajib dipilih',
        'no_rekening.required' => 'No. Rekening wajib dipilih',
        'nilai.required' => 'Nilai wajib diisi',
      ];

      $validator = Validator::make($request->all(), $rules, $messages);
      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => 'Gagal menyimpan data.',
          'errors' => $validator->errors(),
        ]);
      }

      $tanggal = blank($request->tanggal_transaksi)
        ? null
        : Carbon::createFromFormat('d/m/Y', $request->tanggal_transaksi, 'Asia/Jakarta')->format('Y-m-d');

      $nilai = (float) str_replace([',', '.'], ['', ''], $request->nilai);

      if ($dataID) {
        // === UPDATE ===
        $data = [
          'tanggal_transaksi' => $tanggal,
          'jenis_pengeluaran' => $request->jenis_pengeluaran,
          'nama' => strtoupper($request->nama),
          'kode_bank' => $request->kode_bank,
          'no_rekening' => $request->no_rekening,
          'nilai' => $nilai,
          'updated_by' => Auth::user()->username,
        ];

        $ok = UangMukaPembelian::updateOrCreate(['id' => $dataID], $data);
        $desc = $ok ? 'Berhasil Ubah Uang Muka Pembelian' : 'Gagal Ubah Uang Muka Pembelian';
      } else {
        // === INSERT ===
        $noTransaksi = Helper::getNomorTransaksi($user_cabang, 'UMB');

        $cek = UangMukaPembelian::where('kode_cabang', $user_cabang)
          ->where('no_transaksi', $noTransaksi)->first();
        if ($cek) {
          return response()->json(['status' => false, 'message' => 'Nomor transaksi sudah digunakan.']);
        }

        $data = [
          'kode_cabang' => $user_cabang,
          'no_transaksi' => $noTransaksi,
          'tanggal_transaksi' => $tanggal,
          'jenis_pengeluaran' => $request->jenis_pengeluaran,
          'nama' => strtoupper($request->nama),
          'kode_bank' => $request->kode_bank,
          'no_rekening' => $request->no_rekening,
          'nilai' => $nilai,
          'created_by' => Auth::user()->username,
        ];

        $ok = UangMukaPembelian::create($data);

        if ($ok) {
          Helper::updateNomorTransaksi($user_cabang, 'UMB');
        }

        $desc = $ok ? 'Berhasil Tambah Uang Muka Pembelian' : 'Gagal Tambah Uang Muka Pembelian';
      }

      LogActivity::saveLogActivity($desc, $data);

      return response()->json(['status' => (bool) $ok, 'message' => $desc]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
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
    $data = DB::table('t_uang_muka_pembelian as a')
      // ->leftJoin('m_bank_fin as b', 'b.kode_bank', '=', 'a.kode_bank')
      ->leftJoin('m_bank_fin as b', function ($join) {
        $join->on('b.kode_bank', '=', 'a.kode_bank')
          ->on('b.kode_cabang', '=', 'a.kode_cabang'); // syarat di JOIN
      })
      ->where('a.id', $id)
      ->select([
        'a.id',
        'a.no_transaksi',
        'a.tanggal_transaksi',
        'a.jenis_pengeluaran',
        'a.nama',
        'a.kode_bank',
        'b.nama_bank',
        'a.no_rekening',
        'a.nilai',
      ])
      ->first();

    if (blank($data)) {
      return response()->json(['status' => false, 'message' => 'Data tidak ditemukan.']);
    }

    $data->tanggal_transaksi = blank($data->tanggal_transaksi)
      ? '' : date('d/m/Y', strtotime($data->tanggal_transaksi));

    $data->nilai_raw = (float) $data->nilai;
    $data->nilai = number_format($data->nilai, 0, '.', ',');

    return response()->json(['status' => true, 'message' => 'Berhasil', 'data' => $data]);
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
    //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $data = UangMukaPembelian::where('id', $id)->first();
    if (!$data)
      return;

    $arr = $data->toArray();
    $ok = $data->delete();

    $desc = $ok ? 'Berhasil Hapus Uang Muka Pembelian' : 'Gagal Hapus Uang Muka Pembelian';
    LogActivity::saveLogActivity($desc, $arr);
  }
}
