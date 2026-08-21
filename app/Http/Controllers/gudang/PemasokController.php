<?php

namespace App\Http\Controllers\gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Pemasok;
use App\Models\Parameter;
use App\Models\LogActivity;

use App\Helpers\Helpers as Helper;

class PemasokController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function Pemasok(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Data Pemasok';

    $user_cabang = session('kd_cabang');
    $status_aktif = Parameter::query()->where('nama_tabel', 'STATUS')->orderBy('no_urut', 'asc')->get();

    ## Log Activity
    $desc = "View " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.gudang.pemasok', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'status_aktif' => $status_aktif,
    ]);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request): JsonResponse
  {
    $user_cabang = session('kd_cabang');

    $columns = [
      1 => 'a.id',
      2 => 'a.nama_pemasok',
      3 => 'a.npwp',
      4 => 'a.alamat1',
      5 => 'a.kota',
      6 => 'a.kode_pos',
    ];

    $limit = (int) $request->input('length', 10);
    $start = (int) $request->input('start', 0);
    $order = $columns[$request->input('order.0.column')] ?? 'a.id';
    $dir   = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    // Base query
    $base = DB::table('m_pemasok as a')
    ->leftJoin('parameter as b', function ($join) {
      $join->on('b.kode', '=', 'a.is_active')
          ->where('b.nama_tabel', '=', 'STATUS'); // syarat di JOIN
    })
    ->where('a.kode_cabang', $user_cabang);

    // Total baris tanpa filter
    $totalData = (clone $base)->count('a.id');

    // Filtering (search global)
    $query = (clone $base);
    // if ($search = trim((string) $request->input('search.value'))) {
    //     $query->where(function ($q) use ($search) {
    //         $q->where('p.id', 'like', "%{$search}%")
    //           ->orWhere('p.nama_pemasok', 'like', "%{$search}%")
    //           ->orWhere('p.npwp', 'like', "%{$search}%")
    //           ->orWhere('p.alamat1', 'like', "%{$search}%")
    //           ->orWhere('p.kota', 'like', "%{$search}%")
    //           ->orWhere('p.kode_pos', 'like', "%{$search}%");
    //     });
    // }
    if ($request->filled('nama_pemasok')) {
      $query->where('a.nama_pemasok', 'like', '%' . $request->nama_pemasok . '%');
    }
    if ($request->filled('telepon')) {
      $query->where('a.telepon', 'like', '%' . $request->telepon . '%');
    }
    if ($request->filled('npwp')) {
      $query->where('a.npwp', 'like', '%' . $request->npwp . '%');
    }
    if ($request->filled('alamat1')) {
      $query->where('a.alamat1', 'like', '%' . $request->alamat1 . '%');
    }
    if ($request->filled('kontak_person')) {
      $query->where('a.kontak_person', 'like', '%' . $request->kontak_person . '%');
    }
    if ($request->filled('is_active')) {
      if ($request->is_active <> 'all') {
        $query->where('a.is_active', 'like', '%' . $request->is_active . '%');
      }
    }

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('a.id');

    // Ambil data halaman saat ini
    $datas = $query
      ->select([
          'a.id',
          'a.nama_pemasok',
          'a.npwp',
          'a.alamat1',
          'a.kota',
          'a.kode_pos',
          'a.po_box',
          'a.telepon',
          'a.fax',
          'a.email',
          'a.kontak_person',
          'a.file_npwp',
          'a.is_active',
          'b.keterangan as status_aktif',
      ])
      ->orderBy($order, $dir)
      ->offset($start)
      ->limit($limit)
      ->get();

    // Susun payload DataTables
    $dest = public_path('assets/img/pemasok');
    $data = [];
    $fake = $start;
    foreach ($datas as $row) {
      $npwpPath = $dest.DIRECTORY_SEPARATOR.$row->file_npwp;
      $file_npwp = (is_file($npwpPath)) ? "1" : "0";

      $data[] = [
          'id'            => $row->id,
          'fake_id'       => ++$fake,
          'nama_pemasok'  => $row->nama_pemasok,
          'npwp'          => $row->npwp,
          'alamat1'       => $row->alamat1,
          'kota'          => $row->kota,
          'kode_pos'      => $row->kode_pos,
          'po_box'        => $row->po_box,
          'telepon'       => $row->telepon,
          'fax'           => $row->fax,
          'email'         => $row->email,
          'kontak_person' => $row->kontak_person,
          'file_npwp'     => $file_npwp,
          'is_active'     => $row->is_active,
          'status_aktif'  => $row->status_aktif,
      ];
    }

    // Always return full DataTables structure, even if no results
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
    $dataID = $request->id;

    if ($dataID) {
      $result = Pemasok::findOrFail($dataID);

      $rules = [
        'nama_pemasok' => [
            'required',
            'string',
            'max:50',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_pemasok', 'nama_pemasok')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
                })
                ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'alamat1' => 'required',
        'is_active' => 'required',
        // 'npwp' => 'required',
        'telepon' => ['required', 'max:15'],
        'file_npwp' => ['nullable', 'mimes:jpg,jpeg,png,pdf', 'max:350'], // 350 KB
      ];
      
      $messages = [
        'nama_pemasok.required' => 'Nama Pemasok Wajib diisi',
        'nama_pemasok.unique' => 'Nama Pemasok sudah digunakan',
        'alamat1.required' => 'Alamat Wajib diisi',
        'telepon.required'  => 'Telepon Selular Wajib diisi',
        'is_active.required'  => 'Status Aktif Wajib diisi',
        // 'npwp.required'  => 'Nomor NPWP Wajib diisi',
        'file_npwp.mimes' => 'Format file harus pdf, jpg, jpeg, atau png.',
        'file_npwp.max' => 'Ukuran fotfileo maksimal 350 KB.',
      ];
  
      $validator = Validator::make($request->all(), $rules, $messages);
      
      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ]);
      }
      
      // update the value
      $data = [
        'kode_cabang' => $request->kode_cabang,
        'nama_pemasok' => $request->nama_pemasok,
        'npwp' => $request->npwp,
        'alamat1' => $request->alamat1,
        'kota' => $request->kota,
        'kode_pos' => $request->kode_pos,
        'telepon' => $request->telepon,
        'fax' => $request->fax,
        'email' => $request->email,
        'kontak_person' => $request->kontak_person,
        'is_active' => $request->is_active,
        'updated_by' => Auth::user()->username
      ];
  
      if ($request->hasFile('file_npwp')) {
        $file = $request->file('file_npwp');
  
        $dest = public_path('assets/img/pemasok');
        if (!is_dir($dest)) {
            @mkdir($dest, 0775, true);
        }
  
        $filename = Str::slug('npwp-'.$data['npwp']).'-'.time().'.'.$file->getClientOriginalExtension();
        $file->move($dest, $filename);
        $data['file_npwp'] = $filename;
  
        // hapus foto lama jika ada dan berbeda
        $old = $request->input('old_file_npwp');
        if ($old && $old !== $filename) {
          $oldPath = $dest.DIRECTORY_SEPARATOR.$old;
          if (is_file($oldPath)) {
            @unlink($oldPath);
          }
        }
      }
  
      $ok = $result->update($data);
  
      ## Log Activity
      $desc = $ok ? 'Berhasil Ubah Data Pemasok' : 'Gagal Ubah Data Pemasok';
      LogActivity::saveLogActivity($desc, $data);
  
      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
    } else {
      $rules = [
        'nama_pemasok' => [
            'required',
            'string',
            'max:50',
            // Validasi Unik Kombinasi (nama_pemilik + kode_cabang)
            Rule::unique('m_pemasok', 'nama_pemasok')
                ->where(function ($query) use ($request) {
                  return $query
                    ->where('kode_cabang', $request->kode_cabang);   // Cek Kolom 2
                })
                ->ignore($dataID), // Penting: Abaikan ID sendiri saat update
        ],
        'alamat1' => 'required',
        'is_active' => 'required',
        // 'npwp' => 'required',
        'telepon' => ['required', 'max:15'],
        'file_npwp' => ['nullable', 'mimes:jpg,jpeg,png,pdf', 'max:350'], // 350 KB
      ];
      
      $messages = [
        'nama_pemasok.required' => 'Nama Pemasok Wajib diisi',
        'nama_pemasok.unique' => 'Nama Pemasok sudah digunakan',
        'alamat1.required' => 'Alamat Wajib diisi',
        'telepon.required'  => 'Telepon Selular Wajib diisi',
        'is_active.required'  => 'Status Aktif Wajib diisi',
        // 'npwp.required'  => 'Nomor NPWP Wajib diisi',
        'file_npwp.mimes' => 'Format file harus pdf, jpg, jpeg, atau png.',
        'file_npwp.max' => 'Ukuran fotfileo maksimal 350 KB.',
      ];
  
      $validator = Validator::make($request->all(), $rules, $messages);
  
      if ($validator->fails()) {
        return response()->json([
          'status' => false,
          'message' => "Gagal menyimpan data.",
          'errors' => $validator->errors()
        ]);
      }
  
      $lastNum = Pemasok::query()->where('kode_cabang', $request->kode_cabang)->max(DB::raw('CAST(SUBSTRING(kode_pemasok, 2) AS UNSIGNED)')) ?? 0;
      $kode = sprintf('%05d', $lastNum + 1);
  
      $data = [
        'kode_pemasok' => $kode,
        'kode_cabang' => $request->kode_cabang,
        'nama_pemasok' => $request->nama_pemasok,
        'npwp' => $request->npwp,
        'alamat1' => $request->alamat1,
        'kota' => $request->kota,
        'kode_pos' => $request->kode_pos,
        'telepon' => $request->telepon,
        'fax' => $request->fax,
        'email' => $request->email,
        'kontak_person' => $request->kontak_person,
        'is_active' => $request->is_active,
        'created_by' => Auth::user()->username
      ];
  
      if ($request->hasFile('file_npwp')) {
        $file = $request->file('file_npwp');
  
        // Pastikan folder ada
        $dest = public_path('assets/img/pemasok');
        if (!is_dir($dest)) {
          @mkdir($dest, 0775, true);
        }
  
        // Nama file unik
        $filename = Str::slug('npwp-'.$data['npwp']).'-'.time().'.'.$file->getClientOriginalExtension();
  
        // Pindahkan file
        $file->move($dest, $filename);
  
        // Simpan hanya nama file (bukan full path) agar mudah dirender di front-end
        $data['file_npwp'] = $filename;
      }
  
      $ok = Pemasok::create($data);
  
      ## Log Activity
      $desc = $ok ? 'Berhasil Tambah Data Pemasok' : 'Gagal Tambah Data Pemasok';
      LogActivity::saveLogActivity($desc, $data);
  
      return response()->json([
        'status'  => (bool)$ok,
        'message' => $desc
      ]);
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
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id): JsonResponse
  {
    $data = Pemasok::findOrFail($id);
    return response()->json($data);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    // $result = Pemasok::findOrFail($id);

    // $rules = [
    //   'nama_pemasok' => 'required|string|max:50',
    //   'alamat1' => 'required',
    //   'telepon' => 'required|string|max:20',
    // ];

    // $messages = [
    //   'nama_pemasok.required' => 'Nama Supplier Wajib diisi',
    //   'alamat1.required'  => 'Alamat Wajib diisi',
    //   'telepon.required'  => 'Telepon Wajib diisi',
    // ];

    // $validator = Validator::make($request->all(), $rules, $messages);

    // if ($validator->fails()) {
    //   return response()->json([
    //     'status' => false,
    //     'message' => "Gagal menyimpan data.",
    //     'errors' => $validator->errors()
    //   ]);
    // }

    // $data = [
    //   'kode_cabang'    => $request->kode_cabang,
    //   'nama_pemasok'   => $request->nama_pemasok,
    //   'npwp'           => $request->npwp,
    //   'alamat1'        => $request->alamat1,
    //   'kota'           => $request->kota,
    //   'kode_pos'       => $request->kode_pos,
    //   'po_box'         => $request->po_box,
    //   'telepon'        => $request->telepon,
    //   'fax'            => $request->fax,
    //   'email'          => $request->email,
    //   'kontak_person'  => $request->kontak_person,
    //   'updated_by'     => Auth::user()->username,
    // ];

    // $ok = $result->update($data);

    // return response()->json([
    //   'status'  => (bool)$ok,
    //   'message' => $ok ? 'Data berhasil diubah.' : 'Gagal mengubah data.'
    // ]);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $result = Pemasok::findOrFail($id);
    if ($result) {
      $dest = public_path('assets/img/pemasok');
      $photo = $result->file_npwp;
      $photoPath = $dest.DIRECTORY_SEPARATOR.$photo;
      if (is_file($photoPath)) {
        @unlink($photoPath);
      }
    }

    $data = Pemasok::query()->where('id', $id)->first()->toArray();

    $ok = Pemasok::where('id', $id)->delete();

    ## Log Activity
    $desc = $ok ? 'Berhasil Hapus Data Data Pemasok' : 'Gagal Hapus Data Data Pemasok';
    LogActivity::saveLogActivity($desc, $data);
  }

  public function downloadFile(Request $request)
  {
    $id = $request->id;
    $tipe = $request->tipe;
    $result = Pemasok::find($id);
    if ($result) {
      $dest = public_path('assets/img/pemasok');
      $photo = $result->file_npwp;
      $photoPath = $dest.DIRECTORY_SEPARATOR.$photo;
      if (is_file($photoPath)) {
        ## Log Activity
        $data['id'] = $id;
        $data['tipe'] = $tipe;
        $data['file'] = $photo;
        $desc = 'Download File Pemasok';
        LogActivity::saveLogActivity($desc, $data);

        return response()->download($photoPath, $photo);
      }
    } else {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-found', ['pageConfigs' => $pageConfigs]);
    }
  }
}
