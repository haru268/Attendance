<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevisionRequest extends Model
{

    protected $fillable = [
        'user_id',
        'attendance_id',

        'original_clock_in',
        'original_clock_out',
        'original_remarks',


        'proposed_clock_in',
        'proposed_clock_out',
        'proposed_remarks',
        'breaks',


        'status',
        'approval_comment',
    ];


    protected $casts = [
        'breaks' => 'array',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
