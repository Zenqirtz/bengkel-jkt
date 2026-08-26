<?php

namespace App\Http\Controllers\keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Parameter;
use App\Models\Bank;
use App\Models\PelangganFinance;
use App\Models\BuktiPenerimaan;
use App\Models\BuktiPenerimaanDetail;
use App\Models\LogActivity;
use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class BuktiPenerimaanController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function BuktiPenerimaan(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Bukti Penerimaan';

    $user_cabang = session('kd_cabang');
    $bank = Bank::query()->select('kode_bank', 'nama_bank')->where('is_active', 'Y')->orderBy('nama_bank', 'asc')->get();
    $kategori = Parameter::query()->select('kode', 'keterangan')->where('nama_tabel', 'KATEGORI_REKENING')->orderBy('no_urut', 'asc')->get();
    $pelanggan = PelangganFinance::query()->select('kode_pelanggan', 'nama_pelanggan')->where('is_active', 'Y')->orderBy('nama_pelanggan', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.keuangan.bukti-penerimaan', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'bank' => $bank,
      'kategori' => $kategori,
      'pelanggan' => $pelanggan,
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

    try {
      if ($request->tipe == "detail") {
        $columns = [
          1 => 'a.id',
          2 => 'a.uraian',
          3 => 'a.jumlah',
        ];

        $limit = (int) $request->input('length', 10);
        $start = (int) $request->input('start', 0);
        $order = $columns[$request->input('order.0.column')] ?? 'a.id';
        $dir = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';

        // Base query + LEFT JOIN
        $base = DB::table('t_transaksi_masuk_dtl as a')
          ->where('a.kode_cabang', $request->kode_cabang)
          ->where('a.no_transaksi', $request->no_transaksi);

        // Total baris tanpa filter
        $totalData = (clone $base)->count('a.id');

        // Filtering (search global)
        $query = (clone $base);
        if ($search = trim((string) $request->input('search.value'))) {
          $query->where(function ($q) use ($search) {
            $q->where('a.uraian', 'like', "%{$search}%")
              ->orWhere('a.jumlah', 'like', "%{$search}%");
          });
        }

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('a.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'a.id',
            'a.no_transaksi',
            'a.uraian',
            'a.jumlah',
          ])
          ->orderBy($order, $dir)
          // ->offset($start)
          // ->limit($limit)
          ->get();

        // Susun payload DataTables
        $data = [];
        $fake = $start;
        foreach ($datas as $row) {
          $data[] = [
            'id' => $row->id,
            'fake_id' => ++$fake,
            'no_transaksi' => $row->no_transaksi,
            'uraian' => $row->uraian,
            'jumlah' => number_format($row->jumlah, 0, '.', ','),
          ];
        }

        return response()->json([
          'draw' => intval($request->input('draw')),
          'recordsTotal' => intval($totalData),
          'recordsFiltered' => intval($totalFiltered),
          'data' => $data,
        ]);
      } else {
        $columns = [
          1 => 'a.id',
          2 => 'a.tanggal_transaksi',
          3 => 'a.no_transaksi',
          4 => 'a.kategori',
          5 => 'a.no_voucher',
          6 => 'a.nama_pelanggan',
          7 => 'a.memo',
          8 => 'a.nama_cabang',
          9 => 'a.nama_bank',
          10 => 'a.tanggal_ch_bg',
          11 => 'a.no_ch_bg',
          12 => 'a.tanggal_kliring',
          13 => 'a.no_voucher_cabang',
        ];

        $limit = (int) $request->input('length', 10);
        $start = (int) $request->input('start', 0);
        $order = $columns[$request->input('order.0.column')] ?? 'a.id';
        $dir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        // Base query + LEFT JOIN
        $base = DB::table('v_bukti_penerimaan as a')
          ->where('a.kode_cabang', $user_cabang);

        // Total baris tanpa filter
        $totalData = (clone $base)->count('a.id');

        // Filtering (search global)
        $query = (clone $base);
        // if ($search = trim((string) $request->input('search.value'))) {
        //     $query->where(function ($q) use ($search) {
        //         $q->where('b.nama_bank', 'like', "%{$search}%")
        //           ->orWhere('a.no_rekening', 'like', "%{$search}%");
        //     });
        // }
        if ($request->filled('no_transaksi')) {
          $query->where('a.no_transaksi', 'like', '%' . $request->no_transaksi . '%');
        }
        if ($request->filled('memo')) {
          $query->where('a.memo', 'like', '%' . $request->memo . '%');
        }
        if ($request->filled('no_voucher')) {
          $query->where('a.no_voucher', 'like', '%' . $request->no_voucher . '%');
        }
        if ($request->filled('no_ch_bg')) {
          $query->where('a.no_ch_bg', 'like', '%' . $request->no_ch_bg . '%');
        }
        if ($request->filled('tanggal_awal')) {
          $startDate = Carbon::createFromFormat('d/m/Y', $request->tanggal_awal, 'Asia/Jakarta')->format('Y-m-d');
          $query->whereDate('a.tanggal_transaksi', '>=', $startDate);
        }
        if ($request->filled('tanggal_akhir')) {
          $endDate = Carbon::createFromFormat('d/m/Y', $request->tanggal_akhir, 'Asia/Jakarta')->format('Y-m-d');
          $query->whereDate('a.tanggal_transaksi', '<=', $endDate);
        }
        if ($request->filled('kode_bank')) {
          if ($request->kode_bank <> 'all') {
            $query->where('a.kode_bank', 'like', '%' . $request->kode_bank . '%');
          }
        }
        if ($request->filled('kode_kategori')) {
          if ($request->kode_kategori <> 'all') {
            $query->where('a.kode_kategori', 'like', '%' . $request->kode_kategori . '%');
          }
        }

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('a.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'a.id',
            'a.tanggal_transaksi',
            'a.no_transaksi',
            'a.kategori',
            'a.no_voucher',
            'a.nama_pelanggan',
            'a.memo',
            'a.nama_cabang',
            'a.nama_bank',
            'a.tanggal_ch_bg',
            'a.no_ch_bg',
            'a.tanggal_kliring',
            'a.no_voucher_cabang',
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
            'id' => $row->id,
            'fake_id' => ++$fake,
            'tanggal_transaksi' => blank($row->tanggal_transaksi) ? '' : date("d/m/Y", strtotime($row->tanggal_transaksi)),
            'no_transaksi' => $row->no_transaksi,
            'kategori' => $row->kategori,
            'no_voucher' => $row->no_voucher,
            'nama_pelanggan' => $row->nama_pelanggan,
            'memo' => $row->memo,
            'nama_cabang' => $row->nama_cabang,
            'nama_bank' => $row->nama_bank,
            'tanggal_ch_bg' => blank($row->tanggal_ch_bg) ? '' : date("d/m/Y", strtotime($row->tanggal_ch_bg)),
            'no_ch_bg' => $row->no_ch_bg,
            'tanggal_kliring' => blank($row->tanggal_kliring) ? '' : date("d/m/Y", strtotime($row->tanggal_kliring)),
            'no_voucher_cabang' => $row->no_voucher_cabang,
          ];
        }

        return response()->json([
          'draw' => intval($request->input('draw')),
          'recordsTotal' => intval($totalData),
          'recordsFiltered' => intval($totalFiltered),
          'data' => $data,
        ]);
      }
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
  public function store(Request $request)
  {
    try {
      $user_cabang = session('kd_cabang');
      $dataID = $request->id;

      if ($dataID) {
        $rules = [
          'tanggal_transaksi' => 'required',
          'kode_kategori' => 'required',
          'kode_bank' => 'required',
          'kode_bank_asal' => 'required',
          'memo' => 'required',
        ];

        $messages = [
          'tanggal_transaksi.required' => 'Tanggal Wajib diisi',
          'kode_kategori.required' => 'Kategori Wajib diisi',
          'kode_bank.required' => 'Bank Tujuan Wajib diisi',
          'kode_bank_asal.required' => 'Bank Asal Wajib diisi',
          'memo.required' => 'Memo Wajib diisi',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
          return response()->json([
            'status' => false,
            'message' => "Gagal menyimpan data.",
            'errors' => $validator->errors()
          ]);
        }

        $data = [
          'tanggal_transaksi' => blank($request->tanggal_transaksi) ? null : Carbon::createFromFormat('d/m/Y', $request->tanggal_transaksi, 'Asia/Jakarta')->format('Y-m-d'),
          'kode_kategori' => $request->kode_kategori,
          'kode_pelanggan' => $request->kode_pelanggan,
          'memo' => $request->memo,
          'kode_bank' => $request->kode_bank,
          'tanggal_ch_bg' => blank($request->tanggal_ch_bg) ? null : Carbon::createFromFormat('d/m/Y', $request->tanggal_ch_bg, 'Asia/Jakarta')->format('Y-m-d'),
          'no_ch_bg' => $request->no_ch_bg,
          'total' => str_replace([","], "", $request->total),
          'kode_bank_asal' => $request->kode_bank_asal,
          'updated_by' => Auth::user()->username,
        ];

        // update the value
        $ok = BuktiPenerimaan::updateOrCreate(
          ['id' => $dataID],
          $data
        );

        if ($ok) {
          ## INSERT DETAIL BUKTI PENERIMAAN
          // PermintaanBarangDetail::where('kode_cabang', $res->kode_cabang)->where('kode_order', $res->kode_order)->delete();
          if ($request->detail) {
            $no = 1;
            $dataDet = [];
            foreach ($request->detail as $key => $item) {

              $updt = BuktiPenerimaanDetail::find($key);

              if ($updt) {
                $tmp = [
                  'uraian' => $item['uraian'],
                  'jumlah' => str_replace([","], "", $item['jumlah']),
                  'updated_by' => Auth::user()->username,
                ];

                $updt->update($tmp);
              } else {
                $tmp = [
                  'kode_cabang' => $request->kode_cabang,
                  'no_transaksi' => $request->no_transaksi,
                  'uraian' => $item['uraian'],
                  'jumlah' => str_replace([","], "", $item['jumlah']),
                  'created_by' => Auth::user()->username,
                ];

                BuktiPenerimaanDetail::create($tmp);
              }
              $dataDet[] = $tmp;
            }

            $data2['DETAIL'] = $dataDet;
          }
        }

        $data2['HEADER'] = $data;

        ## Log Activity
        $desc = $ok ? 'Berhasil Ubah Bukti Penerimaan' : 'Gagal Ubah Bukti Penerimaan';
        LogActivity::saveLogActivity($desc, $data2);

        return response()->json([
          'status' => (bool) $ok,
          'message' => $desc
        ]);

      } else {
        $rules = [
          'tanggal_transaksi' => 'required',
          'kode_kategori' => 'required',
          'kode_bank' => 'required',
          'kode_bank_asal' => 'required',
          'memo' => 'required',
        ];

        $messages = [
          'tanggal_transaksi.required' => 'Tanggal Wajib diisi',
          'kode_kategori.required' => 'Kategori Wajib diisi',
          'kode_bank.required' => 'Bank Tujuan Wajib diisi',
          'kode_bank_asal.required' => 'Bank Asal Wajib diisi',
          'memo.required' => 'Memo Wajib diisi',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
          return response()->json([
            'status' => false,
            'message' => "Gagal menyimpan data.",
            'errors' => $validator->errors()
          ]);
        }

        ## Nomor Bukti Penerimaan
        $penomoran = Helper::getNomorTransaksi($user_cabang, 'TUM');

        $cekspk = BuktiPenerimaan::where('no_transaksi', $penomoran)->first();
        if (!empty($cekspk)) {
          return response()->json(['status' => false, 'message' => "Nomor Bukti Penerimaan sudah digunakan"]);
        }

        ## Nomor Voucher Masuk
        $noVoucher = Helper::getNomorVoucher($user_cabang, $request->kode_bank, 'NO_VOUCHER_MASUK');

        $data = [
          'kode_cabang' => $user_cabang,
          'no_transaksi' => $penomoran,
          'tanggal_transaksi' => blank($request->tanggal_transaksi) ? null : Carbon::createFromFormat('d/m/Y', $request->tanggal_transaksi, 'Asia/Jakarta')->format('Y-m-d'),
          'kode_kategori' => $request->kode_kategori,
          'no_voucher' => $noVoucher,
          'kode_pelanggan' => $request->kode_pelanggan,
          'memo' => $request->memo,
          'cabang_id' => $user_cabang,
          'kode_bank' => $request->kode_bank,
          'tanggal_ch_bg' => blank($request->tanggal_ch_bg) ? null : Carbon::createFromFormat('d/m/Y', $request->tanggal_ch_bg, 'Asia/Jakarta')->format('Y-m-d'),
          'no_ch_bg' => $request->no_ch_bg,
          'total' => str_replace([","], "", $request->total),
          // 'no_kliring'     => $request->no_kliring,
          // 'tanggal_kliring' => blank($request->tanggal_kliring) ? null : Carbon::createFromFormat('d/m/Y', $request->tanggal_kliring, 'Asia/Jakarta')->format('Y-m-d'),
          // 'no_voucher_cabang'   => $request->no_voucher_cabang,
          'kode_bank_asal' => $request->kode_bank_asal,
          'created_by' => Auth::user()->username,
        ];

        $ok = BuktiPenerimaan::create($data);

        if ($ok) {
          ## INSERT DETAIL BUKTI PENERIMAAN
          BuktiPenerimaanDetail::where('kode_cabang', $user_cabang)->where('no_transaksi', $penomoran)->delete();
          if ($request->detail) {
            $no = 1;
            $dataDet = [];
            foreach ($request->detail as $key => $item) {

              $tmp = [
                'kode_cabang' => $user_cabang,
                'no_transaksi' => $penomoran,
                'uraian' => $item['uraian'],
                'jumlah' => str_replace([","], "", $item['jumlah']),
                'created_by' => Auth::user()->username,
              ];

              BuktiPenerimaanDetail::create($tmp);

              $dataDet[] = $tmp;
            }

            $data2['DETAIL'] = $dataDet;
          }

          ## Update Nomor Bukti Penerimaan
          $res = Helper::updateNomorTransaksi($user_cabang, 'TUM');
        }

        $data2['HEADER'] = $data;

        ## Log Activity
        $desc = $ok ? 'Berhasil Tambah Bukti Penerimaan' : 'Gagal Tambah Bukti Penerimaan';
        LogActivity::saveLogActivity($desc, $data2);

        return response()->json([
          'status' => (bool) $ok,
          'message' => $desc
        ]);
      }
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 200);
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
    $data = DB::table('v_bukti_penerimaan')->where('id', $id)->first();

    if (blank($data)) {
      $result = false;
      return response()->json([
        'status' => (bool) $result,
        'message' => 'Kode Penerimaan tidak ditemukan'
      ]);
    }

    $data->tanggal_transaksi = blank($data->tanggal_transaksi) ? '' : date("d/m/Y", strtotime($data->tanggal_transaksi));
    $data->tanggal_ch_bg = blank($data->tanggal_ch_bg) ? '' : date("d/m/Y", strtotime($data->tanggal_ch_bg));
    $data->tanggal_kliring = blank($data->tanggal_kliring) ? '' : date("d/m/Y", strtotime($data->tanggal_kliring));

    $data->total = number_format($data->total, 2, '.', ',');

    $result = true;
    return response()->json([
      'status' => (bool) $result,
      'message' => 'Berhasil Permintaan Barang',
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
  public function update(Request $request, $id)
  {
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $data = BuktiPenerimaan::query()->where('id', $id)->first()?->toArray() ?? [];

    $ok = BuktiPenerimaan::where('id', $id)->delete();
    if ($ok) {
      BuktiPenerimaanDetail::where('kode_cabang', $data['kode_cabang'])->where('no_transaksi', $data['no_transaksi'])->delete();
    }

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Bukti Penerimaan' : 'Gagal Hapus Bukti Penerimaan';
    LogActivity::saveLogActivity($desc, $data);
  }

  public function cetakBuktiPenerimaan(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Bukti Penerimaan';

    $id = $request->id;

    $data = DB::table('v_bukti_penerimaan')->where('id', $id)->first();

    if (blank($data->no_transaksi)) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }

    $cabang = DB::table('m_cabang')->where('kode_cabang', $data->kode_cabang)->first();
    $cabang->alamat1 = sprintf("%s %s %s", $cabang->alamat1, $cabang->alamat2, $cabang->alamat3);

    // $dest = public_path('assets/img/cabang');
    // $logo_cabang = $dest.DIRECTORY_SEPARATOR.$cabang->logo_cabang;
    // $file_logo = (is_file($logo_cabang)) ? "1" : "0";

    $data->terbilang = Helper::terbilang_rupiah($data->total);

    $data->tanggal_ch_bg = blank($data->tanggal_ch_bg) ? '' : date("d-M-Y", strtotime($data->tanggal_ch_bg));

    // Ambil data detail
    $data_detail = DB::table('t_transaksi_masuk_dtl as a')
      ->select([
        'a.id',
        'a.no_transaksi',
        'a.uraian',
        'a.jumlah',
      ])
      ->where('a.kode_cabang', $data->kode_cabang)
      ->where('a.no_transaksi', $data->no_transaksi)
      ->orderBy('a.id', 'asc')
      ->get();

    ## Log Activity
    $desc = "Cetak " . $title;
    LogActivity::saveLogActivity($desc);

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.keuangan.bukti-penerimaan-cetak', [
      'title' => $title,
      'data' => $data,
      'data_detail' => $data_detail,
      'cabang' => $cabang,
      // 'file_logo' => $file_logo,
      'pageConfigs' => $pageConfigs,
    ]);

  }
}
