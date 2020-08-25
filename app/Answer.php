<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\HasStates;
use App\States\Answer\AnswerState;
use App\States\Answer\InTranslation;
use App\States\Answer\Translated;
use App\States\Answer\Published;

class Answer extends Model
{
    use HasStates;

    protected function registerStates(): void
    {
        $this
            ->addState('status', AnswerState::class)
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
}
