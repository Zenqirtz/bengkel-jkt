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
use App\Models\User;
use App\Models\Group;
use App\Models\Parameter;
use App\Models\ProfilePerusahaan;
use App\Models\UserPrivilege;
use App\Models\CabangPrivilege;
use App\Models\LogActivity;

use App\Helpers\Helpers as Helper;

class RegistrasiUser extends Controller
{
  /**
   * Redirect to user-management view.
   *
   */
  public function RegistrasiUser(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Registrasi User';

    $groups = Group::query()->where('active', 'Y')->orderBy('id', 'asc')->get();
    $userLevels = Parameter::query()->where('nama_tabel', 'USER_LEVEL')->orderBy('no_urut', 'asc')->get();
    $status = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();
    $cabangs = ProfilePerusahaan::query()->orderBy('nourut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.setting.registrasi-user', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'groups' => $groups,
      'userLevels' => $userLevels,
      'cabangs' => $cabangs,
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
    $columns = [
      1 => 'a.id',
      2 => 'a.username',
      3 => 'a.name',
      4 => 'a.email',
      5 => 'd.nama',
      6 => 'e.nama_cabang',
      7 => 'c.keterangan',
      8 => 'a.profile_photo_url',
      9 => 'b.keterangan',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'a.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query + LEFT JOIN
    $base = DB::table('users as a')
        ->leftJoin('parameter as b', function ($join) {
          $join->on('b.kode', '=', 'a.status')
               ->where('b.nama_tabel', '=', 'STATUS'); // syarat di JOIN
        })
        ->leftJoin('parameter as c', function ($join) {
          $join->on('c.kode', '=', 'a.user_level')
               ->where('c.nama_tabel', '=', 'USER_LEVEL'); // syarat di JOIN
        })
        ->leftJoin('group as d', 'd.id', '=', 'a.user_group')
        ->leftJoin('m_cabang as e', 'e.id', '=', 'a.user_cabang');

    // Total baris tanpa filter
    $totalData = (clone $base)->count('a.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('a.id', 'like', "%{$search}%")
    //           ->orWhere('a.username', 'like', "%{$search}%")
    //           ->orWhere('a.name', 'like', "%{$search}%");
    //     });
    // }
    if ($request->filled('username')) {
      $query->where('a.username', 'like', '%' . $request->username . '%');
    }
    if ($request->filled('fullname')) {
      $query->where('a.name', 'like', '%' . $request->fullname . '%');
    }
    if ($request->filled('email')) {
      $query->where('a.email', 'like', '%' . $request->email . '%');
    }
    if ($request->filled('level')) {
      if ($request->level <> 'all') {
        $query->where('a.user_level', 'like', '%' . $request->level . '%');
      }
    }
    if ($request->filled('status')) {
      if ($request->status <> 'all') {
        $query->where('a.status', 'like', '%' . $request->status . '%');
      }
    }
    if ($request->filled('grup')) {
      if ($request->grup <> 'all') {
        $query->where('a.user_group', 'like', '%' . $request->grup . '%');
      }
    }
    if ($request->filled('cabang')) {
      if ($request->cabang <> 'all') {
        $query->where('a.user_cabang', 'like', '%' . $request->cabang . '%');
      }
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('a.id');

    // Ambil data halaman saat ini
    $datas = $query
        ->select([
            'a.id',
            'a.username',
            'a.name',
            'a.email',
            // 'a.user_group',
            // 'a.user_cabang',
            // 'a.user_level',
            'a.status',
            'a.profile_photo_url',
            'b.keterangan as nm_status',
            'c.keterangan as nm_level',
            'd.nama as nm_group',
            'e.nama_cabang',
        ])
        ->orderBy($order, $dir)
        ->offset($start)
        ->limit($limit)
        ->get();

    // Susun payload DataTables
    $dest = public_path('assets/img/avatars');
    $data = [];
    $fake = $start;
    foreach ($datas as $row) {

      $photoPath = $dest.DIRECTORY_SEPARATOR.$row->profile_photo_url;
      $file_photo = (is_file($photoPath)) ? "1" : "0";

      $data[] = [
        'id' => $row->id,
        'fake_id' => ++$fake,
        'username' => $row->username,
        'name' => $row->name,
        'email' => $row->email,
        // 'user_group' => $row->user_group,
        // 'user_cabang' => $row->user_cabang,
        // 'user_level' => $row->user_level,
        'status' => $row->status,
        'file_photo' => $file_photo,
        'nm_status' => $row->nm_status,
        'nm_level' => $row->nm_level,
        'nm_group' => $row->nm_group,
        'nama_cabang' => $row->nama_cabang,
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
        'username'    => 'required|string|max:32|unique:users,username',
        'fullname'    => 'required|string|max:100',
        'email'       => 'required|email|max:100', //|unique:users,email
        'user_group'  => 'required',
        'user_level'  => 'required',
        'user_cabang' => 'required',
        'password'    => 'required|string|min:5',
        'photo'       => 'nullable|image|mimes:jpg,jpeg,png|max:350', // 1MB
      ];
  
      $messages = [
        'username.required' => 'Nama User Wajib diisi',
        'username.unique'  => 'Nama User sudah digunakan',
        'fullname.required' => 'Nama Lengkap Wajib diisi',
        'email.required'  => 'Email Wajib diisi',
        'user_group.required'  => 'Group Akses Wajib diisi',
        'user_level.required'  => 'Level User Wajib diisi',
        'user_cabang.required'  => 'Cabang Wajib diisi',
        'password.required'  => 'Password Wajib diisi',
        'photo.image' => 'File harus berupa gambar.',
        'photo.mimes' => 'Format foto harus jpg, jpeg, atau png.',
        'photo.max'   => 'Ukuran foto maksimal 350KB.',
      ];
  
      $validator = Validator::make($request->all(), $rules, $messages);
  
      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ]);
      }

      $username = mb_strtoupper(preg_replace('/\s+/', '_', trim($request->username)), 'UTF-8');

      $data = [
        'username'          => $username,
        'name'              => $request->fullname,
        'email'             => $request->email,
        'user_group'        => $request->user_group,
        'user_level'        => $request->user_level,
        'user_cabang'       => $request->user_cabang,
        'status'            => $request->status,
        'password'          => bcrypt($request->password),
        'email_verified_at' => date("Y-m-d H:i:s"),
        'created_by'        => Auth::user()->username,
      ];

      // handle upload foto (opsional)
      if ($request->hasFile('photo')) {
          $file = $request->file('photo');

          // Pastikan folder ada
          $dest = public_path('assets/img/avatars');
          if (!is_dir($dest)) {
              @mkdir($dest, 0775, true);
          }

          // Nama file unik
          $filename = Str::slug($username).'-'.time().'.'.$file->getClientOriginalExtension();

          // Pindahkan file
          $file->move($dest, $filename);

          // Simpan hanya nama file (bukan full path) agar mudah dirender di front-end
          $data['profile_photo_url'] = $filename;
      }

      $user = User::create($data);
      if ($user) {
        $lastId = $user->id;
        // Set User Privilege
        UserPrivilege::create([
          'userid'     => $lastId,
          'groupid'    => $request->user_group,
          // 'created_at' => date("Y-m-d H:i:s"),
          'created_by' => auth()->user()?->username,
        ]);

        // Set User Cabang
        CabangPrivilege::create([
          'userid'     => $lastId,
          'cabangid'   => $request->user_cabang,
          // 'created_at' => date("Y-m-d H:i:s"),
          'created_by' => auth()->user()?->username,
        ]);
      }

      ## Log Activity
      $desc = $user ? 'Berhasil Tambah User' : 'Gagal Tambah User';
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status'  => (bool)$user,
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
    $user = User::findOrFail($id);
    return response()->json($user);
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
      $user = User::findOrFail($id);
      $user_group = $user->user_group;
      $user_cabang = $user->user_cabang;

      $rules = [
        'username'    => 'required|string|max:32|unique:users,username,'.$id,
        'fullname'    => 'required|string|max:100',
        'email'       => 'required|email|max:100', //|unique:users,email,'.$id
        'user_group'  => 'required',
        'user_level'  => 'required',
        'user_cabang' => 'required',
        // 'password'    => 'required|string|min:5',
        'photo'       => 'nullable|image|mimes:jpg,jpeg,png|max:350', // 1MB
      ];
  
      $messages = [
        'username.required' => 'Nama User Wajib diisi',
        'username.unique'  => 'Nama User sudah digunakan',
        'fullname.required' => 'Nama Lengkap Wajib diisi',
        'email.required'  => 'Email Wajib diisi',
        'user_group.required'  => 'Group Akses Wajib diisi',
        'user_level.required'  => 'Level User Wajib diisi',
        'user_cabang.required'  => 'Cabang Wajib diisi',
        // 'password.required'  => 'Password Wajib diisi',
        'photo.image' => 'File harus berupa gambar.',
        'photo.mimes' => 'Format foto harus jpg, jpeg, atau png.',
        'photo.max'   => 'Ukuran foto maksimal 350KB.',
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
        'username'          => $request->username,
        'name'              => $request->fullname,
        'email'             => $request->email,
        'user_group'        => $request->user_group,
        'user_level'        => $request->user_level,
        'user_cabang'       => $request->user_cabang,
        'status'            => $request->status,
        'updated_by'        => Auth::user()->username,
      ];

      if(!blank($request->password)) {
        $data['password'] =bcrypt($request->password);
      }

      if ($request->hasFile('photo')) {
        $file = $request->file('photo');
  
        $dest = public_path('assets/img/avatars');
        if (!is_dir($dest)) {
            @mkdir($dest, 0775, true);
        }
  
        $filename = Str::slug($request->username).'-'.time().'.'.$file->getClientOriginalExtension();
        $file->move($dest, $filename);
        $data['profile_photo_url'] = $filename;
  
        // hapus foto lama jika ada dan berbeda
        $old = $request->input('old_photo');
        if ($old && $old !== $filename) {
          $oldPath = $dest.DIRECTORY_SEPARATOR.$old;
          if (is_file($oldPath)) {
            @unlink($oldPath);
          }
        }
      }
  
      $ok = $user->update($data);
      if ($ok) {
  
        // Set User Privilege
        $up = UserPrivilege::where('userid', $id)->where('groupid', $user_group)->first();
        if($up) {
          UserPrivilege::where('id', $up->id)
          ->update([
              'userid'     => $id,
              'groupid'    => $request->user_group,
              'updated_by' => auth()->user()?->username,
          ]);
        } else {
          UserPrivilege::create([
              'userid'     => $id,
              'groupid'    => $request->user_group,
              'created_by' => auth()->user()?->username,
          ]);
        }
        
  
        // Set User Cabang
        $cp = CabangPrivilege::where('userid', $id)->where('cabangid', $user_cabang)->first();
        if ($cp) {
          CabangPrivilege::where('id', $cp->id)
          ->update([
              'userid'     => $id,
              'cabangid'    => $request->user_cabang,
              'updated_by' => auth()->user()?->username, // atau 'updated_by'
          ]);
        } else {
          CabangPrivilege::create([
              'userid'     => $id,
              'cabangid'    => $request->user_cabang,
              'created_by' => auth()->user()?->username, // atau 'updated_by'
          ]);
        }
      }
  
      ## Log Activity
      $desc = $ok ? 'Berhasil Ubah User' : 'Gagal Ubah User';
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
    // $user = User::findOrFail($id);
    // if ($user) {
    //   $dest = public_path('assets/img/avatars');
    //   $photo = $user->profile_photo_url;
    //   $photoPath = $dest.DIRECTORY_SEPARATOR.$photo;
    //   if (is_file($photoPath)) {
    //     @unlink($photoPath);
    //   }
    // }

    $data = User::query()->where('id', $id)->first()->toArray();

    $users = User::where('id', $id)->delete();
    if($users) {
      ## Hapus File
      $dest = public_path('assets/img/avatars');
      $photo = $data['profile_photo_url'];
      $photoPath = $dest.DIRECTORY_SEPARATOR.$photo;
      if (is_file($photoPath)) {
        @unlink($photoPath);
      }

      UserPrivilege::where('userid', $id)->delete();
      CabangPrivilege::where('userid', $id)->delete();
    }

    ## Log Activity
    $desc = $users ? 'Berhasil Hapus User' : 'Gagal Hapus User';
    LogActivity::saveLogActivity($desc, $data);
  }

  public function downloadFile(Request $request)
  {
    $id = $request->id;
    $tipe = $request->tipe;
    $result = User::find($id);
    if ($result) {
      $dest = public_path('assets/img/avatars');
      if($tipe == "photo") {
        $photo = $result->profile_photo_url;
      } else {
        $photo = "";
      }
      $photoPath = $dest.DIRECTORY_SEPARATOR.$photo;
      if (is_file($photoPath)) {
        ## Log Activity
        $data['id'] = $id;
        $data['tipe'] = $tipe;
        $data['file'] = $photo;
        $desc = 'Download File User';
        LogActivity::saveLogActivity($desc, $data);

        return response()->download($photoPath, $photo);
      }
    } else {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }
  }
}
