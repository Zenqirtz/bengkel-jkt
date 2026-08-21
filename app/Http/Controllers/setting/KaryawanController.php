<?php

namespace App\Http\Controllers\setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Karyawan;
use App\Models\PosisiPekerjaan;
use App\Models\Parameter;
use App\Models\LogActivity;
use Carbon\Carbon;
use Intervention\Image\Facades\Image; // PENTING: Import Facade Intervention Image

use App\Helpers\Helpers as Helper;

class KaryawanController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Karyawan(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Karyawan';

    $user_cabang = session('kd_cabang');
    $posisi = PosisiPekerjaan::query()->orderBy('seq_no', 'asc')->get();
    $jabatan = Parameter::query()->where('nama_tabel', 'JABATAN')->orderBy('no_urut', 'asc')->get();
    $status_aktif = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();
    $status_pajak = Parameter::query()->where('nama_tabel', 'STATUS_PAJAK')->orderBy('no_urut', 'asc')->get();
    $status_karyawan = Parameter::query()->where('nama_tabel', 'STATUS_KARYAWAN')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);
    
    return view('content.setting.karyawan', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'data_posisi' => $posisi,
      'jabatan' => $jabatan,
      'status_aktif' => $status_aktif,
      'status_pajak' => $status_pajak,
      'status_karyawan' => $status_karyawan,
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
      2 => 'k.kode_karyawan',
      3 => 'k.nama',
      4 => 'e.keterangan', // Nama Jabatan
      5 => 'pk.posisi_pekerjaan',
      6 => 'k.nik',
      7 => 'k.no_hp',
      8 => 'k.alamat',
      9 => 'c.keterangan', //nm_status_pajak
      10 => 'd.keterangan', //nm_status_karyawan
      11 => 'k.tgl_masuk',
      12 => 'k.tgl_keluar',
      13 => 'k.file_photo',
      14 => 'k.file_ktp',
      15 => 'k.file_ttd',
      16 => 'b.keterangan' //nm_status_aktif
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'k.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('m_karyawan as k')
        ->leftJoin('parameter as b', function ($join) {
          $join->on('b.kode', '=', 'k.status_aktif')
              ->where('b.nama_tabel', '=', 'STATUS'); // syarat di JOIN
        })
        ->leftJoin('parameter as c', function ($join) {
          $join->on('c.kode', '=', 'k.status_pajak')
              ->where('c.nama_tabel', '=', 'STATUS_PAJAK'); // syarat di JOIN
        })
        ->leftJoin('parameter as d', function ($join) {
          $join->on('d.kode', '=', 'k.status_karyawan')
              ->where('d.nama_tabel', '=', 'STATUS_KARYAWAN'); // syarat di JOIN
        })
        ->leftJoin('parameter as e', function ($join) {
          $join->on('e.kode', '=', 'k.kode_jabatan')
              ->where('e.nama_tabel', '=', 'JABATAN'); // syarat di JOIN
        })
        ->leftJoin('m_posisi_pekerjaan as pk', 'pk.kode_posisi', '=', 'k.kode_posisi')
        ->where('k.kode_cabang', $user_cabang);

    // Total baris tanpa filter
    $totalData = (clone $base)->count('k.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('k.id', 'like', "%{$search}%")
    //           ->orWhere('k.nik', 'like', "%{$search}%")
    //           ->orWhere('k.nama', 'like', "%{$search}%")
    //           ->orWhere('k.no_hp', 'like', "%{$search}%")
    //           ->orWhere('k.alamat', 'like', "%{$search}%"); // cari di nama posisi juga
    //     });
    // }
    if ($request->filled('nik')) {
      $query->where('k.nik', 'like', '%' . $request->nik . '%');
    }
    if ($request->filled('nama')) {
      $query->where('k.nama', 'like', '%' . $request->nama . '%');
    }
    if ($request->filled('telepon')) {
      $query->where('k.no_hp', 'like', '%' . $request->telepon . '%');
    }
    if ($request->filled('alamat')) {
      $query->where('k.alamat', 'like', '%' . $request->alamat . '%');
    }
    if ($request->filled('posisi')) {
      if ($request->posisi <> 'all') {
        $query->where('k.kode_posisi', 'like', '%' . $request->posisi . '%');
      }
    }
    if ($request->filled('jabatan')) {
      if ($request->jabatan <> 'all') {
        $query->where('k.kode_jabatan', 'like', '%' . $request->jabatan . '%');
      }
    }
    if ($request->filled('pajak')) {
      if ($request->pajak <> 'all') {
        $query->where('k.status_pajak', 'like', '%' . $request->pajak . '%');
      }
    }
    if ($request->filled('status_karyawan')) {
      if ($request->status_karyawan <> 'all') {
        $query->where('k.status_karyawan', 'like', '%' . $request->status_karyawan . '%');
      }
    }
    if ($request->filled('status_aktif')) {
      if ($request->status_aktif <> 'all') {
        $query->where('k.status_aktif', 'like', '%' . $request->status_aktif . '%');
      }
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('k.id');

    // Ambil data halaman saat ini
    if ($limit == -1) {
      $datas = $query
        ->select([
            'k.id',
            'k.kode_karyawan',
            'k.nama',
            'k.kode_posisi',
            'k.kode_jabatan',
            'k.nik',
            'k.alamat',
            'k.no_hp',
            'k.tgl_masuk',
            'k.tgl_keluar',
            'k.status_pajak',
            'k.status_karyawan',
            'k.status_aktif',
            'k.file_photo',
            'k.file_ktp',
            'pk.posisi_pekerjaan',
            'b.keterangan as nm_status_aktif',
            'c.keterangan as nm_status_pajak',
            'd.keterangan as nm_status_karyawan',
            'e.keterangan as nm_jabatan',
        ])
        ->orderBy($order, $dir)
        ->get();
    } else {
      $datas = $query
        ->select([
            'k.id',
            'k.kode_karyawan',
            'k.nama',
            'k.kode_posisi',
            'k.kode_jabatan',
            'k.nik',
            'k.alamat',
            'k.no_hp',
            'k.tgl_masuk',
            'k.tgl_keluar',
            'k.status_pajak',
            'k.status_karyawan',
            'k.status_aktif',
            'k.file_photo',
            'k.file_ktp',
            'k.file_ttd',
            'pk.posisi_pekerjaan',
            'b.keterangan as nm_status_aktif',
            'c.keterangan as nm_status_pajak',
            'd.keterangan as nm_status_karyawan',
            'e.keterangan as nm_jabatan',
        ])
        ->orderBy($order, $dir)
        ->offset($start)
        ->limit($limit)
        ->get();
    }

    // Susun payload DataTables
    $dest = public_path('assets/img/karyawan');
    $data = [];
    $fake = $start;
    foreach ($datas as $row) {

      // $photoPath = $dest.DIRECTORY_SEPARATOR.$row->file_photo;
      // $file_photo = (is_file($photoPath)) ? "1" : "0";
      // $ktpPath = $dest.DIRECTORY_SEPARATOR.$row->file_ktp;
      // $file_ktp = (is_file($ktpPath)) ? "1" : "0";

      $file_photo = (blank($row->file_photo)) ? "0" : "1";
      $file_ktp = (blank($row->file_ktp)) ? "0" : "1";
      $file_ttd = (blank($row->file_ttd)) ? "0" : "1";

      $data[] = [
        'id'            => $row->id,
        'fake_id'       => ++$fake,
        'kode_karyawan' => $row->kode_karyawan,
        'nama'          => $row->nama,
        'kode_posisi'   => $row->kode_posisi,
        'kode_jabatan'  => $row->kode_jabatan,
        'nik'           => $row->nik,
        'alamat'        => $row->alamat,
        'no_hp'         => $row->no_hp,
        'status_pajak'  => $row->status_pajak,
        'status_aktif'  => $row->status_aktif,
        'file_photo'    => $file_photo,
        'file_ktp'      => $file_ktp,
        'file_ttd'      => $file_ttd,
        'tgl_masuk'     => blank($row->tgl_masuk) ? null : date("d/m/Y", strtotime($row->tgl_masuk)),
        'tgl_keluar'    => blank($row->tgl_keluar) ? null : date("d/m/Y", strtotime($row->tgl_keluar)),
        'status_karyawan'   => $row->status_karyawan,
        'posisi_pekerjaan'  => $row->posisi_pekerjaan, // tampilkan nama posisi join
        'nm_status_aktif'   => $row->nm_status_aktif, // tampilkan nama posisi join
        'nm_status_pajak'   => $row->nm_status_pajak, // tampilkan nama posisi join
        'nm_status_karyawan'=> $row->nm_status_karyawan, // tampilkan nama posisi join
        'nm_jabatan'        => $row->nm_jabatan, // tampilkan nama posisi join
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
    try {
      $rules = [
        'nik'            => 'required|string|max:20|unique:m_karyawan,nik',
        'nama'           => 'required|string|max:100',
        'alamat'         => 'required|string',
        'no_hp'          => 'required|string|max:20',
        'status_pajak'   => 'required|string',
        'status_karyawan'=> 'required|string',
        'status_aktif'   => 'required|string',
        'tgl_masuk'      => 'required',
        'tgl_keluar'     => 'nullable',
        'kode_posisi'    => 'nullable',
        'no_absen'       => 'nullable',
        // 'photo'          => 'nullable|image|mimes:jpg,jpeg,png|max:350', // 1MB
        'photo'          => 'nullable|mimes:jpg,jpeg,png|max:10000',
        'photo_ktp'      => 'nullable|mimes:jpg,jpeg,png|max:10000',
        'photo_ttd'      => 'nullable|mimes:jpg,jpeg,png|max:10000',
      ];
  
      $messages = [
        'nik.required' => 'NIK Wajib diisi',
        'nama.required' => 'Nama Karyawan Wajib diisi',
        'alamat.required'  => 'Alamat Wajib diisi',
        'no_hp.required'  => 'Telepon Selular Wajib diisi',
        'status_pajak.required'  => 'Status Pajak Wajib diisi',
        'status_karyawan.required'  => 'Status Karyawan Wajib diisi',
        'status_aktif.required'  => 'Status Aktif Wajib diisi',
        'tgl_masuk.required'  => 'Tanggal Masuk Wajib diisi',
        'nik.unique'  => 'NIK sudah digunakan',
        'photo.image'    => 'File harus berupa gambar.',
        'photo.mimes'    => 'Format foto harus jpg, jpeg, atau png.',
        'photo.max'      => 'Ukuran foto maksimal 10 MB.',
        'photo_ktp.image'      => 'File harus berupa gambar.',
        'photo_ktp.mimes'      => 'Format foto harus pdf, jpg, jpeg, atau png.',
        'photo_ktp.max'        => 'Ukuran foto maksimal 10 MB.',
        'photo_ttd.image'      => 'File harus berupa gambar.',
        'photo_ttd.mimes'      => 'Format foto harus jpg, jpeg, atau png.',
        'photo_ttd.max'        => 'Ukuran foto maksimal 10 MB.',
      ];
  
      $validator = Validator::make($request->all(), $rules, $messages);
  
      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ]);
      }

      $request->kode_jabatan = '00002';
      $kdjab = (int) $request->kode_jabatan;
      $kdjab = ($kdjab == 2) ? 2 : 1;

      $lastNum = Karyawan::query()->where('kode_cabang', $request->kode_cabang)->max(DB::raw('CAST(kode_karyawan AS UNSIGNED)')) ?? 0;
      // $kdKaryawan = sprintf('%05d', $lastNum + 1);
      $kdKaryawan = sprintf('%02d.%01d.%03d', $request->kode_cabang, $kdjab, $lastNum + 1);
  
      $data = [
        'kode_karyawan'   => $kdKaryawan,
        'kode_cabang'     => $request->kode_cabang,
        'nik'             => $request->nik,
        'nama'            => $request->nama,
        'alamat'          => $request->alamat,
        'no_hp'           => $request->no_hp,
        'status_pajak'    => $request->status_pajak,
        'status_karyawan' => $request->status_karyawan,
        'status_aktif'    => $request->status_aktif,
        'tgl_masuk'       => blank($request->tgl_masuk) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_masuk), 'Asia/Jakarta')->format('Y-m-d'),
        'tgl_keluar'      => blank($request->tgl_keluar) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_keluar), 'Asia/Jakarta')->format('Y-m-d'),
        'kode_posisi'     => $request->kode_posisi,
        'kode_jabatan'     => $request->kode_jabatan,
        'no_absen'        => $request->no_absen,
        'created_by'      => Auth::user()->username,
      ];
  
      // handle upload foto (opsional)
      if ($request->hasFile('photo')) {
        $file = $request->file('photo');

        // Pastikan folder ada
        // $dest = public_path('assets/img/karyawan');
        // if (!is_dir($dest)) {
        //     @mkdir($dest, 0775, true);
        // }

        // // Nama file unik
        // $filename = Str::slug($data['nama']).'-'.time().'.'.$file->getClientOriginalExtension();

        // // Pindahkan file
        // $file->move($dest, $filename);

        // // Simpan hanya nama file (bukan full path) agar mudah dirender di front-end
        // $data['file_photo'] = $filename;

        // Ambil nama file
        $filename = $file->getClientOriginalName();

        // getRealPath() mengambil lokasi file sementara (temp file) sebelum diupload
        $photoBinary = file_get_contents($file->getRealPath());

        // Ambil ukuran file dalam byte
        $fileSize = $file->getSize();

        ## PROSES KOMPRESI MENGGUNAKAN INTERVENTION IMAGE
        if($fileSize > 70000) {
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

        $data['file_photo'] = $filename;
        $data['photo'] = $photoBinary;
      }
  
      if ($request->hasFile('photo_ktp')) {
        $file = $request->file('photo_ktp');
  
        // Pastikan folder ada
        // $dest = public_path('assets/img/karyawan');
        // if (!is_dir($dest)) {
        //     @mkdir($dest, 0775, true);
        // }
  
        // // Nama file unik
        // $filename = Str::slug($data['nik']).'-'.time().'.'.$file->getClientOriginalExtension();
  
        // // Pindahkan file
        // $file->move($dest, $filename);
  
        // // Simpan hanya nama file (bukan full path) agar mudah dirender di front-end
        // $data['file_ktp'] = $filename;

        // Ambil nama file
        $filename = $file->getClientOriginalName();

        // getRealPath() mengambil lokasi file sementara (temp file) sebelum diupload
        $photoBinary = file_get_contents($file->getRealPath());

        // Ambil ukuran file dalam byte
        $fileSize = $file->getSize();

        ## PROSES KOMPRESI MENGGUNAKAN INTERVENTION IMAGE
        if($fileSize > 70000) {
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

        $data['file_ktp'] = $filename;
        $data['photo_ktp'] = $photoBinary;
      }

      if ($request->hasFile('photo_ttd')) {
        $file = $request->file('photo_ttd');

        // Ambil nama file
        $filename = $file->getClientOriginalName();

        // getRealPath() mengambil lokasi file sementara (temp file) sebelum diupload
        $photoBinary = file_get_contents($file->getRealPath());

        // Ambil ukuran file dalam byte
        $fileSize = $file->getSize();

        ## PROSES KOMPRESI MENGGUNAKAN INTERVENTION IMAGE
        if($fileSize > 70000) {
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

        $data['file_ttd'] = $filename;
        $data['photo_ttd'] = $photoBinary;
      }
  
      $ok = Karyawan::create($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil Tambah Karyawan' : 'Gagal Tambah Karyawan';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 500);
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
    $data = Karyawan::findOrFail($id);
    $data->tgl_masuk = blank($data->tgl_masuk) ? null : date("d/m/Y", strtotime($data->tgl_masuk));
    $data->tgl_keluar = blank($data->tgl_keluar) ? null : date("d/m/Y", strtotime($data->tgl_keluar));
    return response()->json($data);
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
    try {
      $result = Karyawan::findOrFail($id);
      // $kode_cabang = $result->kode_cabang;
      // $kode_karyawan = $result->kode_karyawan;

      $rules = [
        'nik'            => 'required|string|max:20|unique:m_karyawan,nik,'.$result->id,
        'nama'           => 'required|string|max:100',
        'alamat'         => 'required|string',
        'no_hp'          => 'required|string|max:20',
        'status_pajak'   => 'required|string',
        'status_karyawan'=> 'required|string',
        'status_aktif'   => 'required|string',
        'tgl_masuk'      => 'required',
        'tgl_keluar'     => 'nullable',
        'kode_posisi'    => 'nullable',
        'no_absen'       => 'nullable',
        // 'photo'          => 'nullable|image|mimes:jpg,jpeg,png|max:350', // 1MB
        'photo'          => 'nullable|mimes:jpg,jpeg,png|max:10000', // 1MB
        'photo_ktp'      => 'nullable|mimes:jpg,jpeg,png|max:10000',
        'photo_ttd'      => 'nullable|mimes:jpg,jpeg,png|max:10000',
      ];

      $messages = [
        'nik.required' => 'NIK Wajib diisi',
        'nama.required' => 'Nama Karyawan Wajib diisi',
        'alamat.required'  => 'Alamat Wajib diisi',
        'no_hp.required'  => 'Telepon Selular Wajib diisi',
        'status_pajak.required'  => 'Status Pajak Wajib diisi',
        'status_karyawan.required'  => 'Status Karyawan Wajib diisi',
        'status_aktif.required'  => 'Status Aktif Wajib diisi',
        'tgl_masuk.required'  => 'Tanggal Masuk Wajib diisi',
        'nik.unique'  => 'NIK sudah digunakan',
        'photo.image'    => 'File harus berupa gambar.',
        'photo.mimes'    => 'Format foto harus jpg, jpeg, atau png.',
        'photo.max'      => 'Ukuran foto maksimal 10 MB.',
        'photo_ktp.image'      => 'File harus berupa gambar.',
        'photo_ktp.mimes'      => 'Format foto harus jpg, jpeg, atau png.',
        'photo_ktp.max'        => 'Ukuran foto maksimal 10 MB.',
        'photo_ttd.image'      => 'File harus berupa gambar.',
        'photo_ttd.mimes'      => 'Format foto harus jpg, jpeg, atau png.',
        'photo_ttd.max'        => 'Ukuran foto maksimal 10 MB.',
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
        // 'kode_karyawan'   => $kdKaryawan,
        // 'kode_cabang'     => $request->kode_cabang,
        'nik'             => $request->nik,
        'nama'            => $request->nama,
        'alamat'          => $request->alamat,
        'no_hp'           => $request->no_hp,
        'status_pajak'    => $request->status_pajak,
        'status_karyawan' => $request->status_karyawan,
        'status_aktif'    => $request->status_aktif,
        'tgl_masuk'       => blank($request->tgl_masuk) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_masuk), 'Asia/Jakarta')->format('Y-m-d'),
        'tgl_keluar'      => blank($request->tgl_keluar) ? null : Carbon::createFromFormat('d/m/Y', trim($request->tgl_keluar), 'Asia/Jakarta')->format('Y-m-d'),
        'kode_posisi'     => $request->kode_posisi,
        'kode_jabatan'     => $request->kode_jabatan,
        'no_absen'        => $request->no_absen,
        'updated_by'      => Auth::user()->username,
      ];

      // handle upload foto (opsional)
      if ($request->hasFile('photo')) {
        $file = $request->file('photo');

        // Pastikan folder ada
        // $dest = public_path('assets/img/karyawan');
        // if (!is_dir($dest)) {
        //     @mkdir($dest, 0775, true);
        // }

        // $filename = Str::slug($data['nama']).'-'.time().'.'.$file->getClientOriginalExtension();
        // $file->move($dest, $filename);
        // $data['file_photo'] = $filename;

        // // hapus foto lama jika ada dan berbeda
        // $old = $request->input('old_photo');
        // if ($old && $old !== $filename) {
        //   $oldPath = $dest.DIRECTORY_SEPARATOR.$old;
        //   if (is_file($oldPath)) {
        //     @unlink($oldPath);
        //   }
        // }

        // Ambil nama file
        $filename = $file->getClientOriginalName();

        // getRealPath() mengambil lokasi file sementara (temp file) sebelum diupload
        $photoBinary = file_get_contents($file->getRealPath());

        // Ambil ukuran file dalam byte
        $fileSize = $file->getSize();

        ## PROSES KOMPRESI MENGGUNAKAN INTERVENTION IMAGE
        if($fileSize > 70000) {
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

        $data['file_photo'] = $filename;
        $data['photo'] = $photoBinary;
      }
  
      if ($request->hasFile('photo_ktp')) {
        $file = $request->file('photo_ktp');
  
        // Pastikan folder ada
        // $dest = public_path('assets/img/karyawan');
        // if (!is_dir($dest)) {
        //     @mkdir($dest, 0775, true);
        // }

        // $filename = Str::slug($data['nik']).'-'.time().'.'.$file->getClientOriginalExtension();
        // $file->move($dest, $filename);
        // $data['file_ktp'] = $filename;

        // // hapus foto lama jika ada dan berbeda
        // $old = $request->input('old_photo_ktp');
        // if ($old && $old !== $filename) {
        //   $oldPath = $dest.DIRECTORY_SEPARATOR.$old;
        //   if (is_file($oldPath)) {
        //     @unlink($oldPath);
        //   }
        // }

        // Ambil nama file
        $filename = $file->getClientOriginalName();

        // getRealPath() mengambil lokasi file sementara (temp file) sebelum diupload
        $photoBinary = file_get_contents($file->getRealPath());

        // Ambil ukuran file dalam byte
        $fileSize = $file->getSize();

        ## PROSES KOMPRESI MENGGUNAKAN INTERVENTION IMAGE
        if($fileSize > 70000) {
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

        $data['file_ktp'] = $filename;
        $data['photo_ktp'] = $photoBinary;
      }

      if ($request->hasFile('photo_ttd')) {
        $file = $request->file('photo_ttd');

        // Ambil nama file
        $filename = $file->getClientOriginalName();

        // getRealPath() mengambil lokasi file sementara (temp file) sebelum diupload
        $photoBinary = file_get_contents($file->getRealPath());

        // Ambil ukuran file dalam byte
        $fileSize = $file->getSize();

        ## PROSES KOMPRESI MENGGUNAKAN INTERVENTION IMAGE
        if($fileSize > 70000) {
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

        $data['file_ttd'] = $filename;
        $data['photo_ttd'] = $photoBinary;
      }

      $ok = $result->update($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil Ubah Karyawan' : 'Gagal Ubah Karyawan';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    // $result = Karyawan::findOrFail($id);
    // if ($result) {
    //   $dest = public_path('assets/img/ktp');
    //   $photo = $result->file_ktp;
    //   $photoPath = $dest.DIRECTORY_SEPARATOR.$photo;
    //   if (is_file($photoPath)) {
    //     @unlink($photoPath);
    //   }

    //   $dest = public_path('assets/img/karyawan');
    //   $photo = $result->file_photo;
    //   $photoPath = $dest.DIRECTORY_SEPARATOR.$photo;
    //   if (is_file($photoPath)) {
    //     @unlink($photoPath);
    //   }
    // }
    $data = Karyawan::query()->where('id', $id)->first()->toArray();

    $ok = Karyawan::where('id', $id)->delete();
    if($ok) {
      ## Hapus File 
      // $dest = public_path('assets/img/karyawan');
      // $photo = $data['file_photo'];
      // $photoPath = $dest.DIRECTORY_SEPARATOR.$photo;
      // if (is_file($photoPath)) {
      //   @unlink($photoPath);
      // }

      // $photo = $data['file_ktp'];
      // $photoPath = $dest.DIRECTORY_SEPARATOR.$photo;
      // if (is_file($photoPath)) {
      //   @unlink($photoPath);
      // }
    }

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Karyawan' : 'Gagal Hapus Karyawan';
    LogActivity::saveLogActivity($desc, $data);
  }

  public function downloadFile(Request $request)
  {
    $id = $request->id;
    $tipe = $request->tipe;
    $result = Karyawan::find($id);
    if ($result) {
      $dest = public_path('assets/img/karyawan');
      if($tipe == "photo") {
        $photo = $result->file_photo;
      } elseif($tipe == "ktp") {
        $photo = $result->file_ktp;
      } else {
        $photo = "";
      }
      $photoPath = $dest.DIRECTORY_SEPARATOR.$photo;
      if (is_file($photoPath)) {
        ## Log Activity
        $data['id'] = $id;
        $data['tipe'] = $tipe;
        $data['file'] = $photo;
        $desc = 'Download File Karyawan';
        LogActivity::saveLogActivity($desc, $data);

        return response()->download($photoPath, $photo);
      }
    } else {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }
  }

  public function getFotoKaryawan(Request $request): JsonResponse
  {
    $id = $request->query('id');
    $tipe = $request->query('tipe');

    if (!$id && !$tipe) {
      return response()->json([]);
    }

    // Ambil data foto
    $data = Karyawan::select('id', 'kode_karyawan', 'nama', 'file_photo', 'file_ktp', 'file_ttd', 'photo', 'photo_ktp', 'photo_ttd')
    ->where('id', $id)
    ->first();

    if($data) {
      $datas['id'] = $data->id;
      $datas['kode_karyawan'] = $data->kode_karyawan;
      $datas['nama'] = $data->nama;

      if($tipe == "photo") {
        $nama_panel         = $data->file_photo;
        $photo_panel_base64 = $data->photo ? base64_encode($data->photo) : null;
      } elseif($tipe == "ktp") {
        $nama_panel         = $data->file_ktp;
        $photo_panel_base64 = $data->photo_ktp ? base64_encode($data->photo_ktp) : null;
      } elseif($tipe == "ttd") {
        $nama_panel         = $data->file_ttd;
        $photo_panel_base64 = $data->photo_ttd ? base64_encode($data->photo_ttd) : null;
      } else {
        $nama_panel = null;
        $photo_panel_base64 = null;
      }

      $datas['nama_panel'] = $nama_panel;
      $datas['photo_panel_base64'] = $photo_panel_base64;

      return response()->json($datas);
    } else {
      return response()->json([]);
    }
  }

  public function hapusFotoKaryawan(Request $request): JsonResponse
  {
    $id = $request->query('id');
    $tipe = $request->query('tipe');

    try {
      $result = Karyawan::findOrFail($id);

      if($tipe == "photo") {
        $data = [
          'file_photo'  => null,
          'photo' => null,
        ];
      } elseif($tipe == "ktp") {
        $data = [
          'file_ktp'  => null,
          'photo_ktp' => null,
        ];
      } elseif($tipe == "ttd") {
        $data = [
          'file_ttd'  => null,
          'photo_ttd' => null,
        ];
      } else {
        return response()->json([
          'status'  => false,
          'message' => 'Gagal Hapus Foto: Tipe tidak sesuai'
        ]);
      }

      $ok = $result->update($data);

      $desc = $ok ? 'Berhasil Hapus Foto' : 'Gagal Hapus Foto';

      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 500);
    }
  }
}