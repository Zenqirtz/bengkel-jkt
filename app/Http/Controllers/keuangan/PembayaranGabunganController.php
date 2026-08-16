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
use App\Models\PembayaranGabungan;
use App\Models\PembayaranGabunganDetail;
use App\Models\LogActivity;
use Carbon\Carbon;

class PembayaranGabunganController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function PembayaranGabungan(): View
  {
    $isList = \Helper::AuthIsPerm("list");
    $isAdd = \Helper::AuthIsPerm("add");
    $isEdit = \Helper::AuthIsPerm("edit");
    $isDel = \Helper::AuthIsPerm("delete");

    if (!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', compact('pageConfigs'));
    }

    $path = request()->path();
    $title = \Helper::getTitleMenu($path) ?? 'Pembayaran Gabungan';

    $bank = Bank::query()
      ->select('kode_bank', 'nama_bank', 'no_rekening')
      ->where('is_active', 'Y')
      ->orderBy('kode_bank', 'asc')
      ->get();

    $bankRekening = $bank->map(fn($b) => [
      'kode_bank' => $b->kode_bank,
      'nama_bank' => $b->nama_bank,
      'no_rekening' => $b->no_rekening,
    ])->values();

    $jenisPembayaran = Parameter::query()
      ->select('kode', 'keterangan')
      ->where('nama_tabel', 'JENIS_PENGELUARAN_UMB')
      ->orderBy('no_urut', 'asc')
      ->get();

    LogActivity::saveLogActivity("View {$title}");

    return view('content.keuangan.pembayaran-gabungan', compact(
      'title',
      'isList',
      'isAdd',
      'isEdit',
      'isDel',
      'bank',
      'bankRekening',
      'jenisPembayaran'
    ));
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request): JsonResponse
  {
    // ── REKENING BY BANK ──────────────────────────────────────────
    if ($request->filled('rekening') && $request->filled('kode_bank')) {
      $data = DB::table('m_bank_fin')
        ->select('no_rekening')
        ->where('kode_bank', $request->kode_bank)
        ->where('is_active', 'Y')
        ->get();
      return response()->json($data);
    }

    // ── SUPPLIER BY JENIS PEMBAYARAN ──────────────────────────────
    // ── SUPPLIER BY JENIS PEMBAYARAN ──────────────────────────────
    if ($request->filled('get_supplier') && $request->filled('jenis_pembayaran')) {
      $user_cabang = session('kd_cabang');
      $jenis = strtoupper(trim($request->jenis_pembayaran));

      // Mapping keterangan → kode tipe t_input_gudang_hdr
      $tipeMap = [
        'PEMBELIAN PART' => 'S',
        'PEMBELIAN BAHAN' => 'P',
        'PEMBELIAN CAT' => 'C',
      ];

      $kodeTipe = $tipeMap[$jenis] ?? null;

      $query = DB::table('m_pemasok as p')
        ->select('p.kode_pemasok', 'p.nama_pemasok')
        ->where('p.kode_cabang', $user_cabang)
        ->where('p.is_active', 'Y')
        ->whereExists(function ($sub) use ($user_cabang, $kodeTipe) {
          $sub->select(DB::raw(1))
            ->from('t_input_gudang_hdr as ig')
            ->where('ig.kode_cabang', $user_cabang)
            ->whereColumn('ig.kode_pemasok', 'p.kode_pemasok')
            ->when($kodeTipe, fn($q) => $q->where('ig.tipe', $kodeTipe));
        })
        ->orderBy('p.nama_pemasok', 'asc');

      return response()->json($query->get());
    }

    // ── IG LIST (filter by kode_pemasok) ─────────────────────────
    if ($request->filled('get_ig')) {
      $user_cabang = session('kd_cabang');
      $search = $request->input('search.value', '');
      $kode_pemasok = $request->input('kode_pemasok', '');
      $jenis = strtoupper(trim($request->input('jenis_pembayaran', '')));
      $current_id = $request->input('current_id', null);

      if (is_array($search) || empty($search))
        $search = '';

      $tipeMap = [
        'PEMBELIAN PART' => 'S',
        'PEMBELIAN BAHAN' => 'P',
        'PEMBELIAN CAT' => 'C',
      ];
      $kodeTipe = $tipeMap[$jenis] ?? null;

      // ── Mapping kolom index → nama kolom DB ──
      $igColumns = [
        1 => 'ig.tanggal',
        2 => 'ig.kode_input',
        3 => 'ig.no_bon',
        4 => 'ig.total',
      ];

      $orderColIndex = (int) $request->input('order.0.column', 1);
      $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
      $orderCol = $igColumns[$orderColIndex] ?? 'ig.kode_input';

      try {
        $query = DB::table('t_input_gudang_hdr as ig')
          ->leftJoin('m_pemasok as p', function ($j) {
            $j->on('p.kode_cabang', '=', 'ig.kode_cabang')
              ->on('p.kode_pemasok', '=', 'ig.kode_pemasok');
          })
          ->select(
            'ig.kode_input as no_ig',
            'ig.tanggal as tgl_input',
            'ig.no_bon',
            DB::raw("COALESCE(p.nama_pemasok, '') as nama_supplier"),
            DB::raw("COALESCE(ig.total, 0) as total")
          )
          ->where('ig.kode_cabang', $user_cabang)
          ->when($kode_pemasok, fn($q) => $q->where('ig.kode_pemasok', $kode_pemasok))
          ->when($kodeTipe, fn($q) => $q->where('ig.tipe', $kodeTipe))
          ->whereNotIn('ig.kode_input', function ($sub) use ($user_cabang, $current_id) {
            $sub->select('dtl.kode_input')
              ->from('t_pembayaran_gabungan_dtl as dtl')
              ->join('t_pembayaran_gabungan_hdr as hdr', 'hdr.id', '=', 'dtl.id_header')
              ->where('hdr.kode_cabang', $user_cabang)
              ->when($current_id, fn($q) => $q->where('hdr.id', '!=', $current_id));
          });

        if ($search) {
          $query->where(function ($q) use ($search) {
            $q->where('ig.kode_input', 'like', "%{$search}%")
              ->orWhere('ig.no_bon', 'like', "%{$search}%");
          });
        }

        $total = (clone $query)->count();
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $data = $query
          ->orderBy($orderCol, $orderDir)      // ← dynamic sort
          ->offset($start)
          ->limit($length)
          ->get();

        return response()->json([
          'draw' => intval($request->input('draw')),
          'recordsTotal' => $total,
          'recordsFiltered' => $total,
          'data' => $data,
        ]);

      } catch (\Exception $e) {
        return response()->json([
          'draw' => intval($request->input('draw')),
          'recordsTotal' => 0,
          'recordsFiltered' => 0,
          'data' => [],
          'error' => $e->getMessage()
        ]);
      }
    }

    // ── LISTING UTAMA ─────────────────────────────────────────────
    $user_cabang = session('kd_cabang');

    try {
      $columns = [
        1 => 'a.id',
        2 => 'a.tanggal_transaksi',
        3 => 'a.no_transaksi',
        4 => 'a.jenis_pembayaran',
        5 => 'a.nama_supplier',
        6 => 'b.nama_bank',
        7 => 'a.no_rekening',
        8 => 'a.total_nilai',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.id';
      $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      $base = DB::table('t_pembayaran_gabungan_hdr as a')
        ->leftJoin('m_bank_fin as b', 'b.kode_bank', '=', 'a.kode_bank')
        ->where('a.kode_cabang', $user_cabang);

      $totalData = (clone $base)->count('a.id');
      $query = clone $base;

      if ($request->filled('no_transaksi')) {
        $query->where('a.no_transaksi', 'like', '%' . $request->no_transaksi . '%');
      }
      if ($request->filled('nama_supplier')) {
        $query->where('a.nama_supplier', 'like', '%' . $request->nama_supplier . '%');
      }
      if ($request->filled('jenis_pembayaran') && $request->jenis_pembayaran !== 'all') {
        $query->where('a.jenis_pembayaran', $request->jenis_pembayaran);
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
          'a.jenis_pembayaran',
          'a.kode_pemasok',
          'a.nama_supplier',
          'a.kode_bank',
          'b.nama_bank',
          'a.no_rekening',
          'a.total_nilai',
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
          'tanggal_transaksi' => blank($row->tanggal_transaksi)
            ? '' : date('d/m/Y', strtotime($row->tanggal_transaksi)),
          'jenis_pembayaran' => $row->jenis_pembayaran,
          'kode_pemasok' => $row->kode_pemasok,
          'nama_supplier' => $row->nama_supplier,
          'kode_bank' => $row->kode_bank,
          'nama_bank' => $row->nama_bank,
          'no_rekening' => $row->no_rekening,
          'total_nilai' => number_format($row->total_nilai, 0, '.', ','),
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
        'jenis_pembayaran' => 'required',
        'nama_supplier' => 'required|string|max:100',
        'kode_bank' => 'required',
        'no_rekening' => 'required',
        'details' => 'required|string',
      ];

      $messages = [
        'tanggal_transaksi.required' => 'Tanggal wajib diisi',
        'jenis_pembayaran.required' => 'Jenis Pembayaran wajib dipilih',
        'nama_supplier.required' => 'Nama Supplier wajib dipilih',
        'kode_bank.required' => 'Keluar Kas/Bank wajib dipilih',
        'no_rekening.required' => 'No. Rekening wajib dipilih',
        'details.required' => 'Minimal satu Input Gudang harus ditambahkan',
      ];

      $validator = Validator::make($request->all(), $rules, $messages);
      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => 'Gagal menyimpan data.',
          'errors' => $validator->errors(),
        ]);
      }

      // Parse detail JSON
      $detailRaw = json_decode($request->details, true);
      if (empty($detailRaw) || !is_array($detailRaw)) {
        return response()->json(['status' => false, 'message' => 'Detail Input Gudang tidak boleh kosong.']);
      }

      // Validasi setiap baris detail
      foreach ($detailRaw as $idx => $row) {
        if (empty($row['kode_input'])) {
          return response()->json(['status' => false, 'message' => "No. IG pada baris ke-" . ($idx + 1) . " tidak boleh kosong."]);
        }
        $nilaiRow = (float) str_replace([',', '.'], ['', ''], $row['nilai'] ?? 0);
        if ($nilaiRow <= 0) {
          return response()->json(['status' => false, 'message' => "Nilai pada IG {$row['kode_input']} harus lebih dari 0."]);
        }
      }

      // Hitung total
      $totalNilai = collect($detailRaw)->sum(fn($r) => (float) str_replace([',', '.'], ['', ''], $r['nilai'] ?? 0));

      $tanggal = blank($request->tanggal_transaksi)
        ? null
        : Carbon::createFromFormat('d/m/Y', $request->tanggal_transaksi, 'Asia/Jakarta')->format('Y-m-d');

      DB::beginTransaction();

      if ($dataID) {
        // === UPDATE ===
        $headerData = [
          'tanggal_transaksi' => $tanggal,
          'jenis_pembayaran' => $request->jenis_pembayaran,
          'kode_pemasok' => $request->kode_pemasok,
          'nama_supplier' => strtoupper($request->nama_supplier),
          'kode_bank' => $request->kode_bank,
          'no_rekening' => $request->no_rekening,
          'total_nilai' => $totalNilai,
          'updated_by' => Auth::user()->username,
        ];

        $ok = PembayaranGabungan::where('id', $dataID)->update($headerData);
        PembayaranGabunganDetail::where('id_header', $dataID)->delete();

        $desc = $ok !== false ? 'Berhasil Ubah Pembayaran Gabungan' : 'Gagal Ubah Pembayaran Gabungan';
        $idHeader = $dataID;

      } else {
        // === INSERT ===
        $noTransaksi = \Helper::getNomorTransaksi($user_cabang, 'LSR');

        $cek = PembayaranGabungan::where('kode_cabang', $user_cabang)
          ->where('no_transaksi', $noTransaksi)->first();
        if ($cek) {
          DB::rollBack();
          return response()->json(['status' => false, 'message' => 'Nomor transaksi sudah digunakan.']);
        }

        $headerData = [
          'kode_cabang' => $user_cabang,
          'no_transaksi' => $noTransaksi,
          'tanggal_transaksi' => $tanggal,
          'jenis_pembayaran' => $request->jenis_pembayaran,
          'kode_pemasok' => $request->kode_pemasok,
          'nama_supplier' => strtoupper($request->nama_supplier),
          'kode_bank' => $request->kode_bank,
          'no_rekening' => $request->no_rekening,
          'total_nilai' => $totalNilai,
          'created_by' => Auth::user()->username,
        ];

        $header = PembayaranGabungan::create($headerData);
        $ok = $header;

        if ($ok) {
          \Helper::updateNomorTransaksi($user_cabang, 'LSR');
        }

        $desc = $ok ? 'Berhasil Tambah Pembayaran Gabungan' : 'Gagal Tambah Pembayaran Gabungan';
        $idHeader = $header->id ?? null;
      }

      // Insert detail
      if ($idHeader) {
        $detailInsert = [];
        foreach ($detailRaw as $row) {
          $detailInsert[] = [
            'id_header' => $idHeader,
            'kode_input' => strtoupper($row['kode_input']),
            'no_bon_toko' => $row['no_bon'] ?? '',
            'nilai' => (float) str_replace([',', '.'], ['', ''], $row['nilai'] ?? 0),
            'created_at' => now(),
            'updated_at' => now(),
          ];
        }
        PembayaranGabunganDetail::insert($detailInsert);
      }

      DB::commit();
      LogActivity::saveLogActivity($desc, $headerData);

      return response()->json(['status' => (bool) $ok, 'message' => $desc]);

    } catch (\Exception $e) {
      DB::rollBack();
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
    $data = DB::table('t_pembayaran_gabungan_hdr as a')
      ->leftJoin('m_bank_fin as b', 'b.kode_bank', '=', 'a.kode_bank')
      ->where('a.id', $id)
      ->select([
        'a.id',
        'a.no_transaksi',
        'a.tanggal_transaksi',
        'a.jenis_pembayaran',
        'a.kode_pemasok',
        'a.nama_supplier',
        'a.kode_bank',
        'b.nama_bank',
        'a.no_rekening',
        'a.total_nilai',
      ])
      ->first();

    if (blank($data)) {
      return response()->json(['status' => false, 'message' => 'Data tidak ditemukan.']);
    }

    $data->tanggal_transaksi = blank($data->tanggal_transaksi)
      ? '' : date('d/m/Y', strtotime($data->tanggal_transaksi));

    $data->total_nilai_raw = (float) $data->total_nilai;
    $data->total_nilai = number_format($data->total_nilai, 0, '.', ',');

    $details = DB::table('t_pembayaran_gabungan_dtl')
      ->where('id_header', $id)
      ->select('id', 'kode_input', 'no_bon_toko', 'nilai')
      ->orderBy('id')
      ->get()
      ->map(fn($d) => [
        'id' => $d->id,
        'kode_input' => $d->kode_input,
        'no_bon' => $d->no_bon_toko,
        'nilai' => (float) $d->nilai,
        'nilai_fmt' => number_format($d->nilai, 0, '.', ','),
      ]);

    $data->details = $details;

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
  public function destroy($id): JsonResponse
  {
    $data = PembayaranGabungan::where('id', $id)->first();

    if (!$data) {
      return response()->json(['status' => false, 'message' => 'Data tidak ditemukan.']);
    }

    $arr = $data->toArray();
    $ok = $data->delete();

    $desc = $ok ? 'Berhasil Hapus Pembayaran Gabungan' : 'Gagal Hapus Pembayaran Gabungan';
    LogActivity::saveLogActivity($desc, $arr);

    return response()->json(['status' => (bool) $ok, 'message' => $desc]);
  }
}
