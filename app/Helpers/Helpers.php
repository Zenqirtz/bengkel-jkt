<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use App\Models\Menu;
use App\Models\PenomoranTransaksi;

class Helpers
{
  /**
   * Generate menu attributes for semi-dark mode
   *
   * @param bool $semiDarkEnabled Whether semi-dark mode is enabled
   * @return array HTML attributes for the menu element
   */
  public static function getMenuAttributes($semiDarkEnabled)
  {
    $attributes = [];

    if ($semiDarkEnabled) {
      $attributes['data-bs-theme'] = 'dark';
    }

    return $attributes;
  }

  public static function appClasses()
  {

    $data = config('custom.custom');


    // default data array
    $DefaultData = [
      'myLayout' => 'vertical',
      'myTheme' => 'light',
      'mySkins' => 'default',
      'hasSemiDark' => false,
      'myRTLMode' => true,
      'hasCustomizer' => true,
      'showDropdownOnHover' => true,
      'displayCustomizer' => true,
      'contentLayout' => 'compact',
      'headerType' => 'fixed',
      'navbarType' => 'sticky',
      'menuFixed' => true,
      'menuCollapsed' => false,
      'footerFixed' => false,
      'customizerControls' => [
        'color',
        'theme',
        'skins',
        'semiDark',
        'layoutCollapsed',
        'layoutNavbarOptions',
        'headerType',
        'contentLayout',
        'rtl'
      ],
      //   'defaultLanguage'=>'en',
    ];

    // if any key missing of array from custom.php file it will be merge and set a default value from dataDefault array and store in data variable
    $data = array_merge($DefaultData, $data);

    // All options available in the template
    $allOptions = [
      'myLayout' => ['vertical', 'horizontal', 'blank', 'front'],
      'menuCollapsed' => [true, false],
      'hasCustomizer' => [true, false],
      'showDropdownOnHover' => [true, false],
      'displayCustomizer' => [true, false],
      'contentLayout' => ['compact', 'wide'],
      'headerType' => ['fixed', 'static'],
      'navbarType' => ['sticky', 'static', 'hidden'],
      'myTheme' => ['light', 'dark', 'system'],
      'mySkins' => ['default', 'bordered', 'raspberry'],
      'hasSemiDark' => [true, false],
      'myRTLMode' => [true, false],
      'menuFixed' => [true, false],
      'footerFixed' => [true, false],
      'customizerControls' => [],
      // 'defaultLanguage'=>array('en'=>'en','fr'=>'fr','de'=>'de','ar'=>'ar'),
    ];

    //if myLayout value empty or not match with default options in custom.php config file then set a default value
    foreach ($allOptions as $key => $value) {
      if (array_key_exists($key, $DefaultData)) {
        if (gettype($DefaultData[$key]) === gettype($data[$key])) {
          // data key should be string
          if (is_string($data[$key])) {
            // data key should not be empty
            if (isset($data[$key]) && $data[$key] !== null) {
              // data key should not be exist inside allOptions array's sub array
              if (!array_key_exists($data[$key], $value)) {
                // ensure that passed value should be match with any of allOptions array value
                $result = array_search($data[$key], $value, 'strict');
                if (empty($result) && $result !== 0) {
                  $data[$key] = $DefaultData[$key];
                }
              }
            } else {
              // if data key not set or
              $data[$key] = $DefaultData[$key];
            }
          }
        } else {
          $data[$key] = $DefaultData[$key];
        }
      }
    }
    $themeVal = $data['myTheme'] == "dark" ? "dark" : "light";
    $themeUpdatedVal = $data['myTheme'] == "dark" ? "dark" : $data['myTheme'];

    // Determine if the layout is admin or front based on template name
    $layoutName = $data['myLayout'];
    $isAdmin = !Str::contains($layoutName, 'front');

    $modeCookieName = $isAdmin ? 'admin-mode' : 'front-mode';
    $colorPrefCookieName = $isAdmin ? 'admin-colorPref' : 'front-colorPref';
    $primaryColorCookieName = $isAdmin ? 'admin-primaryColor' : 'front-primaryColor';

    // Get primary color from custom.php if explicitly set
    $primaryColor = null;
    if (array_key_exists('primaryColor', $data)) {
      $primaryColor = $data['primaryColor'];
    }

    // Check for primary color in cookie
    if (isset($_COOKIE[$primaryColorCookieName])) {
      $primaryColor = $_COOKIE[$primaryColorCookieName];
    }

    // Determine style based on cookies, only if not 'blank-layout'
    if ($layoutName !== 'blank') {
      if (isset($_COOKIE[$modeCookieName])) {
        $themeVal = $_COOKIE[$modeCookieName];
        if ($themeVal === 'system') {
          $themeVal = isset($_COOKIE[$colorPrefCookieName]) ? $_COOKIE[$colorPrefCookieName] : 'light';
        }
        $themeUpdatedVal = $_COOKIE[$modeCookieName];
      }
    }

    // Define standardized cookie names
    $skinCookieName = 'customize_skin';
    $semiDarkCookieName = 'customize_semi_dark';

    // Process skin and semi-dark settings only for admin layouts
    if ($isAdmin) {
      // Get skin from cookie or fall back to config
      $skinFromCookie = isset($_COOKIE[$skinCookieName]) ? $_COOKIE[$skinCookieName] : null;
      $configSkin = isset($data['mySkins']) ? $data['mySkins'] : 'default';
      $skinName = $skinFromCookie ?: $configSkin;

      // Get semi-dark setting from cookie or fall back to config
      $semiDarkFromCookie = isset($_COOKIE[$semiDarkCookieName]) ? $_COOKIE[$semiDarkCookieName] : null;
      // Ensure we have a proper boolean conversion
      $semiDarkEnabled = $semiDarkFromCookie !== null ?
        filter_var($semiDarkFromCookie, FILTER_VALIDATE_BOOLEAN) :
        (bool)$data['hasSemiDark'];
    } else {
      // For front-end layouts, use defaults
      $skinName = 'default';
      $semiDarkEnabled = false;
    }

    // Get menu Collapsed state from cookie or fall back to config
    $menuCollapsedFromCookie = isset($_COOKIE['LayoutCollapsed']) ? $_COOKIE['LayoutCollapsed'] : $data['menuCollapsed'];

    // Get content layout from cookie or fall back to config
    $contentLayoutFromCookie = isset($_COOKIE['contentLayout']) ? $_COOKIE['contentLayout'] : $data['contentLayout'];

    // Get header type from cookie or fall back to config
    $navbarTypeFromCookie = isset($_COOKIE['navbarType']) ? $_COOKIE['navbarType'] : $data['navbarType'];

    // Get Header type from cookie or fall back to config
    $headerTypeFromCookie = isset($_COOKIE['headerType']) ? $_COOKIE['headerType'] : $data['headerType'];

    $directionVal = isset($_COOKIE['direction']) ? ($_COOKIE['direction'] === 'true' ? 'rtl' : 'ltr') : $data['myRTLMode'];

    //layout classes
    $layoutClasses = [
      'layout' => $data['myLayout'],
      'skins' => $data['mySkins'],
      'skinName' => $skinName,
      'semiDark' => $semiDarkEnabled,
      'color' => $primaryColor,
      'theme' => $themeVal,
      'themeOpt' => $data['myTheme'],
      'themeOptVal' => $themeUpdatedVal,
      'rtlMode' => $data['myRTLMode'],
      'textDirection' => $directionVal,
      'menuCollapsed' => $menuCollapsedFromCookie,
      'hasCustomizer' => $data['hasCustomizer'],
      'showDropdownOnHover' => $data['showDropdownOnHover'],
      'displayCustomizer' => $data['displayCustomizer'],
      'contentLayout' => $contentLayoutFromCookie,
      'headerType' => $headerTypeFromCookie,
      'navbarType' => $navbarTypeFromCookie,
      'menuFixed' => $data['menuFixed'],
      'footerFixed' => $data['footerFixed'],
      'customizerControls' => $data['customizerControls'],
      'menuAttributes' => self::getMenuAttributes($semiDarkEnabled),
    ];

    // sidebar Collapsed
    if ($layoutClasses['menuCollapsed'] === 'true' || $layoutClasses['menuCollapsed'] === true) {
      $layoutClasses['menuCollapsed'] = 'layout-menu-collapsed';
    } else {
      $layoutClasses['menuCollapsed'] = '';
    }

    // Header Type
    if ($layoutClasses['headerType'] == 'fixed') {
      $layoutClasses['headerType'] = 'layout-menu-fixed';
    }
    // Navbar Type
    if ($layoutClasses['navbarType'] == 'sticky') {
      $layoutClasses['navbarType'] = 'layout-navbar-fixed';
    } elseif ($layoutClasses['navbarType'] == 'static') {
      $layoutClasses['navbarType'] = '';
    } else {
      $layoutClasses['navbarType'] = 'layout-navbar-hidden';
    }

    // Menu Fixed
    if ($layoutClasses['menuFixed'] == true) {
      $layoutClasses['menuFixed'] = 'layout-menu-fixed';
    }


    // Footer Fixed
    if ($layoutClasses['footerFixed'] == true) {
      $layoutClasses['footerFixed'] = 'layout-footer-fixed';
    }

    // RTL Layout/Mode
    if ($layoutClasses['rtlMode'] == true) {
      $layoutClasses['rtlMode'] = 'rtl';
      $layoutClasses['textDirection'] = isset($_COOKIE['direction']) ? ($_COOKIE['direction'] === 'true' ? 'rtl' : 'ltr') : 'rtl';
    } else {
      $layoutClasses['rtlMode'] = 'ltr';
      $layoutClasses['textDirection'] = isset($_COOKIE['direction']) && $_COOKIE['direction'] === 'true' ? 'rtl' : 'ltr';
    }

    // Show DropdownOnHover for Horizontal Menu
    if ($layoutClasses['showDropdownOnHover'] == true) {
      $layoutClasses['showDropdownOnHover'] = true;
    } else {
      $layoutClasses['showDropdownOnHover'] = false;
    }

    // To hide/show display customizer UI, not js
    if ($layoutClasses['displayCustomizer'] == true) {
      $layoutClasses['displayCustomizer'] = true;
    } else {
      $layoutClasses['displayCustomizer'] = false;
    }

    return $layoutClasses;
  }

