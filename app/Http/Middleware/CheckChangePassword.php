<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckChangePassword
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Pastikan user sudah login
        if (Auth::check()) {
            
            // 2. Cek apakah chgpwd = 0 (harus ganti password)
            if (Auth::user()->chgpwd == 0) {
                
                // 3. PENTING: Cegah infinite loop! 
                // Izinkan akses jika user sedang berada di halaman ubah sandi atau mau logout.
                // Sesuaikan 'ubah-sandi' dan 'logout' dengan nama route Anda.
                if (!$request->routeIs('ubah-sandi*') && !$request->routeIs('logout')) {
                    
                    // Redirect ke route ubah sandi (sesuaikan nama route-nya)
                    return redirect()->route('akun-ubah-sandi')
                        ->with('warning', 'Anda diwajibkan untuk mengubah kata sandi default Anda terlebih dahulu!');
                }
            }
        }

        // Lanjutkan request jika kondisi aman
        return $next($request);
    }
}