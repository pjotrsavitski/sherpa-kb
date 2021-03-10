<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHelperResponseUserRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('helper_response_user_ratings', function (Blueprint $table) {
            $table->text('question');
            $table->text('answer');
            $table->string('language_code', 5);
            $table->unsignedTinyInteger('rating');
            $table->ipAddress('ip');
            $table->timestamp('created_at');

            $table->index('language_code');
            $table->index('rating');
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
        Schema::dropIfExists('helper_response_user_ratings');
    }
}