  public static function updatePageConfig($pageConfigs)
  {
    $demo = 'custom';
    if (isset($pageConfigs)) {
      if (count($pageConfigs) > 0) {
        foreach ($pageConfigs as $config => $val) {
          Config::set('custom.' . $demo . '.' . $config, $val);
        }
      }
    }
  }

  /**
   * Generate CSS for primary color
   *
   * @param string $color Hex color code for primary color
   * @return string CSS for primary color
   */
  public static function generatePrimaryColorCSS($color)
  {
    if (!$color) return '';

    // Check if the color actually came from a cookie or explicit configuration
    // Don't generate CSS if there's no specific need for a custom color
    $configColor = config('custom.custom.primaryColor', null);
    $isFromCookie = isset($_COOKIE['admin-primaryColor']) || isset($_COOKIE['front-primaryColor']);

    if (!$configColor && !$isFromCookie) return '';

    $r = hexdec(substr($color, 1, 2));
    $g = hexdec(substr($color, 3, 2));
    $b = hexdec(substr($color, 5, 2));

    // Calculate contrast color based on YIQ formula
    $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
    $contrastColor = ($yiq >= 150) ? '#000' : '#fff';

    return <<<CSS
:root, [data-bs-theme=light], [data-bs-theme=dark] {
  --bs-primary: {$color};
  --bs-primary-rgb: {$r}, {$g}, {$b};
  --bs-primary-bg-subtle: rgba({$r}, {$g}, {$b}, 0.1);
  --bs-primary-border-subtle: rgba({$r}, {$g}, {$b}, 0.3);
  --bs-primary-contrast: {$contrastColor};
}
CSS;
  }

