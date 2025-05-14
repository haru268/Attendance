<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Attendance extends Model
{
    use HasFactory;

    /* ----------------------------------------
       テーブル定義
    ---------------------------------------- */
    protected $fillable = [
        'user_id',
        'date',        // ← 追加した date カラム
        'clock_in',
        'clock_out',
        'remarks',
    ];

    protected $casts = [
        'date'      => 'date',         // Y‑m‑d
        'clock_in'  => 'datetime:H:i', // 09:00
        'clock_out' => 'datetime:H:i',
    ];

    /* ----------------------------------------
       リレーション
    ---------------------------------------- */

    /** 所属ユーザー */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** 休憩レコード */
    public function breakRecords(): HasMany
    {
        return $this->hasMany(BreakRecord::class);
    }

    /** 修正申請 */
    public function revisionRequests(): HasMany
    {
        return $this->hasMany(RevisionRequest::class);
    }

    /* ----------------------------------------
       便利メソッド
    ---------------------------------------- */

    /** 指定ユーザーの最新勤怠を 1 件取得 */
    public static function latestForUser(int $userId): ?self
    {
        return static::where('user_id', $userId)
                     ->latest('created_at')
                     ->first();
    }
}
