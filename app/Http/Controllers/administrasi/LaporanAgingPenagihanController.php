<?php

namespace App\Http\Controllers\administrasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Spk;
use App\Models\Parameter;
use App\Models\LogActivity;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel; // EXPORT EXCEL
use App\Exports\LaporanAgingPenagihanExport;    // EXPORT EXCEL

use App\Helpers\Helpers as Helper;

class LaporanAgingPenagihanController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function LaporanAgingPenagihan(): View
  {
    $isList = Helper::AuthIsPerm("list");
    $isAdd = Helper::AuthIsPerm("add");
    $isEdit = Helper::AuthIsPerm("edit");
    $isDel = Helper::AuthIsPerm("delete");
    if(!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path = request()->path();
    $title = Helper::getTitleMenu($path) ?? 'Aging Penagihan';

    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter');
    if (empty($datafilter)) {
      $datafilter['jenis_laporan'] = '';
      $datafilter['tgl_awal'] = date("d/m/Y");
    }

    return view('content.administrasi.laporan.laporan-aging-penagihan', [
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

    $totalData = 0;
    $totalFiltered = 0;
    $data = [];
    if ($request->filled('jenis_laporan')) {

      $startDate = ($request->filled('tgl_awal')) 
        ? Carbon::createFromFormat('d/m/Y', $request->tgl_awal, 'Asia/Jakarta')->format('Y-m-d')
        : date("Y-m-d");

      if($request->jenis_laporan == "rekap") {
        // Base query
        $results = DB::select('CALL up_apl_rep_os_penagihan_rkp(?, ?)', [
          $user_cabang,
          $startDate,
        ]);
        
        $datas = [];
        $grandTotalUnit = 0;
        $grandTotalNilai = 0;

        foreach ($results as $row) {
          $grandTotalUnit += $row->unit_total;
          $grandTotalNilai += $row->nilai_total;

          $datas[] = [
            'nama_pelanggan' => $row->nama_pelanggan ?? '',
            'nama_cabang' => $row->nama_cabang ?? '',
            'unit_1_2' => $row->unit_1_2 ?? 0,
            'nilai_1_2' => $row->nilai_1_2 ?? 0,
            'unit_3_5' => $row->unit_3_5 ?? 0,
            'nilai_3_5' => $row->nilai_3_5 ?? 0,
            'unit_5' => $row->unit_5 ?? 0,
            'nilai_5' => $row->nilai_5 ?? 0,
            'unit_blm_dikirim' => $row->unit_blm_dikirim ?? 0,
            'nilai_blm_dikirim' => $row->nilai_blm_dikirim ?? 0,
            'unit_total' => $row->unit_total ?? 0,
            'nilai_total' => $row->nilai_total ?? 0,
          ];
        }

        // Total baris tanpa filter
        $totalData = count($datas);

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = count($datas);

        // Susun payload DataTables
        $data = [];
        $grandUnit1 = 0;
        $grandUnit1Persen = 0;
        $grandNilai1 = 0;
        $grandNilai1Persen = 0;
        $grandUnit3 = 0;
        $grandUnit3Persen = 0;
        $grandNilai3 = 0;
        $grandNilai3Persen = 0;
        $grandUnit5 = 0;
        $grandUnit5Persen = 0;
        $grandNilai5 = 0;
        $grandNilai5Persen = 0;
        $grandUnitBlm = 0;
        $grandUnitBlmPersen = 0;
        $grandNilaiBlm = 0;
        $grandNilaiBlmPersen = 0;
        $grandUnitTot = 0;
        $grandUnitTotPersen = 0;
        $grandNilaiTot = 0;
        $grandNilaiTotPersen = 0;

        $fake = 0; //$start;
        foreach ($datas as $row) {

          $unit_1_2_persen = ($grandTotalUnit > 0) ? (($row['unit_1_2'] / $grandTotalUnit) * 100) : 0;
          $nilai_1_2_persen = ($grandTotalNilai > 0) ? (($row['nilai_1_2'] / $grandTotalNilai) * 100) : 0;
          $unit_3_5_persen = ($grandTotalUnit > 0) ? (($row['unit_3_5'] / $grandTotalUnit) * 100) : 0;
          $nilai_3_5_persen = ($grandTotalNilai > 0) ? (($row['nilai_3_5'] / $grandTotalNilai) * 100) : 0;
          $unit_5_persen = ($grandTotalUnit > 0) ? (($row['unit_5'] / $grandTotalUnit) * 100) : 0;
          $nilai_5_persen = ($grandTotalNilai > 0) ? (($row['nilai_5'] / $grandTotalNilai) * 100) : 0;
          $unit_blm_dikirim_persen = ($grandTotalUnit > 0) ? (($row['unit_blm_dikirim'] / $grandTotalUnit) * 100) : 0;
          $nilai_blm_dikirim_persen = ($grandTotalNilai > 0) ? (($row['nilai_blm_dikirim'] / $grandTotalNilai) * 100) : 0;
          $unit_total_persen = ($grandTotalUnit > 0) ? (($row['unit_total'] / $grandTotalUnit) * 100) : 0;
          $nilai_total_persen = ($grandTotalNilai > 0) ? (($row['nilai_total'] / $grandTotalNilai) * 100) : 0;

          $data[] = [
            'no' => ++$fake,
            'nama_pelanggan' => $row['nama_pelanggan'],
            'nama_cabang' => $row['nama_cabang'],
            'unit_1_2' => number_format($row['unit_1_2'],0,".",","),
            'unit_1_2_persen' => number_format($unit_1_2_persen,2,".",","),
            'nilai_1_2' => number_format($row['nilai_1_2'],0,".",","),
            'nilai_1_2_persen' => number_format($nilai_1_2_persen,2,".",","),
            'unit_3_5' => number_format($row['unit_3_5'],0,".",","),
            'unit_3_5_persen' => number_format($unit_3_5_persen,2,".",","),
            'nilai_3_5' => number_format($row['nilai_3_5'],0,".",","),
            'nilai_3_5_persen' => number_format($nilai_3_5_persen,2,".",","),
            'unit_5' => number_format($row['unit_5'],0,".",","),
            'unit_5_persen' => number_format($unit_5_persen,2,".",","),
            'nilai_5' => number_format($row['nilai_5'],0,".",","),
            'nilai_5_persen' => number_format($nilai_5_persen,2,".",","),
            'unit_blm_dikirim' => number_format($row['unit_blm_dikirim'],0,".",","),
            'unit_blm_dikirim_persen' => number_format($unit_blm_dikirim_persen,2,".",","),
            'nilai_blm_dikirim' => number_format($row['nilai_blm_dikirim'],0,".",","),
            'nilai_blm_dikirim_persen' => number_format($nilai_blm_dikirim_persen,2,".",","),
            'unit_total' => number_format($row['unit_total'],0,".",","),
            'unit_total_persen' => number_format($unit_total_persen,2,".",","),
            'nilai_total' => number_format($row['nilai_total'],0,".",","),
            'nilai_total_persen' => number_format($nilai_total_persen,2,".",","),
          ];

          $grandUnit1           += $row['unit_1_2'];
          $grandUnit1Persen     += $unit_1_2_persen;
          $grandNilai1          += $row['nilai_1_2'];
          $grandNilai1Persen    += $nilai_1_2_persen;
          $grandUnit3           += $row['unit_3_5'];
          $grandUnit3Persen     += $unit_3_5_persen;
          $grandNilai3          += $row['nilai_3_5'];
          $grandNilai3Persen    += $nilai_3_5_persen;
          $grandUnit5           += $row['unit_5'];
          $grandUnit5Persen     += $unit_5_persen;
          $grandNilai5          += $row['nilai_5'];
          $grandNilai5Persen    += $nilai_5_persen;
          $grandUnitBlm         += $row['unit_blm_dikirim'];
          $grandUnitBlmPersen   += $unit_blm_dikirim_persen;
          $grandNilaiBlm        += $row['nilai_blm_dikirim'];
          $grandNilaiBlmPersen  += $nilai_blm_dikirim_persen;
          $grandUnitTot         += $row['unit_total'];
          $grandUnitTotPersen   += $unit_total_persen;
          $grandNilaiTot        += $row['nilai_total'];
          $grandNilaiTotPersen  += $nilai_total_persen;
        }

        return response()->json([
          'draw' => intval($request->input('draw')),
          'recordsTotal' => intval($totalData),
          'recordsFiltered' => intval($totalFiltered),
          'data' => $data,
          'grand_unit_1_2' => number_format($grandUnit1, 0, '.', ','),
          'grand_unit_1_2_persen' => number_format($grandUnit1Persen, 2, '.', ','),
          'grand2_unit_1_2_persen' => number_format($grandUnit1Persen - 30, 2, '.', ','),
          'grand_nilai_1_2' => number_format($grandNilai1, 0, '.', ','),
          'grand_nilai_1_2_persen' => number_format($grandNilai1Persen, 2, '.', ','),
          'grand2_nilai_1_2_persen' => number_format($grandNilai1Persen - 30, 2, '.', ','),
          'grand_unit_3_5' => number_format($grandUnit3, 0, '.', ','),
          'grand_unit_3_5_persen' => number_format($grandUnit3Persen, 2, '.', ','),
          'grand2_unit_3_5_persen' => number_format($grandUnit3Persen - 60, 2, '.', ','),
          'grand_nilai_3_5' => number_format($grandNilai3, 0, '.', ','),
          'grand_nilai_3_5_persen' => number_format($grandNilai3Persen, 2, '.', ','),
          'grand2_nilai_3_5_persen' => number_format($grandNilai3Persen - 60, 2, '.', ','),
          'grand_unit_5' => number_format($grandUnit5, 0, '.', ','),
          'grand_unit_5_persen' => number_format($grandUnit5Persen, 2, '.', ','),
          'grand2_unit_5_persen' => number_format($grandUnit5Persen - 10, 2, '.', ','),
          'grand_nilai_5' => number_format($grandNilai5, 0, '.', ','),
          'grand_nilai_5_persen' => number_format($grandNilai5Persen, 2, '.', ','),
          'grand2_nilai_5_persen' => number_format($grandNilai5Persen - 10, 2, '.', ','),
          'grand_unit_blm_dikirim' => number_format($grandUnitBlm, 0, '.', ','),
          'grand_unit_blm_dikirim_persen' => number_format($grandUnitBlmPersen, 2, '.', ','),
          'grand2_unit_blm_dikirim_persen' => number_format($grandUnitBlmPersen - 0, 2, '.', ','),
          'grand_nilai_blm_dikirim' => number_format($grandNilaiBlm, 0, '.', ','),
          'grand_nilai_blm_dikirim_persen' => number_format($grandNilaiBlmPersen, 2, '.', ','),
          'grand2_nilai_blm_dikirim_persen' => number_format($grandNilaiBlmPersen - 0, 2, '.', ','),
          'grand_unit_total' => number_format($grandUnitTot, 0, '.', ','),
          'grand_unit_total_persen' => number_format($grandUnitTotPersen, 2, '.', ','),
          'grand2_unit_total_persen' => number_format($grandUnitTotPersen - 100, 2, '.', ','),
          'grand_nilai_total' => number_format($grandNilaiTot, 0, '.', ','),
          'grand_nilai_total_persen' => number_format($grandNilaiTotPersen, 2, '.', ','),
          'grand2_nilai_total_persen' => number_format($grandNilaiTotPersen - 100, 2, '.', ','),
        ]);

      } elseif($request->jenis_laporan == "rinci") {

        $datas = DB::select('CALL up_apl_rep_os_penagihan_dtl(?, ?, ?)', [
          $user_cabang,
          $startDate,
          'tanggal',
        ]);

        // Total baris tanpa filter
        $totalData = count($datas);

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = count($datas);

        // Susun payload DataTables
        $data = [];
        $fake = 0; //$start;
        foreach ($datas as $row) {
            $sisaTagihan = $row->total - ($row->total_or_ass + $row->uang_muka);

            $data[] = [
              'no' => ++$fake,
              'tgl_kwitansi' => blank($row->tgl_kwitansi) ? '' : date("d/m/Y", strtotime($row->tgl_kwitansi)),
              'kode_kwitansi' => $row->kode_kwitansi,
              'kode_spk' => $row->kode_spk,
              'no_polisi' => $row->no_polisi,
              'merek_tipe' => $row->merek_tipe,
              'kode_claim' => $row->kode_claim,
              'no_polis' => $row->no_polis,
              'tertanggung' => $row->tertanggung,
              'total' => number_format($row->total,0,".",","),
              'total_or_ass' => number_format($row->total_or_ass,0,".",","),
              'uang_muka' => number_format($row->uang_muka,0,".",","),
              'sisa_tagihan' => number_format($sisaTagihan,0,".",","),
              'tgl_pengiriman' => blank($row->tgl_pengiriman) ? '' : date("d/m/Y", strtotime($row->tgl_pengiriman)),
              'hari' => $row->hari,
              'kode_keluar' => $row->kode_keluar,
              'nama_pelanggan' => $row->nama_pelanggan,
              'minggu' => $row->minggu,
            ];
        }

        return response()->json([
          'draw' => intval($request->input('draw')),
          'recordsTotal' => intval($totalData),
          'recordsFiltered' => intval($totalFiltered),
          'data' => $data,
        ]);
      }

    }

    // ✅ Always return full DataTables structure, even if no results
    return response()->json([
      'draw' => 1,
      'recordsTotal' => 0,
      'recordsFiltered' => 0,
      'data' => [],
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
        'jenis_laporan' => 'required',
        'tgl_awal' => 'required'
      ],
      [ // custom messages
        'jenis_laporan.required'    => 'Jenis Laporan wajib diisi.',
        'tgl_awal.required' => 'Tanggal wajib diisi.'
      ]
    );

    $dataArray['jenis_laporan'] = $request->jenis_laporan;
    $dataArray['tgl_awal'] = $request->tgl_awal;

    ## Log Activity
    $desc = "View Laporan Aging Penagihan";
    LogActivity::saveLogActivity($desc, $dataArray);

    return redirect('administrasi/laporan-aging-penagihan')->with('datafilter', $dataArray);
  }

  /**
   * Export data to Excel.
   */
  public function exportExcel(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');
    
    // --- TAMBAHAN: Ambil Nama Cabang untuk Header Excel ---
    // Asumsi ada helper atau query untuk ambil nama cabang. 
    // Jika tidak ada tabel cabang, ganti dengan hardcode string atau session nama cabang.
    // $cabangInfo = DB::table('cabang')->where('kode_cabang', $user_cabang)->first();
    // $namaCabang = $cabangInfo ? $cabangInfo->nama_cabang : $user_cabang;

    // Siapkan struktur data cabang
    $cabangData = [
        'kode' => $user_cabang,
        'nama' => $namaCabang
    ];
    // -----------------------------------------------------

    $filters = $request->all();

    // --- TAMBAHAN: Format String Periode ---
    // $tglAwal = $request->input('tgl_awal', date('d/m/Y'));
    $periodeStr = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('d-M-Y'); //$tglAwal;
    // ---------------------------------------

    if($request->jenis_laporan == "rekap") {
      $fileName = 'Laporan_Aging_Penagihan_Rekap_' . date('Ymd') . '.xlsx';
    } elseif($request->jenis_laporan == "rinci") {
      $fileName = 'Laporan_Aging_Penagihan_Rinci_' . date('Ymd') . '.xlsx';
    }

    ## Log Activity
    $desc = "Export Laporan Aging Penagihan";
    LogActivity::saveLogActivity($desc, $filters);

    // Masukkan $periodeStr dan $cabangData ke dalam use App\Helpers\Helpers as Helper;

class Export
    return Excel::download(new LaporanAgingPenagihanExport($filters, $cabangData, $periodeStr), $fileName);
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $filters = $request->all();

    // --- TAMBAHAN: Format String Periode ---
    $tglAwal = $filters['tgl_awal']; //date('d/m/Y', strtotime($filters['tgl_awal']));
    $periodeStr = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('d-M-Y'); //$tglAwal;
    // ---------------------------------------

    // --- DATA ---
    $datas = [];
    if($filters['jenis_laporan'] == "rekap") {
      $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');

      $results = DB::select('CALL up_apl_rep_os_penagihan_rkp(?, ?)', [
        $user_cabang,
        $startDate,
      ]);
      
      $temp = [];
      $grandTotalUnit = 0;
      $grandTotalNilai = 0;

      foreach ($results as $row) {
        $grandTotalUnit += $row->unit_total;
        $grandTotalNilai += $row->nilai_total;

        $temp[] = [
          'nama_pelanggan' => $row->nama_pelanggan ?? '',
          'nama_cabang' => $row->nama_cabang ?? '',
          'unit_1_2' => $row->unit_1_2 ?? 0,
          'nilai_1_2' => $row->nilai_1_2 ?? 0,
          'unit_3_5' => $row->unit_3_5 ?? 0,
          'nilai_3_5' => $row->nilai_3_5 ?? 0,
          'unit_5' => $row->unit_5 ?? 0,
          'nilai_5' => $row->nilai_5 ?? 0,
          'unit_blm_dikirim' => $row->unit_blm_dikirim ?? 0,
          'nilai_blm_dikirim' => $row->nilai_blm_dikirim ?? 0,
          'unit_total' => $row->unit_total ?? 0,
          'nilai_total' => $row->nilai_total ?? 0,
        ];
      }

      $fake = 0; //$start;
      foreach ($temp as $row) {

        $unit_1_2_persen = ($grandTotalUnit > 0) ? (($row['unit_1_2'] / $grandTotalUnit) * 100) : 0;
        $nilai_1_2_persen = ($grandTotalNilai > 0) ? (($row['nilai_1_2'] / $grandTotalNilai) * 100) : 0;
        $unit_3_5_persen = ($grandTotalUnit > 0) ? (($row['unit_3_5'] / $grandTotalUnit) * 100) : 0;
        $nilai_3_5_persen = ($grandTotalNilai > 0) ? (($row['nilai_3_5'] / $grandTotalNilai) * 100) : 0;
        $unit_5_persen = ($grandTotalUnit > 0) ? (($row['unit_5'] / $grandTotalUnit) * 100) : 0;
        $nilai_5_persen = ($grandTotalNilai > 0) ? (($row['nilai_5'] / $grandTotalNilai) * 100) : 0;
        $unit_blm_dikirim_persen = ($grandTotalUnit > 0) ? (($row['unit_blm_dikirim'] / $grandTotalUnit) * 100) : 0;
        $nilai_blm_dikirim_persen = ($grandTotalNilai > 0) ? (($row['nilai_blm_dikirim'] / $grandTotalNilai) * 100) : 0;
        $unit_total_persen = ($grandTotalUnit > 0) ? (($row['unit_total'] / $grandTotalUnit) * 100) : 0;
        $nilai_total_persen = ($grandTotalNilai > 0) ? (($row['nilai_total'] / $grandTotalNilai) * 100) : 0;

        $datas[] = [
          'no' => ++$fake,
          'nama_pelanggan' => $row['nama_pelanggan'],
          'nama_cabang' => $row['nama_cabang'],
          'unit_1_2' => $row['unit_1_2'],
          'unit_1_2_persen' => $unit_1_2_persen,
          'nilai_1_2' => $row['nilai_1_2'],
          'nilai_1_2_persen' => $nilai_1_2_persen,
          'unit_3_5' => $row['unit_3_5'],
          'unit_3_5_persen' => $unit_3_5_persen,
          'nilai_3_5' => $row['nilai_3_5'],
          'nilai_3_5_persen' => $nilai_3_5_persen,
          'unit_5' => $row['unit_5'],
          'unit_5_persen' => $unit_5_persen,
          'nilai_5' => $row['nilai_5'],
          'nilai_5_persen' => $nilai_5_persen,
          'unit_blm_dikirim' => $row['unit_blm_dikirim'],
          'unit_blm_dikirim_persen' => $unit_blm_dikirim_persen,
          'nilai_blm_dikirim' => $row['nilai_blm_dikirim'],
          'nilai_blm_dikirim_persen' => $nilai_blm_dikirim_persen,
          'unit_total' => $row['unit_total'],
          'unit_total_persen' => $unit_total_persen,
          'nilai_total' => $row['nilai_total'],
          'nilai_total_persen' => $nilai_total_persen,
        ];
      }

      $title = 'Laporan Rekap Kwitansi Belum Ditagih [Outstanding Penagihan]';

      // $pages = 'content.keuangan.laporan.laporan-outstanding-tahun-print';
    } elseif($filters['jenis_laporan'] == "rinci") {
      $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');

      $results = DB::select('CALL up_apl_rep_os_penagihan_dtl(?, ?, ?)', [
        $user_cabang,
        $startDate,
        'tanggal',
      ]);

      $datas = [];
      foreach ($results as $row) {
        $nama_pelanggan = $row->nama_pelanggan ?? '';
        $minggu = $row->minggu ?? '';

        $row->sisa_tagihan = $row->total - ($row->total_or_ass + $row->uang_muka);
        
        if($nama_pelanggan && $minggu) {
          $datas[$nama_pelanggan][$minggu][] = (array) $row;
        }
      }

      $title = 'Laporan Rincian Kwitansi Belum Ditagih [Outstanding Penagihan]';
    }

    ## Log Activity
    $desc = "Print Laporan Aging Penagihan";
    LogActivity::saveLogActivity($desc, $filters);

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.administrasi.laporan.laporan-aging-penagihan-print', [
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