<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Question;
use App\Http\Resources\QuestionResource;

class QuestionController extends Controller
{
    /**
     * Create new controller instance
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function list()
    {
        return QuestionResource::collection(Question::with(['languages', 'topic', 'answer', 'pendingQuestion'])->get());
    }
}
