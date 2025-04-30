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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
