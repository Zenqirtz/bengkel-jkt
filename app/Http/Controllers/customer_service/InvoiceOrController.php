<?php

namespace App\Http\Controllers\customer_service;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Kendaraan;
use App\Models\Spk;
use App\Models\Parameter;
use App\Models\Marketing;
use App\Models\Perantara;
use App\Models\Asuransi;
use App\Models\LogActivity;
use Carbon\Carbon;

class InvoiceOrController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function InvoiceOr(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Invoice OR';

    $user_cabang = session('kd_cabang');
    $status_spk = Parameter::query()->where('nama_tabel', 'STATUS_SPK')->orderBy('no_urut', 'asc')->get();
    $status = Parameter::query()->where('nama_tabel', 'STATUS_SPK_KET')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.customer-service.invoice-or', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'status_spk' => $status_spk,
      'status' => $status,
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

    $columns = [
      1 => 'k.id',
      2 => 'k.tgl_masuk',
      3 => 'k.kode_spk',
      4 => 'k.tgl_masuk',
      5 => 'e.keterangan', // status
      6 => 'k.no_polisi',
      7 => 'b.nama_tipe',
      8 => 'k.pemilik',
      9 => 'c.nama_pelanggan',
      10 => 'k.tgl_batal',
      11 => 'k.tgl_turun_lapangan',
      12 => 'k.tgl_finishing1',
      13 => 'k.tgl_keluar',
      14 => 'd.keterangan', // status_spk
      15 => 'k.no_polis',
      16 => 'k.kode_claim',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'k.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('t_spk_master as k')
      ->leftJoin('m_tipe_kendaraan as b', function ($join) {
        $join->on('b.kode_tipe', '=', 'k.kode_tipe')
            ->on('b.kode_merek', '=', 'k.kode_merek'); // syarat di JOIN
      })
      ->leftJoin('m_pelanggan_hdr as c', function ($join) {
        $join->on('c.kode_pelanggan', '=', 'k.kode_pelanggan')
            ->on('c.kode_cabang', '=', 'k.kode_cabang'); // syarat di JOIN
      })
      ->leftJoin('parameter as d', function ($join) {
        $join->on('d.kode', '=', 'k.kode_status_spk')
            ->where('d.nama_tabel', '=', 'STATUS_SPK'); // syarat di JOIN
      })
      ->leftJoin('parameter as e', function ($join) {
        $join->on('e.kode', '=', 'k.status_spk')
            ->where('e.nama_tabel', '=', 'STATUS_SPK_KET'); // syarat di JOIN
      })
      ->where('k.kode_cabang', $user_cabang)
      ->where('k.ada_or', '01')
      // ->whereIn('k.status_spk', ['07','10'])
      ->where('c.kode_jenis_pelanggan', '00001');
      // ->whereMonth('k.tgl_masuk', date('m'))
      // ->whereYear('k.tgl_masuk', date('Y'));

    // Total baris tanpa filter
    $totalData = (clone $base)->count('k.id');

    // Filtering (search global)
    $query = (clone $base);
    if ($search = trim((string) $request->input('search.value'))) {
        $query->where(function ($q) use ($search) {
            $q->where('k.kode_spk', 'like', "%{$search}%")
              ->orWhere('k.no_polisi', 'like', "%{$search}%")
              ->orWhere('k.pemilik', 'like', "%{$search}%");
        });
    }

    // Filter berdasarkan input yang dikirim dari DataTables
    if ($request->filled('kode_spk')) {
      $query->where('k.kode_spk', 'like', '%' . $request->kode_spk . '%');
    }
    if ($request->filled('no_polisi')) {
      $query->where('k.no_polisi', 'like', '%' . $request->no_polisi . '%');
    }
    if ($request->filled('tgl_masuk_awal')) {
      $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_masuk_awal, 'Asia/Jakarta')->format('Y-m-d');
      $query->where('k.tgl_masuk', '>=', $startDate);
    }
    if ($request->filled('tgl_masuk_akhir')) {
      $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_masuk_akhir, 'Asia/Jakarta')->format('Y-m-d');
      $query->where('k.tgl_masuk', '<=', $endDate);
    }
    if ($request->filled('nama_pelanggan')) {
      $query->where('c.nama_pelanggan', 'like', '%' . $request->nama_pelanggan . '%');
    }
    if ($request->filled('nama_pemilik')) {
      $query->where('k.pemilik', 'like', '%' . $request->nama_pemilik . '%');
    }
    if ($request->filled('no_polis')) {
      $query->where('k.no_polis', 'like', '%' . $request->no_polis . '%');
    }
    if ($request->filled('kode_claim')) {
      $query->where('k.kode_claim', 'like', '%' . $request->kode_claim . '%');
    }
    if ($request->filled('status_spk')) {
      if ($request->status_spk <> 'all') {
        $query->where('k.kode_status_spk', 'like', '%' . $request->status_spk . '%');
      }
    }
    if ($request->filled('status')) {
      if ($request->status <> 'all') {
        $query->where('k.status_spk', 'like', '%' . $request->status . '%');
      }
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('k.id');

    // Ambil data halaman saat ini
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
          'k.tgl_batal',
          'k.tgl_turun_lapangan',
          'k.tgl_finishing1',
          'k.tgl_keluar',
          'd.keterangan as status_spk',
          'k.no_polis',
          'k.kode_claim',
          'k.status_spk as kode_status_spk',
      ])
      ->orderBy($order, $dir)
      ->offset($start)
      ->limit($limit)
      ->get();

    // Susun payload DataTables
    $data = [];
    $fake = $start;
    foreach ($datas as $row) {
        $data[] = [
            'id'  => $row->id,
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
            'tgl_batal' => blank($row->tgl_batal) ? '' : date("d/m/Y", strtotime($row->tgl_batal)),
            'tgl_turun_lapangan' => blank($row->tgl_turun_lapangan) ? '' : date("d/m/Y", strtotime($row->tgl_turun_lapangan)),
            'tgl_finishing1' => blank($row->tgl_finishing1) ? '' : date("d/m/Y", strtotime($row->tgl_finishing1)),
            'tgl_keluar' => blank($row->tgl_keluar) ? '' : date("d/m/Y", strtotime($row->tgl_keluar)),
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
    $user_cabang = session('kd_cabang');
    $dataID = $request->id;

    if ($dataID) {
      $spk = Spk::findOrFail($dataID);
      $no_invoice_old = $spk->no_invoice;

      $rules = [
        // 'no_invoice' => 'required|string|unique:t_spk_master,no_invoice,'.$request->id,
        'tgl_invoice' => 'required',
      ];

      $messages = [
        'tgl_invoice.required' => 'Tanggal Invoice Wajib diisi',
        // 'no_invoice.required' => 'Nomor Invoice Wajib diisi',
        // 'no_invoice.unique' => 'Nomor Invoice sudah digunakan',
      ];

      $validator = Validator::make($request->all(), $rules, $messages);

      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ], 200);
      }

      // $kode_keluar = "123";
      // $nilai_or = str_replace([","], "", $request->nilai_or);
      // $total_or = str_replace([","], "", $request->total_or);

      $data = [
        'tgl_invoice' => Carbon::createFromFormat('d/m/Y', $request->tgl_invoice, 'Asia/Jakarta')->format('Y-m-d H:i:s'),
        'no_invoice' => $request->no_invoice,
        // 'ada_or' => $request->ada_or,
        // 'nilai_or' => $nilai_or,
        // 'total_or' => $total_or,
        'status_spk' => '08',
        'updated_by' => Auth::user()->username
      ];

      if(blank($no_invoice_old)) {
        $nomor = \Helper::getNomorTransaksi($spk->kode_cabang, 'OR');
        $data['no_invoice'] = $nomor;
      }

      $ok = $spk->update($data);

      if($ok) {
        if(blank($no_invoice_old)) {
          ## Update Nomor Invoice
          $res = \Helper::updateNomorTransaksi($spk->kode_cabang, 'OR', $nomor);
        }
      }

      ## Log Activity
      $desc = $ok ? 'Berhasil Proses Invoice OR' : 'Gagal Proses Invoice OR';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } else {
      return response()->json([
        'status'  => false,
        'message' => 'ID Invoice OR tidak sesuai'
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
    // $data = Spk::findOrFail($id);
    $data = DB::table('v_spk')->where('id', $id)->first();

    if($data->kode_jenis_pelanggan == "00002") {
      $result = false;
      return response()->json([
        'status'  => (bool)$result,
        'message' => 'Pelanggan PRIBADI tidak ada OR'
      ]);
    }

    if($data->ada_or != "01") {
      $result = false;
      return response()->json([
        'status'  => (bool)$result,
        'message' => 'Invoice OR tidak perlu dibuat'
      ]);
    }


    $data->tgl_batal = blank($data->tgl_batal) ? '' : date("d/m/Y", strtotime($data->tgl_batal));
    $data->tgl_keluar = blank($data->tgl_keluar) ? '' : date("d/m/Y", strtotime($data->tgl_keluar));
    $data->tgl_invoice = blank($data->tgl_invoice) ?  date("d/m/Y") : date("d/m/Y", strtotime($data->tgl_invoice));
    $data->nilai_or = number_format($data->nilai_or, 2, ".", ",");
    $data->total_or = number_format($data->total_or, 2, ".", ",");
    $data->is_free = ($data->free > 0) ? "01" : "02";

    // if(blank($data->no_invoice)) {
    //   $nomor = \Helper::getNomorTransaksi($data->kode_cabang, 'OR');
    //   $data->no_invoice = $nomor;
    // }

    $result = true;
    return response()->json([
      'status'  => (bool)$result,
      'message' => 'Berhasil Kirim Invoice OR',
      'data' => $data
    ]);
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
    $datas = Spk::where('id', $id)->delete();
  }

  public function cetakInvoice(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Invoice OR';

    $id = $request->id;

    $data = DB::table('v_spk')->where('id', $id)->first();

    if(blank($data->no_invoice)) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }

    $cabang = DB::table('m_cabang')->where('kode_cabang', $data->kode_cabang)->first();
    $cabang->alamat1 = sprintf("%s %s %s", $cabang->alamat1, $cabang->alamat2, $cabang->alamat3);

    $dest = public_path('assets/img/cabang');
    $logo_cabang = $dest.DIRECTORY_SEPARATOR.$cabang->logo_cabang;
    $file_logo = (is_file($logo_cabang)) ? "1" : "0";

    $data->terbilang = \Helper::terbilang_rupiah($data->total_or);

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.customer-service.invoice-or-print', [
      'title' => $title,
      // 'namaCabang' => $namaCabang,
      // 'periodeStr' => $periodeStr,
      'data' => $data,
      'cabang' => $cabang,
      'file_logo' => $file_logo,
      'pageConfigs' => $pageConfigs,
    ]);

  }

  public function cetakInvoiceFilter(Request $request)
  {
    $user_cabang = session('kd_cabang');

    $cabang = DB::table('m_cabang')->where('kode_cabang', $user_cabang)->first();
    $cabang->alamat1 = sprintf("%s %s %s", $cabang->alamat1, $cabang->alamat2, $cabang->alamat3);

    $dest = public_path('assets/img/cabang');
    $logo_cabang = $dest . DIRECTORY_SEPARATOR . $cabang->logo_cabang;
    $file_logo = (is_file($logo_cabang)) ? "1" : "0";

    $query = DB::table('v_spk')
      ->where('kode_cabang', $user_cabang)
      ->whereNotNull('no_invoice')
      ->where('no_invoice', '!=', '');

    // Filter nama pelanggan / asuransi
    if ($request->filled('nama_pelanggan')) {
      $query->where('nama_pelanggan', 'like', '%' . $request->nama_pelanggan . '%');
    }

    // Filter nomor SPK
    if ($request->filled('kode_spk')) {
      $query->where('kode_spk', 'like', '%' . $request->kode_spk . '%');
    }

    // Filter nomor polisi
    if ($request->filled('no_polisi')) {
      $query->where('no_polisi', 'like', '%' . $request->no_polisi . '%');
    }

    // Filter nomor polis
    if ($request->filled('no_polis')) {
      $query->where('no_polis', 'like', '%' . $request->no_polis . '%');
    }

    // Filter nomor klaim
    if ($request->filled('kode_claim')) {
      $query->where('kode_claim', 'like', '%' . $request->kode_claim . '%');
    }

    // Filter tanggal invoice awal
    if ($request->filled('tgl_invoice_awal')) {
      $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_invoice_awal, 'Asia/Jakarta')->format('Y-m-d');
      $query->where('tgl_invoice', '>=', $startDate);
    }

    // Filter tanggal invoice akhir
    if ($request->filled('tgl_invoice_akhir')) {
      $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_invoice_akhir, 'Asia/Jakarta')->format('Y-m-d');
      $query->where('tgl_invoice', '<=', $endDate);
    }

    // Filter status SPK
    if ($request->filled('status_spk') && $request->status_spk !== 'all') {
      $query->where('kode_status_spk', $request->status_spk);
    }

    $datas = $query->orderBy('tgl_invoice', 'asc')->get();

    $title = 'Cetak Invoice OR';

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.customer-service.invoice-or-print-filter', [
      'title'      => $title,
      'datas'      => $datas,
      'cabang'     => $cabang,
      'file_logo'  => $file_logo,
      'pageConfigs' => $pageConfigs,
    ]);
  }

}
