<?php

namespace App\Http\Controllers\akun;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\ProfilePerusahaan;
// use Carbon\Carbon;

class ProfilController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Profil(): View
  {
    $title = "Profil";
    $users = Auth::user();
    $cabang = ProfilePerusahaan::where('id', $users->user_cabang)->first();
    $group_akses = DB::table('v_group_akses')->where('userid', $users->id)->orderBy('groupid', 'asc')->get();
    $cabang_akses = DB::table('v_cabang_akses')->where('userid', $users->id)->orderBy('nourut', 'asc')->get();

    $nama_cabang = $cabang?->nama_cabang;
    return view('content.akun.profil', [
      'title' => $title,
      'nama_cabang' => $nama_cabang,
      'group_akses' => $group_akses,
      'cabang_akses' => $cabang_akses,
      'users' => $users
    ]);
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

    $akun = User::where('acct_cd', $request->acct_cd)->first();

    if($akun) {
      // update the value
      $datas = User::updateOrCreate(
        ['id' => $dataID],
        [
          'acct_cd' => $request->acct_cd,
          'descs' => $request->descs,
          'updated_by' => Auth::user()->username,
          'updated_at' => date("Y-m-d H:i:s")
        ]
      );

      // user updated
      return response()->json(['status' => true, 'message' => "Berhasil ubah data"]);
    } else {
      return response()->json(['status' => false, 'message' => "Nama Pengguna tidak ditemukan"]);
    }
  }
}