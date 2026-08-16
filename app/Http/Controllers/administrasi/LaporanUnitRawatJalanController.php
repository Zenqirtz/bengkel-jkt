<?php

namespace App\Http\Controllers\administrasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanUnitRawatJalanExport;
use App\Models\Parameter;
use App\Models\LogActivity;

class LaporanUnitRawatJalanController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanUnitRawatJalan(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Laporan Unit Rawat Jalan';

    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter', [
      'tanggal' => date('d/m/Y'),
    ]);

    LogActivity::saveLogActivity("View Laporan " . $title);

    return view('content.administrasi.laporan.laporan-unit-rawat-jalan', [
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
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request): JsonResponse
  {
    $user_cabang = session('kd_cabang');

    $query = $this->buildQuery($user_cabang, $request->all());

    $totalData = (clone $query)->count('k.id');
    $totalFiltered = $totalData;

    $datas = $query
      ->select([
        'k.id',
        'k.kode_spk',
        'k.tgl_masuk',
        'k.no_polisi',
        'k.kode_merek',
        'mk.nama_merek',
        'k.kode_tipe',
        'b.nama_tipe',
        'k.pemilik',
        'k.alamat',
        'k.kode_pelanggan',
        'c.nama_pelanggan',
        'k.no_polis',
        'k.kode_claim',
        'k.tgl_rawat_jalan1',
        'k.tgl_rawat_jalan2'
      ])
      ->orderBy('k.tgl_masuk', 'asc')
      ->get();

    $data = [];
    $fake = 0;
    foreach ($datas as $row) {
      $data[] = [
        'no' => ++$fake,
        'kode_spk' => $row->kode_spk,
        'tgl_masuk' => blank($row->tgl_masuk) ? '' : date('d/m/Y', strtotime($row->tgl_masuk)),
        'tgl_rawat_jalan1' => blank($row->tgl_rawat_jalan1) ? '' : date('d/m/Y', strtotime($row->tgl_rawat_jalan1)),
        'tgl_rawat_jalan2' => blank($row->tgl_rawat_jalan2) ? '' : date('d/m/Y', strtotime($row->tgl_rawat_jalan2)),
        'no_polisi' => $row->no_polisi,
        'merek_tipe' => trim(($row->nama_merek ?? '') . ' ' . ($row->nama_tipe ?? '')),
        'pemilik' => $row->pemilik,
        'nama_pelanggan' => $row->nama_pelanggan,
        'no_polis' => $row->no_polis,
        'kode_claim' => $row->kode_claim,
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
  public function store(Request $request)
  {
    $request->validate(
      ['tanggal' => 'required'],
      ['tanggal.required' => 'Tanggal wajib diisi.']
    );

    $dataArray = ['tanggal' => $request->tanggal];

    LogActivity::saveLogActivity('Filter Laporan Unit Rawat Jalan', $dataArray);

    return redirect('administrasi/laporan-unit-rawat-jalan')
      ->with('datafilter', $dataArray);
  }

  public function exportExcel(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');
    $filters = $request->all();

    $periode = $filters['tanggal'] ?? date('d/m/Y');
    $fileName = 'Laporan_Unit_Rawat_Jalan_' . date('Ymd_His') . '.xlsx';

    LogActivity::saveLogActivity('Export Excel Laporan Unit Rawat Jalan', $filters);

    return Excel::download(
      new LaporanUnitRawatJalanExport($filters, ['kode' => $user_cabang, 'nama' => $namaCabang], $periode),
      $fileName
    );
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

  public function edit($id)
  {
    //
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
    //
  }

  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');
    $filters = $request->all();

    $periode = $filters['tanggal'] ?? date('d/m/Y');

    $datas = $this->buildQuery($user_cabang, $filters)
      ->select([
        'k.kode_spk',
        'k.tgl_masuk',
        'k.no_polisi',
        'mk.nama_merek',
        'b.nama_tipe',
        'k.pemilik',
        'c.nama_pelanggan',
        'k.no_polis',
        'k.kode_claim',
        'k.tgl_rawat_jalan1',
        'k.tgl_rawat_jalan2',
      ])
      ->orderBy('k.tgl_masuk', 'asc')
      ->get();

    LogActivity::saveLogActivity('Print Laporan Unit Rawat Jalan', $filters);

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.administrasi.laporan.laporan-unit-rawat-jalan-print', [
      'title' => 'Laporan Unit Rawat Jalan',
      'namaCabang' => $namaCabang,
      'periodeStr' => $periode,
      'datas' => $datas,
      'no' => 1,
      'pageConfigs' => $pageConfigs,
    ]);
  }

  // ── Query builder bersama ──────────────────────────────────────────
  private function buildQuery(string $user_cabang, array $filters)
  {
    $query = DB::table('t_spk_master as k')
      ->leftJoin('m_tipe_kendaraan as b', function ($j) {
        $j->on('b.kode_tipe', '=', 'k.kode_tipe')
          ->on('b.kode_merek', '=', 'k.kode_merek');
      })
      ->leftJoin('m_merek_kendaraan as mk', 'mk.kode_merek', '=', 'k.kode_merek')
      ->leftJoin('m_pelanggan_hdr as c', function ($j) {
        $j->on('c.kode_pelanggan', '=', 'k.kode_pelanggan')
          ->on('c.kode_cabang', '=', 'k.kode_cabang');
      })
      ->leftJoin('parameter as d', function ($j) {
        $j->on('d.kode', '=', 'k.kode_status_spk')
          ->where('d.nama_tabel', '=', 'STATUS_SPK');
      })
      ->leftJoin('parameter as e', function ($j) {
        $j->on('e.kode', '=', 'k.status_spk')
          ->where('e.nama_tabel', '=', 'STATUS_SPK_KET');
      })
      ->where('k.kode_cabang', $user_cabang)
      ->where('k.ada_rawat_jalan', '1');

    if (!empty($filters['tanggal'])) {
      try {
        $query->whereDate(
          'k.tgl_masuk',
          '<=',
          Carbon::createFromFormat('d/m/Y', $filters['tanggal'], 'Asia/Jakarta')->format('Y-m-d')
        );
      } catch (\Exception $e) {
      }
    }

    return $query;
  }
}
