<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProposedAndOriginalFieldsToRevisionRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('revision_requests', function (Blueprint $table) {
        // すでに無ければ追加
        if (!Schema::hasColumn('revision_requests','original_clock_in')) {
            $table->time('original_clock_in')->nullable()->after('attendance_id');
            $table->time('original_clock_out')->nullable()->after('original_clock_in');
            $table->text('original_remarks')->nullable()->after('original_clock_out');
        }
        if (!Schema::hasColumn('revision_requests','proposed_clock_in')) {
            $table->time('proposed_clock_in')->nullable()->after('original_remarks');
            $table->time('proposed_clock_out')->nullable()->after('proposed_clock_in');
            $table->text('proposed_remarks')->nullable()->after('proposed_clock_out');
        }
        if (!Schema::hasColumn('revision_requests','breaks')) {
            $table->json('breaks')->nullable()->after('proposed_remarks');
        }
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
