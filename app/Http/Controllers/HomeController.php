<?php

namespace App\Http\Controllers;

use App\Language;

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

    /**
     * Show language expert view
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function languageExpert(Language $language) {
        // TODO Might need to add a permission/role check
        return view('language-expert')->with([
            'language' => $language,
        ]);
    }
}
