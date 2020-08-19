<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\HasStates;
use App\States\Question\QuestionState;
use App\States\Question\InTranslation;
use App\States\Question\Translated;
use App\States\Question\Published;

class Question extends Model
{
    use HasStates;

    protected function registerStates(): void
    {
        $this
            ->addState('status', QuestionState::class)
            ->default(InTranslation::class)
            ->allowTransition(InTranslation::class, Translated::class)
            ->allowTransition(InTranslation::class, Published::class)
            ->allowTransition(Translated::class, Published::class);
    }

    public function languages()
    {
        return $this->belongsToMany('App\Language')
            ->withPivot('description')
            ->withTimestamps();
    }

    public function topic()
    {
        return $this->belongsTo('App\Topic');
    }

    public function answer()
    {
        return $this->belongsTo('App\Answer');
    }

    public function pendingQuestion()
    {
        return $this->belongsTo('App\PendingQuestion');
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
