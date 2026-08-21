<?php

namespace App\Http\Controllers\keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanChBgBelumKliringExport;
use App\Models\LogActivity;
use App\Models\Bank;

use App\Helpers\Helpers as Helper;

class LaporanChBgBelumKliringController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanChBgBelumKliring(): View
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
    $title = Helper::getTitleMenu($path) ?? 'CH BG Belum Kliring';

    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter');
    if (empty($datafilter)) {
      $datafilter['kategori'] = '';
      $datafilter['tgl_awal'] = date("d/m/Y");
      $datafilter['tgl_akhir'] = date("d/m/Y");
      $datafilter['kode_bank'] = '';
      $datafilter['urut1'] = 'tgl_voucher';
      $datafilter['urut2'] = 'voucher_keluar';
      $datafilter['urut3'] = 'voucher_masuk';
    }

    // Ambil daftar bank dari master bank (sama seperti Data Rekening)
    $banks = Bank::query()
      ->select('kode_bank', 'nama_bank')
      ->where('kode_cabang', $user_cabang)
      ->where('is_active', 'Y')
      ->orderBy('nama_bank', 'asc')
      ->get();

    return view('content.keuangan.laporan.laporan-ch-bg-belum-kliring', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'datafilter' => $datafilter,
      'banks' => $banks,
    ]);
  }
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request): JsonResponse
  {
    try {
      $user_cabang = session('kd_cabang');
      $kode_bank = $request->kode_bank;
      $kategori = $request->kategori;
      $urut1 = $request->urut1;
      $urut2 = $request->urut2;
      $urut3 = $request->urut3;

      if ($request->filled('tipe')) {

        $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
        $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_akhir, 'Asia/Jakarta')->format('Y-m-d');

        $results = DB::select('CALL up_apl_generate_urut_voucher(?, ?, ?, ?, ?)', [
          $user_cabang, $kode_bank, $startDate, $endDate, $kategori
        ]);

        $data = $results[0];

        $status = ($data->status == "SUCCESS") ? true : false;

        return response()->json([
          'status'  => $status,
          'message' => $data->message
        ]);
      }

      $kolomTanggal = 'k.' . $this->resolveKolomTanggal($kategori ?? '');

      $query = DB::table('tmp_all_transaksi as k')
        ->where('k.kode_cabang', $user_cabang)
        ->where('k.kode_bank', $kode_bank);

      if ($request->filled('tgl_awal')) {
        $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate($kolomTanggal, '>=', $startDate);
      }
      if ($request->filled('tgl_akhir')) {
        $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_akhir, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate($kolomTanggal, '<=', $endDate);
      }

      // Total data
      $totalData = (clone $query)->count();
      $totalFiltered = (clone $query)->count();

      // Urutan kolom mapping
      $urutMap = [
        'tgl_voucher' => 'k.tanggal',
        'voucher_masuk' => 'k.no_voucher_in',
        'voucher_keluar' => 'k.no_voucher_out',
      ];

      $order1 = $urutMap[$urut1] ?? 'k.tanggal';
      $order2 = $urutMap[$urut2] ?? 'k.no_voucher_out';
      $order3 = $urutMap[$urut3] ?? 'k.no_voucher_in';

      // Ambil data
      $datas = $query
        ->select([
          'k.kode_cabang',
          'k.kode_bank',
          'k.tanggal',
          'k.tanggal_ch_bg',
          'k.memo',
          'k.nama_bank',
          'k.no_ch_bg',
          'k.nama_cabang',
          'k.no_transaksi',
          'k.no_voucher_in',
          'k.no_voucher_out',
          'k.debit',
          'k.kredit',
          'k.amount',
          'k.tanggal_kliring',
          'k.no_voucher_cabang',
          'k.no_urut',
          'k.no_perkiraan',
        ])
        ->orderBy($order1)
        ->orderBy($order2)
        ->orderBy($order3)
        ->get();

      // Susun data untuk DataTables
      $data = [];
      $no = 0;
      $saldo = 0;
      $grandTotalDebet = 0;
      $grandTotalKredit = 0;
      $grandTotalSaldo = 0;

      foreach ($datas as $row) {
        $saldo = ($no > 0) ? ($saldo + $row->debit + $row->kredit) : $row->amount;

        $data[] = [
          'no' => ++$no,
          'tanggal' => blank($row->tanggal) ? '' : date("d/m/Y", strtotime($row->tanggal)),
          'tanggal_ch_bg' => blank($row->tanggal_ch_bg) ? '' : date("d/m/Y", strtotime($row->tanggal_ch_bg)),
          'memo' => $row->memo,
          'no_ch_bg' => $row->no_ch_bg,
          'no_voucher_in' => $row->no_voucher_in,
          'no_voucher_out' => $row->no_voucher_out,
          'debit' => number_format($row->debit, 0, '.', ','),
          'kredit' => number_format($row->kredit * -1, 0, '.', ','),
          'saldo' => number_format($saldo, 0, '.', ','),
          'tanggal_kliring' => blank($row->tanggal_kliring) ? '' : date("d/m/Y", strtotime($row->tanggal_kliring)),
        ];

        $grandTotalDebet += $row->debit;
        $grandTotalKredit += $row->kredit;
        // $grandTotalSaldo += $row->amount;
      }

      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => intval($totalData),
        'recordsFiltered' => intval($totalFiltered),
        'data' => $data,
        'grand_debit' => number_format($grandTotalDebet, 0, '.', ','),
        'grand_kredit' => number_format($grandTotalKredit * -1, 0, '.', ','),
        'grand_saldo' => number_format($saldo, 0, '.', ','),
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
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $validatedData = $request->validate(
      [
        'kategori' => 'required',
        'kode_bank' => 'required',
        'tgl_awal' => 'required',
        'tgl_akhir' => 'required'
      ],
      [
        'kategori.required' => 'Kategori wajib diisi.',
        'kode_bank.required' => 'Bank wajib diisi.',
        'tgl_awal.required' => 'Tanggal Awal wajib diisi.',
        'tgl_akhir.required' => 'Tanggal Akhir wajib diisi.'
      ]
    );

    $dataArray['kategori'] = $request->kategori;
    $dataArray['kode_bank'] = $request->kode_bank;
    $dataArray['tgl_awal'] = $request->tgl_awal;
    $dataArray['tgl_akhir'] = $request->tgl_akhir;
    $dataArray['urut1'] = $request->urut1 ?? 'tgl_voucher';
    $dataArray['urut2'] = $request->urut2 ?? 'voucher_keluar';
    $dataArray['urut3'] = $request->urut3 ?? 'voucher_masuk';

    ## Log Activity
    $desc = "View Laporan CH BG Belum Kliring";
    LogActivity::saveLogActivity($desc, $dataArray);

    return redirect('keuangan/laporan-ch-bg-belum-kliring')->with('datafilter', $dataArray);
  }

  /**
   * Export data to Excel.
   */
  public function exportExcel(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $cabangData = [
      'kode' => $user_cabang,
      'nama' => $namaCabang
    ];

    $filters = $request->all();

    $tglAwal = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
    $tglAkhir = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
    $periodeStr = date("d-M-Y", strtotime($tglAwal)) . ' s/d ' . date("d-M-Y", strtotime($tglAkhir));

    $banks = Bank::query()
      ->select('kode_bank', 'nama_bank')
      ->where('kode_cabang', $user_cabang)
      ->where('kode_bank', $filters['kode_bank'])
      ->first();

    $namaBank = blank($banks) ? '' : @$banks->nama_bank;

    $fileName = 'Laporan_CH_BG_Belum_Kliring_' . date('Ymd') . '.xlsx';

    return Excel::download(new LaporanChBgBelumKliringExport($filters, $cabangData, $periodeStr, $namaBank), $fileName);
  }

  /**
   * Print data.
   */

  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan CH BG Belum Kliring';
    $filters = $request->all();

    $banks = Bank::query()
      ->select('kode_bank', 'nama_bank')
      ->where('kode_cabang', $user_cabang)
      ->where('kode_bank', $filters['kode_bank'])
      ->first();

    $nama_bank = blank($banks) ? '' : @$banks->nama_bank;

    $tglAwal = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
    $tglAkhir = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
    $periodeStr = date("d-M-Y", strtotime($tglAwal)) . ' s/d ' . date("d-M-Y", strtotime($tglAkhir));

    $kode_bank = $filters['kode_bank'];
    $kategori = $filters['kategori'];
    $urut1 = $filters['urut1'];
    $urut2 = $filters['urut2'];
    $urut3 = $filters['urut3'];

    $kolomTanggal = 'k.' . $this->resolveKolomTanggal($kategori ?? '');

    $query = DB::table('tmp_all_transaksi as k')
      ->where('k.kode_cabang', $user_cabang)
      ->where('k.kode_bank', $kode_bank);

    if (!empty($filters['tgl_awal'])) {
      $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'], 'Asia/Jakarta')->format('Y-m-d');
      $query->whereDate($kolomTanggal, '>=', $startDate);
    }
    if (!empty($filters['tgl_akhir'])) {
      $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'], 'Asia/Jakarta')->format('Y-m-d');
      $query->whereDate($kolomTanggal, '<=', $endDate);
    }

    // Urutan kolom mapping
    $urutMap = [
      'tgl_voucher' => 'k.tanggal',
      'voucher_masuk' => 'k.no_voucher_in',
      'voucher_keluar' => 'k.no_voucher_out',
    ];

    $order1 = $urutMap[$urut1] ?? 'k.tanggal';
    $order2 = $urutMap[$urut2] ?? 'k.no_voucher_out';
    $order3 = $urutMap[$urut3] ?? 'k.no_voucher_in';

    // Ambil data
    $datas = $query
      ->select([
        'k.kode_cabang',
        'k.kode_bank',
        'k.tanggal',
        'k.tanggal_ch_bg',
        'k.memo',
        'k.nama_bank',
        'k.no_ch_bg',
        'k.nama_cabang',
        'k.no_transaksi',
        'k.no_voucher_in',
        'k.no_voucher_out',
        'k.debit',
        'k.kredit',
        'k.amount',
        'k.tanggal_kliring',
        'k.no_voucher_cabang',
        'k.no_urut',
        'k.no_perkiraan',
      ])
      ->orderBy($order1)
      ->orderBy($order2)
      ->orderBy($order3)
      ->get();

    LogActivity::saveLogActivity('Print Laporan CH BG Belum Kliring', $filters);

    $pages = 'content.keuangan.laporan.laporan-ch-bg-belum-kliring-print';

    $pageConfigs = ['myLayout' => 'blank'];
    return view($pages, [
      'title' => $title,
      'namaCabang' => $namaCabang,
      'periodeStr' => $periodeStr,
      'nama_bank' => $nama_bank,
      'no' => 1,
      'datas' => $datas,
      'datafilter' => $filters,
      'pageConfigs' => $pageConfigs,
    ]);
  }

  private function resolveKolomTanggal(?string $kategori): string
  {
    return match ($kategori) {
      'Tanggal CH/BG' => 'tanggal_ch_bg',
      'Tanggal Kliring' => 'tanggal_kliring',
      default => 'tanggal',
    };
  }
}
