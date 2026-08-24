<?php

namespace App\Http\Controllers\customer_service;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Pemilik;
use App\Models\Parameter;
use App\Models\LogActivity;
// use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class PemilikController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Pemilik(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Pemilik';

    $user_cabang = session('kd_cabang');
    $jenis_pemilik = Parameter::query()->where('nama_tabel', 'JENIS_PEMILIK')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.customer-service.pemilik', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'jenis_pemilik' => $jenis_pemilik,
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
      2 => 'k.nama_pemilik',
      3 => 'b.keterangan',
      4 => 'k.alamat1',
      5 => 'k.kode_pos',
      6 => 'k.telepon',
      7 => 'k.handphone',
      8 => 'k.npwp',
      9 => 'k.no_identitas',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'k.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('m_pemilik_hdr as k')
        ->leftJoin('parameter as b', function ($join) {
          $join->on('b.kode', '=', 'k.kode_jenis_pemilik')
              ->where('b.nama_tabel', '=', 'JENIS_PEMILIK'); // syarat di JOIN
        })
        ->where('k.kode_cabang', $user_cabang);

    // Total baris tanpa filter
    $totalData = (clone $base)->count('k.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('k.nama_pemilik', 'like', "%{$search}%")
    //           ->orWhere('k.alamat1', 'like', "%{$search}%")
    //           ->orWhere('k.telepon', 'like', "%{$search}%")
    //           ->orWhere('k.handphone', 'like', "%{$search}%")
    //           ->orWhere('b.keterangan', 'like', "%{$search}%");
    //     });
    // }
    if ($request->filled('nama_pemilik')) {
      $query->where('k.nama_pemilik', 'like', '%' . $request->nama_pemilik . '%');
    }
    if ($request->filled('jenis_pemilik')) {
      if ($request->jenis_pemilik <> 'all') {
        $query->where('k.kode_jenis_pemilik', 'like', '%' . $request->jenis_pemilik . '%');
      }
    }
    if ($request->filled('alamat')) {
      $query->where('k.alamat1', 'like', '%' . $request->alamat . '%');
    }
    if ($request->filled('kodepos')) {
      $query->where('k.kode_pos', 'like', '%' . $request->kodepos . '%');
    }
    if ($request->filled('handphone')) {
      $query->where('k.handphone', 'like', '%' . $request->handphone . '%');
    }
    if ($request->filled('telepon')) {
      $query->where('k.telepon', 'like', '%' . $request->telepon . '%');
    }
    if ($request->filled('ktp')) {
      $query->where('k.no_identitas', 'like', '%' . $request->ktp . '%');
    }
    if ($request->filled('npwp')) {
      $query->where('k.npwp', 'like', '%' . $request->npwp . '%');
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('k.id');

    // Ambil data halaman saat ini
    $datas = $query
      ->select([
          'k.id',
          'k.nama_pemilik',
          'k.kode_jenis_pemilik',
          'k.alamat1',
          'k.kode_pos',
          'k.telepon',
          'k.handphone',
          'k.npwp',
          'k.no_identitas',
          'k.file_ktp',
          'k.file_npwp',
          'b.keterangan as jenis_pemilik',
      ])
      ->orderBy($order, $dir)
      ->offset($start)
      ->limit($limit)
      ->get();

    // Susun payload DataTables
    $dest = public_path('assets/img/pemilik');
    $data = [];
    $fake = $start;
    foreach ($datas as $row) {
      
      $ktpPath = $dest.DIRECTORY_SEPARATOR.$row->file_ktp;
      $file_ktp = (is_file($ktpPath)) ? "1" : "0";
      $npwpPath = $dest.DIRECTORY_SEPARATOR.$row->file_npwp;
      $file_npwp = (is_file($npwpPath)) ? "1" : "0";

      $data[] = [
          'id' => $row->id,
          'fake_id' => ++$fake,
          'nama_pemilik' => $row->nama_pemilik,
          'kode_jenis_pemilik' => $row->kode_jenis_pemilik,
          'alamat1' => $row->alamat1,
          'kode_pos' => $row->kode_pos,
          'telepon' => $row->telepon,
          'handphone' => $row->handphone,
          'npwp' => $row->npwp,
          'no_identitas' => $row->no_identitas,
          'file_ktp' => $file_ktp,
          'file_npwp' => $file_npwp,
          'jenis_pemilik' => $row->jenis_pemilik,
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
    $rules = [
      'nama_pemilik' => [
          'required',
          'string',
          'max:50',
          // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
          Rule::unique('m_pemilik_hdr', 'nama_pemilik')
              ->where(function ($query) use ($request) {
                return $query
                  ->where('kode_cabang', $request->kode_cabang)   // Cek Kolom 2
                  ->where('no_identitas', $request->no_identitas); // Cek Kolom 3
              })
              ->ignore($request->id), // Penting: Abaikan ID sendiri saat update
      ],
      'kode_jenis_pemilik' => 'required',
      'no_identitas' => ['required', 'string', 'max:16'],
      'alamat1' => 'required',
      'handphone' => ['required', 'string', 'max:15'],
      'file_ktp' => ['nullable', 'mimes:jpg,jpeg,png,pdf', 'max:350'], // 350 KB
      'file_npwp' => ['nullable', 'mimes:jpg,jpeg,png,pdf', 'max:350'], // 350 KB
    ];

    $messages = [
      'nama_pemilik.required' => 'Nama Pemilik Wajib diisi',
      'nama_pemilik.unique' => 'Nama Pemilik sudah digunakan',
      'kode_jenis_pemilik.required' => 'Jenis Pemilik Wajib diisi',
      'no_identitas.required'  => 'Nomor Identitas Wajib diisi',
      'alamat1.required'  => 'Alamat Wajib diisi',
      'handphone.required'  => 'Telepon Selular Wajib diisi',
      'file_ktp.mimes' => 'Format file harus pdf, jpg, jpeg, atau png.',
      'file_ktp.max' => 'Ukuran file maksimal 350 KB.',
      'file_npwp.mimes' => 'Format file harus pdf, jpg, jpeg, atau png.',
      'file_npwp.max' => 'Ukuran fotfileo maksimal 350 KB.',
    ];

    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
      return response()->json([
        'status' => false,
        'message' => "Gagal menyimpan data.",
        'errors' => $validator->errors()
      ]);
    }

    $lastNum = Pemilik::query()->where('kode_cabang', $request->kode_cabang)->max(DB::raw('CAST(kode_pemilik AS UNSIGNED)')) ?? 0;
    $kode = sprintf('%05d', $lastNum + 1);

    $data = [
      'kode_cabang' => $request->kode_cabang,
      'kode_pemilik' => $kode,
      'nama_pemilik' => $request->nama_pemilik,
      'kode_jenis_pemilik' => $request->kode_jenis_pemilik,
      'no_identitas' => $request->no_identitas,
      'alamat1' => $request->alamat1,
      'kode_pos' => $request->kode_pos,
      'telepon' => $request->telepon,
      'fax' => $request->fax,
      'handphone' => $request->handphone,
      'email' => $request->email,
      'npwp' => $request->npwp,
      'created_by' => Auth::user()->username,
    ];

    // handle upload foto (opsional)
    if ($request->hasFile('file_ktp')) {
        $file = $request->file('file_ktp');

        // Pastikan folder ada
        $dest = public_path('assets/img/pemilik');
        if (!is_dir($dest)) {
            @mkdir($dest, 0775, true);
        }

        // Nama file unik
        $filename = Str::slug('ktp-'.$data['no_identitas']).'-'.time().'.'.$file->getClientOriginalExtension();

        // Pindahkan file
        $file->move($dest, $filename);

        // Simpan hanya nama file (bukan full path) agar mudah dirender di front-end
        $data['file_ktp'] = $filename;
    }

    if ($request->hasFile('file_npwp')) {
      $file = $request->file('file_npwp');

      // Pastikan folder ada
      $dest = public_path('assets/img/pemilik');
      if (!is_dir($dest)) {
        @mkdir($dest, 0775, true);
      }

      // Nama file unik
      $filename = Str::slug('npwp-'.$data['npwp']).'-'.time().'.'.$file->getClientOriginalExtension();

      // Pindahkan file
      $file->move($dest, $filename);

      // Simpan hanya nama file (bukan full path) agar mudah dirender di front-end
      $data['file_npwp'] = $filename;
    }

    $ok = Pemilik::create($data);

    ## Log Activity
    $desc = $ok ? 'Berhasil tambah Data Pemilik.' : 'Gagal tambah Data Pemilik.';
    LogActivity::saveLogActivity($desc, $data);

    return response()->json([
      'status'  => (bool)$ok,
      'message' => $desc
    ]);
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
    $data = Pemilik::findOrFail($id);
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
    $result = Pemilik::findOrFail($id);

    $rules = [
      'nama_pemilik' => [
          'required',
          'string',
          'max:50',
          // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
          Rule::unique('m_pemilik_hdr', 'nama_pemilik')
              ->where(function ($query) use ($request) {
                return $query
                  ->where('kode_cabang', $request->kode_cabang)   // Cek Kolom 2
                  ->where('no_identitas', $request->no_identitas); // Cek Kolom 3
              })
              ->ignore($id), // Penting: Abaikan ID sendiri saat update
      ],
      'kode_jenis_pemilik' => 'required',
      'no_identitas' => ['required', 'string', 'max:16'],
      'alamat1' => 'required',
      'handphone' => ['required', 'string', 'max:15'],
      'file_ktp' => ['nullable', 'mimes:jpg,jpeg,png,pdf', 'max:350'], // 350 KB
      'file_npwp' => ['nullable', 'mimes:jpg,jpeg,png,pdf', 'max:350'], // 350 KB
    ];

    $messages = [
      'nama_pemilik.required' => 'Nama Pemilik Wajib diisi',
      'nama_pemilik.unique' => 'Nama Pemilik sudah digunakan',
      'kode_jenis_pemilik.required' => 'Jenis Pemilik Wajib diisi',
      'no_identitas.required'  => 'Nomor Identitas Wajib diisi',
      'alamat1.required'  => 'Alamat Wajib diisi',
      'handphone.required'  => 'Telepon Selular Wajib diisi',
      'file_ktp.mimes' => 'Format file harus pdf, jpg, jpeg, atau png.',
      'file_ktp.max' => 'Ukuran file maksimal 350 KB.',
      'file_npwp.mimes' => 'Format file harus pdf, jpg, jpeg, atau png.',
      'file_npwp.max' => 'Ukuran fotfileo maksimal 350 KB.',
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
      'kode_cabang' => $request->kode_cabang,
      'nama_pemilik' => $request->nama_pemilik,
      'kode_jenis_pemilik' => $request->kode_jenis_pemilik,
      'no_identitas' => $request->no_identitas,
      'alamat1' => $request->alamat1,
      'kode_pos' => $request->kode_pos,
      'telepon' => $request->telepon,
      'fax' => $request->fax,
      'handphone' => $request->handphone,
      'email' => $request->email,
      'npwp' => $request->npwp,
      'updated_by' => Auth::user()->username,
    ];

    if ($request->hasFile('file_ktp')) {
      $file = $request->file('file_ktp');

      $dest = public_path('assets/img/pemilik');
      if (!is_dir($dest)) {
          @mkdir($dest, 0775, true);
      }

      $filename = Str::slug('ktp-'.$data['no_identitas']).'-'.time().'.'.$file->getClientOriginalExtension();
      $file->move($dest, $filename);
      $data['file_ktp'] = $filename;

      // hapus foto lama jika ada dan berbeda
      $old = $request->input('old_file_ktp');
      if ($old && $old !== $filename) {
        $oldPath = $dest.DIRECTORY_SEPARATOR.$old;
        if (is_file($oldPath)) {
          @unlink($oldPath);
        }
      }
    }

    if ($request->hasFile('file_npwp')) {
      $file = $request->file('file_npwp');

      $dest = public_path('assets/img/pemilik');
      if (!is_dir($dest)) {
          @mkdir($dest, 0775, true);
      }

      $filename = Str::slug('npwp-'.$data['npwp']).'-'.time().'.'.$file->getClientOriginalExtension();
      $file->move($dest, $filename);
      $data['file_npwp'] = $filename;

      // hapus foto lama jika ada dan berbeda
      $old = $request->input('old_file_npwp');
      if ($old && $old !== $filename) {
        $oldPath = $dest.DIRECTORY_SEPARATOR.$old;
        if (is_file($oldPath)) {
          @unlink($oldPath);
        }
      }
    }

    $ok = $result->update($data);

    ## Log Activity
    $desc = $ok ? 'Berhasil ubah Data Pemilik.' : 'Gagal ubah Data Pemilik.';
    LogActivity::saveLogActivity($desc, $data);

    return response()->json([
      'status'  => (bool)$ok,
      'message' => $desc
    ]);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $result = Pemilik::findOrFail($id);
    if ($result) {
      $dest = public_path('assets/img/pemilik');
      $photo = $result->file_ktp;
      $photoPath = $dest.DIRECTORY_SEPARATOR.$photo;
      if (is_file($photoPath)) {
        @unlink($photoPath);
      }

      $dest = public_path('assets/img/pemilik');
      $photo = $result->file_npwp;
      $photoPath = $dest.DIRECTORY_SEPARATOR.$photo;
      if (is_file($photoPath)) {
        @unlink($photoPath);
      }
    }

    $data = Pemilik::query()->where('id', $id)->first()->toArray();

    $ok = Pemilik::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Pemilik.' : 'Gagal Hapus Data Pemilik.';
    LogActivity::saveLogActivity($desc, $data);
  }

  public function downloadFile(Request $request)
  {
    $id = $request->id;
    $tipe = $request->tipe;
    $result = Pemilik::find($id);
    if ($result) {
      $dest = public_path('assets/img/pemilik');
      if($tipe == "npwp") {
        $photo = $result->file_npwp;
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
        $desc = 'Download File Pemilik';
        LogActivity::saveLogActivity($desc, $data);

        return response()->download($photoPath, $photo);
      }
    } else {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }
  }
}