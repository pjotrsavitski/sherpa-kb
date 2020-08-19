<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PendingQuestion;
use App\Language;
use App\Http\Resources\PendingQuestionResource;
use App\States\PendingQuestion\Propagated;

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
        if ($pendingQuestion->languages->count() === 1 && $pendingQuestion->languages->first()->code === 'en')
        {
            return $pendingQuestion->languages->first()->id;
        }

        return $pendingQuestion->languages()->where('code', '<>', 'en')->first()->id;
    }

    public function list()
    {
        // TODO Might need additional protection and to provide different results to different roles
        return PendingQuestionResource::collection(PendingQuestion::with('languages')->get());
    }

    public function update(Request $request, PendingQuestion $pendingQuestion)
    {
        $validatedData = $request->validate([
            'question' => 'required',
            'translation' => 'required',
        ]);
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

        if ($request->has('propagate')
            && $request->get('propagate'))
        {
            $pendingQuestion->status->transitionTo(Propagated::class);
        }

        return response()->json(new PendingQuestionResource($pendingQuestion->refresh()), 200);
    }
}
