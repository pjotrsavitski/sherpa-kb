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
use App\Services\LanguageService;
use Illuminate\Support\Collection;

class QuestionController extends Controller
{
    /**
     * LanguageService instance
     *
     * @var LanguageService
     */
    private $languageService;

    /**
     * Create new controller instance
     * 
     * @return void
     */
    public function __construct(LanguageService $languageService)
    {
        $this->middleware('auth');

        $this->languageService = $languageService;
    }

    /**
     * Process validated descriptions and turn those into data set to be used with languages relation.
     * Desctiptions with empty values are removed.
     *
     * @param array $data
     * @return Collection
     */
    private function processDescriptions(array $data): Collection
    {
        return collect($data)
        ->keyBy(function($single) {
            return $this->languageService->getLanguageIdByCode($single['code']);
        })
        ->filter(function($single) {
            return trim($single['value']) !== '';
        })
        ->map(function($single) {
            return [
                'description' => $single['value'],
            ];
        });
    }

    /**
     * Respond with answers using QuestionResource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        return QuestionResource::collection(Question::with(['languages', 'topic', 'answer', 'pendingQuestion'])->get());
    }

    /**
     * Respond with question states.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function states()
    {
        return Question::getStatesFor('status')->map(function($state) {
            return [
                'value' => $state::getMorphClass(),
                'text' => $state::status(),
            ];
        });
    }

    /**
     * Respond with question topics using TopicResource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function topics()
    {
        return TopicResource::collection(Topic::all());
    }

    /**
     * Create new Question and respond with QuestionResource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

        $descriptions = $this->processDescriptions($validatedData['descriptions']);

        $question->languages()->attach($descriptions);

        return response()->json(new QuestionResource($question), 200);
    }

    /**
     * Update existing Question and respond with QuestionResource or code 422 if state transition is not allowed.
     *
     * @param Request $request
     * @param Question $question
     * @return \Illuminate\Http\JsonResponse
     */
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

        $descriptions = $this->processDescriptions($validatedData['descriptions']);

        $question->languages()->syncWithoutDetaching($descriptions);

        return response()->json(new QuestionResource($question->refresh()), 200);
    }
}
