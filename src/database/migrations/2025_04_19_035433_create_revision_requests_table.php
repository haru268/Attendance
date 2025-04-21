<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRevisionRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('revision_requests', function (Blueprint $table) {
            $table->id();
            // 申請者のユーザーID
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // どの勤怠レコードへの申請か
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            // 申請内容：新しい出勤／退勤時刻
            $table->time('requested_clock_in')->nullable();
            $table->time('requested_clock_out')->nullable();
            // 申請理由
            $table->text('reason');
            // 承認ステータス（pending / approved / rejected など）
            $table->string('status')->default('pending');
            $table->text('approval_comment')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('revision_requests');
    }
}
