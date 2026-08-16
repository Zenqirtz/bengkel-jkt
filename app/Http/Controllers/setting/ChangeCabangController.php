<?php

namespace App\Http\Controllers\setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProfilePerusahaan;
// use Carbon\Carbon;

class ChangeCabangController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index($id, Request $request)
  {
    $cabang = ProfilePerusahaan::where('id', $id)->first();

    if($cabang) {
      $tmp['id_cabang'] = $cabang->id;
      $tmp['kd_cabang'] = $cabang->kode_cabang;
      $tmp['nm_cabang'] = $cabang->nama_cabang;
      
      $request->session()->put($tmp);
    }

    // return redirect('/home');
    return back();
  }
}