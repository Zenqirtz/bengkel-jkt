<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image; // PENTING: Import Facade Intervention Image

class DokumenFile extends Model
{
  protected $table = 't_dokumen_photo';

  public $timestamps = true;

  protected $fillable = [
    'parent_id',
    'relasi_tabel',
    'tipe',
    'nama_file',
    'photo',
    'ukuran',
    'created_by',
    'updated_by'
  ];

  public static function saveDokumenFile($parent_id, $relasi_tabel, $tipe, $file)
  {
    if($file) {
      // Ambil extension file
      $ext = $file->extension();

      // Ambil nama file
      $filename = $file->getClientOriginalName();

      // getRealPath() mengambil lokasi file sementara (temp file) sebelum diupload
      $photoBinary = file_get_contents($file->getRealPath());

      // Ambil ukuran file dalam byte
      $fileSize = $file->getSize();

      // PROSES KOMPRESI MENGGUNAKAN INTERVENTION IMAGE
      if($fileSize > 70000 && $ext <> 'pdf') {
        
        // Load gambar ke memori
        $img = Image::make($file->getRealPath());

        // Potong dan sesuaikan menjadi tepat 640 x 480 tanpa gepeng
        $img->fit(640, 480);

        // Paksa ukuran menjadi 640 x 480 (mengabaikan rasio asli)
        // $img->resize(640, 480);

        // Lanjut ke proses encode (kompresi)
        $photoBinary = (string) $img->encode('jpg', 70);

        // Ambil nama file
        $fileSize = strlen($photoBinary);
      }

      $dt = self::query()
      ->where('parent_id', $parent_id)
      ->where('relasi_tabel', $relasi_tabel)
      ->where('tipe', $tipe)
      ->first();

      if($dt) {
        $id = $dt->id;

        $data = [
          'nama_file' => $filename,
          'photo' => $photoBinary,
          'ukuran' => $fileSize,
          'updated_by' => Auth::user()->username
        ];

        self::where('id', $id)->update($data);
      } else {
        $data = [
          'parent_id' => $parent_id,
          'relasi_tabel' => $relasi_tabel,
          'tipe' => $tipe,
          'nama_file' => $filename,
          'photo' => $photoBinary,
          'ukuran' => $fileSize,
          'created_by' => Auth::user()->username
        ];
  
        self::create($data);
      }
    }
  }
}