  public static function getTitleMenu($nama_path)
  {
    $detail_menu = Menu::where('url_menu',$nama_path)->first();
    return $detail_menu->custom_title ?? '';
  }

  public static function AuthIsPerm($priv): bool
  {
      // 1. Cek apakah user sudah login
      if (!Auth::check()) {
        return false;
      }

      // 3. Ambil User ID & URL Params
      $userId = Auth::id();
      
      // Mengambil segment 1 dan 2 (misal: customer-service/kendaraan)
      $params = request()->path();
      
      // 4. Query Database (Menggunakan Query Builder / Explicit Joins)
      // Logika: Cek apakah ada menu yang cocok dengan URL dan user memiliki akses via group
      $query = DB::table('menu as m')
          ->join('group_detail as gd', 'gd.menuid', '=', 'm.id')
          ->join('group as g', 'g.id', '=', 'gd.groupid') // Asumsi 'pid' adalah link ke group id
          ->join('users_group as ug', 'ug.groupid', '=', 'g.id')
          ->where('ug.userid', $userId)
          ->where('m.url_menu', 'LIKE', "%{$params}%");
          // ->where('m.active', 1) // Uncomment jika perlu filter aktif

      if ($priv == "list") {
        $query = $query->where('gd.isList', '1');
      } elseif ($priv == "add") {
        $query = $query->where('gd.isAdd', '1');
      } elseif ($priv == "edit") {
        $query = $query->where('gd.isEdit', '1');
      } elseif ($priv == "delete") {
        $query = $query->where('gd.isDelete', '1');
      }

      $exists = $query->exists(); // Mengembalikan true jika data ditemukan, false jika tidak

      return $exists;
  }

