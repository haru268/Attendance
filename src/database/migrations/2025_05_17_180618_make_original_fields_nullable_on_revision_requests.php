<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeOriginalFieldsNullableOnRevisionRequests extends Migration
{
    public function up()
    {
        Schema::table('revision_requests', function (Blueprint $table) {
   
            $table->time('original_clock_in')
                  ->nullable()
                  ->default(null)
                  ->change();
            $table->time('original_clock_out')
                  ->nullable()
                  ->default(null)
                  ->change();
            $table->text('original_remarks')
                  ->nullable()
                  ->change();
        });
    }

    public function down()
    {
        Schema::table('revision_requests', function (Blueprint $table) {
 
        });
    }
}
