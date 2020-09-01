<?php

namespace App\Http\Controllers;

use App\Language;

class LanguageController extends Controller
{
    /**
     * Responds with exportable corpus data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $languages = Language::all()->map(function($language) {
            return [
                'code' => $language->code,
                'name' => $language->name,
            ];
        });
        return response()->json($languages);
    }
}
