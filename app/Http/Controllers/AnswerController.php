<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Answer;
use App\Language;
use App\Http\Resources\AnswerResource;
use Illuminate\Validation\Rule;
use App\States\Answer\AnswerState;

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

    // TODO Move into a service
    private function getLanguageId(string $code)
    {
        // TODO Create a sttaic lookup structure
        return Language::where('code', $code)->first()->id;
    }
    
    public function list()
    {
        // TODO Might need additional protection and to provide different results to different roles
        return AnswerResource::collection(Answer::with('languages')->get());
    }

    public function states()
    {
        return Answer::getStatesFor('status')->map(function($state) {
            return [
                'value' => $state::getMorphClass(),
                'text' => $state::status(),
            ];
        });
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'descriptions' => 'required|array',
            'descriptions.*.code' => 'required|exists:App\Language,code',
            'descriptions.*.value' => 'required',
        ]);

        // TODO Make this reusable
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

        $answer = new Answer;
        $answer->save();
        $answer->languages()->attach($descriptions);

        return response()->json(new AnswerResource($answer), 200);
    }

    public function update(Request $request, Answer $answer)
    {
        $states = Answer::getStatesFor('status')->map(function($state) {
            return $state::getMorphClass();
        });
        $validatedData = $request->validate([
            'descriptions' => 'required|array',
            'descriptions.*.code' => 'required|exists:App\Language,code',
            'descriptions.*.value' => 'required',
            'status' => [
                'sometimes',
                'required',
                Rule::in($states),
            ],
        ]);

        // Make sure that user is allowed to set status
        if ($request->has('status')) {
            $statusClass = AnswerState::resolveStateClass($validatedData['status']);

            if (!$answer->canTransitionTo($statusClass) && !$answer->status->is($statusClass)) {
                return response()->json([
                    'message' => 'Status transition is not allowed!',
                ], 422);
            }

            if ($answer->canTransitionTo($statusClass)) {
                $answer->status->transitionTo($statusClass);
            }
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

        $answer->languages()->syncWithoutDetaching($descriptions);

        return response()->json(new AnswerResource($answer->refresh()), 200);
    }
}
