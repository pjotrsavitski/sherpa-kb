<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\HasStates;
use App\States\PendingQuestion\PendingQuestionState;
use App\States\PendingQuestion\Pending;
use App\States\PendingQuestion\Propagated;
use App\States\PendingQuestion\Completed;
use App\States\PendingQuestion\Canceled;

class PendingQuestion extends Model
{
    use HasStates;

    protected function registerStates(): void
    {
        $this
            ->addState('status', PendingQuestionState::class)
            ->default(Pending::class)
            ->allowTransition(Pending::class, Propagated::class)
            ->allowTransition(Propagated::class, Completed::class)
            ->allowTransition(Propagated::class, Canceled::class);
    }

    public function languages()
    {
        return $this->belongsToMany('App\Language')
            ->withPivot('description');
    }

    public function getDescription() : string
    {
        $languages = $this->languages()->get();
        
        if ($languages->count() > 1)
        {
            $language = $languages->first(function ($value) {
                return $value->code !== 'en';
            });

            return $language->pivot->description;
        }

        return $languages->first()->pivot->description;
    }

    public function getEnglishDescription() : ?string
    {
        $languages = $this->languages()->get();

        if ($languages->count() > 0)
        {
            $language = $languages->first(function ($value) {
                return $value->code === 'en';
            });

            return $language->pivot->description ?? NULL;
        }

        return NULL;
    }
}
