<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'clock_in', 'clock_out', 'remarks'];

    // 最新レコード取得ヘルパー
    public static function latestForUser($userId)
    {
        return static::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->firstOrFail();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakRecord::class);
    }
}
