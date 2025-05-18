<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakRecordsTable extends Migration
{
    public function up(): void
    {
        Schema::create('break_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->time('break_start')->nullable();  
            $table->time('break_end')->nullable();     
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('break_records');
    }
}