  public static function getNomorTransaksi($cabang, $modul)
  {
    $dtnomor = PenomoranTransaksi::where('kode_cabang', $cabang)->where('modul', $modul)->first();

    $bln = date("m");
    $thn = date("Y");
    
    $tahunawal = $dtnomor->tahun;
    $bulanawal = $dtnomor->bulan;
    $autoreset = $dtnomor->autoreset;
    if($autoreset == "tahun") {
      $num = ($tahunawal <> $thn) ? 1 : $dtnomor->nourut + 1;
    } else {
      $num = ($bulanawal <> $bln) ? 1 : $dtnomor->nourut + 1;
    }

    $cnt = str_pad(($num), $dtnomor->digit_cnt, "0", STR_PAD_LEFT);
    // $cnt = str_pad(($dtnomor->nourut + 1), $dtnomor->digit_cnt, "0", STR_PAD_LEFT);

    $segmen1 = $dtnomor->segmen1;
    $segmen2 = $dtnomor->segmen2;
    $segmen3 = $dtnomor->segmen3;
    $segmen4 = $dtnomor->segmen4;
    $segmen5 = $dtnomor->segmen5;
    $segmen6 = $dtnomor->segmen6;
    $segmen7 = $dtnomor->segmen7;

    $nomor = sprintf("%s%s%s%s%s%s%s", $segmen1, $segmen2, $segmen3, $segmen4, $segmen5, $segmen6, $segmen7);

    $bln = date("m");
    $thn = date("y");
    $blnthn = date("m").date("Y");

    $nomor = str_replace("[BLN]", $bln, $nomor);
    $nomor = str_replace("[THN]", $thn, $nomor);
    $nomor = str_replace("[BLNTHN]", $blnthn, $nomor);
    $nomor = str_replace("[DEPT]", $dtnomor->cabang, $nomor);
    $nomor = str_replace("[BANK]", $dtnomor->cabang, $nomor);
    $nomor = str_replace("[BANK]", $dtnomor->bank, $nomor);
    $nomor = str_replace("[MODUL]", $dtnomor->modul, $nomor);
    $nomor = str_replace("[CNT]", $cnt, $nomor);

    return $nomor ?? '';
  }

