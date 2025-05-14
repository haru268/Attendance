<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateToAttendancesTable extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // 既存レコードに NULL を許すため nullable()
            $table->date('date')->nullable()->after('user_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }
}
