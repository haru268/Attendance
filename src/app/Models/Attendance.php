<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'clock_in',
        'clock_out',
        'remarks',
    ];

    protected $casts = [
        'clock_in'  => 'datetime:H:i',
        'clock_out' => 'datetime:H:i',
    ];

    /**
     * この勤怠を打刻したユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * この勤怠の休憩レコード
     */
    public function breakRecords()
    {
        return $this->hasMany(BreakRecord::class);
    }

    /**
     * この勤怠の修正申請
     */
    public function revisionRequests()
    {
        return $this->hasMany(RevisionRequest::class);
    }
}
