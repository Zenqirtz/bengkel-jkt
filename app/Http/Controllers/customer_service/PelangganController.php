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
use App\Models\Pelanggan;
use App\Models\Parameter;
use App\Models\LogActivity;
// use Carbon\Carbon;

class PelangganController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Pelanggan(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Pelanggan';

    $user_cabang = session('kd_cabang');
    $status_aktif = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();
    $jenis_pelanggan = Parameter::query()->where('nama_tabel', 'JENIS_PELANGGAN')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.customer-service.pelanggan', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'status_aktif' => $status_aktif,
      'jenis_pelanggan' => $jenis_pelanggan,
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
      2 => 'k.nama_pelanggan',
      3 => 'c.keterangan',
      4 => 'd.keterangan',
      5 => 'k.telepon',
      6 => 'b.keterangan',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'k.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('m_pelanggan_hdr as k')
    ->leftJoin('parameter as b', function ($join) {
      $join->on('b.kode', '=', 'k.is_active')
          ->where('b.nama_tabel', '=', 'STATUS'); // syarat di JOIN
    })
    ->leftJoin('parameter as c', function ($join) {
      $join->on('c.kode', '=', 'k.kode_jenis_pelanggan')
          ->where('c.nama_tabel', '=', 'JENIS_PERANTARA'); // syarat di JOIN
    })
    ->where('k.kode_cabang', $user_cabang);

    // Total baris tanpa filter
    $totalData = (clone $base)->count('k.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('k.id', 'like', "%{$search}%")
    //           ->orWhere('k.nama_pelanggan', 'like', "%{$search}%")
    //           ->orWhere('k.telepon', 'like', "%{$search}%")
    //           ->orWhere('c.keterangan', 'like', "%{$search}%")
    //           ->orWhere('b.keterangan', 'like', "%{$search}%");
    //     });
    // }
    if ($request->filled('nama_pelanggan')) {
      $query->where('k.nama_pelanggan', 'like', '%' . $request->nama_pelanggan . '%');
    }
    if ($request->filled('telepon')) {
      $query->where('k.telepon', 'like', '%' . $request->telepon . '%');
    }
    if ($request->filled('npwp')) {
      $query->where('k.npwp', 'like', '%' . $request->npwp . '%');
    }
    if ($request->filled('alamat1')) {
      $query->where('k.alamat1', 'like', '%' . $request->alamat1 . '%');
    }
    if ($request->filled('kode_jenis_pelanggan')) {
      if ($request->kode_jenis_pelanggan <> 'all') {
        $query->where('k.kode_jenis_pelanggan', 'like', '%' . $request->kode_jenis_pelanggan . '%');
      }
    }
    if ($request->filled('is_active')) {
      if ($request->is_active <> 'all') {
        $query->where('k.is_active', 'like', '%' . $request->is_active . '%');
      }
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('k.id');

    // Ambil data halaman saat ini
    $datas = $query
      ->select([
          'k.id',
          'k.nama_pelanggan',
          'k.kode_jenis_pelanggan',
          'k.alamat1',
          'k.telepon',
          'k.is_active',
          'k.npwp',
          'k.file_npwp',
          'b.keterangan as status_aktif',
          'c.keterangan as jenis_pelanggan',
      ])
      ->orderBy($order, $dir)
      ->offset($start)
      ->limit($limit)
      ->get();

    // Susun payload DataTables
    $dest = public_path('assets/img/pelanggan');
    $data = [];
    $fake = $start;
    foreach ($datas as $row) {
      $npwpPath = $dest.DIRECTORY_SEPARATOR.$row->file_npwp;
      $file_npwp = (is_file($npwpPath)) ? "1" : "0";

      $data[] = [
          'id'              => $row->id,
          'fake_id'         => ++$fake,
          'nama_pelanggan'  => $row->nama_pelanggan,
          'jenis_pelanggan' => $row->jenis_pelanggan,
          'alamat1'         => $row->alamat1,
          'status_aktif'    => $row->status_aktif,
          'telepon'         => $row->telepon,
          'npwp'            => $row->npwp,
          'file_npwp'       => $file_npwp,
          'is_active'       => $row->is_active,
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
    $dataID = $request->id;

    try {
      if ($dataID) {
        $result = Pelanggan::findOrFail($dataID);
  
        $rules = [
          'nama_pelanggan' => [
              'required',
              'string',
              'max:50',
              // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
              Rule::unique('m_pelanggan_hdr', 'nama_pelanggan')
                  ->where(function ($query) use ($request) {
                    return $query
                      ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
                  })
                  ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
          ],
          'kode_jenis_pelanggan' => 'required',
          'is_active' => 'required',
          'npwp' => 'required',
          'telepon' => ['required', 'max:15'],
          'file_npwp' => ['nullable', 'mimes:jpg,jpeg,png,pdf', 'max:350'], // 350 KB
        ];
        
        $messages = [
          'nama_pelanggan.required' => 'Nama Pelanggan Wajib diisi',
          'nama_pelanggan.unique' => 'Nama Pelanggan sudah digunakan',
          'kode_jenis_pelanggan.required' => 'Jenis Pemilik Wajib diisi',
          'telepon.required'  => 'Telepon Selular Wajib diisi',
          'is_active.required'  => 'Status Aktif Wajib diisi',
          'npwp.required'  => 'Nomor NPWP Wajib diisi',
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
        
        // update the value
        $data = [
          'kode_cabang' => $request->kode_cabang,
          'nama_pelanggan' => $request->nama_pelanggan,
          'kode_jenis_pelanggan' => $request->kode_jenis_pelanggan,
          'telepon' => $request->telepon,
          'alamat1' => $request->alamat1,
          'npwp' => $request->npwp,
          'is_active' => $request->is_active,
          'status' => $request->status,
          'updated_by' => Auth::user()->username
        ];
    
        if ($request->hasFile('file_npwp')) {
          $file = $request->file('file_npwp');
    
          $dest = public_path('assets/img/pelanggan');
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
        $desc = $ok ? 'Berhasil ubah Data Pelanggan.' : 'Gagal ubah Data Pelanggan.';
        LogActivity::saveLogActivity($desc, $data);
    
        return response()->json([
          'status'  => (bool)$ok,
          'message' => $desc
        ]);
      } else {
        $rules = [
          'nama_pelanggan' => [
              'required',
              'string',
              'max:50',
              // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
              Rule::unique('m_pelanggan_hdr', 'nama_pelanggan')
                  ->where(function ($query) use ($request) {
                    return $query
                      ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
                  })
                  ->ignore($request->id), // Penting: Abaikan ID sendiri saat update
          ],
          'kode_jenis_pelanggan' => 'required',
          'is_active' => 'required',
          'npwp' => 'required',
          'telepon' => ['required', 'max:15'],
          'file_npwp' => ['nullable', 'mimes:jpg,jpeg,png,pdf', 'max:350'], // 350 KB
        ];
    
        $messages = [
          'nama_pelanggan.required' => 'Nama Pelanggan Wajib diisi',
          'nama_pelanggan.unique' => 'Nama Pelanggan sudah digunakan',
          'kode_jenis_pelanggan.required' => 'Jenis Pemilik Wajib diisi',
          'telepon.required'  => 'Telepon Selular Wajib diisi',
          'is_active.required'  => 'Status Aktif Wajib diisi',
          'npwp.required'  => 'Nomor NPWP Wajib diisi',
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
    
        $lastNum = Pelanggan::query()->where('kode_cabang', $request->kode_cabang)->max(DB::raw('CAST(SUBSTRING(kode_pelanggan, 2) AS UNSIGNED)')) ?? 0;
        $kode = sprintf('%05d', $lastNum + 1);
    
        $data = [
          'kode_pelanggan' => $kode,
          'kode_cabang' => $request->kode_cabang,
          'nama_pelanggan' => $request->nama_pelanggan,
          'kode_jenis_pelanggan' => $request->kode_jenis_pelanggan,
          'telepon' => $request->telepon,
          'alamat1' => $request->alamat1,
          'npwp' => $request->npwp,
          'is_active' => $request->is_active,
          'status' => $request->status,
          'created_by' => Auth::user()->username
        ];
    
        if ($request->hasFile('file_npwp')) {
          $file = $request->file('file_npwp');
    
          // Pastikan folder ada
          $dest = public_path('assets/img/pelanggan');
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
    
        $ok = Pelanggan::create($data);
    
        ## Log Activity
        $desc = $ok ? 'Berhasil tambah Data Pelanggan.' : 'Gagal tambah Data Pelanggan.';
        LogActivity::saveLogActivity($desc, $data);
    
        return response()->json([
          'status'  => (bool)$ok,
          'message' => $desc
        ]);
      }
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
    $data = Pelanggan::findOrFail($id);
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
    $result = Pelanggan::findOrFail($id);
    if ($result) {
      $dest = public_path('assets/img/pelanggan');
      $photo = $result->file_npwp;
      $photoPath = $dest.DIRECTORY_SEPARATOR.$photo;
      if (is_file($photoPath)) {
        @unlink($photoPath);
      }
    }

    $data = Pelanggan::query()->where('id', $id)->first()->toArray();

    $ok = Pelanggan::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Pelanggan.' : 'Gagal Hapus Data Pelanggan.';
    LogActivity::saveLogActivity($desc, $data);

  }

  public function downloadFile(Request $request)
  {
    $id = $request->id;
    $tipe = $request->tipe;
    $result = Pelanggan::find($id);
    if ($result) {
      $dest = public_path('assets/img/pelanggan');
      $photo = $result->file_npwp;
      $photoPath = $dest.DIRECTORY_SEPARATOR.$photo;
      if (is_file($photoPath)) {
        ## Log Activity
        $data['id'] = $id;
        $data['tipe'] = $tipe;
        $data['file'] = $photo;
        $desc = 'Download File Pelanggan';
        LogActivity::saveLogActivity($desc, $data);

        return response()->download($photoPath, $photo);
      }
    } else {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }
  }
}