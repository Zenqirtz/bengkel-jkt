<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\ProfilePerusahaan;
use App\Models\LoginActivity;
use App\Models\LogActivity;

class Login extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.authentications.auth-login', ['pageConfigs' => $pageConfigs]);
  }

  public function postlogin(Request $request)
  {
    $username = strtoupper($request->input('username')); // samakan case
    $credentials = [
        'username' => $username,
        'password' => $request->input('password'),
        'status'   => 'Y', // <— kunci: hanya user aktif yang boleh login
    ];

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate(); // keamanan sesi

        $user   = Auth::user(); // sudah pasti status 'Y'
        $cabang = ProfilePerusahaan::where('id', $user->user_cabang)->first();

        $request->session()->put([
            'id'        => $user->id,
            'username'  => $user->username,
            'nama'      => $user->name,
            'id_cabang' => $user->user_cabang,
            'kd_cabang' => $cabang?->kode_cabang,
            'nm_cabang' => $cabang?->nama_cabang,
            'startlogin' => date("Y-m-d H:i:s"),
        ]);

        ## Session Login
        $data = [
          'userid' => $username,
          'session_wkip' => $_SERVER['REMOTE_ADDR'],
          'session_startlogin' => date("Y-m-d H:i:s"),
          'session_loginflag' => 1,
        ];
  
        LoginActivity::create($data);

        ## Log Activity
        $desc = "Login Aplikasi";
        LogActivity::saveLogActivity($desc);

        $chgpwd = $user->chgpwd;
        if($chgpwd == "1") {
          return redirect('/home');
        } else {
          return redirect('/akun/ubah-sandi');
        }
        
    }

    // Jika gagal (password salah atau status != 'Y')
    // Opsional: bedakan pesan jika user ada tapi nonaktif
    $exists = User::where('username', $username)->exists();
    if ($exists && User::where('username', $username)->value('status') !== 'Y') {
        return back()->with('error', 'Status user tidak aktif.');
    }

    return back()->with('error', 'Nama user atau kata sandi tidak sesuai.');
  }
}
