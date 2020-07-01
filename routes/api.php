<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\PendingQuestion;
use App\Language;

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
Route::post('/question', function (Request $request) {
    $validatedData = $request->validate([
        'question' => 'required',
        'language' => 'required|exists:App\Language,name',
    ]);

    $question = new PendingQuestion;
    $question->status = 'Pending';
    $question->save();
    $question->languages()->attach(Language::where('name', $validatedData['language'])->first()->id, [
        'description' => $validatedData['question'],
        'created_at' => $question->created_at,
        'updated_at' => $question->updated_at,
    ]);

    return [
        'message' => 'OK',
    ];
});
