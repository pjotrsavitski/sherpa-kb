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

Auth::routes();

Route::get('/questions', 'QuestionController@list');

Route::get('/pending_questions', 'PendingQuestionController@list');
Route::put('/pending_questions/{pending_question}', 'PendingQuestionController@update');
