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
use App\Models\KasKecil;
use App\Models\LogActivity;
use Carbon\Carbon;

class InputKasController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function InputKas(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Input Kas';

    // Parameter jenis (PENERIMAAN / PENGELUARAN) — sama set dengan Input Bank
    $jenis = Parameter::query()
      ->where('nama_tabel', 'JENIS_BANK')
      ->orderBy('no_urut')
      ->get();

    // Transaksi masuk khusus kas (ada Isi Kas Kecil/DO)
    $jenisMasuk = Parameter::query()
      ->where('nama_tabel', 'JENIS_BANK_MASUK')
      ->orderBy('no_urut')
      ->get();

    $jenisKeluar = Parameter::query()
      ->where('nama_tabel', 'JENIS_BANK_KELUAR')
      ->orderBy('no_urut')
      ->get();

    // COA — pakai set yang sama dengan Input Bank
    // $coa = Parameter::query()
    //   ->where('nama_tabel', 'COA_INPUT_BANK')
    //   ->orderBy('no_urut')
    //   ->get();
    $coa = DB::table('m_coa')
      ->where('active_status', 'Y')
      ->orderBy('acct_cd')
      ->get();

    $spkList = DB::table('t_spk_master')
      ->select('kode_spk', 'no_polisi')
      ->where('kode_cabang', session('kd_cabang'))
      ->whereNotNull('tgl_turun_lapangan')
      ->orderBy('kode_spk', 'desc')
      ->get();

    // LogActivity::saveLogActivity("View {$title}");

    return view('content.keuangan.input-kas', compact(
      'title',
      'isList',
      'isAdd',
      'isEdit',
      'isDel',
      'jenis',
      'jenisMasuk',
      'jenisKeluar',
      'coa',
      'spkList'
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

    // ── Rekening: tidak ada (Input Kas tidak butuh pilih rekening bank)

    // ── List LCR ────────────────────────────────────────────────
    // if ($request->filled('get_lcr')) {
    //   $search = $request->input('search', '');
    //   $query = DB::table('t_penerimaan_gabungan_hdr as h')
    //     ->select('h.id', 'h.no_transaksi', 'h.tanggal_transaksi', 'h.nama_customer', 'h.total_nilai')
    //     ->where('h.kode_cabang', $user_cabang);

    //   if ($search) {
    //     $query->where(function ($q) use ($search) {
    //       $q->where('h.no_transaksi', 'like', "%{$search}%")
    //         ->orWhere('h.nama_customer', 'like', "%{$search}%");
    //     });
    //   }

    //   return response()->json(
    //     $query->orderBy('h.tanggal_transaksi', 'desc')->limit(100)->get()
    //   );
    // }

    if ($request->filled('get_lcr')) {
      $search = $request->input('search', '');
      $usedList = $this->getUsedInvoiceList($request, $user_cabang);

      $query = DB::table('t_penerimaan_gabungan_hdr as h')
        ->select('h.id', 'h.no_transaksi', 'h.tanggal_transaksi', 'h.nama_customer', 'h.total_nilai', 'h.pph')
        ->where('h.kode_cabang', $user_cabang);

      if (!empty($usedList)) {
        $query->whereNotIn('h.no_transaksi', $usedList);
      }

      if ($search) {
        $query->where(function ($q) use ($search) {
          $q->where('h.no_transaksi', 'like', "%{$search}%")
            ->orWhere('h.nama_customer', 'like', "%{$search}%");
        });
      }

      return response()->json(
        $query->orderBy('h.tanggal_transaksi', 'desc')->limit(100)->get()
      );
    }

    // ── List LSR ────────────────────────────────────────────────
    // if ($request->filled('get_lsr')) {
    //   $search = $request->input('search', '');
    //   $query = DB::table('t_pembayaran_gabungan_hdr as h')
    //     ->select('h.id', 'h.no_transaksi', 'h.tanggal_transaksi', 'h.nama_supplier', 'h.total_nilai')
    //     ->where('h.kode_cabang', $user_cabang);

    //   if ($search) {
    //     $query->where(function ($q) use ($search) {
    //       $q->where('h.no_transaksi', 'like', "%{$search}%")
    //         ->orWhere('h.nama_supplier', 'like', "%{$search}%");
    //     });
    //   }

    //   return response()->json(
    //     $query->orderBy('h.tanggal_transaksi', 'desc')->limit(100)->get()
    //   );
    // }
    if ($request->filled('get_lsr')) {
      $search = $request->input('search', '');
      $usedList = $this->getUsedInvoiceList($request, $user_cabang);

      $query = DB::table('t_pembayaran_gabungan_hdr as h')
        ->select('h.id', 'h.no_transaksi', 'h.tanggal_transaksi', 'h.nama_supplier', 'h.total_nilai')
        ->where('h.kode_cabang', $user_cabang);

      if (!empty($usedList)) {
        $query->whereNotIn('h.no_transaksi', $usedList);
      }

      if ($search) {
        $query->where(function ($q) use ($search) {
          $q->where('h.no_transaksi', 'like', "%{$search}%")
            ->orWhere('h.nama_supplier', 'like', "%{$search}%");
        });
      }

      return response()->json(
        $query->orderBy('h.tanggal_transaksi', 'desc')->limit(100)->get()
      );
    }

    // ── List HRL ────────────────────────────────────────────────
    if ($request->filled('get_hrl')) {
      return $this->getHrlList($request, $user_cabang);
    }

    // ── List UMJ ────────────────────────────────────────────────
    if ($request->filled('get_umj')) {
      return $this->getUmjList($request, $user_cabang);
    }

    // ── DataTable Utama ─────────────────────────────────────────
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
      $noUangMuka = null;
      $dp = 0;
      $noUangMukaLama = null;

      $validator = Validator::make($request->all(), [
        'tanggal' => 'required',
        'jenis' => 'required',
        'transaksi' => 'required',
        // 'jml_dibayar' => 'required',
        'account_coa' => 'required',
      ], [
        'tanggal.required' => 'Tanggal wajib diisi',
        'jenis.required' => 'Jenis wajib dipilih',
        'transaksi.required' => 'Transaksi wajib dipilih',
        // 'jml_dibayar.required' => 'Jumlah Dibayar wajib diisi',
        'account_coa.required' => 'Account / COA wajib dipilih',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => 'Gagal menyimpan data.',
          'errors' => $validator->errors(),
        ]);
      }

      $jenisKode = $request->jenis;
      $jenisValid = Parameter::where('nama_tabel', 'JENIS_BANK')
        ->where('kode', $jenisKode)
        ->exists();

      if (!$jenisValid) {
        return response()->json([
          'status' => false,
          'message' => 'Jenis transaksi tidak valid.',
        ]);
      }

      $showUangMuka = $jenisKode === 'PENERIMAAN';
      // Prefix kas: KM = Kas Masuk, KK = Kas Keluar
      $prefix = $jenisKode === 'PENERIMAAN' ? 'KM' : 'KK';

      $tanggal = Carbon::createFromFormat('d/m/Y', $request->tanggal, 'Asia/Jakarta')->format('Y-m-d');
      $nilai = $this->parseNumber($request->nilai ?? 0);
      // $jmlDibayar = $this->parseNumber($request->jml_dibayar ?? 0);
      $pph = $this->parseNumber($request->pph ?? 0);
      $biayaAdmin = $this->parseNumber($request->biaya_admin ?? 0);

      // Validasi & ambil nilai UMJ (hanya untuk Penerimaan)
      if ($showUangMuka && $request->filled('no_uang_muka')) {
        $result = $this->validateUmj($request->no_uang_muka, $user_cabang, $dataID);
        if (!$result['valid']) {
          return response()->json(['status' => false, 'message' => $result['message']]);
        }
        $noUangMuka = $result['no_uang_muka'];
        $dp = $result['dp'];
      }

      // Simpan UMJ lama sebelum update (untuk keperluan bebaskan UMJ lama)
      if ($dataID) {
        $noUangMukaLama = DB::table('t_input_kas')
          ->where('id', $dataID)
          ->value('no_uang_muka');
      }

      $sisa = $nilai - $dp - $pph - $biayaAdmin;

      DB::beginTransaction();

      $headerData = [
        'tanggal' => $tanggal,
        'jenis' => $jenisKode,
        'transaksi' => $request->transaksi,
        // 'no_inv_single' => $request->no_inv_single,
        'no_inv_gabung' => $request->no_inv_gabung,
        // Tidak ada kode_bank & no_rekening
        'nilai' => $nilai,
        // 'jml_dibayar' => $jmlDibayar,
        'dp' => $dp,
        'no_uang_muka' => $noUangMuka,
        'pph' => $pph,
        'biaya_admin' => $biayaAdmin,
        'sisa' => $sisa,
        'account_coa' => $request->account_coa,
        'no_spk' => $request->no_spk,
        'keterangan' => $request->keterangan,
      ];

      if ($dataID) {
        // ── UPDATE ──────────────────────────────────────────
        $headerData['updated_by'] = Auth::user()->username;
        $ok = KasKecil::where('id', $dataID)->update($headerData);

        // Urus perubahan UMJ
        if ($noUangMukaLama && $noUangMukaLama !== $noUangMuka) {
          $this->bebaskanUmj($noUangMukaLama, $user_cabang);
        }
        if ($noUangMuka && $noUangMuka !== $noUangMukaLama) {
          $this->tandaiUmjDipakai($noUangMuka, $user_cabang);
        }

        $desc = $ok !== false ? 'Berhasil Ubah Input Kas' : 'Gagal Ubah Input Kas';
      } else {
        // ── INSERT ──────────────────────────────────────────
        $noVoucher = \Helper::getNomorTransaksi($user_cabang, $prefix);

        if (KasKecil::where('kode_cabang', $user_cabang)->where('no_voucher', $noVoucher)->exists()) {
          DB::rollBack();
          return response()->json(['status' => false, 'message' => 'Nomor voucher sudah digunakan.']);
        }

        $headerData['kode_cabang'] = $user_cabang;
        $headerData['no_voucher'] = $noVoucher;
        // $headerData['status'] = '0';
        $headerData['created_by'] = Auth::user()->username;

        $ok = KasKecil::create($headerData);

        if ($ok) {
          \Helper::updateNomorTransaksi($user_cabang, $prefix);
          if ($noUangMuka) {
            $this->tandaiUmjDipakai($noUangMuka, $user_cabang);
          }
        }

        $desc = $ok ? 'Berhasil Tambah Input Kas' : 'Gagal Tambah Input Kas';
      }

      DB::commit();
      LogActivity::saveLogActivity($desc, $headerData);

      return response()->json(['status' => (bool) $ok, 'message' => $desc]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['status' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
  //  */
  // public function edit($id): JsonResponse
  // {
  //   $data = DB::table('t_input_kas')->where('id', $id)->first();

  //   if (blank($data)) {
  //     return response()->json(['status' => false, 'message' => 'Data tidak ditemukan.']);
  //   }

  //   $data->tanggal = blank($data->tanggal) ? '' : date('d/m/Y', strtotime($data->tanggal));
  //   $data->nilai_fmt = number_format($data->nilai, 0, '.', ',');
  //   // $data->jml_dibayar_fmt = number_format($data->jml_dibayar, 0, '.', ',');
  //   $data->dp_fmt = number_format($data->dp, 0, '.', ',');
  //   $data->pph_fmt = number_format($data->pph, 0, '.', ',');
  //   $data->biaya_admin_fmt = number_format($data->biaya_admin, 0, '.', ',');
  //   $data->sisa_fmt = number_format($data->sisa, 0, '.', ',');

  //   // Detail UMJ
  //   $data->nama_uang_muka = '';
  //   $data->nilai_uang_muka = 0;
  //   $data->nilai_uang_muka_fmt = '0';
  //   $data->jenis_penerimaan_uang_muka = '';
  //   $data->tanggal_uang_muka = '';

  //   if (!blank($data->no_uang_muka)) {
  //     $umj = DB::table('t_uang_muka_penjualan')
  //       ->where('no_transaksi', $data->no_uang_muka)
  //       ->select('nama', 'nilai', 'jenis_penerimaan', 'tanggal_transaksi')
  //       ->first();

  //     if ($umj) {
  //       $data->nama_uang_muka = $umj->nama ?? '';
  //       $data->nilai_uang_muka = $umj->nilai ?? 0;
  //       $data->nilai_uang_muka_fmt = number_format($umj->nilai ?? 0, 0, '.', ',');
  //       $data->jenis_penerimaan_uang_muka = $umj->jenis_penerimaan ?? '';
  //       $data->tanggal_uang_muka = !blank($umj->tanggal_transaksi)
  //         ? date('d/m/Y', strtotime($umj->tanggal_transaksi)) : '';
  //     }
  //   }

  //   return response()->json(['status' => true, 'message' => 'Berhasil', 'data' => $data]);
  // }
  public function edit($id): JsonResponse
  {
    $data = DB::table('t_input_kas')->where('id', $id)->first();

    if (blank($data)) {
      return response()->json(['status' => false, 'message' => 'Data tidak ditemukan.']);
    }

    $data->tanggal = blank($data->tanggal) ? '' : date('d/m/Y', strtotime($data->tanggal));
    $data->nilai_fmt = number_format($data->nilai, 0, '.', ',');
    $data->dp_fmt = number_format($data->dp, 0, '.', ',');
    $data->pph_fmt = number_format($data->pph, 0, '.', ',');
    $data->biaya_admin_fmt = number_format($data->biaya_admin, 0, '.', ',');
    $data->sisa_fmt = number_format($data->sisa, 0, '.', ',');

    // Detail UMJ
    $data->nama_uang_muka = '';
    $data->nilai_uang_muka = 0;
    $data->nilai_uang_muka_fmt = '0';
    $data->jenis_penerimaan_uang_muka = '';
    $data->tanggal_uang_muka = '';

    if (!blank($data->no_uang_muka)) {
      $umj = DB::table('t_uang_muka_penjualan')
        ->where('no_transaksi', $data->no_uang_muka)
        ->select('nama', 'nilai', 'jenis_penerimaan', 'tanggal_transaksi')
        ->first();

      if ($umj) {
        $data->nama_uang_muka = $umj->nama ?? '';
        $data->nilai_uang_muka = $umj->nilai ?? 0;
        $data->nilai_uang_muka_fmt = number_format($umj->nilai ?? 0, 0, '.', ',');
        $data->jenis_penerimaan_uang_muka = $umj->jenis_penerimaan ?? '';
        $data->tanggal_uang_muka = !blank($umj->tanggal_transaksi)
          ? date('d/m/Y', strtotime($umj->tanggal_transaksi)) : '';
      }
    }

    // [BARU] Rincian nilai per invoice gabungan
    $data->rincian_inv_gabung = [];

    if (!blank($data->no_inv_gabung)) {
      $list = array_filter(array_map('trim', explode(',', $data->no_inv_gabung)));

      if ($data->transaksi === 'Pembayaran Upah Harian Lepas') {
        $rows = DB::table('t_harian_lepas_hdr')
          ->whereIn('no_transaksi', $list)
          ->select('no_transaksi', 'total_nilai as nilai') // HRL tidak ada PPH
          ->get();
      } elseif ($data->jenis === 'PENERIMAAN') {
        $rows = DB::table('t_penerimaan_gabungan_hdr')
          ->whereIn('no_transaksi', $list)
          ->select('no_transaksi', 'total_nilai as nilai', 'pph') // ← tambah pph
          ->get();
      } elseif ($data->jenis === 'PENGELUARAN') {
        $rows = DB::table('t_pembayaran_gabungan_hdr')
          ->whereIn('no_transaksi', $list)
          ->select('no_transaksi', 'total_nilai as nilai') // sesuaikan kalau LSR ada kolom pph
          ->get();
      } else {
        $rows = collect();
      }

      $mapNilai = $rows->pluck('nilai', 'no_transaksi');
      $mapPph = $rows->pluck('pph', 'no_transaksi'); // ← tambah

      foreach ($list as $no) {
        $nilai = (float) ($mapNilai[$no] ?? 0);
        $pph = (float) ($mapPph[$no] ?? 0); // ← tambah
        $data->rincian_inv_gabung[] = [
          'no_transaksi' => $no,
          'nilai' => $nilai,
          'nilai_fmt' => number_format($nilai, 0, '.', ','),
          'pph' => $pph,
          'pph_fmt' => number_format($pph, 0, '.', ','), // ← tambah
        ];
      }
    }
    // [BARU] selesai

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
    $data = KasKecil::where('id', $id)->first();

    if (!$data) {
      return response()->json(['status' => false, 'message' => 'Data tidak ditemukan.']);
    }

    $arr = $data->toArray();
    $noUangMuka = $data->no_uang_muka;
    $user_cabang = session('kd_cabang');

    $ok = $data->delete();

    if ($ok && $noUangMuka) {
      $this->bebaskanUmj($noUangMuka, $user_cabang);
    }

    $desc = $ok ? 'Berhasil Hapus Input Kas' : 'Gagal Hapus Input Kas';
    LogActivity::saveLogActivity($desc, $arr);

    return response()->json(['status' => (bool) $ok, 'message' => $desc]);
  }

  // ================================================================
  // PRIVATE HELPERS
  // ================================================================

  private function getUmjList(Request $request, string $user_cabang): JsonResponse
  {
    $search = $request->input('search', '');
    $editId = $request->input('edit_id');

    try {
      $hasIsUsed = $this->columnExists('t_uang_muka_penjualan', 'is_used');

      $query = DB::table('t_uang_muka_penjualan as u')
        ->select(
          'u.id',
          'u.no_transaksi',
          'u.tanggal_transaksi',
          'u.jenis_penerimaan',
          'u.nama',
          'u.nilai'
        )
        ->where('u.kode_cabang', $user_cabang);

      if ($hasIsUsed) {
        $currentUmj = $editId
          ? DB::table('t_input_kas')->where('id', $editId)->value('no_uang_muka')
          : null;

        $query->where(function ($q) use ($currentUmj) {
          $q->where(function ($q2) {
            $q2->where('u.is_used', '!=', 'Y')->orWhereNull('u.is_used');
          });
          if ($currentUmj) {
            $q->orWhere('u.no_transaksi', $currentUmj);
          }
        });
      } else {
        // Fallback: exclude UMJ yang sudah dipakai di t_input_kas
        $sudahDipakai = DB::table('t_input_kas')
          ->where('kode_cabang', $user_cabang)
          ->whereNotNull('no_uang_muka')
          ->where('no_uang_muka', '!=', '');

        if ($editId) {
          $sudahDipakai->where('id', '!=', $editId);
        }

        $arr = $sudahDipakai->pluck('no_uang_muka')->toArray();
        if (!empty($arr)) {
          $query->whereNotIn('u.no_transaksi', $arr);
        }
      }

      if ($search) {
        $query->where(function ($q) use ($search) {
          $q->where('u.no_transaksi', 'like', "%{$search}%")
            ->orWhere('u.nama', 'like', "%{$search}%");
        });
      }

      return response()->json(
        $query->orderBy('u.tanggal_transaksi', 'desc')->limit(100)->get()
      );
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }


  /**
   * Query list HRL untuk popup.
   */
  // private function getHrlList(Request $request, string $user_cabang): JsonResponse
  // {
  //   $search = $request->input('search', '');
  //   $query = DB::table('t_harian_lepas_hdr as h')
  //     ->leftJoin('parameter as p', function ($j) {
  //       $j->on('p.kode', '=', 'h.jenis_pekerjaan')
  //         ->where('p.nama_tabel', '=', 'JENIS_PEKERJAAN_BORONGAN');
  //     })
  //     ->select(
  //       'h.id',
  //       'h.no_transaksi',
  //       DB::raw("DATE_FORMAT(h.tanggal_transaksi, '%d/%m/%Y') as tanggal_transaksi"),
  //       'h.nama_pekerja',
  //       'p.keterangan as nama_jenis_pekerjaan',
  //       'h.total_nilai',
  //       'h.total_nilai as total_nilai_raw'
  //     )
  //     ->where('h.kode_cabang', $user_cabang);

  //   if ($search) {
  //     $query->where(function ($q) use ($search) {
  //       $q->where('h.no_transaksi', 'like', "%{$search}%")
  //         ->orWhere('h.nama_pekerja', 'like', "%{$search}%");
  //     });
  //   }

  //   return response()->json(
  //     $query->orderBy('h.tanggal_transaksi', 'desc')->limit(100)->get()
  //   );
  // }
  private function getHrlList(Request $request, string $user_cabang): JsonResponse
  {
    $search = $request->input('search', '');
    $usedList = $this->getUsedInvoiceList($request, $user_cabang);

    $query = DB::table('t_harian_lepas_hdr as h')
      ->leftJoin('parameter as p', function ($j) {
        $j->on('p.kode', '=', 'h.jenis_pekerjaan')
          ->where('p.nama_tabel', '=', 'JENIS_PEKERJAAN_BORONGAN');
      })
      ->select(
        'h.id',
        'h.no_transaksi',
        DB::raw("DATE_FORMAT(h.tanggal_transaksi, '%d/%m/%Y') as tanggal_transaksi"),
        'h.nama_pekerja',
        'p.keterangan as nama_jenis_pekerjaan',
        'h.total_nilai',
        'h.total_nilai as total_nilai_raw'
      )
      ->where('h.kode_cabang', $user_cabang);

    if (!empty($usedList)) {
      $query->whereNotIn('h.no_transaksi', $usedList);
    }

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('h.no_transaksi', 'like', "%{$search}%")
          ->orWhere('h.nama_pekerja', 'like', "%{$search}%");
      });
    }

    return response()->json(
      $query->orderBy('h.tanggal_transaksi', 'desc')->limit(100)->get()
    );
  }

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
        8 => 'h.nilai',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'h.id';
      $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      $base = DB::table('t_input_kas as h')
        ->where('h.kode_cabang', $user_cabang);

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
        ->select([
          'h.id',
          'h.no_voucher',
          'h.tanggal',
          'h.jenis',
          'h.transaksi',
          'h.no_inv_gabung',
          'h.no_spk',
          'h.nilai',
          'h.no_uang_muka',
          'h.account_coa',
          'h.keterangan',
        ])
        ->orderBy($order, $dir)
        ->offset($start)
        ->limit($limit)
        ->get();

      // ── Lookup nama COA (sama seperti InputBankController) ──────
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
        // Cari nama COA berdasarkan kode (case-insensitive)
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
          'jenis' => $row->jenis,
          'transaksi' => $row->transaksi,
          'no_inv_gabung' => $row->no_inv_gabung,
          'no_spk' => $row->no_spk,
          'nilai' => number_format($row->nilai, 0, '.', ','),
          'no_uang_muka' => $row->no_uang_muka,
          'account_coa' => $namaCoa ?? $row->account_coa, // ← tampilkan nama, fallback ke kode
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

  private function validateUmj(string $noUangMukaRaw, string $user_cabang, $dataID): array
  {
    $noUangMuka = strtoupper(trim($noUangMukaRaw));

    $umjRecord = DB::table('t_uang_muka_penjualan')
      ->where('no_transaksi', $noUangMuka)
      ->where('kode_cabang', $user_cabang)
      ->select('nilai', 'is_used')
      ->first();

    if (!$umjRecord) {
      return ['valid' => false, 'message' => 'Nomor Uang Muka tidak ditemukan.', 'no_uang_muka' => '', 'dp' => 0];
    }

    $isUsed = $umjRecord->is_used ?? null;

    if ($isUsed === 'Y') {
      if ($dataID) {
        $ownerUmj = DB::table('t_input_kas')->where('id', $dataID)->value('no_uang_muka');
        if ($ownerUmj !== $noUangMuka) {
          return ['valid' => false, 'message' => 'Uang Muka sudah digunakan di transaksi lain.', 'no_uang_muka' => '', 'dp' => 0];
        }
      } else {
        return ['valid' => false, 'message' => 'Uang Muka sudah digunakan di transaksi lain.', 'no_uang_muka' => '', 'dp' => 0];
      }
    }

    return [
      'valid' => true,
      'message' => '',
      'no_uang_muka' => $noUangMuka,
      'dp' => (float) ($umjRecord->nilai ?? 0),
    ];
  }

  private function tandaiUmjDipakai(string $noUangMuka, string $cabang): void
  {
    try {
      if (!$this->columnExists('t_uang_muka_penjualan', 'is_used'))
        return;

      DB::table('t_uang_muka_penjualan')
        ->where('no_transaksi', $noUangMuka)
        ->where('kode_cabang', $cabang)
        ->update(['is_used' => 'Y', 'updated_at' => now()]);
    } catch (\Exception $e) {
    }
  }

  private function bebaskanUmj(string $noUangMuka, string $cabang): void
  {
    try {
      if (!$this->columnExists('t_uang_muka_penjualan', 'is_used'))
        return;

      // Cek apakah UMJ ini masih dipakai di t_input_kas maupun t_input_bank
      $masihDipakai = DB::table('t_input_kas')
        ->where('kode_cabang', $cabang)
        ->where('no_uang_muka', $noUangMuka)
        ->exists();

      if (!$masihDipakai) {
        // Cek juga di t_input_bank agar tidak salah bebaskan
        $masihDiBank = DB::table('t_input_bank')
          ->where('kode_cabang', $cabang)
          ->where('no_uang_muka', $noUangMuka)
          ->exists();

        if (!$masihDiBank) {
          DB::table('t_uang_muka_penjualan')
            ->where('no_transaksi', $noUangMuka)
            ->where('kode_cabang', $cabang)
            ->update(['is_used' => 'N', 'updated_at' => now()]);
        }
      }
    } catch (\Exception $e) {
    }
  }

  private function columnExists(string $table, string $column): bool
  {
    static $cache = [];
    $key = "{$table}.{$column}";
    if (!isset($cache[$key])) {
      try {
        DB::table($table)->select($column)->limit(1)->get();
        $cache[$key] = true;
      } catch (\Exception $e) {
        $cache[$key] = false;
      }
    }
    return $cache[$key];
  }

  private function parseNumber($val): float
  {
    return (float) str_replace([',', '.'], ['', ''], $val ?? 0);
  }

  /**
   * Cetak Bukti Penerimaan/Pengeluaran
   */

  public function cetakInputKas(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $title = 'Bukti Kas';

    $ids = $request->input('id', []);
    $ids = array_filter(array_map('intval', (array) $ids));

    if (empty($ids)) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }

    // $headers = DB::table('t_input_kas as h')
    //   ->whereIn('h.id', $ids)
    //   ->where('h.kode_cabang', $user_cabang)
    //   ->select([
    //     'h.id',
    //     'h.no_voucher',
    //     'h.tanggal',
    //     'h.jenis',
    //     'h.transaksi',
    //     'h.no_inv_gabung',
    //     'h.no_spk',
    //     'h.nilai',
    //     'h.account_coa',
    //     'h.keterangan',
    //   ])
    //   ->orderBy('h.tanggal')
    //   ->get();
    $headers = DB::table('t_input_kas as h')
      ->whereIn('h.id', $ids)
      ->where('h.kode_cabang', $user_cabang)
      ->select([
        'h.id',
        'h.no_voucher',
        'h.tanggal',
        'h.jenis',
        'h.transaksi',
        'h.no_inv_gabung',
        'h.no_spk',
        'h.nilai',
        'h.pph',
        'h.account_coa',
        'h.keterangan',
      ])
      ->orderBy('h.tanggal')
      ->get();

    if ($headers->isEmpty()) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }

    // Map nama COA
    $kodeCoa = $headers->pluck('account_coa')->filter()->unique()->values()->toArray();
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

    $namaCoaOf = function ($kode) use ($mapCoa) {
      foreach ($mapCoa as $k => $nama) {
        if (strtolower(trim($k)) === strtolower(trim($kode))) {
          return $nama;
        }
      }
      return null;
    };

    // $totalNilai = 0;
    // $details = $headers->map(function ($row) use ($namaCoaOf, &$totalNilai) {
    //   $uraian = trim(($row->transaksi ?? '') . ($row->keterangan ? ' : ' . $row->keterangan : ''));
    //   $totalNilai += (float) $row->nilai;

    //   return (object) [
    //     'no_voucher' => $row->no_voucher,
    //     'account_coa' => $namaCoaOf($row->account_coa) ?? $row->account_coa,
    //     'uraian' => $uraian !== '' ? $uraian : ($namaCoaOf($row->account_coa) ?? $row->account_coa),
    //     'no_spk' => $row->no_spk,
    //     'no_inv_gabung' => $row->no_inv_gabung,
    //     'nilai_fmt' => number_format($row->nilai, 0, '.', ','),
    //   ];
    // });
    $totalNilai = 0;
    $totalPph = 0;
    $details = $headers->map(function ($row) use ($namaCoaOf, &$totalNilai, &$totalPph) {
      $uraian = trim(($row->transaksi ?? '') . ($row->keterangan ? ' : ' . $row->keterangan : ''));
      $totalNilai += (float) $row->nilai;
      $totalPph += (float) ($row->pph ?? 0);

      return (object) [
        'no_voucher' => $row->no_voucher,
        'account_coa' => $namaCoaOf($row->account_coa) ?? $row->account_coa,
        'uraian' => $uraian !== '' ? $uraian : ($namaCoaOf($row->account_coa) ?? $row->account_coa),
        'no_spk' => $row->no_spk,
        'no_inv_gabung' => $row->no_inv_gabung,
        'nilai_fmt' => number_format($row->nilai, 0, '.', ','),
        'pph_fmt' => number_format($row->pph ?? 0, 0, '.', ','),
      ];
    });

    $jenisUtama = $headers->pluck('jenis')->unique()->first();
    $firstHeader = $headers->first();

    // $data = (object) [
    //   'tanggal_fmt' => blank($firstHeader->tanggal) ? '' : date('d-M-Y', strtotime($firstHeader->tanggal)),
    //   'judul' => $jenisUtama === 'PENERIMAAN' ? 'BUKTI PENERIMAAN KAS' : 'BUKTI PENGELUARAN KAS',
    //   'total_nilai_fmt' => number_format($totalNilai, 0, '.', ','),
    //   'terbilang' => $this->terbilang($totalNilai) . ' RUPIAH',
    // ];
    $data = (object) [
      'tanggal_fmt' => blank($firstHeader->tanggal) ? '' : date('d-M-Y', strtotime($firstHeader->tanggal)),
      'judul' => $jenisUtama === 'PENERIMAAN' ? 'BUKTI PENERIMAAN KAS' : 'BUKTI PENGELUARAN KAS',
      'total_nilai_fmt' => number_format($totalNilai, 0, '.', ','),
      'total_pph_fmt' => number_format($totalPph, 0, '.', ','),
      'terbilang' => $this->terbilang($totalNilai) . ' RUPIAH',
      'no_voucher_utama' => $firstHeader->no_voucher ?? '',
    ];

    $cabang = DB::table('m_cabang')->where('kode_cabang', $user_cabang)->first();

    LogActivity::saveLogActivity("Print {$title}");

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.keuangan.input-kas-cetak', [
      'title' => $title,
      'data' => $data,
      'details' => $details,
      'cabang' => $cabang,
      'pageConfigs' => $pageConfigs,
    ]);
  }

  private function terbilang($angka): string
  {
    $angka = (int) round(abs($angka));

    $huruf = ['', 'SATU', 'DUA', 'TIGA', 'EMPAT', 'LIMA', 'ENAM', 'TUJUH', 'DELAPAN', 'SEMBILAN', 'SEPULUH', 'SEBELAS'];

    if ($angka < 12) {
      $hasil = $huruf[$angka];
    } elseif ($angka < 20) {
      $hasil = $this->terbilang($angka - 10) . ' BELAS';
    } elseif ($angka < 100) {
      $hasil = $this->terbilang(intval($angka / 10)) . ' PULUH ' . $this->terbilang($angka % 10);
    } elseif ($angka < 200) {
      $hasil = 'SERATUS ' . $this->terbilang($angka - 100);
    } elseif ($angka < 1000) {
      $hasil = $this->terbilang(intval($angka / 100)) . ' RATUS ' . $this->terbilang($angka % 100);
    } elseif ($angka < 2000) {
      $hasil = 'SERIBU ' . $this->terbilang($angka - 1000);
    } elseif ($angka < 1000000) {
      $hasil = $this->terbilang(intval($angka / 1000)) . ' RIBU ' . $this->terbilang($angka % 1000);
    } elseif ($angka < 1000000000) {
      $hasil = $this->terbilang(intval($angka / 1000000)) . ' JUTA ' . $this->terbilang($angka % 1000000);
    } elseif ($angka < 1000000000000) {
      $hasil = $this->terbilang(intval($angka / 1000000000)) . ' MILYAR ' . $this->terbilang($angka % 1000000000);
    } else {
      $hasil = $this->terbilang(intval($angka / 1000000000000)) . ' TRILIUN ' . $this->terbilang($angka % 1000000000000);
    }

    return preg_replace('/\s+/', ' ', trim($hasil));
  }

  /**
   * Ambil daftar no_transaksi (invoice) yang sudah dipakai di t_input_kas.
   * Exclude record yang sedang diedit (kalau ada edit_id).
   */
  private function getUsedInvoiceList(Request $request, string $user_cabang): array
  {
    $usedQuery = DB::table('t_input_kas')
      ->where('kode_cabang', $user_cabang)
      ->whereNotNull('no_inv_gabung')
      ->where('no_inv_gabung', '!=', '');

    $usedRaw = $usedQuery->pluck('no_inv_gabung')->toArray();

    $usedList = [];
    foreach ($usedRaw as $raw) {
      foreach (explode(',', $raw) as $no) {
        $no = trim($no);
        if ($no !== '')
          $usedList[] = $no;
      }
    }

    return array_unique($usedList);
  }
}
