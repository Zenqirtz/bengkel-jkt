<?php

namespace App\Http\Controllers\akun;

use App\Http\Controllers\Controller;
// use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ProfilePerusahaan;
// use Carbon\Carbon;

class UbahSandiController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function index(): View
  {
    return $this->UbahSandi();
  }

  public function UbahSandi(): View
  {
    $title = "Ubah Sandi";
    $users = Auth::user();
    $cabang = ProfilePerusahaan::where('id', $users->user_cabang)->first();
    $nama_cabang = $cabang?->nama_cabang;

    return view('content.akun.udah-sandi', [
      'title' => $title,
      'nama_cabang' => $nama_cabang,
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
    $dataID = Auth::user()->id;

    if($dataID) {
      // update the value
      $datas = User::updateOrCreate(
        ['id' => $dataID],
        [
          'password' => bcrypt($request->newPassword),
          'chgpwd' => '1',
          'updated_by' => Auth::user()->username
        ]
      );

      // user updated
      return response()->json(['status' => true, 'message' => "Berhasil ubah sandi"]);
    } else {
      return response()->json(['status' => false, 'message' => "Nama Pengguna tidak ditemukan"]);
    }
  }
}