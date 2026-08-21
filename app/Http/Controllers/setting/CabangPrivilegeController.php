<?php

namespace App\Http\Controllers\setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CabangPrivilege;
use App\Models\ProfilePerusahaan;
use App\Models\User;
use App\Models\LogActivity;
// use Carbon\Carbon;

use App\Helpers\Helpers as Helper;

class CabangPrivilegeController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function CabangPrivilege(): View
  {
    $isList = Helper::AuthIsPerm("list");
    $isAdd = Helper::AuthIsPerm("add");
    if(!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path = request()->path();
    $title = Helper::getTitleMenu($path) ?? 'Cabang Privilege';

    $userId = session('idUser');

    // dropdown user (kolom atas)
    $users = User::query()->where('status', 'Y')->orderBy('name', 'asc')->get();

    // cabang yang sudah dimiliki user (kolom kiri)
    $user_cabangs = DB::table('m_cabang as c')  
        ->join('users_cabang as uc', 'c.id', '=', 'uc.cabangid')
        ->select('c.id', 'c.nama_cabang')
        ->where('uc.userid', $userId)
        ->orderBy('c.nama_cabang', 'asc')
        ->get();

    // ambil ID yang sudah dimiliki
    $ownedIds = $user_cabangs->pluck('id');

    // cabang aktif untuk kolom kanan, TIDAK menampilkan yang sudah dimiliki
    $cabangs = ProfilePerusahaan::query()
        ->when($ownedIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $ownedIds))
        ->orderBy('nama_cabang', 'asc')
        ->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.setting.cabang-privilege', [
      'title' => $title,
      'isAdd' => $isAdd,
      'userid' => $userId,
      'data_users' => $users,
      'data_user_cabangs' => $user_cabangs,
      'data_cabangs' => $cabangs
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
      return redirect('setting/akses-cabang-privilege')->with('idUser', $user->id);
    } else {
      return redirect('setting/akses-cabang-privilege')->with('error', 'Nama User tidak ditemukan');
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
            'cabangid'    => $value,
            'created_by' => Auth::user()->username,
            'updated_by' => Auth::user()->username,
          ];

          CabangPrivilege::create( $data);

          $datas[] = $data;

        }

        ## Log Activity
        $desc = 'Berhasil Tambah Data Cabang Privilege';
        LogActivity::saveLogActivity($desc, $datas);
        
        return redirect('setting/akses-cabang-privilege')->with('idUser', $userid)->with('success', 'Berhasil tambah cabang privilege');
      } else {
        return redirect('setting/akses-cabang-privilege')->with('error', 'Silahkan Pilih Daftar Cabang Yang Ada Pada Sistem');
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
          $data = CabangPrivilege::query()->where('userid', $userid)->where('cabangid', $value)->first()->toArray();

          CabangPrivilege::where('userid', $userid)->where('cabangid', $value)->delete();

          $datas[] = $data;

        }

        ## Log Activity
        $desc = 'Berhasil Hapus Data Cabang Privilege';
        LogActivity::saveLogActivity($desc, $datas);
        
        return redirect('setting/akses-cabang-privilege')->with('idUser', $userid)->with('success', 'Berhasil hapus cabang privilege');
      } else {
        return redirect('setting/akses-cabang-privilege')->with('error', 'Silahkan Pilih Daftar Cabang Yang Dimiliki User');
      }
    } else {
      return redirect('setting/akses-cabang-privilege')->with('error', 'Gagal proses cabang privilege');
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