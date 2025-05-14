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
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('attendance_id');
            $table->dateTime('original_clock_in');
            $table->dateTime('original_clock_out')->nullable();
            $table->json('breaks')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status',['pending','approved'])->default('pending');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('attendance_id')->references('id')->on('attendances');
        });
    }

    public function down()
    {
        Schema::dropIfExists('revision_requests');
    }
}