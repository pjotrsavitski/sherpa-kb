<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Question;
use App\Http\Resources\QuestionResource;
use App\Topic;
use App\Http\Resources\TopicResource;
use Illuminate\Validation\Rule;
use App\States\Question\QuestionState;
use App\Language;
use App\Answer;

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

    // TODO Move into a service
    private function getLanguageId(string $code)
    {
        // TODO Create a sttaic lookup structure
        return Language::where('code', $code)->first()->id;
    }

    public function list()
    {
        return QuestionResource::collection(Question::with(['languages', 'topic', 'answer', 'pendingQuestion'])->get());
    }

    public function states()
    {
        return Question::getStatesFor('status')->map(function($state) {
            return [
                'value' => $state::getMorphClass(),
                'text' => $state::status(),
            ];
        });
    }

    public function topics()
    {
        return TopicResource::collection(Topic::all());
    }

    public function store(Request $request) {
        $validatedData = $request->validate([
            'descriptions' => 'required|array',
            'descriptions.*.code' => 'required|exists:App\Language,code',
            'descriptions.*.value' => 'required', 
            'topic' => 'sometimes|required|exists:App\Topic,id',
        ]);

        $question = new Question;
        $question->save();

        if ($request->has('topic')) {
            $question->topic()->associate(Topic::find($request->get('topic')))->save();
        }

        if ($request->has('answer')) {
            $question->answer()->associate(Answer::find($request->get('answer')))->save();
        }

        $descriptions = collect($validatedData['descriptions'])
        ->keyBy(function($single) {
            return $this->getLanguageId($single['code']);
        })
        ->filter(function($single) {
            return trim($single['value']) !== '';
        })
        ->map(function($single) {
            return [
                'description' => $single['value'],
            ];
        });

        $question->languages()->attach($descriptions);

        return response()->json(new QuestionResource($question), 200);
    }

    public function update(Request $request, Question $question)
    {
        $states = Question::getStatesFor('status')->map(function($state) {
            return $state::getMorphClass();
        });
        $validatedData = $request->validate([
            'descriptions' => 'required|array',
            'descriptions.*.code' => 'required|exists:App\Language,code',
            'descriptions.*.value' => 'required', 
            'topic' => 'sometimes|required|exists:App\Topic,id',
            'status' => [
                'sometimes',
                'required',
                Rule::in($states),
            ],
        ]);

        // Make sure that user is allowed to set status
        if ($request->has('status')) {
            $statusClass = QuestionState::resolveStateClass($request->get('status'));

            if (!$question->canTransitionTo($statusClass) && !$question->status->is($statusClass)) {
                return response()->json([
                    'message' => 'Status transition is not allowed!',
                ], 422);
            }

            if ($question->canTransitionTo($statusClass)) {
                $question->status->transitionTo($statusClass);
            }
        }

        if ($request->has('topic')) {
            $question->topic()->associate(Topic::find($request->get('topic')))->save();
        } else {
            $question->topic()->dissociate()->save();
        }

        if ($request->has('answer')) {
            $question->answer()->associate(Answer::find($request->get('answer')))->save();
        } else {
            $question->answer()->dissociate()->save();
        }

        $descriptions = collect($validatedData['descriptions'])
        ->keyBy(function($single) {
            return $this->getLanguageId($single['code']);
        })
        ->filter(function($single) {
            return trim($single['value']) !== '';
        })
        ->map(function($single) {
            return [
                'description' => $single['value'],
            ];
        });

        $question->languages()->syncWithoutDetaching($descriptions);

        return response()->json(new QuestionResource($question->refresh()), 200);
    }
}
