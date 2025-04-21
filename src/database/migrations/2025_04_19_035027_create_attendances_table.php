<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            // 外部キー：ユーザー
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // 出勤／退勤の時刻
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            // 備考
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
