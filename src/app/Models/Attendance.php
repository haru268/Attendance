<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Attendance extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'date',        
        'clock_in',
        'clock_out',
        'remarks',
    ];

    protected $casts = [
        'date'      => 'date',         
        'clock_in'  => 'datetime', 
        'clock_out' => 'datetime',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function breakRecords(): HasMany
    {
        return $this->hasMany(BreakRecord::class);
    }


    public function revisionRequests(): HasMany
    {
        return $this->hasMany(RevisionRequest::class);
    }


    public static function latestForUser(int $userId): ?self
    {
        return static::where('user_id', $userId)
                     ->latest('created_at')
                     ->first();
    }
}
