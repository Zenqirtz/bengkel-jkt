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
use App\Models\PenerimaanGabungan;
use App\Models\PenerimaanGabunganDetail;
use App\Models\LogActivity;
use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class PenerimaanGabunganController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function PenerimaanGabungan(): View
  {
    $isList = Helper::AuthIsPerm("list");
    $isAdd = Helper::AuthIsPerm("add");
    $isEdit = Helper::AuthIsPerm("edit");
    $isDel = Helper::AuthIsPerm("delete");

    if (!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', compact('pageConfigs'));
    }

    $path = request()->path();
    $title = Helper::getTitleMenu($path) ?? 'Penerimaan Gabungan';

    $user_cabang = session('kd_cabang');

    $bank = Bank::query()
      ->select('kode_bank', 'nama_bank', 'no_rekening')
      ->where('is_active', 'Y')
      ->where('kode_cabang', $user_cabang)
      ->orderBy('nama_bank', 'asc')
      ->get();

    $bankRekening = $bank->map(fn($b) => [
      'kode_bank' => $b->kode_bank,
      'nama_bank' => $b->nama_bank,
      'no_rekening' => $b->no_rekening,
    ])->values();

    $jenisPenerimaan = Parameter::query()
      ->select('kode', 'keterangan')
      ->where('nama_tabel', 'JENIS_PENERIMAAN_UMP')
      ->orderBy('no_urut', 'asc')
      ->get();

    LogActivity::saveLogActivity("View {$title}");

    return view('content.keuangan.penerimaan-gabungan', compact(
      'title',
      'isList',
      'isAdd',
      'isEdit',
      'isDel',
      'bank',
      'bankRekening',
      'jenisPenerimaan'
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

    // ── REKENING BY BANK ──────────────────────────────────────────
    if ($request->filled('rekening') && $request->filled('kode_bank')) {
      $data = DB::table('m_bank_fin')
        ->select('no_rekening')
        ->where('kode_bank', $request->kode_bank)
        ->where('kode_cabang', $user_cabang)
        ->where('is_active', 'Y')
        ->get();
      return response()->json($data);
    }

    // ── CUSTOMER BY JENIS PEMBAYARAN ──────────────────────────────
    if ($request->filled('get_customer') && $request->filled('jenis_pembayaran')) {
      $user_cabang = session('kd_cabang');
      $jenis = $request->jenis_pembayaran;

      $query = DB::table('m_pelanggan_hdr')
        ->select('kode_pelanggan', 'nama_pelanggan')
        ->where('kode_cabang', $user_cabang)
        ->where('is_active', 'Y')
        ->orderBy('nama_pelanggan', 'asc');

      if (in_array($jenis, ['INVOICE OR', 'INVOICE ASURANSI'])) {
        $query->where('kode_jenis_pelanggan', '00001');
      } elseif ($jenis === 'INVOICE PRIBADI') {
        $query->where('kode_jenis_pelanggan', '00002');
      }

      return response()->json($query->get());
    }

    // ── SPK LIST ──────────────────────────────────────────────────
    if ($request->filled('get_spk')) {
      $user_cabang = session('kd_cabang');
      $search = $request->input('search.value', '');
      $kode_pelanggan = $request->input('kode_pelanggan', '');
      $jenis = $request->input('jenis_pembayaran', '');

      if (is_array($search) || empty($search))
        $search = '';

      // ── Mapping kolom index → nama kolom DB ──

      if(in_array($jenis, ['INVOICE PRIBADI', 'INVOICE ASURANSI'])) {
        $spkColumns = [
          1 => 's.tgl_kwitansi',
          2 => 's.kode_spk',
          3 => 's.nama_pelanggan',
          4 => 's.grand_total',
        ];

        $orderColIndex = (int) $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $spkColumns[$orderColIndex] ?? 's.tgl_kwitansi';
      } else {
        $spkColumns = [
          1 => 's.tgl_masuk',
          2 => 's.kode_spk',
          3 => 's.nama_pelanggan',
          4 => 's.total_or',
        ];

        $orderColIndex = (int) $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $spkColumns[$orderColIndex] ?? 's.tgl_masuk';
      }
      

      // $query = DB::table('v_spk as s')
      //   ->leftJoin('m_pelanggan_hdr as p', 'p.kode_pelanggan', '=', 's.kode_pelanggan')
      //   ->select(
      //     's.kode_spk as no_spk',
      //     's.tgl_masuk',
      //     DB::raw("COALESCE(p.nama_pelanggan, s.pemilik) as nama_customer"),
      //     DB::raw("COALESCE(s.nilai_or, s.total_or, 0) as total_or")
      //   )
      //   ->where('s.kode_cabang', $user_cabang)
      //   ->whereNotIn('s.status_spk', ['SPK BATAL', 'SPK TUTUP', 'SPK KELUAR']);
      // $query = DB::table('v_spk as s')
      //   ->leftJoin('m_pelanggan_hdr as p', 'p.kode_pelanggan', '=', 's.kode_pelanggan')
      //   ->select(
      //     's.kode_spk as no_spk',
      //     's.tgl_masuk',
      //     DB::raw("COALESCE(p.nama_pelanggan, s.pemilik) as nama_customer"),
      //     DB::raw("COALESCE(s.nilai_or, s.total_or, 0) as total_or")
      //   )
      //   ->where('s.kode_cabang', $user_cabang)
      //   ->whereNotIn('s.status_spk', ['SPK BATAL', 'SPK TUTUP', 'SPK KELUAR'])
      //   ->where(function ($q) {
      //     $q->whereNull('s.tanggal_lunas_or') // belum pernah lunas resmi sama sekali
      //       ->orWhereExists(function ($sub) {
      //         $sub->select(DB::raw(1))
      //           ->from('t_uang_muka_kwitansi_or as u')
      //           ->whereColumn('u.kode_spk', 's.kode_spk')
      //           ->whereNull('u.tgl_kwitansi'); // ada DP yang belum resmi ditarik/dipotongkan
      //       });
      //   });

      if(in_array($jenis, ['INVOICE PRIBADI', 'INVOICE ASURANSI'])) {

        $query = DB::table('v_trx_kwitansi as s')
        ->select(
          's.kode_spk as no_spk',
          's.tgl_kwitansi as tanggal',
          's.nama_pelanggan as nama_customer',
          's.grand_total as nilai'
        )
        ->where('s.kode_cabang', $user_cabang)
        ->where('s.kode_pelanggan', $kode_pelanggan)
        ->whereNotNull('s.tgl_kwitansi');

      } elseif($jenis == 'INVOICE OR') {

        $query = DB::table('v_spk as s')
        ->select(
          's.kode_spk as no_spk',
          's.tgl_masuk as tanggal',
          's.nama_pelanggan as nama_customer',
          's.total_or as nilai'
        )
        ->where('s.kode_cabang', $user_cabang)
        ->where('s.kode_pelanggan', $kode_pelanggan)
        ->whereNotNull('s.tgl_invoice')
        ->whereNull('s.tanggal_lunas_or');

      } else {
        return response()->json([
          'draw' => intval($request->input('draw')),
          'recordsTotal' => 0,
          'recordsFiltered' => 0,
          'data' => [],
        ]);
      }
      
      // if (in_array($jenis, ['INVOICE OR', 'INVOICE ASURANSI'])) {
      //   $query->where('s.ada_or', '01');
      //   if ($kode_pelanggan)
      //     $query->where('s.kode_pelanggan', $kode_pelanggan);
      // } elseif ($jenis === 'INVOICE PRIBADI') {
      //   if ($kode_pelanggan)
      //     $query->where('s.kode_pelanggan', $kode_pelanggan);
      // } else {
      //   if ($kode_pelanggan)
      //     $query->where('s.kode_pelanggan', $kode_pelanggan);
      // }

      if ($search) {
        $query->where(function ($q) use ($search) {
          $q->where('s.kode_spk', 'like', "%{$search}%")
            ->orWhere('s.nama_pelanggan', 'like', "%{$search}%");
        });
      }

      $total = (clone $query)->count();
      $start = (int) $request->input('start', 0);
      $length = (int) $request->input('length', 10);

      $datas = $query
        ->orderBy($orderCol, $orderDir)        // ← dynamic sort
        ->orderBy('s.kode_spk', $orderDir)     // ← secondary sort
        ->offset($start)
        ->limit($length)
        ->get();

      $data = [];
      foreach ($datas as $row) {
        $data[] = [
          'no_spk' => $row->no_spk,
          'tanggal' => blank($row->tanggal) ? '' : date('d/m/Y', strtotime($row->tanggal)),
          'nama_customer' => $row->nama_customer,
          'nilai' => number_format($row->nilai, 0, '.', ','),
        ];
      }

      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => $total,
        'recordsFiltered' => $total,
        'data' => $data,
      ]);
    }
    // ── LISTING UTAMA ─────────────────────────────────────────────
    $user_cabang = session('kd_cabang');

    try {
      $columns = [
        1 => 'a.id',
        2 => 'a.tanggal_transaksi',
        3 => 'a.no_transaksi',
        4 => 'a.jenis_pembayaran',
        5 => 'a.nama_customer',
        6 => 'b.nama_bank',
        7 => 'a.no_rekening',
        8 => 'a.total_nilai',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.id';
      $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      $base = DB::table('t_penerimaan_gabungan_hdr as a')
        ->leftJoin('m_bank_fin as b', function ($join) {
          $join->on('b.kode_bank', '=', 'a.kode_bank')
              ->on('b.kode_cabang', '=', 'a.kode_cabang'); // syarat di JOIN
        })
        ->where('a.kode_cabang', $user_cabang);

      $totalData = (clone $base)->count('a.id');
      $query = clone $base;

      if ($request->filled('no_transaksi'))
        $query->where('a.no_transaksi', 'like', '%' . $request->no_transaksi . '%');
      if ($request->filled('nama_customer'))
        $query->where('a.nama_customer', 'like', '%' . $request->nama_customer . '%');
      if ($request->filled('jenis_pembayaran') && $request->jenis_pembayaran !== 'all')
        $query->where('a.jenis_pembayaran', $request->jenis_pembayaran);
      if ($request->filled('kode_bank') && $request->kode_bank !== 'all')
        $query->where('a.kode_bank', $request->kode_bank);
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
          'a.nama_customer',
          'a.kode_bank',
          'b.nama_bank',
          'a.no_rekening',
          'a.total_nilai',
          'a.pph',
          'a.biaya_merimen',
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
          'jenis_pembayaran' => $row->jenis_pembayaran,
          'nama_customer' => $row->nama_customer,
          'kode_bank' => $row->kode_bank,
          'nama_bank' => $row->nama_bank,
          'no_rekening' => $row->no_rekening,
          'total_nilai' => number_format($row->total_nilai + $row->pph + $row->biaya_merimen, 0, '.', ','),
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
        'nama_customer' => 'required|string|max:100',
        'kode_bank' => 'required',
        'no_rekening' => 'required',
        'details' => 'required|string',
      ];

      $messages = [
        'tanggal_transaksi.required' => 'Tanggal wajib diisi',
        'jenis_pembayaran.required' => 'Jenis Pembayaran wajib dipilih',
        'nama_customer.required' => 'Nama Customer wajib diisi',
        'kode_bank.required' => 'Masuk Kas/Bank wajib dipilih',
        'no_rekening.required' => 'No. Rekening wajib dipilih',
        'details.required' => 'Minimal satu SPK harus ditambahkan',
      ];

      $validator = Validator::make($request->all(), $rules, $messages);
      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => 'Gagal menyimpan data.',
          'errors' => $validator->errors(),
        ]);
      }

      $detailRaw = json_decode($request->details, true);
      if (empty($detailRaw) || !is_array($detailRaw)) {
        return response()->json(['status' => false, 'message' => 'Detail SPK tidak boleh kosong.']);
      }

      // foreach ($detailRaw as $idx => $row) {
      //   if (empty($row['no_spk'])) {
      //     return response()->json(['status' => false, 'message' => "No. SPK pada baris ke-" . ($idx + 1) . " tidak boleh kosong."]);
      //   }
      //   $nilaiRow = (float) str_replace([',', '.'], ['', ''], $row['nilai'] ?? 0);
      //   if ($nilaiRow <= 0) {
      //     return response()->json(['status' => false, 'message' => "Nilai pada SPK {$row['no_spk']} harus lebih dari 0."]);
      //   }
      // }
      foreach ($detailRaw as $idx => $row) {
        if (empty($row['no_spk'])) {
          return response()->json(['status' => false, 'message' => "No. SPK pada baris ke-" . ($idx + 1) . " tidak boleh kosong."]);
        }
        $nilaiRow = (float) str_replace([',', '.'], ['', ''], $row['nilai'] ?? 0);
        $pphRow = (float) str_replace([',', '.'], ['', ''], $row['pph'] ?? 0);

        if ($nilaiRow <= 0) {
          return response()->json(['status' => false, 'message' => "Nilai pada SPK {$row['no_spk']} harus lebih dari 0."]);
        }
        if ($pphRow < 0 || $pphRow > $nilaiRow) {
          return response()->json(['status' => false, 'message' => "PPh pada SPK {$row['no_spk']} tidak valid."]);
        }
      }

      // // Total Nilai header = jumlah nilai SPK - jumlah PPh per SPK
      // $totalNilai = collect($detailRaw)->sum(function ($r) {
      //   $nilai = (float) str_replace([',', '.'], ['', ''], $r['nilai'] ?? 0);
      //   $pph = (float) str_replace([',', '.'], ['', ''], $r['pph'] ?? 0);
      //   return $nilai - $pph;
      // });
      // Total Nilai header = jumlah nilai SPK - jumlah PPh per SPK
      $totalNilai = collect($detailRaw)->sum(function ($r) {
        $nilai = (float) str_replace([',', '.'], ['', ''], $r['nilai'] ?? 0);
        // $pph = (float) str_replace([',', '.'], ['', ''], $r['pph'] ?? 0);
        // return $nilai - $pph;
        return $nilai;
      });

      // Total PPh header = jumlah PPh semua SPK
      $totalPph = collect($detailRaw)->sum(function ($r) {
        return (float) str_replace([',', '.'], ['', ''], $r['pph'] ?? 0);
      });

      // Total Biaya Merimen header = jumlah Biaya Merimen semua SPK
      $totalMerimen = collect($detailRaw)->sum(function ($r) {
        return (float) str_replace([',', '.'], ['', ''], $r['biaya_merimen'] ?? 0);
      });

      // $totalNilai = collect($detailRaw)->sum(fn($r) => (float) str_replace([',', '.'], ['', ''], $r['nilai'] ?? 0));

      $tanggal = blank($request->tanggal_transaksi)
        ? null
        : Carbon::createFromFormat('d/m/Y', $request->tanggal_transaksi, 'Asia/Jakarta')->format('Y-m-d');

      DB::beginTransaction();

      if ($dataID) {
        // === UPDATE ===
        $headerData = [
          'tanggal_transaksi' => $tanggal,
          'jenis_pembayaran' => $request->jenis_pembayaran,
          'kode_pelanggan' => $request->kode_pelanggan,
          'nama_customer' => strtoupper($request->nama_customer),
          'kode_bank' => $request->kode_bank,
          'no_rekening' => $request->no_rekening,
          'total_nilai' => $totalNilai,
          'pph' => $totalPph,
          'biaya_merimen' => $totalMerimen,
          'updated_by' => Auth::user()->username,
        ];

        $ok = PenerimaanGabungan::where('id', $dataID)->update($headerData);
        PenerimaanGabunganDetail::where('id_header', $dataID)->delete();

        $desc = $ok !== false ? 'Berhasil Ubah Penerimaan Gabungan' : 'Gagal Ubah Penerimaan Gabungan';
        $idHeader = $dataID;

      } else {
        // === INSERT ===
        $noTransaksi = Helper::getNomorTransaksi($user_cabang, 'LCR');

        $cek = PenerimaanGabungan::where('kode_cabang', $user_cabang)
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
          'kode_pelanggan' => $request->kode_pelanggan,
          'nama_customer' => strtoupper($request->nama_customer),
          'kode_bank' => $request->kode_bank,
          'no_rekening' => $request->no_rekening,
          'total_nilai' => $totalNilai,
          'pph' => $totalPph,
          'biaya_merimen' => $totalMerimen,
          'created_by' => Auth::user()->username,
        ];

        $header = PenerimaanGabungan::create($headerData);
        $ok = $header;

        if ($ok)
          Helper::updateNomorTransaksi($user_cabang, 'LCR');

        $desc = $ok ? 'Berhasil Tambah Penerimaan Gabungan' : 'Gagal Tambah Penerimaan Gabungan';
        $idHeader = $header->id ?? null;
      }

      // Insert detail
      if ($idHeader) {
        $detailInsert = [];
        // foreach ($detailRaw as $row) {
        //   $detailInsert[] = [
        //     'id_header' => $idHeader,
        //     'no_spk' => strtoupper($row['no_spk']),
        //     'nama_customer' => strtoupper($row['nama_customer'] ?? ''),
        //     'nilai' => (float) str_replace([',', '.'], ['', ''], $row['nilai'] ?? 0),
        //     'created_at' => now(),
        //     'updated_at' => now(),
        //   ];
        // }
        foreach ($detailRaw as $row) {
          $detailInsert[] = [
            'id_header' => $idHeader,
            'no_spk' => strtoupper($row['no_spk']),
            'nama_customer' => strtoupper($row['nama_customer'] ?? ''),
            'nilai' => (float) str_replace([',', '.'], ['', ''], $row['nilai'] ?? 0),
            'pph' => (float) str_replace([',', '.'], ['', ''], $row['pph'] ?? 0),
            'biaya_merimen' => (float) str_replace([',', '.'], ['', ''], $row['biaya_merimen'] ?? 0),
            'created_at' => now(),
            'updated_at' => now(),
          ];
        }
        PenerimaanGabunganDetail::insert($detailInsert);
      }

      DB::commit();

      // Update outstanding setelah commit berhasil
      try {
        $this->updateOutstandingSpk($detailRaw, false);
      } catch (\Exception $e) {
        \Log::warning('updateOutstandingSpk gagal: ' . $e->getMessage());
      }

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
    // $data = DB::table('t_penerimaan_gabungan_hdr as a')
    //   ->leftJoin('m_bank_fin as b', 'b.kode_bank', '=', 'a.kode_bank')
    //   ->leftJoin('m_pelanggan_hdr as p', 'p.kode_pelanggan', '=', 'a.kode_pelanggan')
    //   ->where('a.id', $id)
    //   ->select([
    //     'a.id',
    //     'a.no_transaksi',
    //     'a.tanggal_transaksi',
    //     'a.jenis_pembayaran',
    //     'a.nama_customer',
    //     'a.kode_pelanggan',
    //     'a.kode_bank',
    //     'b.nama_bank',
    //     'a.no_rekening',
    //     'a.total_nilai',
    //   ])
    //   ->first();

    $data = PenerimaanGabungan::where('id', $id)->first();

    if (blank($data)) {
      return response()->json(['status' => false, 'message' => 'Data tidak ditemukan.']);
    }

    $data->tanggal_transaksi = blank($data->tanggal_transaksi)
      ? '' : date('d/m/Y', strtotime($data->tanggal_transaksi));

    $data->total_nilai_raw = (float) $data->total_nilai;
    $data->total_nilai = number_format($data->total_nilai, 0, '.', ',');

    // $details = DB::table('t_penerimaan_gabungan_dtl')
    //   ->where('id_header', $id)
    //   ->select('id', 'no_spk', 'nama_customer', 'nilai')
    //   ->orderBy('id')
    //   ->get()
    //   ->map(fn($d) => [
    //     'id' => $d->id,
    //     'no_spk' => $d->no_spk,
    //     'nama_customer' => $d->nama_customer,
    //     'nilai' => (float) $d->nilai,
    //     'nilai_fmt' => number_format($d->nilai, 0, '.', ','),
    //   ]);

    $details = DB::table('t_penerimaan_gabungan_dtl')
      ->where('id_header', $id)
      ->select('id', 'no_spk', 'nama_customer', 'nilai', 'pph', 'biaya_merimen')
      ->orderBy('id')
      ->get()
      ->map(fn($d) => [
        'id' => $d->id,
        'no_spk' => $d->no_spk,
        'nama_customer' => $d->nama_customer,
        'nilai' => (float) $d->nilai,
        'nilai_fmt' => number_format($d->nilai, 0, '.', ','),
        'pph' => (float) $d->pph,
        'pph_fmt' => number_format($d->pph, 0, '.', ','),
        'biaya_merimen' => (float) $d->biaya_merimen,
        'biaya_merimen_fmt' => number_format($d->biaya_merimen, 0, '.', ','),
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
    $data = PenerimaanGabungan::where('id', $id)->first();
    if (!$data) {
      return response()->json(['status' => false, 'message' => 'Data tidak ditemukan.']);
    }

    // Ambil detail sebelum hapus
    $details = PenerimaanGabunganDetail::where('id_header', $id)
      ->get()
      ->map(fn($d) => ['no_spk' => $d->no_spk])
      ->toArray();

    $arr = $data->toArray();
    $ok = $data->delete();

    if ($ok) {
      try {
        $this->updateOutstandingSpk($details, true);
      } catch (\Exception $e) {
        \Log::warning('updateOutstandingSpk (destroy) gagal: ' . $e->getMessage());
      }
    }

    $desc = $ok ? 'Berhasil Hapus Penerimaan Gabungan' : 'Gagal Hapus Penerimaan Gabungan';
    LogActivity::saveLogActivity($desc, $arr);

    return response()->json(['status' => (bool) $ok, 'message' => $desc]);
  }

  // ================================================================
  // PRIVATE HELPER
  // ================================================================
  private function updateOutstandingSpk(array $details, bool $hapus = false): void
  {
    foreach ($details as $row) {
      if (empty($row['no_spk']))
        continue;

      try {
        DB::table('t_spk_master')
          ->where('kode_spk', $row['no_spk'])
          ->update([
            'post_lunas_invoice' => $hapus ? 0 : 1,
            'tanggal_lunas_or' => $hapus ? null : now(),  // ← tambah ini
            'updated_at' => now(),
          ]);
      } catch (\Exception $e) {
        \Log::warning('update t_spk_master gagal untuk ' . $row['no_spk'] . ': ' . $e->getMessage());
      }
    }
  }
}