  public static function updateNomorTransaksi($cabang, $modul, $nomor='')
  {
    $dtnomor = PenomoranTransaksi::where('kode_cabang', $cabang)->where('modul', $modul)->first();

    $bln = date("m");
    $thn = date("Y");
    
    $tahunawal = $dtnomor->tahun;
    $bulanawal = $dtnomor->bulan;
    $autoreset = $dtnomor->autoreset;
    if($autoreset == "tahun") {
      $nourut = ($tahunawal <> $thn) ? 1 : $dtnomor->nourut + 1;
    } else {
      $nourut = ($bulanawal <> $bln) ? 1 : $dtnomor->nourut + 1;
    }

    $contoh = (strlen($nomor)) ? $nomor : $dtnomor->contoh;

    // if ($dtnomor) {
    //   $nourut = $dtnomor->nourut + 1;
    // } else {
    //   $nourut = 1;
    // }

    $result = PenomoranTransaksi::where('kode_cabang', $cabang)
        ->where('modul', $modul)
        ->update([
          'nourut' => $nourut,
          'contoh' => $contoh,
          'bulan' => $bln,
          'tahun' => $thn,
        ]);

    return $result;
  }

  public static function getNomorVoucher($cabang, $bank, $doc, $jenis='')
  {
    DB::statement('CALL sp_generate_no_voucher(?, ?, ?, ?, @nomor_baru)', [
      $cabang,
      $bank,
      $doc,
      $jenis
    ]);

    $results = DB::select('SELECT @nomor_baru AS nomor_voucher');

    $nomorVoucherFinal = $results[0]->nomor_voucher;
    
    return $nomorVoucherFinal;
  }

  public static function listMonths()
  {
    return [
      1 => 'Januari',
      2 => 'Februari',
      3 => 'Maret',
      4 => 'April',
      5 => 'Mei',
      6 => 'Juni',
      7 => 'Juli',
      8 => 'Agustus',
      9 => 'September',
      10 => 'Oktober',
      11 => 'November',
      12 => 'Desember'
    ];
  }

  public static function listYears($backwards = 5, $forwards = 0)
  {
    $currentYear = date('Y');
    $startYear = $currentYear - $backwards;
    $endYear = $currentYear + $forwards;
    
    $years = [];
    // Loop dari tahun terlama ke terbaru (atau sebaliknya tinggal tukar posisi)
    for ($i = $endYear; $i >= $startYear; $i--) {
        $years[$i] = $i;
    }

    return $years;
  }

  public static function terbilang($angka)
  {
    $angka = abs((float)$angka);
    $baca  = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
    $terbilang = "";

    if ($angka < 12) {
        $terbilang = " " . $baca[(int)$angka];
    } else if ($angka < 20) {
        $terbilang = self::terbilang($angka - 10) . " belas";
    } else if ($angka < 100) {
        $terbilang = self::terbilang($angka / 10) . " puluh" . self::terbilang($angka % 10);
    } else if ($angka < 200) {
        $terbilang = " seratus" . self::terbilang($angka - 100);
    } else if ($angka < 1000) {
        $terbilang = self::terbilang($angka / 100) . " ratus" . self::terbilang($angka % 100);
    } else if ($angka < 2000) {
        $terbilang = " seribu" . self::terbilang($angka - 1000);
    } else if ($angka < 1000000) {
        $terbilang = self::terbilang($angka / 1000) . " ribu" . self::terbilang($angka % 1000);
    } else if ($angka < 1000000000) {
        $terbilang = self::terbilang($angka / 1000000) . " juta" . self::terbilang(fmod($angka, 1000000));
    } else if ($angka < 1000000000000) {
        $terbilang = self::terbilang($angka / 1000000000) . " miliar" . self::terbilang(fmod($angka, 1000000000));
    } else if ($angka < 1000000000000000) {
        $terbilang = self::terbilang($angka / 1000000000000) . " triliun" . self::terbilang(fmod($angka, 1000000000000));
    }

    return $terbilang;
  }

  public static function terbilang_rupiah($angka)
  {
    $hasil = trim(self::terbilang($angka));
    if ($hasil == "") {
        return "NOL RUPIAH";
    }
    return strtoupper($hasil) . " RUPIAH";
  }
}
