<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendingQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pending_questions', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20);
            $table->integer('group_no')->nullable();
            $table->timestamps();
        });

        Schema::create('language_pending_question', function (Blueprint $table) {
            $table->unsignedBigInteger('pending_question_id');
            $table->unsignedBigInteger('language_id');
            $table->text('description');
            $table->timestamps();
            $table->primary(['pending_question_id', 'language_id'], 'pending_question_language_primary');
            $table->foreign('pending_question_id')
                ->references('id')
                ->on('pending_questions')
                ->onDelete('cascade');
            $table->foreign('language_id')
                ->references('id')
                ->on('languages')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('language_pending_question');
        Schema::dropIfExists('pending_questions');
    }
}
