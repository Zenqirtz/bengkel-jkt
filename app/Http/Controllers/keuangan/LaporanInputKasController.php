<?php

namespace App\Http\Controllers\keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanInputKasExport;

class LaporanInputKasController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanInputKas(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Laporan Input Kas';
    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter_input_kas');
    if (empty($datafilter)) {
      $datafilter['tgl_awal'] = date("d/m/Y");
      $datafilter['tgl_akhir'] = date("d/m/Y");
      $datafilter['kode_kas'] = null; // opsional: filter per jenis kas (KK/KM dll)
    }

    return view('content.keuangan.laporan.laporan-input-kas', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'datafilter' => $datafilter,
    ]);
  }

  /**
   * Hitung saldo awal = akumulasi (debet - kredit) sebelum tgl_awal.
   * Dipanggil terpisah di index() & printData(), sama seperti pola gudang-bahan
   * yang tidak berbagi query builder antar method.
   */
  private function hitungSaldoAwal(string $user_cabang, ?string $startDate): float
  {
    if (empty($startDate)) {
      return 0;
    }

    $row = DB::table('v_rpt_kas_harian')
      ->where('kode_cabang', $user_cabang)
      ->whereDate('tanggal', '<', $startDate)
      ->selectRaw('COALESCE(SUM(debet),0) - COALESCE(SUM(kredit),0) as saldo')
      ->first();

    return (float) ($row->saldo ?? 0);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request): JsonResponse
  {
    $user_cabang = session('kd_cabang');

    $tgl_awal = $request->filled('tgl_awal')
      ? $request->tgl_awal
      : session('datafilter_input_kas.tgl_awal', date("d/m/Y"));

    $tgl_akhir = $request->filled('tgl_akhir')
      ? $request->tgl_akhir
      : session('datafilter_input_kas.tgl_akhir', date("d/m/Y"));

    $query = DB::table('v_rpt_kas_harian')
      ->where('kode_cabang', $user_cabang);

    $startDate = null;

    // Filter tanggal
    if (!empty($tgl_awal)) {
      try {
        $startDate = Carbon::createFromFormat('d/m/Y', $tgl_awal)->format('Y-m-d');
        $query->whereDate('tanggal', '>=', $startDate);
      } catch (\Exception $e) {
        // Handle error
      }
    }

    if (!empty($tgl_akhir)) {
      try {
        $endDate = Carbon::createFromFormat('d/m/Y', $tgl_akhir)->format('Y-m-d');
        $query->whereDate('tanggal', '<=', $endDate);
      } catch (\Exception $e) {
        // Handle error
      }
    }

    $totalData = (clone $query)->count();
    $totalFiltered = (clone $query)->count();

    $datas = $query
      ->select([
        'tanggal',
        'no_bukti',
        'memo',
        'no_spk',
        'no_input_gudang',
        'debet',
        'kredit',
        'or_free',
      ])
      ->orderBy('tanggal', 'asc')
      ->orderBy('no_bukti', 'asc')
      ->get();

    $saldoAwal = $this->hitungSaldoAwal($user_cabang, $startDate);

    $data = [];
    $no = 0;
    $saldo = $saldoAwal;

    // Baris Saldo Awal
    $data[] = [
      'no' => ++$no,
      'tanggal' => $startDate ? Carbon::parse($startDate)->format('d/m/Y') : '',
      'no_bukti' => '',
      'memo' => 'Saldo Awal',
      'no_spk' => '',
      'no_input_gudang' => '',
      'debet' => number_format($saldoAwal, 0, '.', '.'),
      'kredit' => number_format(0, 0, '.', '.'),
      'saldo' => number_format($saldo, 0, '.', '.'),
      'or_free' => '',
      'is_saldo_awal' => true,
    ];

    // Hanya proses transaksi tambahan jika ada
    if (count($datas) > 0) {
      foreach ($datas as $row) {
        $debet = (float) $row->debet;
        $kredit = (float) $row->kredit;
        $saldo = $saldo + $debet - $kredit;

        $data[] = [
          'no' => ++$no,
          'tanggal' => Carbon::parse($row->tanggal)->format('d/m/Y'),
          'no_bukti' => $row->no_bukti,
          'memo' => $row->memo,
          'no_spk' => $row->no_spk ?? '',
          'no_input_gudang' => $row->no_input_gudang ?? '',
          'debet' => number_format($debet, 0, '.', '.'),
          'kredit' => number_format($kredit, 0, '.', '.'),
          'saldo' => number_format($saldo, 0, '.', '.'),
          'or_free' => $row->or_free ?? '',
          'is_saldo_awal' => false,
          'is_grand_total' => false,
        ];
      }
    }

    // Baris Grand Total - SELALU ditampilkan (walau tidak ada transaksi, minimal saldo awal)
    $data[] = [
      'no' => '',
      'tanggal' => '',
      'no_bukti' => '',
      'memo' => '',
      'no_spk' => '',
      'no_input_gudang' => '',
      'debet' => 'Grand Total',
      'kredit' => '',
      'saldo' => number_format($saldo, 0, '.', '.'),
      'or_free' => '',
      'is_saldo_awal' => false,
      'is_grand_total' => true,
    ];

    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => intval($totalData) + 2, // +1 saldo awal, +1 grand total
      'recordsFiltered' => intval($totalFiltered) + 2,
      'data' => $data,
    ]);
  }

  /**
   * /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $request->validate(
      [
        'tgl_awal' => 'required',
        'tgl_akhir' => 'required',
      ],
      [
        'tgl_awal.required' => 'Tanggal Awal wajib diisi.',
        'tgl_akhir.required' => 'Tanggal Akhir wajib diisi.',
      ]
    );

    $dataArray['tgl_awal'] = $request->tgl_awal;
    $dataArray['tgl_akhir'] = $request->tgl_akhir;
    $dataArray['kode_kas'] = $request->kode_kas;

    return redirect('keuangan/laporan-input-kas')->with('datafilter_input_kas', $dataArray);
  }

  /**
   * Export ke Excel.
   */
  public function exportExcel(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $cabangData = [
      'kode' => $user_cabang,
      'nama' => $namaCabang,
    ];

    $filters = $request->all();

    // Format Periode
    $tglAwal = $request->input('tgl_awal', date('d/m/Y'));
    $tglAkhir = $request->input('tgl_akhir', date('d/m/Y'));
    $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;

    $fileName = 'Laporan_Input_Kas_' . date('Ymd_His') . '.xlsx';

    return Excel::download(
      new LaporanInputKasExport($filters, $cabangData, $periodeStr),
      $fileName
    );
  }


  /**
   * Print view.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan Input Kas';
    $filters = $request->all();

    $tglAwal = $filters['tgl_awal'] ?? date('d/m/Y');
    $tglAkhir = $filters['tgl_akhir'] ?? date('d/m/Y');
    $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;

    // Query data
    $query = DB::table('v_rpt_kas_harian')
      ->where('kode_cabang', $user_cabang)
      ->select([
        'tanggal',
        'no_bukti',
        'memo',
        'no_spk',
        'no_input_gudang',
        'debet',
        'kredit',
        'or_free',
      ]);

    $startDate = null;

    // Filter tanggal
    if (!empty($filters['tgl_awal'])) {
      $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
      $query->whereDate('tanggal', '>=', $startDate);
    }

    if (!empty($filters['tgl_akhir'])) {
      $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
      $query->whereDate('tanggal', '<=', $endDate);
    }

    $datas = $query->orderBy('tanggal', 'asc')->orderBy('no_bukti', 'asc')->get();

    $saldoAwal = $this->hitungSaldoAwal($user_cabang, $startDate);

    $pageConfigs = ['myLayout' => 'blank'];

    return view('content.keuangan.laporan.laporan-input-kas-print', [
      'title' => $title,
      'namaCabang' => $namaCabang,
      'periodeStr' => $periodeStr,
      'no' => 1,
      'datas' => $datas,
      'saldoAwal' => $saldoAwal,
      'datafilter' => $filters,
      'pageConfigs' => $pageConfigs,
    ]);
  }
}
