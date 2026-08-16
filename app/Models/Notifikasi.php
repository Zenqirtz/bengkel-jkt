<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
  protected $table = 'notifikasi';
  protected $fillable = [
    'user_id',
    'target_level',
    'kode_cabang',
    'tipe',
    'group_key',
    'count',    // ← tambah group_key & count
    'title',
    'message',
    'url',
    'is_read',
  ];
  protected $casts = ['is_read' => 'boolean'];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  // ← tambah method ini
  public static function aggregate(
    array $attrs,
    string $groupKey,
    string $titleTemplate,
    string $msgTemplate
  ): void {
    $existing = static::where('group_key', $groupKey)
      ->whereDate('created_at', today())
      ->where('is_read', false)
      ->first();

    if ($existing) {
      $newCount = $existing->count + 1;
      $existing->update([
        'count' => $newCount,
        'title' => str_replace('{count}', $newCount, $titleTemplate),
        'message' => str_replace('{count}', $newCount, $msgTemplate),
      ]);
    } else {
      static::create(array_merge($attrs, [
        'group_key' => $groupKey,
        'count' => 1,
        'title' => str_replace('{count}', 1, $titleTemplate),
        'message' => str_replace('{count}', 1, $msgTemplate),
      ]));
    }
  }
}
