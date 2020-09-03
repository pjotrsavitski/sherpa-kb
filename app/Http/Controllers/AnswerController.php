<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Answer;
use App\Language;
use App\Http\Resources\AnswerResource;
use Illuminate\Validation\Rule;
use App\States\Answer\AnswerState;
use App\States\Answer\Published;
use App\Services\LanguageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AnswerController extends Controller
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
        $this->middleware('auth')->except('apiForLanguage');

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
     * Respond with answers using AnswerResource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        return AnswerResource::collection(Answer::with('languages')->get());
    }

    /**
     * Respond with answer states.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function states()
    {
        return Answer::getStatesFor('status')->map(function($state) {
            return [
                'value' => $state::getMorphClass(),
                'text' => $state::status(),
            ];
        });
    }

    /**
     * Create new Answer and respond with AnswerResource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'descriptions' => 'required|array',
            'descriptions.*.code' => 'required|exists:App\Language,code',
            'descriptions.*.value' => 'required',
        ]);

        $descriptions = $this->processDescriptions($validatedData['descriptions']);

        $answer = new Answer;
        $answer->save();
        $answer->languages()->attach($descriptions);

        return response()->json(new AnswerResource($answer), 200);
    }

    /**
     * Update existing Answer and respond with AnswerResource or code 422 if state transition is not allowed.
     *
     * @param Request $request
     * @param Answer $answer
     * @return \Illuminate\Http\JsonResponse
     */
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

        $descriptions = $this->processDescriptions($validatedData['descriptions']);

        $answer->languages()->syncWithoutDetaching($descriptions);

        return response()->json(new AnswerResource($answer->refresh()), 200);
    }

    /**
     * Responds with published answers that have been translated to a given language.
     *
     * @param Language $language
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiForLanguage(Language $language)
    {
        $data = [];

        Answer::with(['languages'])
        ->whereState('status', Published::class)
        ->whereHas('languages', function(Builder $query) use ($language) {
            $query->where('id', '=', $language->id);
        })
        ->chunk(50, function($answers) use ($language, &$data) {
            foreach($answers as $answer) {
                $data[] = [
                    'id' => $answer->id,
                    'description' => $answer->languages->keyBy('id')->get($language->id)->pivot->description,
                ];
            }
        });

        return response()->json($data);
    }
}
