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
use App\Models\ProfilePerusahaan;
use App\Models\Parameter;
use App\Models\LogActivity;
use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class ProfilePerusahaanController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function ProfilePerusahaan(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Profile Perusahaan';

    $status_aktif = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.setting.profile-perusahaan', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'status_aktif' => $status_aktif,
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
      1 => 'a.id',
      2 => 'a.nama_cabang',
      3 => 'a.alamat1',
      4 => 'a.kode_pos',
      5 => 'a.npwp',
      6 => 'a.telepon',
      7 => 'a.fax',
      8 => 'b.keterangan',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'a.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('m_cabang as a')
    ->leftJoin('parameter as b', function ($join) {
      $join->on('b.kode', '=', 'a.is_active')
          ->where('b.nama_tabel', '=', 'STATUS'); // syarat di JOIN
    });

    // Total baris tanpa filter
    $totalData = (clone $base)->count('a.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('k.id', 'like', "%{$search}%")
    //           ->orWhere('k.nama_marketing', 'like', "%{$search}%")
    //           ->orWhere('k.no_identitas', 'like', "%{$search}%")
    //           ->orWhere('k.tipe_marketing', 'like', "%{$search}%")
    //           ->orWhere('b.keterangan', 'like', "%{$search}%");
    //     });
    // }
    if ($request->filled('nama_cabang')) {
      $query->where('a.nama_cabang', 'like', '%' . $request->nama_cabang . '%');
    }
    if ($request->filled('telepon')) {
      $query->where('a.telepon', 'like', '%' . $request->telepon . '%');
    }
    if ($request->filled('fax')) {
      $query->where('a.fax', 'like', '%' . $request->fax . '%');
    }
    if ($request->filled('npwp')) {
      $query->where('a.npwp', 'like', '%' . $request->npwp . '%');
    }
    if ($request->filled('alamat')) {
      $query->where('a.alamat1', 'like', '%' . $request->alamat . '%');
    }
    if ($request->filled('is_active')) {
      if ($request->is_active <> 'all') {
        $query->where('a.is_active', 'like', '%' . $request->is_active . '%');
      }
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('a.id');

    // Ambil data halaman saat ini
    $datas = $query
      ->select([
          'a.id',
          'a.kode_cabang',
          'a.nama_singkat',
          'a.nama_cabang',
          'a.alamat1',
          'a.kode_pos',
          'a.telepon',
          'a.fax',
          'a.npwp',
          'a.logo_cabang',
          'a.is_active',
          'b.keterangan as status_aktif',
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
            'id'              => $row->id,
            'fake_id'         => ++$fake,
            'kode_cabang'     => $row->kode_cabang,
            'nama_singkat'    => blank($row->nama_singkat) ? '' : $row->nama_singkat,
            'nama_cabang'     => $row->nama_cabang,
            'alamat1'         => $row->alamat1,
            'kode_pos'        => $row->kode_pos,
            'telepon'         => $row->telepon,
            'fax'             => $row->fax,
            'npwp'            => $row->npwp,
            // 'nourut'          => $row->nourut,
            'logo_cabang'     => $row->logo_cabang,
            'is_active'       => $row->is_active,
            'status_aktif'    => $row->status_aktif,
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
      $lastNum = ProfilePerusahaan::query()->max(DB::raw('CAST(kode_cabang AS UNSIGNED)')) ?? 0;
      $kdCabang = sprintf('%04d', $lastNum + 1);

      $rules = [
        'nama_singkat' => 'required|string|max:4|unique:m_cabang,nama_singkat',
        'nama_cabang' => 'required|string|max:60|unique:m_cabang,nama_cabang',
        'alamat1'     => 'required',
        'is_active'   => 'required',
        'telepon'     => 'nullable|string|max:20',
        'fax'         => 'nullable|string|max:20',
        'npwp'        => 'required|max:32',
        'email'       => 'nullable|email|max:100',
        // 'nourut'      => 'nullable|integer|min:0',
        'photo'       => 'nullable|image|mimes:jpg,jpeg,png|max:350', // 1MB
      ];

      $messages = [
        'nama_singkat.required' => 'Nama Singkat Wajib diisi',
        'nama_singkat.unique'  => 'Nama Singkat sudah digunakan',
        'nama_cabang.required' => 'Nama Perusahaan Wajib diisi',
        'nama_cabang.unique'  => 'Nama Perusahaan sudah digunakan',
        'alamat1.required'  => 'Alamat Wajib diisi',
        'npwp.required'  => 'NPWP Wajib diisi',
        'is_active.required'  => 'Status Aktif Wajib diisi',
        'photo.image'    => 'File harus berupa gambar.',
        'photo.mimes'    => 'Format foto harus jpg, jpeg, atau png.',
        'photo.max'      => 'Ukuran foto maksimal 350 KB.',
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
        'kode_cabang' => $kdCabang,
        'nama_singkat' => $request->nama_singkat,
        'nama_cabang' => $request->nama_cabang,
        'alamat1' => $request->alamat1,
        'kode_pos' => $request->kode_pos,
        'telepon' => $request->telepon,
        'fax' => $request->fax,
        'npwp' => $request->npwp,
        'email' => $request->email,
        'is_active' => $request->is_active,
        // 'nourut' => $request->nourut,
        'created_by' => Auth::user()->username
      ];

      // handle upload foto (opsional)
      if ($request->hasFile('photo')) {
          $file = $request->file('photo');

          // Pastikan folder ada
          $dest = public_path('assets/img/cabang');
          if (!is_dir($dest)) {
              @mkdir($dest, 0775, true);
          }

          // Nama file unik
          $filename = Str::slug($data['nama_cabang']).'-'.time().'.'.$file->getClientOriginalExtension();

          // Pindahkan file
          $file->move($dest, $filename);

          // Simpan hanya nama file (bukan full path) agar mudah dirender di front-end
          $data['logo_cabang'] = $filename;
      }

      $ok = ProfilePerusahaan::create($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil tambah Data Profile Perusahaan' : 'Gagal tambah Data Profile Perusahaan';
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
    $data = ProfilePerusahaan::findOrFail($id);
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
      $result = ProfilePerusahaan::findOrFail($id);

      $rules = [
        'nama_singkat' => 'required|string|max:4|unique:m_cabang,nama_singkat,'.$result->id,
        'nama_cabang' => 'required|string|max:60|unique:m_cabang,nama_cabang,'.$result->id,
        'alamat1'     => 'required',
        'is_active'   => 'required',
        'telepon'     => 'nullable|string|max:20',
        'fax'         => 'nullable|string|max:20',
        'npwp'        => 'required|max:32',
        'email'       => 'nullable|email|max:100',
        // 'nourut'      => 'nullable|integer|min:0',
        'photo'       => 'nullable|image|mimes:jpg,jpeg,png|max:350', // 1MB
      ];

      $messages = [
        'nama_singkat.required' => 'Nama Singkat Wajib diisi',
        'nama_singkat.unique'  => 'Nama Singkat sudah digunakan',
        'nama_cabang.required' => 'Nama Perusahaan Wajib diisi',
        'nama_cabang.unique'  => 'Nama Perusahaan sudah digunakan',
        'alamat1.required'  => 'Alamat Wajib diisi',
        'npwp.required'  => 'NPWP Wajib diisi',
        'is_active.required'  => 'Status Aktif Wajib diisi',
        'photo.image'    => 'File harus berupa gambar.',
        'photo.mimes'    => 'Format foto harus jpg, jpeg, atau png.',
        'photo.max'      => 'Ukuran foto maksimal 350 KB.',
      ];

      $validator = Validator::make($request->all(), $rules, $messages);

      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ], 200);
      }

      $data = [
        'nama_singkat' => $request->nama_singkat,
        'nama_cabang' => $request->nama_cabang,
        'alamat1' => $request->alamat1,
        'kode_pos' => $request->kode_pos,
        'telepon' => $request->telepon,
        'fax' => $request->fax,
        'npwp' => $request->npwp,
        'email' => $request->email,
        'is_active' => $request->is_active,
        // 'nourut' => $request->nourut,
        'updated_by' => Auth::user()->username
      ];

      if ($request->hasFile('photo')) {
        $file = $request->file('photo');

        $dest = public_path('assets/img/cabang');
        if (!is_dir($dest)) {
            @mkdir($dest, 0775, true);
        }

        $filename = Str::slug($data['nama_cabang']).'-'.time().'.'.$file->getClientOriginalExtension();
        $file->move($dest, $filename);
        $data['logo_cabang'] = $filename;

        // hapus foto lama jika ada dan berbeda
        $old = $request->input('old_photo');
        if ($old && $old !== $filename) {
          $oldPath = $dest.DIRECTORY_SEPARATOR.$old;
          if (is_file($oldPath)) {
            @unlink($oldPath);
          }
        }
      }

      $ok = $result->update($data);

      ## Log Activity
      $desc = $ok ? 'Berhasil ubah Data Profile Perusahaan' : 'Gagal ubah Data Profile Perusahaan';
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
    // $result = ProfilePerusahaan::findOrFail($id);
    // if ($result) {
    //   $dest = public_path('assets/img/cabang');
    //   $photo = $result->logo_cabang;
    //   $photoPath = $dest.DIRECTORY_SEPARATOR.$photo;
    //   if (is_file($photoPath)) {
    //     @unlink($photoPath);
    //   }
    // }

    $data = ProfilePerusahaan::query()->where('id', $id)->first()->toArray();

    $ok = ProfilePerusahaan::where('id', $id)->delete();
    if($ok) {
      ## Hapus File 
      $dest = public_path('assets/img/cabang');
      $photo = $data['logo_cabang'];
      $photoPath = $dest.DIRECTORY_SEPARATOR.$photo;
      if (is_file($photoPath)) {
        @unlink($photoPath);
      }
    }

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Profile Perusahaan' : 'Gagal Hapus Data Profile Perusahaan';
    LogActivity::saveLogActivity($desc, $data);
  }
}