<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInitialTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_dummy')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });

        // 2. password_resets
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });



        // 4. failed_jobs
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // 5. attendances
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date')->index();
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // 6. break_records
        Schema::create('break_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->timestamps();
        });

        // 7. revision_requests
        Schema::create('revision_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->time('original_clock_in')->nullable();
            $table->time('original_clock_out')->nullable();
            $table->time('proposed_clock_in')->nullable();
            $table->time('proposed_clock_out')->nullable();
            $table->text('original_remarks')->nullable();
            $table->text('proposed_remarks')->nullable();
            $table->json('breaks')->nullable();
            $table->enum('status', ['pending','approved'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // DROP は依存関係の逆順で
        Schema::dropIfExists('revision_requests');
        Schema::dropIfExists('break_records');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');
    }
}
