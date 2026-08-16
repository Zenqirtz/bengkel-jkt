<?php
namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\Spk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotifikasiController extends Controller
{
  private function queryNotifikasi()
  {
    $user = Auth::user();
    $userLevel = $user->user_level ?? null;
    $kdCabang = session('kd_cabang');

    return Notifikasi::where(function ($q) use ($user, $userLevel, $kdCabang) {
      // Notifikasi personal
      $q->where('user_id', $user->id)
        // ATAU broadcast sesuai cabang
        ->orWhere(function ($q2) use ($userLevel, $kdCabang) {
          $q2->whereNull('user_id')
            ->where('kode_cabang', $kdCabang)
            ->where(function ($q3) use ($userLevel) {
              // target_level null = semua level dapat
              // target_level = userLevel = level tertentu dapat
              $q3->whereNull('target_level')
                ->orWhere('target_level', $userLevel);
            });
        });
    });
  }
  public function index()
  {
    $notifikasi = $this->queryNotifikasi()
      ->orderBy('created_at', 'desc')
      ->take(10)
      ->get()
      ->map(function ($n) {
        return [
          'id' => $n->id,
          'title' => $n->title,
          'message' => $n->message,
          'url' => $n->url,
          'is_read' => $n->is_read,
          'count' => $n->count ?? 1,  // ← tambah ini
          'time_ago' => $n->created_at->diffForHumans(),
        ];
      });

    $unread = $this->queryNotifikasi()
      ->where('is_read', false)
      ->sum('count');  // ← sum, bukan count();

    return response()->json([
      'data' => $notifikasi,
      'unread' => $unread,
    ]);
  }

  public function markRead($id)
  {
    $notif = Notifikasi::where('id', $id)
      ->where(function ($q) {
        $q->where('user_id', Auth::id())
          ->orWhereNull('user_id');
      })
      ->first();

    if ($notif) {
      $notif->update(['is_read' => true]);
    }

    return response()->json(['success' => true]);
  }

  public function markAllRead()
  {
    $this->queryNotifikasi()
      ->where('is_read', false)
      ->update(['is_read' => true]);

    return response()->json(['success' => true]);
  }

  public function semua()
  {
    $notifikasi = $this->queryNotifikasi()
      ->orderBy('created_at', 'desc')
      ->paginate(20);

    $this->queryNotifikasi()
      ->where('is_read', false)
      ->update(['is_read' => true]);

    return view('content.notifikasi.notifikasi-detail', compact('notifikasi'));
  }

  // ↓ Digest harian — dipanggil via route atau scheduler
  public function kirimDigestHarian()
  {
    $cabangList = DB::table('m_cabang')->get();

    foreach ($cabangList as $cabang) {
      $totalSpkHariIni = Spk::where('kode_cabang', $cabang->kode_cabang)
        ->whereDate('tgl_masuk', today())
        ->count();

      $totalPending = DB::table('v_warning_turun_lapangan')
        ->where('kode_cabang', $cabang->kode_cabang)
        ->count();

      $totalEstBelumKirim = DB::table('v_rep_estimasi_belum_dikirim')
        ->where('kode_cabang', $cabang->kode_cabang)
        ->count();

      if ($totalSpkHariIni > 0 || $totalPending > 0 || $totalEstBelumKirim > 0) {
        Notifikasi::create([
          'user_id' => null,
          'target_level' => 'UL03',
          'kode_cabang' => $cabang->kode_cabang,
          'tipe' => 'digest',
          'title' => 'Ringkasan Harian — ' . $cabang->nama_cabang,
          'message' => "SPK masuk hari ini: {$totalSpkHariIni} | Pending: {$totalPending} | Estimasi belum kirim: {$totalEstBelumKirim}",
          'url' => url('home'),
          'is_read' => false,
        ]);
      }
    }

    return response()->json(['success' => true, 'message' => 'Digest harian berhasil dikirim.']);
  }
}
