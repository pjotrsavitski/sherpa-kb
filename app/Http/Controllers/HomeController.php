<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PendingQuestion;
use App\Question;
use App\Answer;
use App\Http\Resources\PendingQuestionResource;
use App\Http\Resources\QuestionResource;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
}
