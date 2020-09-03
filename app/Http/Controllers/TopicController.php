<?php

namespace App\Http\Controllers;

use App\Topic;

class TopicController extends Controller
{
    /**
     * Responds with topics for API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function api()
    {
        $topics = Topic::all()->map(function($topic) {
            return [
                'id' => $topic->id,
                'description' => $topic->description,
            ];
        });
        return response()->json($topics);
    }
}
