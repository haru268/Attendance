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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->time('requested_clock_in')->nullable();
            $table->time('requested_clock_out')->nullable();
            $table->text('reason');
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
