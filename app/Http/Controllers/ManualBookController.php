<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\ManualBook;
use App\Models\LogActivity;
use Illuminate\Support\Facades\DB;

class ManualBookController extends Controller
{
  private function isAuthorized(): bool
  {
    $userId = Auth::id();

    return DB::table('users')
      ->where('id', $userId)
      ->where('user_level', 'UL03')
      ->exists();
  }

  /**
   * Cek apakah manual book global sudah ada.
   * Dipanggil dari JS setiap kali dropdown dibuka.
   */
  // public function cek(): JsonResponse
  // {
  //   $data = ManualBook::select('id', 'nama_file', 'ukuran')->first();

  //   return response()->json([
  //     'status' => true,
  //     'ada' => (bool) $data,
  //     'data' => $data,
  //   ]);
  // }
  public function cek(): JsonResponse
  {
    $data = ManualBook::select('id', 'nama_file', 'ukuran')->first();

    return response()->json([
      'status' => true,
      'ada' => (bool) $data,
      'data' => $data,
      'can_manage' => $this->isAuthorized(), // ← tambah ini
    ]);
  }

  /**
   * Upload manual book global — hanya boleh 1 file,
   * berlaku untuk semua cabang. Harus hapus dulu kalau mau ganti.
   */
  // public function store(Request $request): JsonResponse
  // {
  //   $sudahAda = ManualBook::exists();
  //   if ($sudahAda) {
  //     return response()->json([
  //       'status' => false,
  //       'message' => 'Manual book sudah ada. Hapus dahulu sebelum upload baru.',
  //     ], 200);
  //   }
  public function store(Request $request): JsonResponse
  {
    if (!$this->isAuthorized()) {
      return response()->json([
        'status' => false,
        'message' => 'Anda tidak memiliki akses untuk upload manual book.',
      ], 403);
    }

    $rules = [
      'file' => 'required|mimes:pdf|max:20480',
    ];
    $messages = [
      'file.required' => 'File PDF wajib diisi.',
      'file.mimes' => 'File harus berformat PDF.',
      'file.max' => 'Ukuran file maksimal 20 MB.',
    ];

    $validator = Validator::make($request->all(), $rules, $messages);
    if ($validator->fails()) {
      return response()->json([
        'status' => false,
        'message' => 'Gagal menyimpan data.',
        'errors' => $validator->errors(),
      ], 200);
    }

    try {
      $file = $request->file('file');
      $binary = file_get_contents($file->getRealPath());

      $data = [
        'nama_file' => $file->getClientOriginalName(),
        'file_pdf' => $binary,
        'ukuran' => $file->getSize(),
        'created_by' => Auth::user()->username,
      ];

      $ok = ManualBook::create($data);

      $desc = $ok ? 'Berhasil upload manual book' : 'Gagal upload manual book';
      unset($data['file_pdf']);
      LogActivity::saveLogActivity($desc, $data);

      return response()->json([
        'status' => (bool) $ok,
        'message' => $desc,
        'data' => $ok ? [
          'id' => $ok->id,
          'nama_file' => $ok->nama_file,
          'ukuran' => $ok->ukuran,
        ] : null,
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
      ], 200);
    }
  }

  /**
   * Hapus manual book global.
   */
  // public function destroy(): JsonResponse
  // {
  //   $record = ManualBook::first();
  //   if (!$record) {
  //     return response()->json([
  //       'status' => false,
  //       'message' => 'Manual book tidak ditemukan.',
  //     ], 200);
  //   }
  public function destroy(): JsonResponse
  {
    if (!$this->isAuthorized()) {
      return response()->json([
        'status' => false,
        'message' => 'Anda tidak memiliki akses untuk menghapus manual book.',
      ], 403);
    }

    $record = ManualBook::first(); // ← ini yang kurang
    if (!$record) {
      return response()->json([
        'status' => false,
        'message' => 'Manual book tidak ditemukan.',
      ], 200);
    }

    $log = $record->only(['id', 'nama_file', 'ukuran']);
    $ok = $record->delete();

    $desc = $ok ? 'Berhasil hapus manual book' : 'Gagal hapus manual book';
    LogActivity::saveLogActivity($desc, $log);

    return response()->json([
      'status' => (bool) $ok,
      'message' => $desc,
    ]);
  }

  /**
   * Stream isi PDF mentah (dipanggil oleh PDF.js di halaman flipbook).
   */
  public function view()
  {
    $record = ManualBook::firstOrFail();

    return response($record->file_pdf)
      ->header('Content-Type', 'application/pdf')
      ->header('Content-Disposition', 'inline; filename="' . $record->nama_file . '"')
      ->header('Cache-Control', 'no-store');
  }

  /**
   * Download manual book sebagai file PDF.
   */
  public function download()
  {
    $record = ManualBook::firstOrFail();

    return response($record->file_pdf)
      ->header('Content-Type', 'application/pdf')
      ->header('Content-Disposition', 'attachment; filename="' . $record->nama_file . '"')
      ->header('Cache-Control', 'no-store');
  }
  /**
   * Halaman flipbook — dibuka di tab baru lewat ikon mata.
   */
  public function flipbook()
  {
    return view('content.manual-book.flipbook');
  }
}
