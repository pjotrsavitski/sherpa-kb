<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PendingQuestion;
use App\Language;
use App\Http\Resources\PendingQuestionResource;
use App\States\PendingQuestion\Propagated;
use App\States\PendingQuestion\PendingQuestionState;
use Illuminate\Validation\Rule;

class PendingQuestionController extends Controller
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

    private function getEnglishLanguageId(): int
    {
        return Language::where('code', 'en')->first()->id;
    }

    /**
     * Returns main language unique identifier or the one for English if that is the only one being set
     *
     * @param PendingQuestion $pendingQuestion
     * @return integer
     */
    private function getMainLanguageId(PendingQuestion $pendingQuestion): int
    {
        if ($pendingQuestion->languages->count() === 1 && $pendingQuestion->languages->first()->code === 'en') {
            return $pendingQuestion->languages->first()->id;
        }

        return $pendingQuestion->languages()->where('code', '<>', 'en')->first()->id;
    }

    public function list()
    {
        return PendingQuestionResource::collection(PendingQuestion::with('languages')->get());
    }

    public function update(Request $request, PendingQuestion $pendingQuestion)
    {
        $states = PendingQuestion::getStatesFor('status')->map(function($state) {
            return $state::getMorphClass();
        });
        $validatedData = $request->validate([
            'question' => 'required',
            'translation' => 'required',
            'status' => [
                'sometimes',
                'required',
                Rule::in($states),
            ],
        ]);

        // TODO Need to make sure that current user is allowed to set status as propagted
        if ($request->has('propagate') && $request->get('propagate')) {
            $pendingQuestion->status->transitionTo(Propagated::class);
        }

        // Make sure that user is allowed to set status
        if ($request->has('status')) {
            $statusClass = PendingQuestionState::resolveStateClass($request->get('status'));

            if (!$pendingQuestion->canTransitionTo($statusClass) && !$pendingQuestion->status->is($statusClass)) {
                return response()->json([
                    'message' => 'Status transition is not allowed!',
                ], 422);
            }

            if ($pendingQuestion->canTransitionTo($statusClass)) {
                $pendingQuestion->status->transitionTo($statusClass);
            }
        }

        // XXX Need to check permissions
        // TODO Need to determine the language that is allowed to be set
        if ($request->has('question')
            && $request->has('translation')
            && $request->get('question')
            && $request->get('translation'))
        {
            $languageData = [];

            $mainLanguageId = $this->getMainLanguageId($pendingQuestion);
            $englishLanguageId = $this->getEnglishLanguageId();

            $languageData[$mainLanguageId] = ['description' => $validatedData['question']];

            if ($mainLanguageId !== $englishLanguageId) {
                $languageData[$englishLanguageId] = ['description' => $validatedData['translation']];
            }
            
            $pendingQuestion->languages()->sync($languageData);
        }

        return response()->json(new PendingQuestionResource($pendingQuestion->refresh()), 200);
    }

    public function states()
    {
        return PendingQuestion::getStatesFor('status')->map(function($state) {
            return [
                'value' => $state::getMorphClass(),
                'text' => ucfirst($state::getMorphClass()),
            ];
        });
    }
}
