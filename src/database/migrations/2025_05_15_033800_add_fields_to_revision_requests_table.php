<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToRevisionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('revision_requests', function (Blueprint $table) {
          
            if (! Schema::hasColumn('revision_requests', 'original_clock_in')) {
              
                $table->time('original_clock_in')->nullable()->after('attendance_id');
                $table->time('original_clock_out')->nullable()->after('original_clock_in');
            }
            if (! Schema::hasColumn('revision_requests', 'breaks')) {
               
                $table->json('breaks')->nullable()->after('original_clock_out');
            }
            if (! Schema::hasColumn('revision_requests', 'remarks')) {
               
                $table->text('remarks')->nullable()->after('breaks');
            }
            if (! Schema::hasColumn('revision_requests', 'status')) {
               
                $table->enum('status', ['pending','approved'])
                      ->default('pending')
                      ->after('remarks');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('revision_requests', function (Blueprint $table) {
            $table->dropColumn([
                'original_clock_in',
                'original_clock_out',
                'breaks',
                'remarks',
                'status',
            ]);
        });
    }
}
