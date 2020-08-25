<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes([
    'register' => false,
    'reset' => false,
    'verify' => false,
]);

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/home/questions', 'QuestionController@index')->name('questions');
Route::get('/home/pending_questions', 'PendingQuestionController@index')->name('pending_questions');
Route::get('/home/answers', 'AnswerController@index')->name('answers');

Route::get('/questions', 'QuestionController@list');
Route::get('/questions/states', 'QuestionController@states');
Route::get('/questions/topics', 'QuestionController@topics');
Route::put('/questions/{question}', 'QuestionController@update');

Route::get('/pending_questions', 'PendingQuestionController@list');
Route::get('/pending_questions/states', 'PendingQuestionController@states');
Route::put('/pending_questions/{pending_question}', 'PendingQuestionController@update');

Route::get('/answers', 'AnswerController@list');
Route::get('/answers/states', 'AnswerController@states');
Route::post('/answers', 'AnswerController@store');
