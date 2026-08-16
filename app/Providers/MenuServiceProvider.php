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
      static $cachedPerRequest = [];

      $user = auth()->user();
      $userId = $user?->id;
      
      if (!$userId) {
        View::share('menuData', [
            (object)['menu' => []],
            (object)['menu' => []],
        ]);
        return;
      }

      if (isset($cachedPerRequest[$userId])) {
        View::share('menuData', $cachedPerRequest[$userId]);
        return;
      }

      $cacheKey = 'user_menu_' . $userId . '_' . ($user->user_group ?? 0);
      $menuData = Cache::remember($cacheKey, 300, function () use ($userId) {
        $groupIds = DB::table('users_group')
          ->where('userid', $userId)
          ->pluck('groupid');

        if ($groupIds->isEmpty()) {
          return [
            (object)['menu' => []],
            (object)['menu' => []],
          ];
        }

        $rows = DB::table('v_menu as m')
          ->join('group_detail as gd', function ($join) use ($groupIds) {
              $join->on('gd.menuid', '=', 'm.id')
                    ->whereIn('gd.groupid', $groupIds)
                    ->where('gd.isList', '=', 1);
          })
          ->where('m.active', 'Y')
          ->orderBy('m.sort_path')
          ->select('m.id', 'm.parent_id', 'm.title', 'm.url_menu', 'm.slug', 'm.sort_path')
          ->distinct()
          ->get();

        $byParent = $rows->groupBy(fn($m) => $m->parent_id ?: 0);

        $build = function ($parentId) use (&$build, $byParent) {
            $children = $byParent->get($parentId ?: 0, collect());

            return $children->map(function ($m) use (&$build) {
                $node = (object) [
                    'name' => $m->title,
                    'slug' => $m->slug,
                ];

                if (!empty($m->url_menu) && $m->url_menu !== '#') {
                    $node->url = $m->url_menu;
                }

                $kids = $build($m->id);
                if ($kids->isNotEmpty()) {
                    $node->submenu = $kids->values()->all();
                }

                return $node;
            });
        };

        $tree = $build(0)->values()->all();
        $verticalMenuData = (object) ['menu' => $tree];
        return [$verticalMenuData, $verticalMenuData];
      });

      $cachedPerRequest[$userId] = $menuData;
      View::share('menuData', $menuData);
    });  

    
  }
}
