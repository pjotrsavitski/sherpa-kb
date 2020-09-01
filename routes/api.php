<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// TODO Need to protect the API endpoit somehow, using reCAPTCHA might work
Route::post('/question', 'PendingQuestionController@store');

Route::get('/languages', 'LanguageController@list');
Route::get('/export/{language:code}', 'ExportController@export');
