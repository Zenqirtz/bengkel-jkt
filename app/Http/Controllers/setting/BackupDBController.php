<?php

namespace App\Http\Controllers\setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Models\BackupDB;
use App\Models\LogActivity;
use Carbon\Carbon;


use App\Helpers\Helpers as Helper;

class BackupDBController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function BackupDB(): View
  {
    $isList = Helper::AuthIsPerm("list");
    $isAdd = Helper::AuthIsPerm("add");
    $isEdit = Helper::AuthIsPerm("edit");
    $isDel = Helper::AuthIsPerm("delete");
    if(!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path = request()->path();
    $title = Helper::getTitleMenu($path) ?? 'Backup DB';

    // $disk = Storage::disk('backups');
    // $files = $disk->files();

    // $backups = [];
    // foreach ($files as $file) {
    //     // Kita hanya ingin file .sql
    //     if (Str::endsWith($file, '.sql')) {
    //         $backups[] = [
    //             'file_name' => $file,
    //             'file_size' => $this->formatBytes($disk->size($file)),
    //             'last_modified' => date('Y-m-d H:i:s', $disk->lastModified($file)),
    //         ];
    //     }
    // }

    // // Urutkan berdasarkan tanggal modifikasi, terbaru di atas
    // usort($backups, function ($a, $b) {
    //     return strtotime($b['last_modified']) - strtotime($a['last_modified']);
    // });

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);


    return view('content.setting.backup-db', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      // 'backups' => $backups,
    ]);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request): JsonResponse
  {
    $columns = [
      1 => 'id',
      2 => 'file_backup',
      3 => 'file_size',
      4 => 'created_at',
    ];

    $totalData = BackupDB::count(); // Total records without filtering
    $totalFiltered = $totalData;

    $limit = $request->input('length');
    $start = $request->input('start');
    $order = $columns[$request->input('order.0.column')] ?? 'id';
    $dir = $request->input('order.0.dir') ?? 'desc';

    $query = BackupDB::query();

      // Search handling
    if (!empty($request->input('search.value'))) {
      $search = $request->input('search.value');

      $query->where(function ($q) use ($search) {
        $q->where('id', 'LIKE', "%{$search}%")
          ->orWhere('file_backup', 'LIKE', "%{$search}%");
      });

      $totalFiltered = $query->count();
    }

    $datas = $query->offset($start)
      ->limit($limit)
      ->orderBy($order, $dir)
      ->get();

    $data = [];
    $ids = $start;

    foreach ($datas as $row) {
      $data[] = [
        'id'          => $row->id,
        'fake_id'     => ++$ids,
        'file_backup' => $row->file_backup,
        'file_size' => $this->formatBytes($row->file_size),
        'created_at' => date("Y-m-d H:i:s", strtotime($row->created_at)),
      ];
    }

    // ✅ Always return full DataTables structure, even if no results
    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => intval($totalData),
      'recordsFiltered' => intval($totalFiltered),
      'data' => $data,
    ]);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    // Ambil kredensial database dari config
    $dbConfig = config('database.connections.mysql');
        
    $host = $dbConfig['host'];
    $port = $dbConfig['port'];
    $dbName = $dbConfig['database'];
    $user = $dbConfig['username'];
    $password = $dbConfig['password'];

    // Buat nama file unik
    $fileName = sprintf("%s_%s.sql", $dbName, date("YmdHis")); //'export_' . time() . '_' . Str::random(4) . '.sql';
    $filePath = Storage::disk('backups')->path($fileName);

    // Perintah mysqldump
    // Pastikan untuk menangani password dengan aman
    // Menggunakan array untuk process menghindari masalah shell escaping
    $command = [
        'mysqldump',
        '--host=' . $host,
        '--port=' . $port,
        '--user=' . $user,
        '--password=' . $password,
        $dbName,
    ];

    $process = new Process($command);

    try {
      // Jalankan proses dan simpan outputnya ke file
      $process->run();

      if ($process->isSuccessful()) {
          Storage::disk('backups')->put($fileName, $process->getOutput());

          $fileSize = Storage::disk('backups')->size($fileName);

          $data = [
            'file_backup' => $fileName,
            'file_size' => $fileSize,
            'created_by' => Auth::user()->username,
            'updated_by' => Auth::user()->username
          ];

          $ok = BackupDB::create($data);

          ## Log Activity
          $desc = $ok ? 'Berhasil Backup Database' : 'Gagal Backup Database';
          LogActivity::saveLogActivity($desc, $data);

          return response()->json([
            'status'  => (bool)$ok,
            'message' => $desc
          ]);

          // return response()->json(['status' => true, 'message' => "Database berhasil di-backup!"]);
      } else {
        // Tangani jika proses gagal
        throw new ProcessFailedException($process);
      }

    } catch (ProcessFailedException $exception) {
      // Hapus file jika ada kegagalan
      if (Storage::disk('backups')->exists($fileName)) {
          Storage::disk('backups')->delete($fileName);
      }

      ## Log Activity
      $desc = 'Gagal Backup Database: ' . $exception->getMessage();
      LogActivity::saveLogActivity($desc);
      
      // Berikan pesan error yang lebih spesifik
      return response()->json(['status' => false, 'message' => 'Backup gagal: ' . $exception->getMessage()]);
    }
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    $result = BackupDB::findOrFail($id);
    if ($result) {
      $fileName = $result->file_backup;
      if (Storage::disk('backups')->exists($fileName)) {
        ## Log Activity
        $data = BackupDB::query()->where('id', $id)->first()->toArray();
        $desc = 'Berhasil Download Database';
        LogActivity::saveLogActivity($desc, $data);

        return Storage::disk('backups')->download($fileName);
      }
    }
    return redirect('setting/backup-database')->with('error', 'File backup tidak ditemukan');
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id): JsonResponse
  {
    $data = BackupDB::findOrFail($id);
    return response()->json($data);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id) {}

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $result = BackupDB::findOrFail($id);
    if ($result) {
      $fileName = $result->file_backup;
      if (Storage::disk('backups')->exists($fileName)) {
        Storage::disk('backups')->delete($fileName);
      }
    }

    $data = BackupDB::query()->where('id', $id)->first()->toArray();

    $ok = BackupDB::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Backup Database' : 'Gagal Hapus Backup Database';
    LogActivity::saveLogActivity($desc, $data);
  }

  /**
   * Helper untuk format ukuran file
   */
  private function formatBytes($bytes, $precision = 2)
  {
      $units = ['B', 'KB', 'MB', 'GB', 'TB'];

      $bytes = max($bytes, 0);
      $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
      $pow = min($pow, count($units) - 1);

      $bytes /= (1 << (10 * $pow));

      return round($bytes, $precision) . ' ' . $units[$pow];
  }
}