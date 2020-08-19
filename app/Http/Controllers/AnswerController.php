<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Answer;
use App\Http\Resources\AnswerResource;

class AnswerController extends Controller
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
        // TODO Might need additional protection and to provide different results to different roles
        return AnswerResource::collection(Answer::with('languages')->get())
    }
}
