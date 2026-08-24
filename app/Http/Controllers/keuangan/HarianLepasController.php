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
use App\Models\HarianLepas;
use App\Models\HarianLepasDetail;
use App\Models\LogActivity;
use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class HarianLepasController extends Controller
{
  /**
   * Redirect to view.
   */
  public function HarianLepas(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Harian Lepas';

    $user_cabang = session('kd_cabang');

    $bank = Bank::query()
      ->select('kode_bank', 'nama_bank', 'no_rekening')
      ->where('is_active', 'Y')
      ->where('kode_cabang', $user_cabang)
      ->orderBy('nama_bank', 'asc')
      ->get();

    // Prepend KAS di posisi paling atas
    $kasItem = (object) [
      'kode_bank' => 'KAS',
      'nama_bank' => 'KAS',
      'no_rekening' => '',
    ];
    $bank = collect([$kasItem])->merge($bank);

    $jenisPekerjaan = Parameter::query()
      ->select('kode', 'keterangan')
      ->where('nama_tabel', 'JENIS_PEKERJAAN_BORONGAN')
      ->orderBy('no_urut', 'asc')
      ->get();

    // LogActivity::saveLogActivity("View {$title}");

    return view('content.keuangan.harian-lepas', compact(
      'title',
      'isList',
      'isAdd',
      'isEdit',
      'isDel',
      'bank',
      'jenisPekerjaan'
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

    // ── REKENING BY BANK ─────────────────────────────────────
    if ($request->filled('rekening') && $request->filled('kode_bank')) {

      if (strtoupper($request->kode_bank) === 'KAS') {
        return response()->json([]);
      }

      $data = DB::table('m_bank_fin')
        ->select('no_rekening')
        ->where('kode_cabang', $user_cabang)
        ->where('kode_bank', $request->kode_bank)
        ->where('is_active', 'Y')
        ->get();
      return response()->json($data);
    }


    // ── PEKERJA BY JENIS PEKERJAAN ────────────────────────────
    if ($request->filled('get_pekerja') && $request->filled('jenis_pekerjaan')) {
      $user_cabang = session('kd_cabang');
      $jenis = strtoupper(trim($request->jenis_pekerjaan));

      $karyawanKolom = ($jenis === '01') ? 'pekerja_las' : 'pekerja_dempul';

      $query = DB::table('m_karyawan as k')
        ->select('k.kode_karyawan', 'k.nama')
        ->where('k.kode_cabang', $user_cabang)
        ->where('k.status_aktif', 'Y')
        ->join(DB::raw("(
        SELECT DISTINCT {$karyawanKolom} as kode_karyawan
        FROM t_spk_master
        WHERE kode_cabang = '{$user_cabang}'
          AND tgl_turun_lapangan IS NOT NULL
    ) as spk"), 'spk.kode_karyawan', '=', 'k.kode_karyawan')
        ->orderBy('k.nama', 'asc');

      return response()->json($query->get());
    }

    // ── SPK LIST BY PEKERJA + JENIS PEKERJAAN ─────────────────
    if ($request->filled('get_spk')) {
      $kode_karyawan = $request->input('kode_karyawan', '');
      $jenis = strtoupper(trim($request->input('jenis_pekerjaan', '')));
      $current_id = $request->input('current_id', null);
      $search = $request->input('search.value', '');
      if (is_array($search))
        $search = '';

      // Mapping jenis → kolom tanggal mulai di t_spk_master
      $kolom = [
        '01' => ['tgl_las1', 'kode_karyawan_las'], //LAS KETOK
        '02' => ['tgl_dempul1', 'kode_karyawan_dempul'], //DEMPUL CAT
      ];

      // Ambil karyawan field sesuai jenis
      // Kolom pekerja di t_spk_master disesuaikan dengan aplikasi yang ada
      // (dari progres kerja: pekerja_las & pekerja_dempul disimpan di t_spk_master)
      $karyawanKolom = ($jenis === '01') ? 'pekerja_las' : 'pekerja_dempul';
      $tglMulaiKolom = ($jenis === '01') ? 'tgl_las1' : 'tgl_dempul1';

      // Upah per SPK per jenis: dari kolom yang disimpan saat input progres
      // Kolom upah: upah_las / upah_dempul (tambahan di t_spk_master sesuai meeting)
      $upahKolom = ($jenis === '01') ? 'upah_las' : 'upah_dempul';

      try {
        $query = DB::table('t_spk_master as k')
          ->leftJoin('m_tipe_kendaraan as t', function ($j) {
            $j->on('t.kode_tipe', '=', 'k.kode_tipe')
              ->on('t.kode_merek', '=', 'k.kode_merek');
          })
          ->leftJoin('m_jenis_kendaraan as j', 'j.kode_jenis', '=', 't.kode_jenis')
          ->select(
            'k.id',
            'k.kode_spk',
            'k.no_polisi',
            // 'k.pemilik',
            DB::raw("COALESCE(CONCAT(j.nama_jenis, ' ', t.nama_tipe), '-') as nama_tipe"),
            DB::raw("COALESCE(k.{$upahKolom}, 0) as upah"),
            // Hitung total yang sudah dibayar (sum dari detail HRL yang sudah ada)
            DB::raw("
              COALESCE((
                  SELECT SUM(d.persen)
                  FROM t_harian_lepas_dtl d
                  INNER JOIN t_harian_lepas_hdr h ON h.id = d.id_header
                  WHERE h.kode_cabang = k.kode_cabang
                    AND d.kode_spk = k.kode_spk
                    AND h.jenis_pekerjaan = '{$jenis}'
                    AND h.kode_karyawan = '{$kode_karyawan}'
                  " . ($current_id ? "AND h.id != {$current_id}" : "") . "
              ), 0) as persenSudah
            ")
          )
          ->where('k.kode_cabang', $user_cabang)
          // Hanya SPK yang sudah turun lapangan
          ->whereNotNull('k.tgl_turun_lapangan')
          // Hanya yang memiliki nilai upah > 0
          ->whereRaw("COALESCE(k.{$upahKolom}, 0) > 0");

        // Filter by pekerja
        if ($kode_karyawan) {
          $query->where("k.{$karyawanKolom}", $kode_karyawan);
        }

        if ($search) {
          $query->where(function ($q) use ($search) {
            $q->where('k.kode_spk', 'like', "%{$search}%")
              ->orWhere('k.no_polisi', 'like', "%{$search}%")
              ->orWhere('k.pemilik', 'like', "%{$search}%");
          });
        }

        $total = (clone $query)->count();
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        // $rows = $query
        //   ->orderBy('k.kode_spk', 'desc')
        //   ->offset($start)
        //   ->limit($length)
        //   ->get();
        // Mapping kolom untuk ordering
        $spkColumns = [
          1 => 'k.kode_spk',
          2 => 'nama_tipe',
          3 => 'k.no_polisi',
          4 => 'k.' . $upahKolom,
          5 => 'persenSudah',
          6 => 'persenSudah',
        ];
        $orderColIndex = (int) $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $spkColumns[$orderColIndex] ?? 'k.kode_spk';

        $rows = $query
          ->orderBy($orderCol, $orderDir)
          ->offset($start)
          ->limit($length)
          ->get();

        // Hitung sisa & persen yang belum terbayar
        $data = $rows->map(function ($row) {
          $upah = (float) $row->upah;
          $persenSudah = (float) $row->persenSudah;
          // $sisa = max(0, $upah - $sudahDibayar);
          // $persenSudah = $upah > 0 ? round(($sudahDibayar / $upah) * 100, 2) : 0;
          // $persenSisa = max(0, 100 - $persenSudah);
          $total = $upah * ($persenSudah / 100);
          $sisa = max(0, $upah - $total);

          return [
            'id' => $row->id,
            'kode_spk' => $row->kode_spk,
            'no_polisi' => $row->no_polisi,
            // 'pemilik' => $row->pemilik,
            'nama_tipe' => $row->nama_tipe,
            // 'upah' => $upah,
            'upah' => number_format($upah, 0, '.', ','),
            'total' => number_format($total, 0, '.', ','),
            // 'upah_fmt' => number_format($upah, 0, '.', ','),
            // 'sudah_dibayar' => $sudahDibayar,
            'sisa' => $sisa,
            // 'sisa_fmt' => number_format($sisa, 0, '.', ','),
            'persen_sudah' => $persenSudah,
            // 'persen_sisa' => $persenSisa,
          ];
        })->filter(fn($r) => $r['sisa'] > 0)->values(); // hanya tampilkan yang masih ada sisa

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

    // ── LISTING UTAMA ─────────────────────────────────────────
    try {
      $columns = [
        1 => 'a.id',
        2 => 'a.tanggal_transaksi',
        3 => 'a.no_transaksi',
        4 => 'd.keterangan', // nama_jenis_pekerjaan
        5 => 'a.nama_pekerja',
        6 => 'b.nama_bank',
        7 => 'a.no_rekening',
        8 => 'a.total_nilai',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'a.id';
      $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      $base = DB::table('t_harian_lepas_hdr as a')
        // ->leftJoin('m_bank_fin as b', 'b.kode_bank', '=', 'a.kode_bank')
        ->leftJoin('m_bank_fin as b', function ($join) {
          $join->on('b.kode_cabang', '=', 'a.kode_cabang')
            ->on('b.kode_bank', '=', 'a.kode_bank'); // syarat di JOIN
        })
        ->leftJoin('parameter as c', function ($join) {
          $join->on('c.kode', '=', 'a.jenis_pekerjaan')
            ->where('c.nama_tabel', '=', 'JENIS_PEKERJAAN_BORONGAN'); // syarat di JOIN
        })
        ->where('a.kode_cabang', $user_cabang);

      $totalData = (clone $base)->count('a.id');
      $query = clone $base;

      if ($request->filled('no_transaksi')) {
        $query->where('a.no_transaksi', 'like', '%' . $request->no_transaksi . '%');
      }
      if ($request->filled('nama_pekerja')) {
        $query->where('a.nama_pekerja', 'like', '%' . $request->nama_pekerja . '%');
      }
      // if ($request->filled('jenis_pekerjaan') && $request->jenis_pekerjaan !== 'all') {
      //   $query->where('a.jenis_pekerjaan', $request->jenis_pekerjaan);
      // }
      // SESUDAH
      if ($request->filled('jenis_pekerjaan') && $request->jenis_pekerjaan !== 'all') {
        $query->where('a.jenis_pekerjaan', $request->jenis_pekerjaan);
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
          'a.jenis_pekerjaan',
          'c.keterangan as nama_jenis_pekerjaan',
          'a.kode_karyawan',
          'a.nama_pekerja',
          'a.kode_bank',
          // 'b.nama_bank',
          // 'a.no_rekening',
          DB::raw("COALESCE(b.nama_bank, a.kode_bank) as nama_bank"),
          DB::raw("COALESCE(NULLIF(a.no_rekening, ''), '-') as no_rekening"),
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
          'tanggal_transaksi' => blank($row->tanggal_transaksi) ? '' : date('d/m/Y', strtotime($row->tanggal_transaksi)),
          'jenis_pekerjaan' => $row->jenis_pekerjaan,
          'nama_jenis_pekerjaan' => $row->nama_jenis_pekerjaan,
          'kode_karyawan' => $row->kode_karyawan,
          'nama_pekerja' => $row->nama_pekerja,
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
        'jenis_pekerjaan' => 'required',
        'nama_pekerja' => 'required|string|max:100',
        'kode_bank' => 'required',
        'no_rekening' => 'required_unless:kode_bank,KAS',
        'details' => 'required|string',
      ];

      $messages = [
        'tanggal_transaksi.required' => 'Tanggal wajib diisi',
        'jenis_pekerjaan.required' => 'Jenis Pekerjaan wajib dipilih',
        'nama_pekerja.required' => 'Nama Pekerja wajib dipilih',
        'kode_bank.required' => 'Keluar Kas/Bank wajib dipilih',
        'no_rekening.required_unless' => 'No. Rekening wajib diisi',
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

      // Parse detail JSON
      $detailRaw = json_decode($request->details, true);
      if (empty($detailRaw) || !is_array($detailRaw)) {
        return response()->json(['status' => false, 'message' => 'Detail SPK tidak boleh kosong.']);
      }

      // Validasi setiap baris
      foreach ($detailRaw as $idx => $row) {
        if (empty($row['kode_spk'])) {
          return response()->json(['status' => false, 'message' => "Kode SPK pada baris ke-" . ($idx + 1) . " tidak boleh kosong."]);
        }
        $persen = (float) ($row['persen'] ?? 0);
        if ($persen <= 0 || $persen > 100) {
          return response()->json(['status' => false, 'message' => "Persentase pada SPK {$row['kode_spk']} harus antara 1–100%."]);
        }
        $nilai = (float) str_replace([',', '.'], ['', ''], $row['nilai'] ?? 0);
        if ($nilai <= 0) {
          return response()->json(['status' => false, 'message' => "Nilai pada SPK {$row['kode_spk']} harus lebih dari 0."]);
        }
      }

      $totalNilai = collect($detailRaw)->sum(fn($r) => (float) str_replace([',', '.'], ['', ''], $r['nilai'] ?? 0));

      $tanggal = blank($request->tanggal_transaksi)
        ? null
        : Carbon::createFromFormat('d/m/Y', $request->tanggal_transaksi, 'Asia/Jakarta')->format('Y-m-d');

      DB::beginTransaction();

      if ($dataID) {
        // === UPDATE ===
        $headerData = [
          'tanggal_transaksi' => $tanggal,
          'jenis_pekerjaan' => $request->jenis_pekerjaan,
          'kode_karyawan' => $request->kode_karyawan,
          'nama_pekerja' => strtoupper($request->nama_pekerja),
          'kode_bank' => $request->kode_bank,
          'no_rekening' => $request->no_rekening,
          'total_nilai' => $totalNilai,
          'updated_by' => Auth::user()->username,
        ];

        $ok = HarianLepas::where('id', $dataID)->update($headerData);
        HarianLepasDetail::where('id_header', $dataID)->delete();

        $desc = $ok !== false ? 'Berhasil Ubah Harian Lepas' : 'Gagal Ubah Harian Lepas';
        $idHeader = $dataID;

      } else {
        // === INSERT ===
        $noTransaksi = Helper::getNomorTransaksi($user_cabang, 'HRL');

        $cek = HarianLepas::where('kode_cabang', $user_cabang)
          ->where('no_transaksi', $noTransaksi)->first();
        if ($cek) {
          DB::rollBack();
          return response()->json(['status' => false, 'message' => 'Nomor transaksi sudah digunakan.']);
        }

        $headerData = [
          'kode_cabang' => $user_cabang,
          'no_transaksi' => $noTransaksi,
          'tanggal_transaksi' => $tanggal,
          'jenis_pekerjaan' => $request->jenis_pekerjaan,
          'kode_karyawan' => $request->kode_karyawan,
          'nama_pekerja' => strtoupper($request->nama_pekerja),
          'kode_bank' => $request->kode_bank,
          'no_rekening' => $request->no_rekening,
          'total_nilai' => $totalNilai,
          'created_by' => Auth::user()->username,
        ];

        $header = HarianLepas::create($headerData);
        $ok = $header;

        if ($ok) {
          Helper::updateNomorTransaksi($user_cabang, 'HRL');
        }

        $desc = $ok ? 'Berhasil Tambah Harian Lepas' : 'Gagal Tambah Harian Lepas';
        $idHeader = $header->id ?? null;
      }

      // Insert detail
      if ($idHeader) {
        $detailInsert = [];
        foreach ($detailRaw as $row) {
          $upah = (float) str_replace([',', '.'], ['', ''], $row['upah'] ?? 0);
          $sisa = (float) str_replace([',', '.'], ['', ''], $row['sisa'] ?? 0);
          $persen = (float) ($row['persen'] ?? 0);
          $nilai = (float) str_replace([',', '.'], ['', ''], $row['nilai'] ?? 0);

          $detailInsert[] = [
            'id_header' => $idHeader,
            'kode_spk' => strtoupper($row['kode_spk']),
            'no_polisi' => $row['no_polisi'] ?? '',
            // 'nama_pemilik' => $row['nama_pemilik'] ?? '',
            'nama_tipe' => $row['nama_tipe'] ?? '',
            'upah' => $upah,
            'sisa' => $sisa,
            'persen' => $persen,
            'nilai' => $nilai,
            'created_at' => now(),
            'updated_at' => now(),
          ];
        }
        HarianLepasDetail::insert($detailInsert);
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
    $data = DB::table('t_harian_lepas_hdr as a')
      ->leftJoin('m_bank_fin as b', 'b.kode_bank', '=', 'a.kode_bank')
      ->where('a.id', $id)
      ->select([
        'a.id',
        'a.no_transaksi',
        'a.tanggal_transaksi',
        'a.jenis_pekerjaan',
        'a.kode_karyawan',
        'a.nama_pekerja',
        'a.kode_bank',
        'a.kode_cabang',
        // 'b.nama_bank',
        DB::raw("COALESCE(b.nama_bank, a.kode_bank) as nama_bank"),
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

    // $details = DB::table('t_harian_lepas_dtl')
    //   ->where('id_header', $id)
    //   // ->select('id', 'kode_spk', 'no_polisi', 'nama_pemilik', 'upah', 'persen', 'nilai')
    //   ->select('id', 'kode_spk', 'no_polisi', 'nama_tipe', 'upah', 'persen', 'nilai')
    //   ->orderBy('id')
    //   ->get()
    //   ->map(fn($d) => [
    //     'id' => $d->id,
    //     'kode_spk' => $d->kode_spk,
    //     'no_polisi' => $d->no_polisi,
    //     // 'nama_pemilik' => $d->nama_pemilik,
    //     'nama_tipe' => $d->nama_tipe,
    //     'upah' => (float) $d->upah,
    //     'upah_fmt' => number_format($d->upah, 0, '.', ','),
    //     'persen' => (float) $d->persen,
    //     'nilai' => (float) $d->nilai,
    //     'nilai_fmt' => number_format($d->nilai, 0, '.', ','),
    //   ]);
    $details = DB::table('t_harian_lepas_dtl as d')
      ->where('d.id_header', $id)
      ->select(
        'd.id',
        'd.kode_spk',
        'd.no_polisi',
        'd.nama_tipe',
        'd.upah',
        'd.persen',
        'd.nilai',
        DB::raw("
      COALESCE((
        SELECT SUM(d2.persen)
        FROM t_harian_lepas_dtl d2
        INNER JOIN t_harian_lepas_hdr h2 ON h2.id = d2.id_header
        WHERE h2.kode_cabang = '{$data->kode_cabang}'
          AND d2.kode_spk = d.kode_spk
          AND h2.jenis_pekerjaan = '{$data->jenis_pekerjaan}'
          AND h2.kode_karyawan = '{$data->kode_karyawan}'
          AND h2.id != {$id}
      ), 0) as persen_awal
    ")
      )
      ->orderBy('d.id')
      ->get()
      ->map(fn($d) => [
        'id' => $d->id,
        'kode_spk' => $d->kode_spk,
        'no_polisi' => $d->no_polisi,
        'nama_tipe' => $d->nama_tipe,
        'upah' => (float) $d->upah,
        'upah_fmt' => number_format($d->upah, 0, '.', ','),
        'persen' => (float) $d->persen,
        'persen_awal' => (float) $d->persen_awal,
        'total_awal' => round(((float) $d->persen_awal / 100) * (float) $d->upah),
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
    $data = HarianLepas::where('id', $id)->first();

    if (!$data) {
      return response()->json(['status' => false, 'message' => 'Data tidak ditemukan.']);
    }

    $arr = $data->toArray();
    $ok = $data->delete();

    $desc = $ok ? 'Berhasil Hapus Harian Lepas' : 'Gagal Hapus Harian Lepas';
    LogActivity::saveLogActivity($desc, $arr);

    return response()->json(['status' => (bool) $ok, 'message' => $desc]);
  }

  /**
   * Print Bukti Penerimaan Uang Harian Lepas
   */
  public function printHarianLepas(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $title = 'Bukti Penerimaan Uang';

    // Ambil id[] dari query string
    $ids = $request->input('id', []);
    $ids = array_filter(array_map('intval', (array) $ids));

    if (empty($ids)) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }
    // Ambil semua header
    $headers = DB::table('t_harian_lepas_hdr as a')
      ->leftJoin('parameter as p', function ($join) {
        $join->on('p.kode', '=', 'a.jenis_pekerjaan')
          ->where('p.nama_tabel', '=', 'JENIS_PEKERJAAN_BORONGAN');
      })
      ->whereIn('a.id', $ids)
      ->select([
        'a.id',
        'a.no_transaksi',
        'a.tanggal_transaksi',
        'a.jenis_pekerjaan',
        'p.keterangan as nama_jenis_pekerjaan',
        'a.kode_karyawan',
        'a.nama_pekerja',
        'a.kode_bank',
        'a.no_rekening',
        'a.total_nilai',
      ])
      ->get();

    if ($headers->isEmpty()) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }

    // Pakai data header pertama untuk info nama pekerja & jenis
    $firstHeader = $headers->first();

    // Ambil semua detail dari semua id
    $allDetails = DB::table('t_harian_lepas_dtl')
      ->whereIn('id_header', $ids)
      ->select('id', 'id_header', 'kode_spk', 'no_polisi', 'nama_tipe', 'upah', 'sisa', 'persen', 'nilai')
      ->orderBy('id_header')
      ->orderBy('id')
      ->get();

    // Total keseluruhan
    $totalNilai = $headers->sum('total_nilai');

    // Buat objek data gabungan
    $data = (object) [
      'no_transaksi' => $headers->count() == 1
        ? $firstHeader->no_transaksi
        : $headers->pluck('no_transaksi')->implode(', '),
      'tanggal_transaksi' => $firstHeader->tanggal_transaksi,
      'tanggal_fmt' => blank($firstHeader->tanggal_transaksi)
        ? '' : date('d-M-y', strtotime($firstHeader->tanggal_transaksi)),
      'nama_jenis_pekerjaan' => $firstHeader->nama_jenis_pekerjaan,
      'nama_pekerja' => $firstHeader->nama_pekerja,
      'total_nilai' => $totalNilai,
      'total_nilai_fmt' => number_format($totalNilai, 0, '.', ','),
    ];

    // Format detail
    // $details = $allDetails->map(function ($d) {
    //   $d->upah_fmt = number_format($d->upah, 0, '.', ',');
    //   $d->nilai_fmt = $d->nilai > 0 ? number_format($d->nilai, 0, '.', ',') : '-';
    //   $d->persen_fmt = $d->persen > 0 ? (int) $d->persen . '%' : '';
    //   return $d;
    // });
    $runningPersen = [];

    $details = $allDetails->map(function ($d) use (&$runningPersen) {
      $spk = $d->kode_spk;
      $persen = (float) $d->persen;

      if (!isset($runningPersen[$spk])) {
          $runningPersen[$spk] = 0;
      }
      $runningPersen[$spk] += $persen;

      $upah = (float) $d->upah;
      $sisa = (float) $d->sisa;
      $nilai = (float) $d->nilai;
      // $sisa = max(0, $upah - $nilai);

      $d->upah_kerja_fmt = number_format($upah, 0, '.', ',');
      $d->sisa_upah_fmt = number_format($sisa, 0, '.', ',');
      $d->upah_fmt = $d->upah_kerja_fmt; // fallback
      $d->nilai_fmt = $nilai > 0 ? number_format($nilai, 0, '.', ',') : '-';
      $d->persen_fmt = $d->persen > 0 ? (int) $d->persen . '%' : '';

      if ($runningPersen[$spk] >= 100) {
          $d->persen_fmt = '';
          $d->persen_selesai_fmt = '100%';
      } else {
          $d->persen_selesai_fmt = ''; 
      }

      return $d;
    });

    $cabang = DB::table('m_cabang')->where('kode_cabang', $user_cabang)->first();

    LogActivity::saveLogActivity("Print " . $title);

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.keuangan.harian-lepas-print', [
      'title' => $title,
      'data' => $data,
      'details' => $details,
      'cabang' => $cabang,
      'pageConfigs' => $pageConfigs,
    ]);
  }
}
