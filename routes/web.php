<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\PendingQuestionController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\UserController;

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
    if (Auth::check()) {
        return redirect('home');
    }
    
    return view('welcome');
});

Auth::routes([
    'register' => false,
    'reset' => false,
    'verify' => false,
]);

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/questions', [QuestionController::class, 'list']);
Route::get('/questions/states', [QuestionController::class, 'states']);
Route::get('/questions/topics', [QuestionController::class, 'topics']);
Route::post('/questions', [QuestionController::class, 'store']);
Route::put('/questions/{question}', [QuestionController::class, 'update']);

Route::get('/pending_questions', [PendingQuestionController::class, 'list']);
Route::get('/pending_questions/states', [PendingQuestionController::class, 'states']);
Route::put('/pending_questions/{pending_question}', [PendingQuestionController::class, 'update']);

Route::get('/answers', [AnswerController::class, 'list']);
Route::get('/answers/states', [AnswerController::class, 'states']);
Route::post('/answers', [AnswerController::class, 'store']);
Route::put('/answers/{answer}', [AnswerController::class, 'update']);

Route::get('/users', [UserController::class, 'index'])->name('users');
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{user}', [UserController::class, 'update']);
