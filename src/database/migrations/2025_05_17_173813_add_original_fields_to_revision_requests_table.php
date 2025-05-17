<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOriginalFieldsToRevisionRequestsTable extends Migration
{
    public function up(): void
    {
        Schema::table('revision_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('revision_requests', 'original_clock_in')) {
                $table->time('original_clock_in')
                      ->nullable()
                      ->after('attendance_id');
            }
            if (! Schema::hasColumn('revision_requests', 'original_clock_out')) {
                $table->time('original_clock_out')
                      ->nullable()
                      ->after('original_clock_in');
            }
            if (! Schema::hasColumn('revision_requests', 'original_remarks')) {
                $table->text('original_remarks')
                      ->nullable()
                      ->after('original_clock_out');
            }
        });
    }

    public function down(): void
    {
        Schema::table('revision_requests', function (Blueprint $table) {
            $table->dropColumn([
                'original_clock_in',
                'original_clock_out',
                'original_remarks',
            ]);
        });
    }
}
