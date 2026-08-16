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
use App\Models\Parameter;
use App\Models\Spk;
use App\Models\SpkFoto;
use App\Models\LogActivity;
use Carbon\Carbon;
use Intervention\Image\Facades\Image; // PENTING: Import Facade Intervention Image

class FotoPekerjaanController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function FotoPekerjaan(): View
  {
    $isList = \Helper::AuthIsPerm("list");
    $isAdd = \Helper::AuthIsPerm("add");
    $isEdit = \Helper::AuthIsPerm("edit");
    $isDel = \Helper::AuthIsPerm("delete");
    if(!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $user_cabang = session('kd_cabang');
    $path = request()->path();
    $title = \Helper::getTitleMenu($path) ?? 'Upload Foto Pekerjaan';

    $status_spk = Parameter::query()->where('nama_tabel', 'STATUS_SPK')->orderBy('no_urut', 'asc')->get();
    $status = Parameter::query()->where('nama_tabel', 'STATUS_SPK_KET')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.administrasi.foto-pekerjaan', [
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

    if($request->tipe == "spk") {
      $columns = [
        1 => 'k.id',
        2 => 'k.tgl_masuk',
        3 => 'k.kode_spk',
        4 => 'e.keterangan', // status
        5 => 'k.no_polisi',
        6 => 'b.nama_tipe',
        7 => 'k.pemilik',
        8 => 'c.nama_pelanggan',
        9 => 'k.tgl_batal',
        10 => 'k.tgl_turun_lapangan',
        11 => 'k.tgl_finishing1',
        12 => 'k.tgl_keluar',
        13 => 'd.keterangan', // status_spk
        14 => 'k.no_polis',
        15 => 'k.kode_claim',
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
      ->where('k.kode_cabang', $user_cabang);
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
        $query->whereDate('k.tgl_masuk', '>=', $startDate);
      }
      if ($request->filled('tgl_masuk_akhir')) {
        $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_masuk_akhir, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('k.tgl_masuk', '<=', $endDate);
      }
      if ($request->filled('tipe_kendaraan')) {
        $query->where('b.nama_tipe', 'like', '%' . $request->tipe_kendaraan . '%');
      }
      if ($request->filled('nama_pemilik')) {
        $query->where('k.pemilik', 'like', '%' . $request->nama_pemilik . '%');
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
        $cekFoto = SpkFoto::where('kode_cabang', $row->kode_cabang)
        ->where('kode_spk', $row->kode_spk)
        ->count();

        $photo = ($cekFoto > 0) ? '1' : '0';

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
          'photo' => $photo,
          'tgl_masuk' => blank($row->tgl_masuk) ? '' : date("d/m/Y", strtotime($row->tgl_masuk)),
          // 'no_polis' => $row->no_polis,
          // 'kode_claim' => $row->kode_claim,
          // 'tgl_batal' => blank($row->tgl_batal) ? '' : date("d/m/Y", strtotime($row->tgl_batal)),
          // 'tgl_turun_lapangan' => blank($row->tgl_turun_lapangan) ? '' : date("d/m/Y", strtotime($row->tgl_turun_lapangan)),
          // 'tgl_finishing1' => blank($row->tgl_finishing1) ? '' : date("d/m/Y", strtotime($row->tgl_finishing1)),
          // 'tgl_keluar' => blank($row->tgl_keluar) ? '' : date("d/m/Y", strtotime($row->tgl_keluar)),
        ];
      }
    } else {
      $totalData = 0;
      $totalFiltered = 0;
      $data = [];
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
    $res = Spk::find($dataID);
    
    if ($res) {
      $rules = [
        'photo'       => 'required|array',
        'photo.*'       => 'image|mimes:jpg,jpeg,png', //|max:100
      ];

      $messages = [
        'photo.required' => 'File Foto Wajib diisi',
        'photo.*.image'    => 'File harus berupa gambar.',
        'photo.*.mimes'    => 'Format foto harus jpg, jpeg, atau png.',
        'photo.*.max'      => 'Ukuran foto maksimal 100 KB.',
      ];

      $validator = Validator::make($request->all(), $rules, $messages);

      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ], 200);
      }

      try {
        if ($request->hasFile('photo')) {
          // $file = $request->file('photo');
          foreach ($request->file('photo') as $file) {

            // Ambil nama file
            $filename = $file->getClientOriginalName();

            // ---------------------------------------------------------
            // 1. ORIGINAL FILE
            // ---------------------------------------------------------

            // getRealPath() mengambil lokasi file sementara (temp file) sebelum diupload
            $photoBinary = file_get_contents($file->getRealPath());

            // Ambil ukuran file dalam byte
            $fileSize = $file->getSize();

            if($fileSize > 70000) {
              // ---------------------------------------------------------
              // 2. PROSES KOMPRESI MENGGUNAKAN INTERVENTION IMAGE
              // ---------------------------------------------------------

              // Load gambar ke memori
              $img = Image::make($file->getRealPath());

              // Potong dan sesuaikan menjadi tepat 640 x 480 tanpa gepeng
              $img->fit(640, 480);

              // Paksa ukuran menjadi 640 x 480 (mengabaikan rasio asli)
              // $img->resize(640, 480);

              // Lanjut ke proses encode (kompresi)
              $photoBinary = (string) $img->encode('jpg', 70);

              // Ambil nama file
              $fileSize = strlen($photoBinary);
            }
            

            $lastNum = SpkFoto::query()->where('kode_cabang', $res->kode_cabang)->where('kode_spk', $res->kode_spk)->max(DB::raw('CAST(no_urut AS UNSIGNED)')) ?? 0;
            $nourut = $lastNum + 1;

            $data = [
              'kode_cabang' => $res->kode_cabang,
              'kode_spk' => $res->kode_spk,
              'no_urut' => $nourut,
              'nama_panel' => $filename,
              'photo_panel' => $photoBinary,
              'ukuran' => $fileSize,
              'created_by' => Auth::user()->username
            ];

            $ok = SpkFoto::create($data);

            ## Log Activity
            if($ok) {
              $desc = 'Berhasil upload file foto kerusakan';
              unset($data['photo_panel']);
              LogActivity::saveLogActivity($desc, $data);
            } else {
              $desc = 'Gagal upload file foto kerusakan';
              unset($data['photo_panel']);
              LogActivity::saveLogActivity($desc, $data);
              break;
            }
            
          }
          
          // $desc = $ok ? 'Berhasil upload file foto kerusakan' : 'Gagal upload file foto kerusakan';
          // LogActivity::saveLogActivity($desc, $data);
    
          return response()->json([
            'status'  => (bool)$ok,
            'message' => $desc
          ]);
        }

        return response()->json([
          'status' => false,
          'message' => 'File foto tidak ditemukan.'
        ], 200);

      } catch (\Exception $e) {
          // Tangkap error jika terjadi masalah saat insert ke database
          return response()->json([
              'status' => false,
              'message' => 'Terjadi kesalahan: ' . $e->getMessage()
          ], 200);
      }

    } else {
      return response()->json([
        'status'  => false,
        'message' => 'ID SPK tidak ditemukan'
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

    $data->tgl_masuk = blank($data->tgl_masuk) ? '' : date("d/m/Y", strtotime($data->tgl_masuk));
    
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
    // $datas = Spk::where('id', $id)->delete();
    $data = SpkFoto::query()
      ->select('id', 'kode_cabang',	'kode_spk',	'no_urut', 'nama_panel', 'ukuran',	'created_at',	'created_by',	'updated_at',	'updated_by')
      ->where('id', $id)
      ->first()
      ->toArray();

    $ok = SpkFoto::where('id', $id)->delete();
    // if($ok) {
    //   ## Hapus File 
    //   $dest = public_path('assets/img/cabang');
    //   $photo = $data['logo_cabang'];
    //   $photoPath = $dest.DIRECTORY_SEPARATOR.$photo;
    //   if (is_file($photoPath)) {
    //     @unlink($photoPath);
    //   }
    // }

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Foto Pekerjaan' : 'Gagal Hapus Foto Pekerjaan';
    LogActivity::saveLogActivity($desc, $data);
  }

  // Merender BLOB menjadi gambar utuh
  public function renderImage($id)
  {
      $photo = SpkFoto::select('photo_panel')->where('id', $id)->firstOrFail();

      // Mengembalikan response berupa file gambar, bukan HTML
      return response($photo->photo_panel)
              ->header('Content-Type', 'image/jpeg') // Sesuaikan ekstensi jika perlu
              ->header('Cache-Control', 'max-age=86400'); // Cache 1 hari
  }

  public function getFotoPekerjaan(Request $request): JsonResponse
  {
    $kdCabang = $request->query('kode_cabang');
    $kdSPK = $request->query('kode_spk');

    if (!$kdCabang && !$kdSPK) {
      return response()->json([]);
    }

    // Ambil data foto
    $photos = SpkFoto::where('kode_cabang', $kdCabang)->where('kode_spk', $kdSPK)->orderBy('no_urut', 'asc')->get();
    
    // Mapping data foto agar format gambar menjadi Base64 string
    $data = $photos->map(function($item) {
        return [
            'id' => $item->id,
            'no_urut' => $item->no_urut,
            'nama_panel' => $item->nama_panel,
            // Lakukan Encode base64 di PHP, bukan di JS
            'photo_panel_base64' => $item->photo_panel ? base64_encode($item->photo_panel) : null
        ];
    });

    // Gabungkan ke respon JSON
    // $data->photos = $photosArray;

    return response()->json($data);
  }

}