<?php

namespace App\Http\Controllers\setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\UserPrivilege;
use App\Models\Group;
use App\Models\User;
use App\Models\LogActivity;
// use Carbon\Carbon;

class UserPrivilegeController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function UserPrivilege(): View
  {
    $isList = \Helper::AuthIsPerm("list");
    $isAdd = \Helper::AuthIsPerm("add");
    if(!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path = request()->path();
    $title = \Helper::getTitleMenu($path) ?? 'User Privilege';

    $userId = session('idUser');

    // dropdown user (kolom atas)
    $users = User::query()->where('status', 'Y')->orderBy('name', 'asc')->get();

    // group yang sudah dimiliki user (kolom kiri)
    $user_groups = DB::table('group as g')      // jika nama tabel sebenarnya 'groups', ganti jadi 'groups as g'
        ->join('users_group as ug', 'g.id', '=', 'ug.groupid')
        ->select('g.id', 'g.nama')
        ->where('ug.userid', $userId)
        ->orderBy('g.nama', 'asc')
        ->get();

    // ambil ID yang sudah dimiliki
    $ownedIds = $user_groups->pluck('id');

    // group aktif untuk kolom kanan, TIDAK menampilkan yang sudah dimiliki
    $groups = Group::query()
        ->where('active', 'Y')
        ->when($ownedIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $ownedIds))
        ->orderBy('nama', 'asc')
        ->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.setting.user-privilege', [
      'title' => $title,
      'isAdd' => $isAdd,
      'userid' => $userId,
      'data_users' => $users,
      'data_user_groups' => $user_groups,
      'data_groups' => $groups
    ]);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    $user = User::where('id', $request->user)->first();

    if ($user) {
      return redirect('setting/akses-user-privilege')->with('idUser', $user->id);
    } else {
      return redirect('setting/akses-user-privilege')->with('error', 'Nama User tidak ditemukan');
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
    $userid = $request->userid;
    $btn = $request->btnSimpan;

    if ($btn == "selected") {
      $validatedData = $request->validate(
        [
          'userid' => 'required'
        ],
        [ // custom messages
          'userid.required'    => 'Nama User wajib diisi.',
        ]
      );

      $aryData = $request->group2;
      if ($aryData) {
        $datas = [];
        foreach ($aryData as $key => $value) {

          $data = [
            'userid'     => $userid,
            'groupid'    => $value,
            'created_by' => Auth::user()->username,
            'updated_by' => Auth::user()->username,
          ];

          UserPrivilege::create($data);

          $datas[] = $data;
        }

        ## Log Activity
        $desc = 'Berhasil Tambah Data User Privilege';
        LogActivity::saveLogActivity($desc, $datas);
        
        return redirect('setting/akses-user-privilege')->with('idUser', $userid)->with('success', 'Berhasil tambah user privilege');
      } else {
        return redirect('setting/akses-user-privilege')->with('error', 'Silahkan Pilih Daftar Hak Akses Yang Ada Pada Sistem');
      }
    } elseif($btn == "diselected") {
      $validatedData = $request->validate(
        [
          'userid' => 'required'
        ],
        [ // custom messages
          'userid.required'    => 'Nama User wajib diisi.',
        ]
      );

      $aryData = $request->group;
      if ($aryData) {
        $datas = [];
        foreach ($aryData as $key => $value) {
          $data = UserPrivilege::query()->where('userid', $userid)->where('groupid', $value)->first()->toArray();

          UserPrivilege::where('userid', $userid)->where('groupid', $value)->delete();

          $datas[] = $data;
        }

        ## Log Activity
        $desc = 'Berhasil Hapus Data User Privilege';
        LogActivity::saveLogActivity($desc, $datas);
        
        return redirect('setting/akses-user-privilege')->with('idUser', $userid)->with('success', 'Berhasil hapus user privilege');
      } else {
        return redirect('setting/akses-user-privilege')->with('error', 'Silahkan Pilih Daftar Hak Akses Yang Dimiliki User');
      }
    } else {
      return redirect('setting/akses-user-privilege')->with('error', 'Gagal proses user privilege');
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
    //
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
    //
  }
}