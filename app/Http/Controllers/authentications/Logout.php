<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;

use App\Models\User;
use App\Models\LoginActivity;
use App\Models\LogActivity;

class Logout extends Controller
{
  public function index()
  {
    ## Session Login
    LoginActivity::updateOrCreate(
      [
        'userid'    => Auth::user()->username,
        'session_loginflag'    => 1
      ],
      [
        'session_endlogin' => date("Y-m-d H:i:s"),
        'session_loginflag' => 0,
        'session_closeby' => Auth::user()->username
      ]
    );

    ## Log Activity
    $desc = "Logout Aplikasi";
    LogActivity::saveLogActivity($desc);

    Auth::logout();
    Session::flush();
    return redirect('/');
  }
}
