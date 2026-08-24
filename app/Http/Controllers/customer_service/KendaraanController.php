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
use Illuminate\Validation\Rule;
use App\Models\Pemilik;
use App\Models\Kendaraan;
use App\Models\MerekKendaraan;
use App\Models\JenisKendaraan;
use App\Models\TipeKendaraan;
use App\Models\Parameter;
use App\Models\LogActivity;
use App\Models\DokumenFile;
use Carbon\Carbon;
use Intervention\Image\Facades\Image; // PENTING: Import Facade Intervention Image

use App\Helpers\Helpers as Helper;

class KendaraanController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Kendaraan(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Kendaraan';

    $user_cabang = session('kd_cabang');
    $pemilik = Pemilik::query()->select('kode_pemilik','nama_pemilik')->where('kode_cabang', $user_cabang)->orderBy('nama_pemilik', 'asc')->get();
    $merek_kendaraan = MerekKendaraan::query()->select('kode_merek','nama_merek')->where('is_active', 'Y')->orderBy('nama_merek', 'asc')->get();
    $jenis_kendaraan = JenisKendaraan::query()->select('kode_jenis','nama_jenis')->where('is_active', 'Y')->orderBy('nama_jenis', 'asc')->get();
    $jenis_pemilik = Parameter::query()->where('nama_tabel', 'JENIS_PEMILIK')->orderBy('no_urut', 'asc')->get();
    $bahan_bakar = Parameter::query()->where('nama_tabel', 'BAHAN_BAKAR')->orderBy('no_urut', 'asc')->get();
    $perseneling = Parameter::query()->where('nama_tabel', 'PERSENELING')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.customer-service.kendaraan', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'pemilik' => $pemilik,
      'jenis_pemilik' => $jenis_pemilik,
      'merek_kendaraan' => $merek_kendaraan,
      'jenis_kendaraan' => $jenis_kendaraan,
      'bahan_bakar' => $bahan_bakar,
      'perseneling' => $perseneling,
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
      2 => 'k.no_polisi',
      3 => 'e.nama_pemilik',
      4 => 'k.nama_distnk',
      5 => 'b.nama_merek',
      6 => 'b.nama_tipe',
      7 => 'b.nama_jenis',
      8 => 'k.no_rangka',
      9 => 'k.no_mesin',
      10 => 'k.no_model',
      11 => 'k.tahun',
      12 => 'k.ukuran_cc',
      13 => 'c.keterangan', //kode_jenis_perseneling
      14 => 'k.warna',
      15 => 'd.keterangan', //kode_bahan_bakar
      16 => 'k.tgl_stnk_berakhir',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'k.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('m_mobil as k')
    ->leftJoin('v_tipe_kendaraan as b', 'b.kode_tipe', '=', 'k.kode_tipe')
    ->leftJoin('parameter as c', function ($join) {
      $join->on('c.kode', '=', 'k.kode_jenis_perseneling')
          ->where('c.nama_tabel', '=', 'PERSENELING'); // syarat di JOIN
    })
    ->leftJoin('parameter as d', function ($join) {
      $join->on('d.kode', '=', 'k.kode_bahan_bakar')
          ->where('d.nama_tabel', '=', 'BAHAN_BAKAR'); // syarat di JOIN
    })
    ->leftJoin('m_pemilik_hdr as e', function ($join) {
      $join->on('e.kode_pemilik', '=', 'k.kode_pemilik')
          ->on('e.kode_cabang', '=', 'k.kode_cabang'); // syarat di JOIN
    })
    ->where('k.kode_cabang', $user_cabang);

    // Total baris tanpa filter
    $totalData = (clone $base)->count('k.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('k.id', 'like', "%{$search}%")
    //           ->orWhere('k.no_polisi', 'like', "%{$search}%")
    //           ->orWhere('k.nama_distnk', 'like', "%{$search}%");
    //     });
    // }
    if ($request->filled('no_polisi')) {
      $query->where('k.no_polisi', 'like', '%' . $request->no_polisi . '%');
    }
    if ($request->filled('nama_distnk')) {
      $query->where('k.nama_distnk', 'like', '%' . $request->nama_distnk . '%');
      $query->where('e.nama_pemilik', 'like', '%' . $request->nama_distnk . '%');
    }
    if ($request->filled('no_rangka')) {
      $query->where('k.no_rangka', 'like', '%' . $request->no_rangka . '%');
    }
    if ($request->filled('no_mesin')) {
      $query->where('k.no_mesin', 'like', '%' . $request->no_mesin . '%');
    }
    if ($request->filled('no_model')) {
      $query->where('k.no_model', 'like', '%' . $request->no_model . '%');
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('k.id');

    // Ambil data halaman saat ini
    $datas = $query
      ->select([
          'k.id',
          'k.kode_cabang',
          'k.no_polisi',
          'k.kode_pemilik',
          'k.nama_distnk',
          'k.kode_merek',
          'k.no_mesin',
          'k.no_rangka',
          'k.no_model',
          'k.kode_tipe',
          'k.tipe_stnk',
          'k.tahun',
          'k.ukuran_cc',
          'k.jenis',
          'k.kode_jenis_perseneling',
          'k.warna',
          'k.kode_bahan_bakar',
          'k.tgl_stnk_berakhir',
          'b.nama_tipe',
          'b.nama_jenis',
          'b.nama_merek',
          'c.keterangan as perseneling',
          'd.keterangan as bahan_bakar',
          'e.nama_pemilik',
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
            'no_polisi' => $row->no_polisi,
            'kode_pemilik' => $row->kode_pemilik,
            'nama_distnk' => $row->nama_distnk,
            'kode_merek' => $row->kode_merek,
            'no_mesin' => $row->no_mesin,
            'no_rangka' => $row->no_rangka,
            'no_model' => $row->no_model,
            'kode_tipe' => $row->kode_tipe,
            'tipe_stnk' => $row->tipe_stnk,
            'tahun' => $row->tahun,
            'ukuran_cc' => $row->ukuran_cc,
            'jenis' => $row->jenis,
            'kode_jenis_perseneling' => $row->kode_jenis_perseneling,
            'warna' => $row->warna,
            'kode_bahan_bakar' => $row->kode_bahan_bakar,
            'nama_tipe' => $row->nama_tipe,
            'nama_jenis' => $row->nama_jenis,
            'nama_merek' => $row->nama_merek,
            'perseneling' => $row->perseneling,
            'bahan_bakar' => $row->bahan_bakar,
            'nama_pemilik' => $row->nama_pemilik,
            'tgl_stnk_berakhir' => $row->tgl_stnk_berakhir ? date("d/m/Y", strtotime($row->tgl_stnk_berakhir)) : '',
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

    $file = $request->file('file_stnk');

    if ($file) {
      // Ambil extension file
      $ext = $file->extension();

      // UPLOAD FILE
      if($ext == "pdf") {
        $rules = [
          'file_stnk'  => 'required|mimes:pdf|max:250',
        ];
    
        $messages = [
          'file_stnk.required' => 'File Foto STNK Wajib diisi',
          'file_stnk.image'    => 'File harus berupa PDF.',
          'file_stnk.mimes'    => 'Format foto STNK harus PDF.',
          'file_stnk.max'      => 'Ukuran file PDF maksimal 250 KB.',
        ];
      } else {
        $rules = [
          'file_stnk'  => 'required|image|mimes:jpg,jpeg,png',
        ];
    
        $messages = [
          'file_stnk.required' => 'File Foto STNK Wajib diisi',
          'file_stnk.image'    => 'File harus berupa gambar.',
          'file_stnk.mimes'    => 'Format foto STNK harus jpg, jpeg, atau png.',
        ];
      }

      $validator = Validator::make($request->all(), $rules, $messages);

      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal Upload File.",
          'errors' => $validator->errors()
        ], 200);
      }
    }

    if ($dataID) {
      $kendaraan = Kendaraan::findOrFail($dataID);

      $rules = [
        'no_polisi' => [
            'required',
            'string',
            'max:50',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_mobil', 'no_polisi')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang)   // Cek Kolom 2
                    ->where('kode_pemilik', $request->kode_pemilik);   // Cek Kolom 3
                })
                ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'no_rangka' => [
          'required',
          'string',
          'max:50',
          // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
          Rule::unique('m_mobil', 'no_rangka')
              ->where(function ($query) use ($request) {
                return $query
                  ->where('kode_cabang', $request->kode_cabang)   // Cek Kolom 2
                  ->where('kode_pemilik', $request->kode_pemilik);   // Cek Kolom 3
              })
              ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'no_mesin' => [
          'required',
          'string',
          'max:50',
          // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
          Rule::unique('m_mobil', 'no_mesin')
              ->where(function ($query) use ($request) {
                return $query
                  ->where('kode_cabang', $request->kode_cabang)   // Cek Kolom 2
                  ->where('kode_pemilik', $request->kode_pemilik);   // Cek Kolom 3
              })
              ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'kode_pemilik' => 'required',
        'nama_distnk' => 'required',
        'kode_merek' => 'required',
        'jenis_kendaraan' => 'required',
        'kode_tipe' => 'required',
        'kode_jenis_perseneling' => 'required',
        'kode_bahan_bakar' => 'required',
      ];
  
      $messages = [
        'no_polisi.required' => 'Nomor Polisi Wajib diisi',
        'no_polisi.unique' => 'Nomor Polisi sudah digunakan',
        'kode_pemilik.required'  => 'Nama Pemilik Wajib diisi',
        'nama_distnk.required'  => 'Nama STNK Wajib diisi',
        'kode_merek.required'  => 'Merek Kendaraan Wajib diisi',
        'jenis_kendaraan.required'  => 'Jenis Kendaraan Wajib diisi',
        'kode_tipe.required'  => 'Tipe Kendaraan Wajib diisi',
        'kode_jenis_perseneling.required'  => 'Perseneling Wajib diisi',
        'kode_bahan_bakar.required'  => 'Bahan Bakar Wajib diisi',
        'no_rangka.required'  => 'Nomor Rangka Wajib diisi',
        'no_rangka.unique' => 'Nomor Rangka sudah digunakan',
        'no_mesin.required'  => 'Nomor Mesin Wajib diisi',
        'no_mesin.unique' => 'Nomor Mesin sudah digunakan',
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
        'kode_cabang' => $user_cabang,
        'no_polisi' => $request->no_polisi,
        'kode_pemilik' => $request->kode_pemilik,
        'nama_distnk' => $request->nama_distnk,
        'kode_merek' => $request->kode_merek,
        'no_mesin' => $request->no_mesin,
        'no_rangka' => $request->no_rangka,
        'kode_tipe' => $request->kode_tipe,
        // 'tipe_stnk' => $request->tipe_stnk,
        'tahun' => $request->tahun,
        'ukuran_cc' => $request->ukuran_cc,
        'jenis' => $request->jenis_kendaraan,
        'kode_jenis_perseneling' => $request->kode_jenis_perseneling,
        'warna' => $request->warna,
        'kode_bahan_bakar' => $request->kode_bahan_bakar,
        // 'kode_mesin' => $request->kode_mesin,
        // 'kode_penggerak_roda' => $request->kode_penggerak_roda,
        'tgl_stnk_berakhir' => $request->tgl_stnk_berakhir ? Carbon::createFromFormat('d/m/Y', $request->tgl_stnk_berakhir, 'Asia/Jakarta')->format('Y-m-d') : null,
        'no_model' => $request->no_model,
        'updated_by' => Auth::user()->username
      ];

      $ok = $kendaraan->update($data);

      if($ok) {
        // Upload File
        if ($file) {
          $namaTabel = Kendaraan::getModel()->getTable();

          DokumenFile::saveDokumenFile($dataID, $namaTabel, 'STNK', $file);
        }
      }

      ## Log Activity
      $desc = $ok ? 'Berhasil ubah Data Kendaraan.' : 'Gagal ubah Data Kendaraan.';
      if($file) {
        unset($data['file_stnk']);
      }
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } else {
      $rules = [
        'no_polisi' => [
            'required',
            'string',
            'max:50',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_mobil', 'no_polisi')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang)   // Cek Kolom 2
                    ->where('kode_pemilik', $request->kode_pemilik);   // Cek Kolom 3
                })
                ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'no_rangka' => [
          'required',
          'string',
          'max:50',
          // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
          Rule::unique('m_mobil', 'no_rangka')
              ->where(function ($query) use ($request) {
                return $query
                  ->where('kode_cabang', $request->kode_cabang)   // Cek Kolom 2
                  ->where('kode_pemilik', $request->kode_pemilik);   // Cek Kolom 3
              })
              ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'no_mesin' => [
          'required',
          'string',
          'max:50',
          // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
          Rule::unique('m_mobil', 'no_mesin')
              ->where(function ($query) use ($request) {
                return $query
                  ->where('kode_cabang', $request->kode_cabang)   // Cek Kolom 2
                  ->where('kode_pemilik', $request->kode_pemilik);   // Cek Kolom 3
              })
              ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'kode_pemilik' => 'required',
        'nama_distnk' => 'required',
        'kode_merek' => 'required',
        'jenis_kendaraan' => 'required',
        'kode_tipe' => 'required',
        'kode_jenis_perseneling' => 'required',
        'kode_bahan_bakar' => 'required',
      ];
  
      $messages = [
        'no_polisi.required' => 'Nomor Polisi Wajib diisi',
        'no_polisi.unique' => 'Nomor Polisi sudah digunakan',
        'kode_pemilik.required'  => 'Nama Pemilik Wajib diisi',
        'nama_distnk.required'  => 'Nama STNK Wajib diisi',
        'kode_merek.required'  => 'Merek Kendaraan Wajib diisi',
        'jenis_kendaraan.required'  => 'Jenis Kendaraan Wajib diisi',
        'kode_tipe.required'  => 'Tipe Kendaraan Wajib diisi',
        'kode_jenis_perseneling.required'  => 'Perseneling Wajib diisi',
        'kode_bahan_bakar.required'  => 'Bahan Bakar Wajib diisi',
        'no_rangka.required'  => 'Nomor Rangka Wajib diisi',
        'no_rangka.unique' => 'Nomor Rangka sudah digunakan',
        'no_mesin.required'  => 'Nomor Mesin Wajib diisi',
        'no_mesin.unique' => 'Nomor Mesin sudah digunakan',
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
        'kode_cabang' => $user_cabang,
        'no_polisi' => $request->no_polisi,
        'kode_pemilik' => $request->kode_pemilik,
        'nama_distnk' => $request->nama_distnk,
        'kode_merek' => $request->kode_merek,
        'no_mesin' => $request->no_mesin,
        'no_rangka' => $request->no_rangka,
        'kode_tipe' => $request->kode_tipe,
        // 'tipe_stnk' => $request->tipe_stnk,
        'tahun' => $request->tahun,
        'ukuran_cc' => $request->ukuran_cc,
        'jenis' => $request->jenis_kendaraan,
        'kode_jenis_perseneling' => $request->kode_jenis_perseneling,
        'warna' => $request->warna,
        'kode_bahan_bakar' => $request->kode_bahan_bakar,
        // 'kode_mesin' => $request->kode_mesin,
        // 'kode_penggerak_roda' => $request->kode_penggerak_roda,
        'tgl_stnk_berakhir' => $request->tgl_stnk_berakhir ? Carbon::createFromFormat('d/m/Y', $request->tgl_stnk_berakhir, 'Asia/Jakarta')->format('Y-m-d') : null,
        'no_model' => $request->no_model,
        'created_by' => Auth::user()->username
      ];

      $ok = Kendaraan::create($data);

      if($ok) {
        // Upload File
        if ($file) {
          $lastId = $ok->id; 
          $namaTabel = Kendaraan::getModel()->getTable();

          DokumenFile::saveDokumenFile($lastId, $namaTabel, 'STNK', $file);
        }
      }

      ## Log Activity
      $desc = $ok ? 'Berhasil tambah Data Kendaraan.' : 'Gagal tambah Data Kendaraan.';
      if($file) {
        unset($data['file_stnk']);
      }
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
    $data = Kendaraan::findOrFail($id);
    unset($data->file_stnk);
    $data->tgl_stnk_berakhir = $data->tgl_stnk_berakhir ? date("d/m/Y", strtotime($data->tgl_stnk_berakhir)) : '';

    // $dtTipe = TipeKendaraan::query()->where('kode_tipe', $data->kode_tipe)->first();
    $dtTipe = DB::table('v_tipe_kendaraan')->where('kode_tipe', $data->kode_tipe)->first();
    if($dtTipe) {
      $data->nama_tipe = $dtTipe->nama_tipe;
      $data->nama_jenis = $dtTipe->nama_jenis;
      $data->nama_merek = $dtTipe->nama_merek;
      $data->jenis = (blank($data->jenis)) ? $dtTipe->kode_jenis : $data->jenis;
    }

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
    $data = Kendaraan::query()->where('id', $id)->first()->toArray();

    $ok = Kendaraan::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Kendaraan.' : 'Gagal Hapus Data Kendaraan.';
    LogActivity::saveLogActivity($desc, $data);
  }

  /**
   * Mengambil data Tipe Kendaraan berdasarkan Merek dan Jenis.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function getTipeKendaraan(Request $request): JsonResponse
  {
    $merekId = $request->query('merek_id');
    $jenisId = $request->query('jenis_id');

    if (!$merekId || !$jenisId) {
        return response()->json([]);
    }

    // PERHATIAN: Sesuaikan nama kolom 'kode_merek' dan 'jenis_kendaraan_id' 
    // dengan nama kolom asli yang ada di tabel 'tipe_kendaraan' database Anda.
    $data = TipeKendaraan::where('kode_merek', $merekId)
              ->where('kode_jenis', $jenisId) // atau 'kode_jenis' atau 'id_jenis'
              ->where('is_active', 'Y')
              ->select('id', 'kode_tipe', 'nama_tipe') // Sesuaikan nama kolom output (misal: nama_tipe)
              ->orderBy('nama_tipe', 'asc')
              ->get();

    return response()->json($data);
  }

  public function getKendaraan($id): JsonResponse
  {
    if(!$id) {
      return response()->json([]);
    }

    $user_cabang = session('kd_cabang');
    $data = DB::table('v_mobil')->where('kode_cabang', $user_cabang)->where('no_polisi', 'like', '%' . $id . '%')->first();
    // $data = DB::table('v_mobil')->where('id', $id)->first();

    return response()->json($data);
  }

  public function getPemilik($id): JsonResponse
  {
    if(!$id) {
      return response()->json([]);
    }

    $user_cabang = session('kd_cabang');
    $data = Pemilik::where('kode_cabang', $user_cabang)->where('kode_pemilik', $id)->first();

    return response()->json($data);
  }

  public function getFotoSTNK(Request $request): JsonResponse
  {
    $id = $request->query('id');

    if (!$id) {
      return response()->json([]);
    }

    // Ambil data foto
    $namaTabel = Kendaraan::getModel()->getTable();
    $tipe = 'STNK';
    $photos = DokumenFile::where('parent_id', $id)->where('relasi_tabel', $namaTabel)->where('tipe', $tipe)->orderBy('id', 'desc')->get();

    // Mapping data foto agar format gambar menjadi Base64 string
    $data = $photos->map(function($item) {
      if($item->photo) {
        return [
            'id' => $item->id,
            'no_urut' => 1,
            'nama_panel' => $item->nama_file,
            // Lakukan Encode base64 di PHP, bukan di JS
            'photo_panel_base64' => $item->photo ? base64_encode($item->photo) : null
        ];
      }
    });

    // Gabungkan ke respon JSON
    // $data->photos = $photosArray;

    return response()->json($data);
  }

  public function hapusFotoSTNK($id)
  {
    $data = DokumenFile::query()->where('id', $id)->first()->toArray();

    $ok = DokumenFile::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Foto STNK Kendaraan.' : 'Gagal Hapus Foto STNK Kendaraan.';
    unset($data['photo']);
    LogActivity::saveLogActivity($desc, $data);
  }
}