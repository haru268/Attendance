<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakRecord extends Model
{
    use HasFactory;

    protected $table = 'break_records';

    /**
     * マスアサインメント可能な属性
     * - attendance_id: Attendance モデルの外部キー
     * - break_start: 休憩開始時刻
     * - break_end: 休憩終了時刻
     */
    protected $fillable = [
        'attendance_id',
        'break_start',   
        'break_end',     
    ];

    /**
     * 型キャスト
     * H:i フォーマットの日付として扱う
     */
    protected $casts = [
        'break_start' => 'datetime:H:i',
        'break_end'   => 'datetime:H:i',
    ];

    /**
     * 親となる勤怠レコード
     * @return BelongsTo
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
