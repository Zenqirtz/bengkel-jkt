<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\ProfilePerusahaan;
use App\Models\Spk;
use App\Models\Kwitansi;
use App\Models\LogActivity;
use App\Helpers\Helpers as Helper;
use Carbon\Carbon;

class DashboardController extends Controller
{

  /**
   * Master definisi semua kartu ringkasan bulanan yang mungkin muncul.
   * key => [label, icon, warna, callback penghitung nilai]
   * Callback menerima ($kode_cabang, $bulan, $tahun) dan mengembalikan array:
   *   ['value' => ..., 'sub' => ..., 'is_currency' => bool]
   */
  protected function cardDefinitions(): array
  {
    return [
      'outstanding_belum_tagih' => [
        'label' => 'Outstanding',
        'icon' => 'ri-alarm-warning-line',
        'color' => 'danger',
        'badge' => 'WARNING',
        'sub_label' => 'Belum tertagih',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $row = DB::table('v_rep_kwt_or_belum_ditagih')
            ->where('kode_cabang', $kode_cabang)
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total_or),0) as total')
            ->first();
          return ['value' => $row->total ?? 0, 'sub' => ($row->jumlah ?? 0) . ' Belum tertagih', 'is_currency' => true];
        },
      ],

      'kwitansi_terbit' => [
        'label' => 'Kwitansi Terbit',
        'icon' => 'ri-receipt-line',
        'color' => 'success',
        'sub_label' => 'Kwitansi',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $startDate = sprintf('%04d-%02d-01 00:00:00', $tahun, $bulan);
          $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
          $row = DB::table('t_kwitansi')
            ->where('kode_cabang', $kode_cabang)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(grand_total),0) as total')
            ->first();
          return ['value' => $row->total ?? 0, 'sub' => ($row->jumlah ?? 0) . ' Kwitansi', 'is_currency' => true];
        },
      ],

      'invoice_or' => [
        'label' => 'Invoice OR',
        'icon' => 'ri-money-dollar-circle-line',
        'color' => 'primary',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $startDate = sprintf('%04d-%02d-01 00:00:00', $tahun, $bulan);
          $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
          $total = DB::table('t_spk_master')
            ->where('kode_cabang', $kode_cabang)
            ->where('kode_jenis_pelanggan', '00001')
            ->where('ada_or', '01')
            ->whereBetween('tgl_masuk', [$startDate, $endDate])
            ->sum('total_or');
          return ['value' => $total, 'sub' => null, 'is_currency' => true];
        },
      ],

      'invoice_or_terbit' => [
        'label' => 'Invoice OR Terbit',
        'icon' => 'ri-money-dollar-circle-line',
        'color' => 'primary',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $startDate = sprintf('%04d-%02d-01 00:00:00', $tahun, $bulan);
          $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
          $row = DB::table('t_spk_master')
            ->where('kode_cabang', $kode_cabang)
            ->where('kode_jenis_pelanggan', '00001')
            ->where('ada_or', '01')
            ->whereBetween('tgl_invoice', [$startDate, $endDate])
            ->whereNotNull('no_invoice')
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total_or),0) as total')
            ->first();
          return ['value' => $row->total ?? 0, 'sub' => ($row->jumlah ?? 0) . ' Invoice', 'is_currency' => true];
        },
      ],

      'outstanding_tawar' => [
        'label' => 'Outstanding (Tawar)',
        'icon' => 'ri-price-tag-3-line',
        'color' => 'warning',
        'sub_label' => 'Belum jadi estimasi disetujui',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $row = DB::table('t_estimasi_hdr as a')
            ->leftJoin('t_kwitansi as g', function($j) {
              $j->on('a.kode_cabang', '=', 'g.kode_cabang')
                ->on('a.kode_spk', '=', 'g.kode_spk')
                ->on('a.kode_estimasi', '=', 'g.kode_estimasi');
            })
            ->where('a.kode_cabang', $kode_cabang)
            ->whereNull('a.batal_oleh')
            ->whereNull('a.tgl_batal')
            ->where(function($q) {
              $q->whereNull('g.tanggal')->orWhere('g.tanggal', '>=', DB::raw('CURDATE()'));
            })
            ->selectRaw("
              COUNT(*) as jumlah,
              COALESCE(SUM(
                (a.total_perbaikan - (a.disc_perbaikan * a.total_perbaikan / 100)) +
                (a.total_sparepart - (a.disc_sparepart * a.total_sparepart / 100)) +
                (a.total_lain - (a.disc_lain * a.total_lain / 100)) +
                a.ppn
              ), 0) as total
            ")
            ->first();
          return ['value' => $row->total ?? 0, 'sub' => ($row->jumlah ?? 0) . ' SPK', 'is_currency' => true];
        },
      ],

      'outstanding_tagih' => [
        'label' => 'Outstanding (Tagih)',
        'icon' => 'ri-file-list-3-line',
        'color' => 'warning',
        'sub_label' => 'Kwitansi belum ditagih',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $row = DB::table('v_rep_os_penagihan_rkp')
            ->where('kode_cabang', $kode_cabang)
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total),0) as total')
            ->first();
          return ['value' => $row->total ?? 0, 'sub' => ($row->jumlah ?? 0) . ' SPK', 'is_currency' => true];
        },
      ],

      'outstanding_or' => [
        'label' => 'Outstanding (OR)',
        'icon' => 'ri-shield-check-line',
        'color' => 'warning',
        'sub_label' => 'Invoice OR belum lunas',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $row = DB::table('v_rpt_outstanding_or')
            ->where('kode_cabang', $kode_cabang)
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total_or),0) as total')
            ->first();
          return ['value' => $row->total ?? 0, 'sub' => ($row->jumlah ?? 0) . ' Invoice', 'is_currency' => true];
        },
      ],

      'mobil_masuk' => [
        'label' => 'Mobil Masuk',
        'icon' => 'ri-car-line',
        'color' => 'info',
        'sub_label' => 'Total bulan ini',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $startDate = sprintf('%04d-%02d-01 00:00:00', $tahun, $bulan);
          $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
          $jumlah = DB::table('t_spk_master')
            ->where('kode_cabang', $kode_cabang)
            ->whereBetween('tgl_masuk', [$startDate, $endDate])
            ->count();
          return ['value' => $jumlah, 'sub' => 'Total bulan ini', 'is_currency' => false];
        },
      ],

      'mobil_keluar' => [
        'label' => 'Mobil Keluar',
        'icon' => 'ri-car-line',
        'color' => 'info',
        'sub_label' => 'Total bulan ini',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $startDate = sprintf('%04d-%02d-01 00:00:00', $tahun, $bulan);
          $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
          $jumlah = DB::table('t_spk_master')
            ->where('kode_cabang', $kode_cabang)
            ->whereBetween('tgl_keluar', [$startDate, $endDate])
            ->whereNotNull('tgl_keluar')
            ->count();
          return ['value' => $jumlah, 'sub' => 'Total bulan ini', 'is_currency' => false];
        },
      ],

      'pelunasan' => [
        'label' => 'Pelunasan',
        'icon' => 'ri-checkbox-circle-line',
        'color' => 'success',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $startDate = sprintf('%04d-%02d-01 00:00:00', $tahun, $bulan);
          $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
          $row = DB::table('v_rpt_pelunasan_or')
            ->where('kode_cabang', $kode_cabang)
            ->whereBetween('tanggal_lunas_or', [$startDate, $endDate])
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total_or),0) as total')
            ->first();
          return ['value' => $row->total ?? 0, 'sub' => ($row->jumlah ?? 0) . ' Invoice', 'is_currency' => true];
        },
      ],

      'spk_pending' => [
        'label' => 'SPK Yang Harus Diselesaikan',
        'icon' => 'ri-time-line',
        'color' => 'danger',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $jumlah = DB::table('t_spk_master')
            ->where('kode_cabang', $kode_cabang)
            ->where('tgl_turun_lapangan', '>=', '2008-10-01')
            ->whereNull('kode_keluar')
            ->count();
          return ['value' => $jumlah, 'sub' => null, 'is_currency' => false];
        },
      ],

      'spk_turun_lapangan' => [
        'label' => 'SPK Turun Lapangan',
        'icon' => 'ri-tools-line',
        'color' => 'info',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $startDate = sprintf('%04d-%02d-01 00:00:00', $tahun, $bulan);
          $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
          $jumlah = DB::table('t_spk_master')
            ->where('kode_cabang', $kode_cabang)
            ->whereBetween('tgl_turun_lapangan', [$startDate, $endDate])
            ->whereNull('batal_by')
            ->count();
          return ['value' => $jumlah, 'sub' => null, 'is_currency' => false];
        },
      ],

      'mobil_rawat_jalan' => [
        'label' => 'Mobil Rawat Jalan',
        'icon' => 'ri-heart-pulse-line',
        'color' => 'info',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $startDate = sprintf('%04d-%02d-01 00:00:00', $tahun, $bulan);
          $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
          $jumlah = DB::table('t_spk_master')
            ->where('kode_cabang', $kode_cabang)
            ->where('ada_rawat_jalan', '1')
            ->whereBetween('tgl_masuk', [$startDate, $endDate])
            ->count();
          return ['value' => $jumlah, 'sub' => null, 'is_currency' => false];
        },
      ],

      'estimasi_belum_dibuat' => [
        'label' => 'Estimasi Belum Dibuat',
        'icon' => 'ri-file-list-3-line',
        'color' => 'danger',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $jumlah = DB::table('t_spk_master')
            ->where('kode_cabang', $kode_cabang)
            ->whereIn('status_spk', ['01', '02'])
            ->whereNull('batal_by')
            ->count();
          return ['value' => $jumlah, 'sub' => null, 'is_currency' => false];
        },
      ],

      'estimasi_belum_dikirim' => [
        'label' => 'Estimasi Belum Dikirim',
        'icon' => 'ri-check-double-line',
        'color' => 'danger',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $jumlah = DB::table('v_rep_estimasi_belum_dikirim')
            ->where('kode_cabang', $kode_cabang)
            ->count();
          return ['value' => $jumlah, 'sub' => null, 'is_currency' => false];
        },
      ],

      'kwitansi_belum_dikirim' => [
        'label' => 'Kwitansi Belum Dikirim',
        'icon' => 'ri-mail-send-line',
        'color' => 'danger',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $startDate = sprintf('%04d-%02d-01 00:00:00', $tahun, $bulan);
          $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
          $jumlah = DB::table('t_kwitansi as k')
            ->where('k.kode_cabang', $kode_cabang)
            ->whereBetween('k.tanggal', [$startDate, $endDate])
            ->whereNotExists(function ($q) {
              $q->select(DB::raw(1))
                ->from('t_kirim_kwitansi as tk')
                ->whereColumn('tk.kode_cabang', 'k.kode_cabang')
                ->whereColumn('tk.kode_spk', 'k.kode_spk')
                ->whereColumn('tk.kode_kwitansi', 'k.kode_kwitansi');
            })
            ->count();
          return ['value' => $jumlah, 'sub' => null, 'is_currency' => false];
        },
      ],

      'total_permintaan' => [
        'label' => 'Total Permintaan',
        'icon' => 'ri-shopping-cart-line',
        'color' => 'primary',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $startDate = sprintf('%04d-%02d-01 00:00:00', $tahun, $bulan);
          $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
          $jumlah = DB::table('t_permintaan_barang_hdr')
            ->where('kode_cabang', $kode_cabang)
            ->whereBetween('tanggal_permintaan', [$startDate, $endDate])
            ->count();
          return ['value' => $jumlah, 'sub' => null, 'is_currency' => false];
        },
      ],

      'po_belum_dibuat' => [
        'label' => 'PO Belum Dibuat',
        'icon' => 'ri-file-warning-line',
        'color' => 'danger',
        'calc' => function ($kode_cabang, $bulan, $tahun) {
          $startDate = sprintf('%04d-%02d-01 00:00:00', $tahun, $bulan);
          $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
          $jumlah = DB::table('t_permintaan_barang_hdr as p')
            ->where('p.kode_cabang', $kode_cabang)
            ->whereBetween('p.tanggal_permintaan', [$startDate, $endDate])
            ->whereNotExists(function ($q) {
              $q->select(DB::raw(1))
                ->from('t_order_hdr as o')
                ->whereColumn('o.kode_cabang', 'p.kode_cabang')
                ->whereColumn('o.kode_permintaan', 'p.kode_permintaan')
                ->where(function ($qq) {
                  $qq->whereNull('o.batal')->orWhere('o.batal', '!=', '1');
                });
            })
            ->count();
          return ['value' => $jumlah, 'sub' => null, 'is_currency' => false];
        },
      ],
    ];
  }

  /**
   * Susunan kartu untuk tiap role. Urutan array = urutan tampil di dashboard.
   * $groupNama = nama grup dari tabel `group` (sudah di-uppercase & trim),
   * di-resolve dari users.user_group. Role ditentukan dari group, BUKAN
   * user_level (user_level cuma bedain level admin/user biasa).
   * Group yang belum ada di spesifikasi (mis. KEPALA CABANG, ADMIN HO,
   * KEPALA BENGKEL, HRD, KASIR) sementara jatuh ke default (set Super Admin) —
   * sesuaikan kalau perlu set kartu tersendiri.
   */
  protected function cardsForRole(string $groupNama): array
  {
    $superAdminCards = [
      'outstanding_belum_tagih', 'kwitansi_terbit', 'invoice_or',
      'outstanding_tawar', 'outstanding_or', 'mobil_masuk', 'mobil_keluar', 'pelunasan',
    ];

    return match (true) {
      in_array($groupNama, ['TOP MANAGEMENT', 'OWNER']) => $superAdminCards,
      in_array($groupNama, ['CUSTOMER SERVICE', 'SA / ESTIMATOR']) => [
        'mobil_masuk', 'mobil_keluar', 'invoice_or_terbit', 'spk_pending',
        'spk_turun_lapangan', 'outstanding_or', 'mobil_rawat_jalan', 'estimasi_belum_dibuat',
      ],
      $groupNama === 'ADMINISTRASI' => [
        'estimasi_belum_dibuat', 'estimasi_belum_dikirim', 'outstanding_tawar',
        'mobil_keluar', 'mobil_masuk', 'outstanding_tagih',
      ],
      $groupNama === 'KEUANGAN' => [
        'outstanding_tawar', 'outstanding_tagih', 'outstanding_or', 'kwitansi_belum_dikirim',
      ],
      $groupNama === 'GUDANG' => [
        'total_permintaan', 'po_belum_dibuat', 'spk_turun_lapangan',
      ],
      // Default/fallback (termasuk KEPALA CABANG, ADMIN HO, KEPALA BENGKEL, HRD, KASIR,
      // atau nama group yang belum dikenali): tampilkan set Super Admin
      default => $superAdminCards,
    };
  }

  protected function fmtRp(int|float|string $angka): string
  {
    $angka = (float) $angka;
    if ($angka >= 1000000000) return 'Rp ' . number_format($angka / 1000000000, 1) . 'M';
    if ($angka >= 1000000) return 'Rp ' . number_format($angka / 1000000, 1) . 'jt';
    if ($angka >= 1000) return 'Rp ' . number_format($angka / 1000, 1) . 'rb';
    return 'Rp ' . number_format($angka, 0, ',', '.');
  }

  /**
   * Redirect to view.
   *
   */
  public function Dashboard(): View
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
    $title = Helper::getTitleMenu($path) ?? ' Dashboard';

    $users = Auth::user();
    $startlogin = session('startlogin');
    $kode_cabang = session('kd_cabang');
    $nama_cabang = session('nm_cabang');

    // Role dashboard dipatok dari users.user_group (FK ke group.id) -> group.nama
    $groupNama = strtoupper(trim(
      DB::table('group')->where('id', $users->user_group ?? 0)->value('nama') ?? ''
    ));

    $tahunRange = Cache::remember("tahun_range_{$kode_cabang}", 3600, function () use ($kode_cabang) {
      return DB::selectOne("
        SELECT MIN(YEAR(tgl_masuk)) as min_tahun, MAX(YEAR(tgl_masuk)) as max_tahun
        FROM t_spk_master
        WHERE kode_cabang = ?
      ", [$kode_cabang]);
    });

    $tahunMin = $tahunRange->min_tahun ?? date('Y');
    $tahunMax = $tahunRange->max_tahun ?? date('Y');

    $datafilter = session('datafilter');
    if (request()->has('tahun')) {
      $tahun = request()->input('tahun');
      session(['datafilter' => ['tahun' => $tahun]]);
    } elseif (!empty($datafilter)) {
      $tahun = $datafilter['tahun'];
    } else {
      $tahun = date("Y");
    }

    $bulan = request()->input('bulan', date('m'));

    ## Bangun kartu ringkasan bulanan & statistik chart (Cache 300s agar refresh instan dan super enteng)
    $cacheKey = "dash_metrics_{$kode_cabang}_{$groupNama}_{$tahun}_{$bulan}";
    $dashMetrics = Cache::remember($cacheKey, 300, function () use ($groupNama, $kode_cabang, $bulan, $tahun) {
      $cardKeys = $this->cardsForRole($groupNama);
      $cardDefs = $this->cardDefinitions();

      $ringkasanCards = [];
      foreach ($cardKeys as $key) {
        if (!isset($cardDefs[$key])) continue;
        $def = $cardDefs[$key];
        $result = $def['calc']($kode_cabang, $bulan, $tahun);
        $ringkasanCards[] = [
          'key' => $key,
          'label' => $def['label'],
          'icon' => $def['icon'],
          'color' => $def['color'],
          'badge' => $def['badge'] ?? null,
          'display_value' => $result['is_currency'] ? $this->fmtRp($result['value']) : number_format($result['value'], 0, ',', '.'),
          'sub' => $result['sub'] ?? ($def['sub_label'] ?? null),
        ];
      }

      $totalSpkBelumTurunLap = DB::table('t_spk_master')
        ->where('kode_cabang', $kode_cabang)
        ->whereIn('status_spk', ['01', '02', '03', '04', '05'])
        ->whereNull('tgl_turun_lapangan')
        ->whereNull('batal_by')
        ->count();

      $totalSpkPending = DB::table('t_spk_master')
        ->where('kode_cabang', $kode_cabang)
        ->where('tgl_turun_lapangan', '>=', '2008-10-01')
        ->whereNull('kode_keluar')
        ->count();

      $totalEstBelumBuat = DB::table('t_spk_master')
        ->where('kode_cabang', $kode_cabang)
        ->whereIn('status_spk', ['01', '02'])
        ->whereNull('batal_by')
        ->count();

      $totalEstBelumKirim = DB::table('v_rep_estimasi_belum_dikirim')
        ->where('kode_cabang', $kode_cabang)
        ->count();

      ## Chart Statistik SPK (Masuk & Keluar)
      $startYear = sprintf('%04d-01-01 00:00:00', $tahun);
      $endYear = sprintf('%04d-12-31 23:59:59', $tahun);

      $spkMasukData = array_fill(0, 12, 0);
      $spkKeluarData = array_fill(0, 12, 0);

      $spkMasuk = DB::table('t_spk_master')
        ->selectRaw('MONTH(tgl_masuk) as bulan, COUNT(*) as total')
        ->where('kode_cabang', $kode_cabang)
        ->whereBetween('tgl_masuk', [$startYear, $endYear])
        ->groupBy(DB::raw('MONTH(tgl_masuk)'))
        ->get();

      foreach ($spkMasuk as $row) {
        $spkMasukData[$row->bulan - 1] = $row->total;
      }

      $spkKeluar = DB::table('t_spk_master')
        ->selectRaw('MONTH(tgl_keluar) as bulan, COUNT(*) as total')
        ->where('kode_cabang', $kode_cabang)
        ->whereBetween('tgl_keluar', [$startYear, $endYear])
        ->whereNotNull('tgl_keluar')
        ->groupBy(DB::raw('MONTH(tgl_keluar)'))
        ->get();

      foreach ($spkKeluar as $row) {
        $spkKeluarData[$row->bulan - 1] = $row->total;
      }

      return [
        'cards' => $ringkasanCards,
        'spkBelumTurun' => $totalSpkBelumTurunLap,
        'spkPending' => $totalSpkPending,
        'estBelumBuat' => $totalEstBelumBuat,
        'estBelumKirim' => $totalEstBelumKirim,
        'spkMasukData' => $spkMasukData,
        'spkKeluarData' => $spkKeluarData,
      ];
    });

    $ringkasanCards = $dashMetrics['cards'];
    $totalSpkBelumTurunLap = $dashMetrics['spkBelumTurun'];
    $totalSpkPending = $dashMetrics['spkPending'];
    $totalEstBelumBuat = $dashMetrics['estBelumBuat'];
    $totalEstBelumKirim = $dashMetrics['estBelumKirim'];
    $spkMasukData = $dashMetrics['spkMasukData'];
    $spkKeluarData = $dashMetrics['spkKeluarData'];

    $data['SPK_BLM_TURUN_LAP'] = $totalSpkBelumTurunLap;
    $data['SPK_PENDING'] = $totalSpkPending;
    $data['EST_BLM_BUAT'] = $totalEstBelumBuat;
    $data['EST_BLM_KIRIM'] = $totalEstBelumKirim;
    $data['spkMasukData'] = $spkMasukData;
    $data['spkKeluarData'] = $spkKeluarData;
    $data['RINGKASAN_CARDS'] = $ringkasanCards;

    return view('content.dashboard.customer-service', [
      'title' => $title,
      'users' => $users,
      'nama_cabang' => $nama_cabang,
      'tahun' => $tahun,
      'startlogin' => $startlogin,
      'data' => $data,
      'bulan' => $bulan,
      'tahunMin' => $tahunMin,
      'tahunMax' => $tahunMax,
    ]);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(Request $request): JsonResponse
  {
    $user_cabang = session('kd_cabang');

    if ($request->tipe == "spk") {
      $columns = [
        1 => 'k.tgl_masuk',
        2 => 'k.kode_spk',
        3 => 'e.keterangan', // status
        4 => 'k.no_polisi',
        5 => 'b.nama_tipe',
        6 => 'k.pemilik',
        7 => 'c.nama_pelanggan',
        8 => 'd.keterangan', // status_spk
        9 => 'k.no_polis',
        10 => 'k.kode_claim'
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'k.id';
      $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      $base = DB::table('t_spk_master as k')
        ->leftJoin('m_tipe_kendaraan as b', function ($join) {
          $join->on('b.kode_tipe', '=', 'k.kode_tipe')
            ->on('b.kode_merek', '=', 'k.kode_merek');
        })
        ->leftJoin('m_pelanggan_hdr as c', function ($join) {
          $join->on('c.kode_pelanggan', '=', 'k.kode_pelanggan')
            ->on('c.kode_cabang', '=', 'k.kode_cabang');
        })
        ->leftJoin('parameter as d', function ($join) {
          $join->on('d.kode', '=', 'k.kode_status_spk')
            ->where('d.nama_tabel', '=', 'STATUS_SPK');
        })
        ->leftJoin('parameter as e', function ($join) {
          $join->on('e.kode', '=', 'k.status_spk')
            ->where('e.nama_tabel', '=', 'STATUS_SPK_KET');
        })
        ->where('k.kode_cabang', $user_cabang)
        ->whereMonth('k.tgl_masuk', date('m'))
        ->whereYear('k.tgl_masuk', date('Y'));

      $totalData = (clone $base)->count('k.id');

      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('k.kode_spk', 'like', "%{$search}%")
            ->orWhere('k.no_polisi', 'like', "%{$search}%")
            ->orWhere('b.nama_tipe', 'like', "%{$search}%")
            ->orWhere('k.pemilik', 'like', "%{$search}%")
            ->orWhere('c.nama_pelanggan', 'like', "%{$search}%")
            ->orWhere('d.keterangan', 'like', "%{$search}%")
            ->orWhere('k.no_polis', 'like', "%{$search}%")
            ->orWhere('k.kode_claim', 'like', "%{$search}%");
        });
      }

      $totalFiltered = (clone $query)->count('k.id');

      $datas = $query
        ->select([
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
    } elseif ($request->tipe == "detail1") {
      $columns = [
        1 => 'k.kode_spk',
        2 => 'k.tgl_masuk',
        3 => 'k.kode_estimasi',
        4 => 'k.tgl_estimasi',
        5 => 'k.no_polisi',
        6 => 'k.merek_tipe',
        7 => 'k.pemilik'
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'k.id';
      $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      $base = DB::table('v_rep_belum_turun_lapangan as k')
        ->where('k.kode_cabang', $user_cabang);

      $totalData = (clone $base)->count('k.id');

      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('k.kode_spk', 'like', "%{$search}%")
            ->orWhere('k.no_polisi', 'like', "%{$search}%")
            ->orWhere('k.merek_tipe', 'like', "%{$search}%")
            ->orWhere('k.pemilik', 'like', "%{$search}%");
        });
      }

      $totalFiltered = (clone $query)->count('k.id');

      $datas = $query
        ->select([
          'k.id',
          'k.kode_spk',
          'k.tgl_masuk',
          'k.pemilik',
          'k.telepon',
          'k.kode_cabang',
          'k.nama_cabang',
          'k.merek_tipe',
          'k.no_polisi',
          'k.nama_pelanggan',
          'k.kode_estimasi',
          'k.tgl_estimasi',
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
          'kode_cabang' => $row->kode_cabang,
          'kode_spk' => $row->kode_spk,
          'pemilik' => $row->pemilik,
          'telepon' => $row->telepon,
          'merek_tipe' => $row->merek_tipe,
          'no_polisi' => $row->no_polisi,
          'nama_pelanggan' => $row->nama_pelanggan,
          'kode_estimasi' => $row->kode_estimasi,
          'tgl_masuk' => blank($row->tgl_masuk) ? '' : date("d/m/Y", strtotime($row->tgl_masuk)),
          'tgl_estimasi' => blank($row->tgl_estimasi) ? '' : date("d/m/Y", strtotime($row->tgl_estimasi)),
        ];
      }
    } elseif ($request->tipe == "detail2") {
      $columns = [
        1 => 'k.kode_spk',
        2 => 'k.tgl_masuk',
        3 => 'k.no_polisi',
        4 => 'k.tipe_kendaraan',
        5 => 'k.pemilik',
        6 => 'k.tgl_turun_lapangan',
        7 => 'k.tgl_rencana_selesai',
        8 => 'k.sisa_waktu',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'k.id';
      $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      $base = DB::table('v_warning_turun_lapangan as k')
        ->where('k.kode_cabang', $user_cabang);

      $totalData = (clone $base)->count('k.id');

      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('k.kode_spk', 'like', "%{$search}%")
            ->orWhere('k.no_polisi', 'like', "%{$search}%")
            ->orWhere('k.tipe_kendaraan', 'like', "%{$search}%")
            ->orWhere('k.pemilik', 'like', "%{$search}%");
        });
      }

      $totalFiltered = (clone $query)->count('k.id');

      $datas = $query
        ->select([
          'k.id',
          'k.kode_cabang',
          'k.kode_spk',
          'k.tgl_masuk',
          'k.no_polisi',
          'k.tipe_kendaraan',
          'k.pemilik',
          'k.tgl_turun_lapangan',
          'k.tgl_rencana_selesai',
          'k.sisa_waktu',
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
          'kode_cabang' => $row->kode_cabang,
          'kode_spk' => $row->kode_spk,
          'pemilik' => $row->pemilik,
          'tipe_kendaraan' => $row->tipe_kendaraan,
          'no_polisi' => $row->no_polisi,
          'sisa_waktu' => $row->sisa_waktu,
          'tgl_masuk' => blank($row->tgl_masuk) ? '' : date("d/m/Y", strtotime($row->tgl_masuk)),
          'tgl_turun_lapangan' => blank($row->tgl_turun_lapangan) ? '' : date("d/m/Y", strtotime($row->tgl_turun_lapangan)),
          'tgl_rencana_selesai' => blank($row->tgl_rencana_selesai) ? '' : date("d/m/Y", strtotime($row->tgl_rencana_selesai)),
        ];
      }
    } elseif ($request->tipe == "detail3") {
      $columns = [
        1 => 'k.kode_spk',
        2 => 'k.tgl_masuk',
        3 => 'k.no_polisi',
        4 => 'k.tipe_kendaraan',
        5 => 'k.pemilik',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'k.id';
      $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      $base = DB::table('v_rep_estimasi_belum_dibuat as k')
        ->where('k.kode_cabang', $user_cabang);

      $totalData = (clone $base)->count('k.id');

      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('k.kode_spk', 'like', "%{$search}%")
            ->orWhere('k.no_polisi', 'like', "%{$search}%")
            ->orWhere('k.tipe_kendaraan', 'like', "%{$search}%")
            ->orWhere('k.pemilik', 'like', "%{$search}%");
        });
      }

      $totalFiltered = (clone $query)->count('k.id');

      $datas = $query
        ->select([
          'k.id',
          'k.kode_cabang',
          'k.kode_spk',
          'k.tgl_masuk',
          'k.no_polisi',
          'k.tipe_kendaraan',
          'k.pemilik',
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
          'kode_cabang' => $row->kode_cabang,
          'kode_spk' => $row->kode_spk,
          'pemilik' => $row->pemilik,
          'tipe_kendaraan' => $row->tipe_kendaraan,
          'no_polisi' => $row->no_polisi,
          'tgl_masuk' => blank($row->tgl_masuk) ? '' : date("d/m/Y", strtotime($row->tgl_masuk)),
        ];
      }
    } elseif ($request->tipe == "detail4") {
      $columns = [
        1 => 'k.kode_estimasi',
        2 => 'k.tanggal',
        3 => 'k.kode_spk',
        4 => 'k.tgl_masuk',
        5 => 'k.no_polisi',
        6 => 'k.tipe_kendaraan',
        7 => 'k.pemilik',
        8 => 'k.nama_pelanggan',
      ];

      $limit = (int) $request->input('length', 10);
      $start = (int) $request->input('start', 0);
      $order = $columns[$request->input('order.0.column')] ?? 'k.id';
      $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

      $base = DB::table('v_rep_estimasi_belum_dikirim as k')
        ->where('k.kode_cabang', $user_cabang);

      $totalData = (clone $base)->count('k.id');

      $query = (clone $base);
      if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
          $q->where('k.kode_spk', 'like', "%{$search}%")
            ->orWhere('k.no_polisi', 'like', "%{$search}%")
            ->orWhere('k.tipe_kendaraan', 'like', "%{$search}%")
            ->orWhere('k.pemilik', 'like', "%{$search}%");
        });
      }

      $totalFiltered = (clone $query)->count('k.id');

      $datas = $query
        ->select([
          'k.id',
          'k.kode_cabang',
          'k.kode_estimasi',
          'k.tanggal',
          'k.kode_spk',
          'k.tgl_masuk',
          'k.no_polisi',
          'k.tipe_kendaraan',
          'k.pemilik',
          'k.nama_pelanggan',
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
          'kode_cabang' => $row->kode_cabang,
          'kode_estimasi' => $row->kode_estimasi,
          'kode_spk' => $row->kode_spk,
          'pemilik' => $row->pemilik,
          'nama_pelanggan' => $row->nama_pelanggan,
          'tipe_kendaraan' => $row->tipe_kendaraan,
          'no_polisi' => $row->no_polisi,
          'tgl_masuk' => blank($row->tgl_masuk) ? '' : date("d/m/Y", strtotime($row->tgl_masuk)),
          'tanggal' => blank($row->tanggal) ? '' : date("d/m/Y", strtotime($row->tanggal)),
        ];
      }
    } else {
      $totalData = 0;
      $totalFiltered = 0;
      $data = [];
    }

    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => intval($totalData),
      'recordsFiltered' => intval($totalFiltered),
      'data' => $data,
    ]);
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    if ($id == "1") {
      $title = 'SPK Belum Turun Lapangan';
    } elseif ($id == "2") {
      $title = 'SPK Yang Harus Diselesaikan';
    } elseif ($id == "3") {
      $title = 'Estimasi Belum Dibuat';
    } elseif ($id == "4") {
      $title = 'Estimasi Belum Dikirim';
    } else {
      $title = '';
    }

    return view('content.dashboard.customer-service-detail', [
      'title' => $title,
      'tipe' => $id,
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
    $dataArray['tahun'] = $request->tahun;

    return redirect('home')->with('datafilter', $dataArray);
  }

}
