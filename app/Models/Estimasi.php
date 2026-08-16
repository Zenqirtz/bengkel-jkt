<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estimasi extends Model
{
  protected $table = 't_estimasi_hdr';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_estimasi',
    'tanggal',
    'kode_konsep_estimasi',
    'kode_estimator',
    'kode_pelanggan',
    'kode_spk',
    'kode_claim',
    // 'persen_jasa',
    // 'persen_bahan',
    'disc_perbaikan',
    'disc_sparepart',
    'disc_lain',
    'disc_total',
    'total_perbaikan',
    'total_sparepart',
    'total_lain',
    'total',
    'ppn',
    'ket1',
    'ket2',
    'ket3',
    'total_perbaikan_s',
    'total_sparepart_s',
    'total_lain_s',
    'total_or_ass',
    'penyusutan_sparepart',
    'prorata',
    'total_s',
    'ppn_s',
    'pph',
    'memo',
    'kode_surveyor',
    'nama_surveyor',
    'tgl_survey',
    'lama_pekerjaan',
    'kode_pengiriman',
    'tgl_pengiriman',
    'pengirim',
    'penerima',
    'kode_piutang',
    'tgl_piutang',
    'kode_faktur',
    'kode_persetujuan',
    'tgl_persetujuan',
    'disetujui_oleh',
    'batal_oleh',
    'tgl_batal',
    'kode_kwitansi',
    'tgl_kwitansi',
    'tgl_kirim_kwitansi',
    'tgl_lunas_kwitansi',
    'surat_kuasa',
    'surat_pernyataan_puas',
    'biaya_penyusutan_sparepart',
    'memo_kewajiban_tertanggung',
    'no_polis',
    'sifat_ppn',
    'sparepart_ppn',
    'lain_ppn',
    'salvage',
    'created_by',
    'updated_by',
  ];
}
