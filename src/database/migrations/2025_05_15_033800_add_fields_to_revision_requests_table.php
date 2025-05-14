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
            // すでに存在しない場合だけ追加
            if (! Schema::hasColumn('revision_requests', 'original_clock_in')) {
                // 元の出勤・退勤時刻
                $table->time('original_clock_in')->nullable()->after('attendance_id');
                $table->time('original_clock_out')->nullable()->after('original_clock_in');
            }
            if (! Schema::hasColumn('revision_requests', 'breaks')) {
                // 修正後の休憩時間(JSON)
                $table->json('breaks')->nullable()->after('original_clock_out');
            }
            if (! Schema::hasColumn('revision_requests', 'remarks')) {
                // 修正後の備考
                $table->text('remarks')->nullable()->after('breaks');
            }
            if (! Schema::hasColumn('revision_requests', 'status')) {
                // ステータス（pending/approved）
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
