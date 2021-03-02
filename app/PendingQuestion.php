<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\ModelStates\HasStates;
use Spatie\Activitylog\Traits\LogsActivity;
use App\States\PendingQuestion\PendingQuestionState;
use App\States\PendingQuestion\Pending;
use App\States\PendingQuestion\Propagated;
use App\States\PendingQuestion\Completed;
use App\States\PendingQuestion\Canceled;

class PendingQuestion extends Model
{
    use HasStates;
    use LogsActivity;

    protected static $logAttributes = ['*'];
    protected static $submitEmptyLogs = false;

    protected function registerStates(): void
    {
        $this
            ->addState('status', PendingQuestionState::class)
            ->default(Pending::class)
            ->allowTransition(Pending::class, Propagated::class)
            ->allowTransition(Propagated::class, Pending::class)
            ->allowTransition(Propagated::class, Completed::class)
            ->allowTransition(Propagated::class, Canceled::class);
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany('App\Language')
            ->using('App\PendingQuestionLanguage')
            ->withPivot('description')
            ->withTimestamps();
    }
}
