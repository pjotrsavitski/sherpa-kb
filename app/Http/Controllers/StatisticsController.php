<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Question;
use App\Answer;

class StatisticsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:master|administrator');
    }

    /**
     * Returns statistics data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $data = [
            'questions' => [
                'count' => Question::count(),
                'translations' => DB::table('language_question')
                    ->join('languages', 'languages.id', '=', 'language_question.language_id')
                    ->select(DB::raw('count(*) AS count, languages.code AS code'))
                    ->groupBy('language_id')
                    ->get(),
            ],
            'answers' => [
                'count' => Answer::count(),
                'translations' => DB::table('answer_language')
                    ->join('languages', 'languages.id', '=', 'answer_language.language_id')
                    ->select(DB::raw('count(*) AS count, languages.code AS code'))
                    ->groupBy('language_id')
                    ->get(),
            ],
        ];

        return response()->json($data);
    }
}
