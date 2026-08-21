<?php

namespace App\Http\Controllers\gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanPembelianSparepartExport;

use App\Helpers\Helpers as Helper;

class LaporanPembelianSparepartController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanPembelianSparepart(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Laporan Pembelian Sparepart';
    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter_sparepart');
    if (empty($datafilter)) {
      $datafilter['supplier'] = '';
      $datafilter['no_spk'] = '';
      $datafilter['tgl_awal'] = date("d/m/Y");
      $datafilter['tgl_akhir'] = date("d/m/Y");
    }

    // Get data pemasok untuk dropdown
    $pemasok = DB::table('m_pemasok')
      ->where('kode_cabang', $user_cabang)
      ->select('kode_pemasok', 'nama_pemasok')
      ->orderBy('nama_pemasok', 'asc')
      ->get();

    return view('content.gudang.laporan.laporan-pembelian-sparepart', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'datafilter' => $datafilter,
      'pemasok' => $pemasok,
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

    $supplier = $request->filled('supplier')
      ? $request->supplier
      : session('datafilter_sparepart.supplier', '');

    $no_spk = $request->filled('no_spk')
      ? $request->no_spk
      : session('datafilter_sparepart.no_spk', '');

    $tgl_awal = $request->filled('tgl_awal')
      ? $request->tgl_awal
      : session('datafilter_sparepart.tgl_awal', date("d/m/Y"));

    $tgl_akhir = $request->filled('tgl_akhir')
      ? $request->tgl_akhir
      : session('datafilter_sparepart.tgl_akhir', date("d/m/Y"));

    $query = DB::table('v_rep_pembelian_sparepart as k')
      ->where('k.kode_cabang', $user_cabang);

    // Filter supplier
    if (!empty($supplier)) {
      $query->where('k.kode_pemasok', $supplier);
    }

    // Filter no SPK
    if (!empty($no_spk)) {
      $query->where('k.kode_spk', 'like', '%' . $no_spk . '%');
    }

    // Filter tanggal
    if (!empty($tgl_awal)) {
      try {
        $startDate = Carbon::createFromFormat('d/m/Y', $tgl_awal)->format('Y-m-d');
        $query->whereDate('k.tanggal', '>=', $startDate);
      } catch (\Exception $e) {
        // Handle error
      }
    }

    if (!empty($tgl_akhir)) {
      try {
        $endDate = Carbon::createFromFormat('d/m/Y', $tgl_akhir)->format('Y-m-d');
        $query->whereDate('k.tanggal', '<=', $endDate);
      } catch (\Exception $e) {
        // Handle error
      }
    }

    $totalData = (clone $query)->count();
    $totalFiltered = (clone $query)->count();

    $datas = $query
      ->select([
        'k.tanggal',
        'k.kode_input',
        'k.nama_pemasok',
        'k.nama_sparepart',
        'k.qty',
        'k.harga',
        'k.jumlah',
        'k.kode_spk',
        'k.no_po',
        'k.merek_tipe',
        'k.no_polisi'
      ])
      ->orderBy('k.tanggal', 'asc')
      ->orderBy('k.kode_input', 'asc')
      ->get();

    // $data = [];
    // $no = 0;

    // foreach ($datas as $row) {
    //   $data[] = [
    //     'no' => ++$no,
    //     'tanggal' => Carbon::parse($row->tanggal)->format('d/m/Y'),
    //     'kode_input' => $row->kode_input,
    //     'nama_pemasok' => $row->nama_pemasok,
    //     'nama_sparepart' => $row->nama_sparepart,
    //     'qty' => number_format($row->qty, 2, '.', '.'),
    //     'harga' => number_format($row->harga, 0, '.', '.'),
    //     'jumlah' => number_format($row->jumlah, 0, '.', '.'),
    //     'total_ap' => number_format($row->jumlah, 0, '.', '.'),
    //     'kode_spk' => $row->kode_spk ?? '',
    //     'no_po' => $row->no_po ?? '',
    //     'merek_tipe' => $row->merek_tipe ?? '',
    //     'no_polisi' => $row->no_polisi ?? '',
    //   ];
    // }

    $data = [];
    $no = 0;
    $grandTotal = ['qty' => 0, 'jumlah' => 0, 'total_ap' => 0];

    foreach ($datas as $row) {
      $grandTotal['qty'] += $row->qty;
      $grandTotal['jumlah'] += $row->jumlah;
      $grandTotal['total_ap'] += $row->jumlah;

      $data[] = [
        'no' => ++$no,
        'tanggal' => Carbon::parse($row->tanggal)->format('d/m/Y'),
        'kode_input' => $row->kode_input,
        'nama_pemasok' => $row->nama_pemasok,
        'nama_sparepart' => $row->nama_sparepart,
        'qty' => number_format($row->qty, 2, '.', '.'),
        'harga' => number_format($row->harga, 0, '.', '.'),
        'jumlah' => number_format($row->jumlah, 0, '.', '.'),
        'total_ap' => number_format($row->jumlah, 0, '.', '.'),
        'kode_spk' => $row->kode_spk ?? '',
        'no_po' => $row->no_po ?? '',
        'merek_tipe' => $row->merek_tipe ?? '',
        'no_polisi' => $row->no_polisi ?? '',
      ];
    }

    if (count($datas) > 0) {
      $data[] = [
        'no' => '',
        'tanggal' => '',
        'kode_input' => 'Grand Total',
        'nama_pemasok' => '',
        'nama_sparepart' => '',
        'qty' => number_format($grandTotal['qty'], 2, '.', '.'),
        'harga' => '',
        'jumlah' => number_format($grandTotal['jumlah'], 0, '.', '.'),
        'total_ap' => number_format($grandTotal['total_ap'], 0, '.', '.'),
        'kode_spk' => '',
        'no_po' => '',
        'merek_tipe' => '',
        'no_polisi' => '',
      ];
    }

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
    $validatedData = $request->validate(
      [
        'tgl_awal' => 'required',
        'tgl_akhir' => 'required'
      ],
      [
        'tgl_awal.required' => 'Tanggal Awal wajib diisi.',
        'tgl_akhir.required' => 'Tanggal Akhir wajib diisi.'
      ]
    );

    $dataArray['supplier'] = $request->supplier ?? '';
    $dataArray['no_spk'] = $request->no_spk ?? '';
    $dataArray['tgl_awal'] = $request->tgl_awal;
    $dataArray['tgl_akhir'] = $request->tgl_akhir;

    return redirect('gudang/laporan-pembelian-sparepart')->with('datafilter_sparepart', $dataArray);
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

    // Format Periode
    $tglAwal = $request->input('tgl_awal', date('d/m/Y'));
    $tglAkhir = $request->input('tgl_akhir', date('d/m/Y'));
    $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;

    $fileName = 'Laporan_Pembelian_Sparepart_' . date('Ymd_His') . '.xlsx';

    return Excel::download(
      new LaporanPembelianSparepartExport($filters, $cabangData, $periodeStr),
      $fileName
    );
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan Pembelian Sparepart';
    $filters = $request->all();

    $supplier = $filters['supplier'] ?? '';
    $no_spk = $filters['no_spk'] ?? '';
    $tglAwal = $filters['tgl_awal'] ?? date('d/m/Y');
    $tglAkhir = $filters['tgl_akhir'] ?? date('d/m/Y');
    $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;

    // Query data
    $query = DB::table('v_rep_pembelian_sparepart as k')
      ->where('k.kode_cabang', $user_cabang)
      ->select([
        'k.tanggal',
        'k.kode_input',
        'k.nama_pemasok',
        'k.nama_sparepart',
        'k.qty',
        'k.harga',
        'k.jumlah',
        'k.kode_spk',
        'k.no_po',
        'k.merek_tipe',
        'k.no_polisi'
      ]);

    // Filter supplier
    if (!empty($supplier)) {
      $query->where('k.kode_pemasok', $supplier);
    }

    // Filter no SPK
    if (!empty($no_spk)) {
      $query->where('k.kode_spk', 'like', '%' . $no_spk . '%');
    }

    // Filter tanggal
    if (!empty($filters['tgl_awal'])) {
      $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
      $query->whereDate('k.tanggal', '>=', $startDate);
    }

    if (!empty($filters['tgl_akhir'])) {
      $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
      $query->whereDate('k.tanggal', '<=', $endDate);
    }

    $datas = $query->orderBy('k.tanggal', 'asc')->orderBy('k.kode_input', 'asc')->get();

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.gudang.laporan.laporan-pembelian-sparepart-print', [
      'title' => $title,
      'namaCabang' => $namaCabang,
      'periodeStr' => $periodeStr,
      'no' => 1,
      'datas' => $datas,
      'datafilter' => $filters,
      'pageConfigs' => $pageConfigs,
    ]);
  }
}
