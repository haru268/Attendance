<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakRecordsTable extends Migration
{
    public function up()
{
    Schema::create('break_records', function (Blueprint $table) {
        $table->id();
        $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
        $table->time('break_start');
        $table->time('break_end');
        $table->timestamps();
    });
}



    public function down()
    {
        Schema::dropIfExists('break_records');
    }
}
