<?php                         
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('revision_requests', function (Blueprint $t) {
            $t->time('proposed_clock_in')->nullable()->after('original_remarks');
            $t->time('proposed_clock_out')->nullable()->after('proposed_clock_in');
            $t->text('proposed_remarks')->nullable()->after('proposed_clock_out');
        });
    }
    public function down(): void
    {
        Schema::table('revision_requests', function (Blueprint $t) {
            $t->dropColumn(['proposed_clock_in','proposed_clock_out','proposed_remarks']);
        });
    }
};
