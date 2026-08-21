<?php

namespace App\Http\Controllers\setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\GroupPrivilege;
use App\Models\Menu;
use App\Models\Group;
use App\Models\LogActivity;
// use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class GroupPrivilegeController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function GroupPrivilege(): View
  {
    $isList = Helper::AuthIsPerm("list");
    $isAdd = Helper::AuthIsPerm("add");
    if(!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path = request()->path();
    $title = Helper::getTitleMenu($path) ?? 'Group Privilege';

    $groupId = session('idGroup');

    // dropdown user (kolom atas)
    $groups = Group::query()->where('active', 'Y')->orderBy('id', 'asc')->get();

    
    // if ($groupId) {
    //   // group detail yang sudah dimiliki user
    //   $menus = DB::table('v_menu as a')  
    //     ->leftJoin('group_detail as b', 'a.id', '=', 'b.menuid')
    //     ->select('a.id', 'a.title', 'a.level', 'b.isList', 'b.isAdd', 'b.isEdit', 'b.isDelete')
    //     ->where('a.active', 'Y')
    //     ->where('b.groupid', $groupId)
    //     ->get();
    // } else {
    //   // list menu all
    //   $menus = DB::table('v_menu')
    //     ->select('id', 'title', 'level', DB::raw('0 AS isList'), DB::raw('0 AS isAdd'), DB::raw('0 AS isEdit'), DB::raw('0 AS isDelete'))
    //     ->where('active', 'Y')
    //     ->get();
    // }

    $menus = DB::table('v_menu as a')
      ->leftJoin('group_detail as b', function ($join) use ($groupId) {
          $join->on('a.id', '=', 'b.menuid')
              ->where('b.groupid', '=', $groupId);   // ← syarat di JOIN
      })
      ->where('a.active', 'Y')
      ->select(
        'a.id','a.title','a.level',
        DB::raw('COALESCE(b.isList, 0) as isList'),
        DB::raw('COALESCE(b.isAdd, 0) as isAdd'),
        DB::raw('COALESCE(b.isEdit, 0) as isEdit'),
        DB::raw('COALESCE(b.isDelete, 0) as isDelete')
      )
      ->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.setting.group-privilege', [
      'title' => $title,
      'isAdd' => $isAdd,
      'groupid' => $groupId,
      'data_groups' => $groups,
      // 'data_group_details' => $group_details,
      'data_menus' => $menus
    ]);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    $data = Group::where('id', $request->id)->first();

    if ($data) {
      return redirect('setting/akses-group-privilege')->with('idGroup', $data->id);
    } else {
      return redirect('setting/akses-group-privilege')->with('error', 'Nama User tidak ditemukan');
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
    $groupid = $request->groupid;
    $btn = $request->btnSimpan;

    if ($btn == "simpan") {
      $validatedData = $request->validate(
        [
          'groupid' => 'required'
        ],
        [ // custom messages
          'groupid.required'    => 'Nama Group wajib diisi.',
        ]
      );

      $aryData = $request->frmChk;

      if ($aryData) {
        GroupPrivilege::where('groupid', $groupid)->delete();

        $datas = [];
        foreach ($aryData as $key => $value) {
          $data = [
            'groupid'    => $groupid,
            'menuid'     => $key,
            'isList'     => (isset($value['lihat']) || isset($value['tambah']) || isset($value['ubah']) || isset($value['hapus'])) ? '1' : '0',
            'isAdd'      => (isset($value['tambah'])) ? '1' : '0',
            'isEdit'     => (isset($value['ubah'])) ? '1' : '0',
            'isDelete'   => (isset($value['hapus'])) ? '1' : '0',
            // 'created_at' => date("Y-m-d H:i:s"),
            'created_by' => auth()->user()?->username,
            'updated_by' => auth()->user()?->username,
          ];

          GroupPrivilege::create($data);

          $datas[] = $data;
        }

        ## Log Activity
        $desc = 'Berhasil Tambah Data Group Privilege';
        LogActivity::saveLogActivity($desc, $datas);
        
        return redirect('setting/akses-group-privilege')->with('idGroup', $groupid)->with('success', 'Berhasil tambah group privilege');
      } else {
        return redirect('setting/akses-group-privilege')->with('error', 'Silahkan Pilih Daftar Cabang Yang Ada Pada Sistem');
      }
    } else {
      return redirect('setting/akses-group-privilege')->with('error', 'Gagal proses group privilege');
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