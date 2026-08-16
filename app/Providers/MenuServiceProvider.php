<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Routing\Route;

// use App\Models\Menu;

class MenuServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    // $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
    // $verticalMenuData = json_decode($verticalMenuJson);
    // $horizontalMenuJson = file_get_contents(base_path('resources/menu/horizontalMenu.json'));
    // $horizontalMenuData = json_decode($horizontalMenuJson);

    // Jalankan setelah middleware web/ auth berjalan (saat render view)
    View::composer('*', function ($view) {
      // ================== Ambil user & group id ==================
      $user = auth()->user();
      $userId = $user?->id;
      
      // Jika user belum login, tampilkan menu kosong (atau semua menu publik jika ada konsep publik)
      if (!$userId) {
        View::share('menuData', [
            (object)['menu' => []],   // vertical
            (object)['menu' => []],   // horizontal (belum dipakai)
        ]);
        return;
      }

      // Group yang dimiliki user (bisa banyak)
      $groupIds = DB::table('users_group')
        ->where('userid', $userId)
        ->pluck('groupid');

      // Jika user tidak punya grup → kosong
      if ($groupIds->isEmpty()) {
        View::share('menuData', [
            (object)['menu' => []],   // vertical
            (object)['menu' => []],   // horizontal (belum dipakai)
        ]);
        return;
      }

      // -------- Vertical Menu from DB (tabel menu) --------
      // Cache 5 menit agar hemat query; silakan sesuaikan durasinya
      // $verticalMenuData = Cache::remember('menu.vertical.v_menu', 300, function () {
        $rows = DB::table('v_menu as m')
          ->join('group_detail as gd', function ($join) use ($groupIds) {
              $join->on('gd.menuid', '=', 'm.id')
                    ->whereIn('gd.groupid', $groupIds)
                    ->where('gd.isList', '=', 1);         // tampilkan yg boleh di-list
          })
          ->where('m.active', 'Y')
          ->orderBy('m.sort_path')                      // contoh 002/003/001 → urut hierarkis
          ->select('m.id', 'm.parent_id', 'm.title', 'm.url_menu', 'm.slug')
          ->distinct()
          ->get();

        $byParent = $rows->groupBy(fn($m) => $m->parent_id ?: 0);

        $build = function ($parentId) use (&$build, $byParent) {
            $children = $byParent->get($parentId ?: 0, collect());

            return $children->map(function ($m) use (&$build) {
                // bentuk sebagai stdClass (bukan array)
                $node = (object) [
                    'name' => $m->title,
                    // slug boleh string atau array; template kamu sudah handle dua-duanya
                    // 'slug' => Str::slug($m->slug), // atau pakai nama route kalau ada
                    'slug' => $m->slug, // atau pakai nama route kalau ada
                ];

                if (!empty($m->url_menu) && $m->url_menu !== '#') {
                    $node->url = $m->url_menu;
                }

                $kids = $build($m->id);
                if ($kids->isNotEmpty()) {
                    // pastikan anak juga berupa array of stdClass
                    $node->submenu = $kids->values()->all();
                }

                return $node; // stdClass
            });
        };

        $tree = $build(0)->values()->all();

        // bungkus seperti json_decode (object berisi key "menu")
        // return (object) ['menu' => $tree];
      // });

      $verticalMenuData = (object) ['menu' => $tree];
      $horizontalMenuData = $verticalMenuData;

      // Share all menuData to all the views
      $this->app->make('view')->share('menuData', [$verticalMenuData, $horizontalMenuData]);

    });  

    
  }
}
