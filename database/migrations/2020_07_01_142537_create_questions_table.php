<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20);
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->unsignedBigInteger('answer_id')->nullable();
            $table->unsignedBigInteger('pending_question_id')->nullable();
            $table->timestamps();
            $table->foreign('topic_id')
                ->references('id')
                ->on('topics')
                ->onDelete('SET NULL');
            $table->foreign('answer_id')
                ->references('id')
                ->on('answers')
                ->onDelete('SET NULL');
            $table->foreign('pending_question_id')
                ->references('id')
                ->on('pending_questions')
                ->onDelete('SET NULL');
        });

        Schema::create('language_question', function (Blueprint $table) {
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('language_id');
            $table->text('description');
            $table->timestamps();
            $table->primary(['question_id', 'language_id'], 'language_question_primary');
            $table->foreign('question_id')
                ->references('id')
                ->on('questions')
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
        Schema::dropIfExists('language_question');
        Schema::dropIfExists('questions');
    }
}
