<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequestedColsToRevisionRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('revision_requests', function (Blueprint $table) {
        $table->time('requested_clock_in')->nullable()->after('original_remarks');
        $table->time('requested_clock_out')->nullable()->after('requested_clock_in');
        // 備考は既に remarks 列があるので追加不要
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('revision_requests', function (Blueprint $table) {
            //
        });
    }
}
