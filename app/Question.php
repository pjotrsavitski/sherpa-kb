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
            ->withPivot('description');
    }
}
