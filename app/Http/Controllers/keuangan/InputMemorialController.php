<?php

namespace App\Http\Controllers\keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Parameter;
use App\Models\InputMemorial;
use App\Models\LogActivity;
use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class InputMemorialController extends Controller
{
  /**
   * Redirect to view.
   */
  public function InputMemorial(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Input Memorial';

    $jenis = Parameter::where('nama_tabel', 'JENIS_BANK')
      ->orderBy('no_urut')->get();

    $jenisTransaksi = Parameter::where('nama_tabel', 'JENIS_MEMORIAL')
      ->orderBy('no_urut')->get();

    $tipeMemorial = Parameter::where('nama_tabel', 'TIPE_MEMORIAL')
      ->orderBy('no_urut')->get();

    // $coa = Parameter::where('nama_tabel', 'COA_INPUT_BANK')
    //   ->orderBy('no_urut')->get();
    $coa = DB::table('m_coa')
      ->where('active_status', 'Y')
      ->orderBy('acct_cd')
      ->get();

    LogActivity::saveLogActivity("View {$title}");

    return view('content.keuangan.input-memorial', compact(
      'title',
      'isList',
      'isAdd',
      'isEdit',
      'isDel',
      'jenis',
      'jenisTransaksi',
      'tipeMemorial',
      'coa'
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

    // ── Sub: cari SPK (serverSide: true — sama dengan PenerimaanGabungan) ──
    if ($request->filled('get_spk')) {
      $search = $request->input('search.value', '');
      if (is_array($search) || empty($search))
        $search = '';

      // ── Mapping index kolom DataTables → kolom DB ──────────────
      $spkColumns = [
        1 => 's.tgl_masuk',
        2 => 's.kode_spk',
        3 => 'nama_pemilik',
        4 => 's.no_polisi',
      ];

      $orderColIndex = (int) $request->input('order.0.column', 1);
      $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
      $orderCol = $spkColumns[$orderColIndex] ?? 's.tgl_masuk';

      try {
        $query = DB::table('v_spk as s')
          ->leftJoin('m_pelanggan_hdr as p', 'p.kode_pelanggan', '=', 's.kode_pelanggan')
          ->select(
            's.kode_spk as no_spk',
            's.tgl_masuk',
            DB::raw("COALESCE(p.nama_pelanggan, s.pemilik) as nama_pemilik"),
            's.no_polisi'
          )
          ->where('s.kode_cabang', $user_cabang)
          ->whereNotIn('s.status_spk', ['SPK BATAL', 'SPK TUTUP', 'SPK KELUAR']);

        if ($search) {
          $query->where(function ($q) use ($search) {
            $q->where('s.kode_spk', 'like', "%{$search}%")
              ->orWhere('p.nama_pelanggan', 'like', "%{$search}%")
              ->orWhere('s.pemilik', 'like', "%{$search}%");
          });
        }

        $total = (clone $query)->count();
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $data = $query
          ->orderBy($orderCol, $orderDir)      // ← dynamic sort
          ->orderBy('s.kode_spk', $orderDir)   // ← secondary sort
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
          'error' => $e->getMessage(),
        ]);
      }
    }

    // ── Sub: cari IG (serverSide: true — sama dengan PembayaranGabungan) ──
    if ($request->filled('get_ig')) {
      $search = $request->input('search.value', '');
      if (is_array($search) || empty($search))
        $search = '';

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
          ->where('ig.kode_cabang', $user_cabang);

        if ($search) {
          $query->where(function ($q) use ($search) {
            $q->where('ig.kode_input', 'like', "%{$search}%")
              ->orWhere('ig.no_bon', 'like', "%{$search}%")
              ->orWhere('p.nama_pemasok', 'like', "%{$search}%");
          });
        }

        $total = (clone $query)->count();
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $data = $query->orderBy('ig.kode_input', 'desc')
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
          'error' => $e->getMessage(),
        ]);
      }
    }

    // ── Sub: cari Invoice berdasarkan No. SPK ──
    if ($request->filled('get_invoice_by_spk')) {
      $noSpk = $request->input('no_spk', '');

      $data = DB::table('t_spk_master')
        ->select(
          'no_invoice',
          DB::raw('COALESCE(total_or, 0) as nilai')
        )
        ->where('kode_spk', $noSpk)
        ->whereNotNull('no_invoice')
        ->where('no_invoice', '!=', '')
        ->get();

      return response()->json($data);
    }

    // ── DataTable utama ──
    return $this->getDataTable($request, $user_cabang);
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

      $validator = Validator::make($request->all(), [
        'tanggal' => 'required',
        'jenis' => 'required',
        'transaksi' => 'required',
        'jml_dibayar' => 'required',
        'account_coa' => 'required',
      ], [
        'tanggal.required' => 'Tanggal wajib diisi',
        'jenis.required' => 'Jenis wajib dipilih',
        'transaksi.required' => 'Transaksi wajib dipilih',
        'jml_dibayar.required' => 'Jumlah wajib diisi',
        'account_coa.required' => 'Account/COA wajib dipilih',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => 'Gagal menyimpan data.',
          'errors' => $validator->errors(),
        ]);
      }

      $tanggal = Carbon::createFromFormat('d/m/Y', $request->tanggal, 'Asia/Jakarta')->format('Y-m-d');
      $nilai = $this->parseNumber($request->nilai ?? 0);
      $jmlDibayar = $this->parseNumber($request->jml_dibayar ?? 0);
      $sisa = $nilai - $jmlDibayar;

      DB::beginTransaction();

      $headerData = [
        'tanggal' => $tanggal,
        'jenis' => $request->jenis,
        'transaksi' => $request->transaksi,
        'tipe' => $request->tipe,
        'no_spk' => $request->no_spk,
        'no_invoice' => $request->no_invoice,
        'nama_supplier' => $request->nama_supplier ? strtoupper($request->nama_supplier) : null,
        'no_ig' => $request->no_ig,
        'no_bon_toko' => $request->no_bon_toko,
        'nilai' => $nilai,
        'jml_dibayar' => $jmlDibayar,
        'sisa' => $sisa,
        'account_coa' => $request->account_coa,
        'keterangan' => $request->keterangan,
      ];

      if ($dataID) {
        // UPDATE
        $headerData['updated_by'] = Auth::user()->username;
        $ok = InputMemorial::where('id', $dataID)->update($headerData);
        $desc = $ok !== false ? 'Berhasil Ubah Input Memorial' : 'Gagal Ubah Input Memorial';
      } else {
        // INSERT
        $noVoucher = Helper::getNomorTransaksi($user_cabang, 'JM');

        if (InputMemorial::where('kode_cabang', $user_cabang)->where('no_voucher', $noVoucher)->exists()) {
          DB::rollBack();
          return response()->json(['status' => false, 'message' => 'Nomor voucher sudah digunakan.']);
        }

        $headerData['kode_cabang'] = $user_cabang;
        $headerData['no_voucher'] = $noVoucher;
        // $headerData['status'] = '0';
        $headerData['created_by'] = Auth::user()->username;

        $ok = InputMemorial::create($headerData);
        if ($ok)
          Helper::updateNomorTransaksi($user_cabang, 'JM');

        $desc = $ok ? 'Berhasil Tambah Input Memorial' : 'Gagal Tambah Input Memorial';
      }

      DB::commit();

      // Sync outstanding SPK
      if ($request->tipe === 'SPK' && !empty($request->no_spk)) {
        if ($dataID) {
          $voucherFinal = InputMemorial::where('id', $dataID)->value('no_voucher');
        } else {
          $voucherFinal = $noVoucher ?? null;
        }

        $this->syncOutstandingSpk([
          'tipe' => $request->tipe,
          'no_spk' => $request->no_spk,
          'no_invoice' => $request->no_invoice,
          'no_voucher' => $voucherFinal,
          'tanggal' => $tanggal,
        ], $user_cabang, false);
      }

      // Sync outstanding IG — TERPISAH dari blok SPK ✓
      if ($request->tipe === 'UMUM' && !empty($request->no_ig)) {
        $this->syncOutstandingIg([
          'tipe' => $request->tipe,
          'no_ig' => $request->no_ig,
          'updated_by' => Auth::user()->username,
        ], $user_cabang, false);
      }

      LogActivity::saveLogActivity($desc, $headerData);


      return response()->json(['status' => (bool) $ok, 'message' => $desc]);

    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['status' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }
  }
  private function syncOutstandingSpk(array $data, string $user_cabang, bool $hapus = false): void
  {
    try {
      if (($data['tipe'] ?? '') !== 'SPK')
        return;
      if (empty($data['no_spk']))
        return;

      if ($hapus) {
        DB::table('t_spk_master')
          ->where('kode_spk', $data['no_spk'])
          ->where('no_invoice', $data['no_invoice'])
          ->update([
            'post_lunas_invoice' => 0,
            'tanggal_lunas_or' => null,
            'kode_lunas_or' => null,
            'updated_at' => now(),
          ]);
      } else {
        DB::table('t_spk_master')
          ->where('kode_spk', $data['no_spk'])
          ->where('no_invoice', $data['no_invoice'])
          ->update([
            'post_lunas_invoice' => 1,
            'tanggal_lunas_or' => $data['tanggal'] ?? now(),
            'kode_lunas_or' => $data['no_voucher'] ?? null,
            'updated_at' => now(),
          ]);
      }
    } catch (\Exception $e) {
      \Log::warning('syncOutstandingSpk gagal: ' . $e->getMessage(), $data);
    }
  }


  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id): JsonResponse
  {
    $data = DB::table('t_input_memorial')->where('id', $id)->first();

    if (blank($data)) {
      return response()->json(['status' => false, 'message' => 'Data tidak ditemukan.']);
    }

    $data->tanggal = blank($data->tanggal) ? '' : date('d/m/Y', strtotime($data->tanggal));
    $data->nilai_fmt = number_format($data->nilai, 0, '.', ',');
    $data->jml_dibayar_fmt = number_format($data->jml_dibayar, 0, '.', ',');
    $data->sisa_fmt = number_format($data->sisa, 0, '.', ',');

    // Ambil nama_pemilik & no_polisi dari v_spk jika tipe SPK
    if ($data->tipe === 'SPK' && !blank($data->no_spk)) {
      $spk = DB::table('v_spk as s')
        ->leftJoin('m_pelanggan_hdr as p', 'p.kode_pelanggan', '=', 's.kode_pelanggan')
        ->select(
          DB::raw("COALESCE(p.nama_pelanggan, s.pemilik) as nama_pemilik"),
          's.no_polisi'
        )
        ->where('s.kode_spk', $data->no_spk)
        ->first();

      $data->nama_pemilik = $spk->nama_pemilik ?? '';
      $data->no_polisi = $spk->no_polisi ?? '';
    } else {
      $data->nama_pemilik = '';
      $data->no_polisi = '';
    }

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
    $data = InputMemorial::where('id', $id)->first();
    if (!$data) {
      return response()->json(['status' => false, 'message' => 'Data tidak ditemukan.']);
    }

    $arr = $data->toArray();

    // Kembalikan outstanding sebelum hapus
    $this->syncOutstandingSpk([
      'tipe' => $arr['tipe'] ?? '',
      'no_spk' => $arr['no_spk'] ?? '',
      'no_invoice' => $arr['no_invoice'] ?? '',
      'no_voucher' => $arr['no_voucher'] ?? '',
      'tanggal' => $arr['tanggal'] ?? '',
    ], session('kd_cabang'), true);

    if (($arr['tipe'] ?? '') === 'UMUM' && !empty($arr['no_ig'])) {
      $this->syncOutstandingIg([
        'tipe' => $arr['tipe'],
        'no_ig' => $arr['no_ig'],
      ], session('kd_cabang'), true);
    }

    $ok = $data->delete();


    $desc = $ok ? 'Berhasil Hapus Input Memorial' : 'Gagal Hapus Input Memorial';
    LogActivity::saveLogActivity($desc, $arr);

    return response()->json(['status' => (bool) $ok, 'message' => $desc]);
  }



  // ================================================================
  // PRIVATE — DataTable utama
  // ================================================================
  private function getDataTable(Request $request, string $user_cabang): JsonResponse
  {
    try {
      $columns = [
        1 => 'h.id',
        2 => 'h.tanggal',
        3 => 'h.no_voucher',
        4 => 'h.jenis',
        5 => 'h.transaksi',
        6 => 'h.no_spk',
        7 => 'h.account_coa',
        8 => 'h.jml_dibayar',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'h.id';
      $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      $base = DB::table('t_input_memorial as h')->where('h.kode_cabang', $user_cabang);
      $totalData = (clone $base)->count('h.id');
      $query = clone $base;

      if ($request->filled('no_voucher'))
        $query->where('h.no_voucher', 'like', '%' . $request->no_voucher . '%');
      if ($request->filled('jenis') && $request->jenis !== 'all')
        $query->where('h.jenis', $request->jenis);
      if ($request->filled('transaksi') && $request->transaksi !== 'all')
        $query->where('h.transaksi', $request->transaksi);
      if ($request->filled('tanggal_awal'))
        $query->whereDate(
          'h.tanggal',
          '>=',
          Carbon::createFromFormat('d/m/Y', $request->tanggal_awal, 'Asia/Jakarta')->format('Y-m-d')
        );
      if ($request->filled('tanggal_akhir'))
        $query->whereDate(
          'h.tanggal',
          '<=',
          Carbon::createFromFormat('d/m/Y', $request->tanggal_akhir, 'Asia/Jakarta')->format('Y-m-d')
        );

      $totalFiltered = (clone $query)->count('h.id');

      $datas = $query
        ->leftJoin('parameter as p_jenis', function ($join) {
          $join->on('p_jenis.kode', '=', 'h.jenis')
            ->where('p_jenis.nama_tabel', 'JENIS_BANK');
        })
        ->leftJoin('parameter as p_transaksi', function ($join) {
          $join->on('p_transaksi.kode', '=', 'h.transaksi')
            ->where('p_transaksi.nama_tabel', 'JENIS_MEMORIAL');
        })
        ->select([
          'h.id',
          'h.no_voucher',
          'h.tanggal',
          'h.jenis',
          DB::raw("COALESCE(p_jenis.keterangan, h.jenis) as jenis_label"),
          'h.transaksi',
          DB::raw("COALESCE(p_transaksi.keterangan, h.transaksi) as transaksi_label"),
          'h.tipe',
          'h.no_spk',
          'h.no_invoice',
          'h.account_coa',
          'h.jml_dibayar',
          'h.keterangan',
        ])
        ->orderBy($order, $dir)
        ->offset($start)
        ->limit($limit)
        ->get();

      // ← FIX: lookup nama COA dari m_coa
      $kodeCoa = $datas->pluck('account_coa')->filter()->unique()->values()->toArray();

      $mapCoa = [];
      if (!empty($kodeCoa)) {
        $mapCoa = DB::table('m_coa')
          ->whereRaw(
            'LOWER(TRIM(acct_cd)) IN (' . implode(',', array_fill(0, count($kodeCoa), '?')) . ')',
            array_map('strtolower', array_map('trim', $kodeCoa))
          )
          ->pluck('descs', 'acct_cd')
          ->toArray();
      }

      $data = [];
      $fake = $start;
      foreach ($datas as $row) {
        // Cari nama COA
        $namaCoa = null;
        foreach ($mapCoa as $kode => $nama) {
          if (strtolower(trim($kode)) === strtolower(trim($row->account_coa))) {
            $namaCoa = $nama;
            break;
          }
        }

        $data[] = [
          'id' => $row->id,
          'fake_id' => ++$fake,
          'no_voucher' => $row->no_voucher,
          'tanggal' => blank($row->tanggal) ? '' : date('d/m/Y', strtotime($row->tanggal)),
          'jenis' => $row->jenis_label,
          'transaksi' => $row->transaksi_label,
          'tipe' => $row->tipe,
          'no_spk' => $row->no_spk,
          'no_invoice' => $row->no_invoice,
          'account_coa' => $namaCoa ?? $row->account_coa, // ← tampilkan nama, fallback ke kode
          'jml_dibayar' => number_format($row->jml_dibayar, 0, '.', ','),
          'keterangan' => $row->keterangan,
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
        'draw' => 0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage(),
      ]);
    }
  }

  private function syncOutstandingIg(array $data, string $user_cabang, bool $hapus = false): void
  {
    try {
      if (($data['tipe'] ?? '') !== 'UMUM')
        return;
      if (empty($data['no_ig']))
        return;
      if ($hapus) {
        // Hapus memorial → kembalikan ke belum bayar
        DB::table('t_input_gudang_hdr')
          ->where('kode_cabang', $user_cabang)
          ->where('kode_input', $data['no_ig'])
          ->update([
            'is_bayar' => 'N', // ← bukan '0'
            'updated_at' => now(),
            'updated_by' => null,
          ]);
      } else {
        // Simpan memorial → tandai sudah bayar
        DB::table('t_input_gudang_hdr')
          ->where('kode_cabang', $user_cabang)
          ->where('kode_input', $data['no_ig'])
          ->update([
            'is_bayar' => 'Y', // ← bukan '1'
            'updated_at' => now(),
            'updated_by' => $data['updated_by'] ?? null,
          ]);
      }
    } catch (\Exception $e) {
      \Log::warning('syncOutstandingIg gagal: ' . $e->getMessage(), $data);
    }
  }


  // ================================================================
  // PRIVATE — Helper
  // ================================================================
  private function parseNumber($val): float
  {
    return (float) str_replace([',', '.'], ['', ''], $val ?? 0);
  }
}

