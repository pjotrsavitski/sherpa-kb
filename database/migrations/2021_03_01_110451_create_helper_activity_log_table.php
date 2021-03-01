<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHelperActivityLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('helper_activity_log', function (Blueprint $table) {
            $table->text('question');
            $table->text('answer');
            $table->string('language_code', 5);
            $table->ipAddress('ip');
            $table->timestamp('created_at');

            $table->index('language_code');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('helper_activity_log');
    }
}
