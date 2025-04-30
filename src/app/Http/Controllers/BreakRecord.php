<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakRecord extends Model
{
    use HasFactory;

    protected $table = 'break_records'; 

    protected $fillable = ['attendance_id', 'start', 'end'];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
