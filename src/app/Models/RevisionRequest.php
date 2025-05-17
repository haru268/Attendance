<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevisionRequest extends Model
{
    /**
     * 一括代入を許可するカラム
     */
    protected $fillable = [
        'user_id',
        'attendance_id',

        // 元データ
        'original_clock_in',
        'original_clock_out',
        'original_remarks',

        // 申請データ
        'proposed_clock_in',
        'proposed_clock_out',
        'proposed_remarks',
        'breaks',

        // ステータス等
        'status',
        'approval_comment',
    ];

    /**
     * JSON キャスト
     */
    protected $casts = [
        'breaks' => 'array',
    ];

    /* ─────── リレーション ─────── */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
