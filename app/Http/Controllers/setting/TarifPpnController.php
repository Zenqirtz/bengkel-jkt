<?php

namespace App\Http\Controllers\setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\TarifPpn;
use App\Models\LogActivity;
use Carbon\Carbon;

class TarifPpnController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function TarifPPN(): View
  {
    $isList = \Helper::AuthIsPerm("list");
    $isAdd = \Helper::AuthIsPerm("add");
    $isEdit = \Helper::AuthIsPerm("edit");
    $isDel = \Helper::AuthIsPerm("delete");
    if(!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path = request()->path();
    $title = \Helper::getTitleMenu($path) ?? 'Tarif PPN';

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.setting.tarif-ppn', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
    ]);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request): JsonResponse
  {
    $columns = [
      1 => 'id',
      2 => 'startdate',
      3 => 'enddate',
      4 => 'ppn',
    ];

    $totalData = TarifPpn::count(); // Total records without filtering
    // $totalFiltered = $totalData;

    $limit = $request->input('length');
    $start = $request->input('start');
    $order = $columns[$request->input('order.0.column')] ?? 'id';
    $dir = $request->input('order.0.dir') ?? 'desc';

    $query = TarifPpn::query();

    // Search handling
    // if (!empty($request->input('search.value'))) {
    //   $search = $request->input('search.value');

    //   $query->where(function ($q) use ($search) {
    //     $q->where('id', 'LIKE', "%{$search}%")
    //       ->orWhere('startdate', 'LIKE', "%{$search}%")
    //       ->orWhere('enddate', 'LIKE', "%{$search}%")
    //       ->orWhere('ppn', 'LIKE', "%{$search}%");
    //   });

    //   $totalFiltered = $query->count();
    // }

    if ($request->filled('tanggal')) {
      try {
        $tanggal = Carbon::createFromFormat('d/m/Y', $request->tanggal)->endOfDay();
        $query->where('startdate', '<=', $tanggal);
        $query->where('enddate', '>=', $tanggal);
      } catch (\Exception $e) {}
    }
    if ($request->filled('ppn')) {
      $query->where('ppn', 'like', '%' . $request->ppn . '%');
    }

    $totalFiltered = $query->count();

    $datas = $query->offset($start)
      ->limit($limit)
      ->orderBy($order, $dir)
      ->get();

    $data = [];
    $ids = $start;

    foreach ($datas as $row) {
      $data[] = [
        'id' => $row->id,
        'fake_id' => ++$ids,
        'startdate' => date("d/m/Y", strtotime($row->startdate)),
        'enddate' => date("d/m/Y", strtotime($row->enddate)),
        'ppn' => $row->ppn,
      ];
    }

    // ✅ Always return full DataTables structure, even if no results
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
    // 1. Ambil ID dan format tanggal input terlebih dahulu
    $dataID = $request->id;
    $startDate = Carbon::createFromFormat('d/m/Y', $request->startdate, 'Asia/Jakarta')->format('Y-m-d');
    $endDate = Carbon::createFromFormat('d/m/Y', $request->enddate, 'Asia/Jakarta')->format('Y-m-d');

    // 2. Validasi dasar: pastikan tanggal akhir tidak kurang dari tanggal awal
    if ($startDate > $endDate) {
         return response()->json([
            'status' => false,
            'message' => "Periode Akhir tidak boleh kurang dari Periode Awal."
        ]); // 422 adalah status error validasi
    }

    // 3. Buat query untuk cek tumpang tindih (overlap)
    $overlapQuery = TarifPpn::where(function ($q) use ($startDate, $endDate) {
        // Logika overlap:
        // (start_baru <= end_lama) AND (end_baru >= start_lama)
        $q->where('startdate', '<=', $endDate)
          ->where('enddate', '>=', $startDate);
    });

    // 4. Jika ini adalah UPDATE, kita harus mengecualikan ID data itu sendiri
    if ($dataID) {
        $overlapQuery->where('id', '!=', $dataID);
    }

    // 5. Eksekusi pengecekan
    $existingOverlap = $overlapQuery->first();

    // 6. JIKA DITEMUKAN overlap, kembalikan pesan error
    if ($existingOverlap) {
        return response()->json([
            'status' => false,
            'message' => "Periode tanggal tumpang tindih dengan data yang sudah ada (Periode: " .
                         Carbon::parse($existingOverlap->startdate)->format('d/m/Y') . " s/d " .
                         Carbon::parse($existingOverlap->enddate)->format('d/m/Y') . ")."
        ]); // 422 Unprocessable Entity
    }

    // 7. Lolos Validasi: Lanjutkan simpan data
    // (Saya sederhanakan logika if-else Anda agar lebih bersih)

    if ($dataID) {
      // --- PROSES UPDATE ---
      $datas = TarifPpn::find($dataID);
      if ($datas) {
          $data = [
            'startdate' => $startDate,
            'enddate' => $endDate,
            'ppn' => $request->ppn,
            'updated_by' => Auth::user()->username
          ];

          $ok = $datas->update($data);

          ## Log Activity
          $desc = $ok ? 'Berhasil Ubah Data Tarif PPN' : 'Gagal Ubah Data Tarif PPN';
          LogActivity::saveLogActivity($desc, $data);

          return response()->json([
            'status'  => (bool)$ok,
            'message' => $desc
          ]);
      }
      // Handle jika ID tidak ditemukan (meskipun jarang terjadi)
      return response()->json(['status' => false, 'message' => "Data tidak ditemukan"]);

    } else {
      // --- PROSES CREATE ---
      $data = [
        'startdate' => $startDate,
        'enddate' => $endDate,
        'ppn' => $request->ppn,
        'created_by' => Auth::user()->username,
      ];

      $ok = TarifPpn::create($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil Tambah Data Tarif PPN' : 'Gagal Tambah Data Tarif PPN';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
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
    $data = TarifPpn::findOrFail($id);
    $data->startdate = date("d/m/Y", strtotime($data->startdate));
    $data->enddate = date("d/m/Y", strtotime($data->enddate));
    return response()->json($data);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id) {}

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $data = TarifPpn::query()->where('id', $id)->first()->toArray();

    $ok = TarifPpn::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Tarif PPN' : 'Gagal Hapus Data Tarif PPN';
    LogActivity::saveLogActivity($desc, $data);
  }
}