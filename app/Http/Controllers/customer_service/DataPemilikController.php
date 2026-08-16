<?php

namespace App\Http\Controllers\customer_service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\DataPemilik;
use App\Models\Parameter;
use Carbon\Carbon;

class DataPemilikController extends Controller
{

  public function DataPemilik(): View
  {
    $jenis_pemilik = Parameter::query()->where('nama_tabel', 'JENIS_PEMILIK')->orderBy('no_urut', 'asc')->get();
    $agama = Parameter::query()->where('nama_tabel', 'AGAMA')->orderBy('no_urut', 'asc')->get();

    $title = "Data Pemilik";
    $user_cabang = session('kd_cabang');

    return view('content.customer-service.data-pemilik', [
      'title' => $title,
      'user_cabang' => $user_cabang,
      'jenis_pemilik' => $jenis_pemilik,
      'agama' => $agama
    ]);
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $user_cabang = session('kd_cabang');

    if ($request->ajax()) {
      try {
        $draw = $request->input('draw');
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');

        $query = DataPemilik::where('kode_cabang', $user_cabang);

        if (!empty($searchValue)) {
          $query->where(function ($q) use ($searchValue) {
            $q->where('nama_pemilik', 'like', "%{$searchValue}%")
              ->orWhere('alamat1', 'like', "%{$searchValue}%")
              ->orWhere('kota', 'like', "%{$searchValue}%")
              ->orWhere('telepon', 'like', "%{$searchValue}%")
              ->orWhere('handphone', 'like', "%{$searchValue}%")
              ->orWhere('email', 'like', "%{$searchValue}%");
          });
        }

        $totalRecords = DataPemilik::where('kode_cabang', $user_cabang)->count();
        $filteredRecords = $query->count();

        $data = $query->orderBy('id', 'desc')
          ->skip($start)
          ->take($length)
          ->select([
            'id',
            'kode_cabang',
            'nama_pemilik',
            'kode_jenis_pemilik',
            'alamat1',
            'alamat2',
            'kota',
            'telepon',
            'handphone',
            'email'
          ])
          ->get();

        $formattedData = $data->map(function ($row, $index) use ($start) {
          return [
            'id' => $row->id,
            'fake_id' => $start + $index + 1,
            'kode_cabang' => $row->kode_cabang,
            'nama_pemilik' => $row->nama_pemilik,
            'kode_jenis_pemilik' => $row->kode_jenis_pemilik,
            'alamat1' => $row->alamat1,
            'alamat2' => $row->alamat2,
            'kota' => $row->kota,
            'telepon' => $row->telepon,
            'handphone' => $row->handphone,
            'email' => $row->email,
            'action' => ''
          ];
        });

        return response()->json([
          'draw' => intval($draw),
          'recordsTotal' => $totalRecords,
          'recordsFiltered' => $filteredRecords,
          'data' => $formattedData
        ]);

      } catch (\Exception $e) {
        return response()->json([
          'draw' => 0,
          'recordsTotal' => 0,
          'recordsFiltered' => 0,
          'data' => [],
          'error' => $e->getMessage()
        ], 500);
      }
    }

    return $this->DataPemilik();
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    try {
      $id = $request->input('id');

      $rules = [
        'nama_pemilik' => 'required|string|max:100',
        'kode_jenis_pemilik' => 'required',
        'handphone' => 'required|string|max:20',
        'kode_cabang' => 'required',
        'no_identitas' => 'nullable|numeric|digits_between:1,20',
        'alamat' => 'nullable|string',
        'kota' => 'nullable|string|max:50',
        'kode_pos' => 'nullable|numeric|digits:5',
        'po_box' => 'nullable|string|max:20',
        'telepon' => 'nullable|string|max:20',
        'fax' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:100',
        'tgl_lahir' => 'nullable',
        'kode_agama' => 'nullable|string|max:10',
        'npwp' => 'nullable|numeric|digits_between:1,20',
        'file_identitas' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'file_npwp' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
      ];

      $messages = [
        'nama_pemilik.required' => 'Nama pemilik wajib diisi',
        'kode_jenis_pemilik.required' => 'Jenis pemilik wajib dipilih',
        'handphone.required' => 'Nomor HP wajib diisi',
        'no_identitas.numeric' => 'No. Identitas harus berupa angka',
        'no_identitas.digits_between' => 'No. Identitas maksimal 20 digit',
        'kode_pos.numeric' => 'Kode Pos harus berupa angka',
        'kode_pos.digits' => 'Kode Pos harus 5 digit',
        'npwp.numeric' => 'NPWP harus berupa angka',
        'npwp.digits_between' => 'NPWP maksimal 20 digit',
        'file_identitas.image' => 'File harus berupa gambar',
        'file_identitas.mimes' => 'Format file harus jpg, jpeg, atau png',
        'file_identitas.max' => 'Ukuran file maksimal 2MB',
        'file_npwp.image' => 'File harus berupa gambar',
        'file_npwp.mimes' => 'Format file harus jpg, jpeg, atau png',
        'file_npwp.max' => 'Ukuran file maksimal 2MB',
      ];

      $validator = Validator::make($request->all(), $rules, $messages);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Validasi gagal',
          'errors' => $validator->errors()
        ], 422);
      }

      $data = $request->all();

      // Mapping alamat ke alamat1
      if (isset($data['alamat'])) {
        $data['alamat1'] = $data['alamat'];
        unset($data['alamat']);
      }

      // Convert date format
      if (!empty($data['tgl_lahir'])) {
        try {
          $data['tgl_lahir'] = Carbon::createFromFormat('d/m/Y', $data['tgl_lahir'], 'Asia/Jakarta')->format('Y-m-d');
        } catch (\Exception $e) {
          $dateParts = explode('/', $data['tgl_lahir']);
          if (count($dateParts) === 3) {
            $data['tgl_lahir'] = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
          }
        }
      }

      // Handle file upload for Identitas
      if ($request->hasFile('file_identitas')) {
        $file = $request->file('file_identitas');
        $dest = public_path('assets/img/identitas');
        if (!is_dir($dest)) {
          @mkdir($dest, 0775, true);
        }
        $filename = Str::slug($data['nama_pemilik']) . '-identitas-' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($dest, $filename);
        $data['file_identitas'] = $filename;
      }

      // Handle file upload for NPWP
      if ($request->hasFile('file_npwp')) {
        $file = $request->file('file_npwp');
        $dest = public_path('assets/img/npwp');
        if (!is_dir($dest)) {
          @mkdir($dest, 0775, true);
        }
        $filename = Str::slug($data['nama_pemilik']) . '-npwp-' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($dest, $filename);
        $data['file_npwp'] = $filename;
      }

      // Convert empty strings to null
      foreach ($data as $key => $value) {
        if ($value === '') {
          $data[$key] = null;
        }
      }

      if ($id) {
        // UPDATE
        $pemilik = DataPemilik::findOrFail($id);

        // Delete old files if new ones uploaded
        if ($request->hasFile('file_identitas') && $pemilik->file_identitas) {
          $oldPath = public_path('assets/img/identitas/' . $pemilik->file_identitas);
          if (is_file($oldPath))
            @unlink($oldPath);
        }

        if ($request->hasFile('file_npwp') && $pemilik->file_npwp) {
          $oldPath = public_path('assets/img/npwp/' . $pemilik->file_npwp);
          if (is_file($oldPath))
            @unlink($oldPath);
        }

        $data['updated_by'] = auth()->user()?->username ?? 'ADMIN';
        $pemilik->update($data);
        $message = 'Data pemilik berhasil diupdate';
      } else {
        // CREATE
        $data['created_by'] = auth()->user()?->username ?? 'ADMIN';
        $pemilik = DataPemilik::create($data);
        $message = 'Data pemilik berhasil ditambahkan';
      }

      return response()->json([
        'success' => true,
        'message' => $message,
        'data' => $pemilik
      ], 200);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Display the specified resource.
   */
  public function show($id)
  {
    try {
      $pemilik = DataPemilik::findOrFail($id);

      return response()->json([
        'success' => true,
        'data' => $pemilik
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Data tidak ditemukan'
      ], 404);
    }
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit($id)
  {
    try {
      $pemilik = DataPemilik::findOrFail($id);

      // Convert tanggal ke format d/m/Y
      if ($pemilik->tgl_lahir) {
        $pemilik->tgl_lahir = date("d/m/Y", strtotime($pemilik->tgl_lahir));
      }

      // Convert alamat1 ke alamat untuk form
      $pemilik->alamat = $pemilik->alamat1 ?? '';

      return response()->json($pemilik);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Data tidak ditemukan'
      ], 404);
    }
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, $id)
  {
    try {
      $pemilik = DataPemilik::findOrFail($id);

      $rules = [
        'nama_pemilik' => 'required|string|max:100',
        'kode_jenis_pemilik' => 'required',
        'handphone' => 'required|string|max:20',
        'kode_cabang' => 'required',
        'no_identitas' => 'nullable|numeric|digits_between:1,20',
        'alamat' => 'nullable|string',
        'kota' => 'nullable|string|max:50',
        'kode_pos' => 'nullable|numeric|digits:5',
        'po_box' => 'nullable|string|max:20',
        'telepon' => 'nullable|string|max:20',
        'fax' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:100',
        'tgl_lahir' => 'nullable',
        'kode_agama' => 'nullable|string|max:10',
        'npwp' => 'nullable|numeric|digits_between:1,20',
        'file_identitas' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'file_npwp' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
      ];

      $validator = Validator::make($request->all(), $rules);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Validasi gagal',
          'errors' => $validator->errors()
        ], 422);
      }

      $data = $request->all();

      // Mapping alamat ke alamat1
      if (isset($data['alamat'])) {
        $data['alamat1'] = $data['alamat'];
        unset($data['alamat']);
      }

      // Convert date format
      if (!empty($data['tgl_lahir'])) {
        try {
          $data['tgl_lahir'] = Carbon::createFromFormat('d/m/Y', $data['tgl_lahir'], 'Asia/Jakarta')->format('Y-m-d');
        } catch (\Exception $e) {
          // Already Y-m-d format
        }
      }

      // Handle file uploads
      if ($request->hasFile('file_identitas')) {
        $file = $request->file('file_identitas');
        $dest = public_path('assets/img/identitas');
        if (!is_dir($dest))
          @mkdir($dest, 0775, true);

        $filename = Str::slug($data['nama_pemilik']) . '-identitas-' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($dest, $filename);
        $data['file_identitas'] = $filename;

        // Delete old file
        $old = $request->input('old_file_identitas');
        if ($old && $old !== $filename) {
          $oldPath = $dest . DIRECTORY_SEPARATOR . $old;
          if (is_file($oldPath))
            @unlink($oldPath);
        }
      }

      if ($request->hasFile('file_npwp')) {
        $file = $request->file('file_npwp');
        $dest = public_path('assets/img/npwp');
        if (!is_dir($dest))
          @mkdir($dest, 0775, true);

        $filename = Str::slug($data['nama_pemilik']) . '-npwp-' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($dest, $filename);
        $data['file_npwp'] = $filename;

        // Delete old file
        $old = $request->input('old_file_npwp');
        if ($old && $old !== $filename) {
          $oldPath = $dest . DIRECTORY_SEPARATOR . $old;
          if (is_file($oldPath))
            @unlink($oldPath);
        }
      }

      // Convert empty to null
      foreach ($data as $key => $value) {
        if ($value === '')
          $data[$key] = null;
      }

      $data['updated_by'] = auth()->user()?->username ?? 'ADMIN';
      $pemilik->update($data);

      return response()->json([
        'success' => true,
        'message' => 'Data pemilik berhasil diupdate',
        'data' => $pemilik
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy($id)
  {
    try {
      $pemilik = DataPemilik::findOrFail($id);

      // Delete file identitas
      if ($pemilik->file_identitas) {
        $filePath = public_path('assets/img/identitas/' . $pemilik->file_identitas);
        if (file_exists($filePath) && is_file($filePath)) {
          @unlink($filePath);
        }
      }

      // Delete file npwp
      if ($pemilik->file_npwp) {
        $filePath = public_path('assets/img/npwp/' . $pemilik->file_npwp);
        if (file_exists($filePath) && is_file($filePath)) {
          @unlink($filePath);
        }
      }

      $pemilik->delete();

      return response()->json([
        'success' => true,
        'message' => 'Data pemilik berhasil dihapus'
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get pemilik by jenis (Badan/Perorangan)
   */
  public function getByJenis($kodeJenis)
  {
    try {
      $user_cabang = session('kd_cabang');
      $data = DataPemilik::where('kode_cabang', $user_cabang)
        ->where('kode_jenis_pemilik', $kodeJenis)
        ->orderBy('nama_pemilik', 'asc')
        ->get();

      return response()->json([
        'success' => true,
        'data' => $data
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 500);
    }
  }
}
