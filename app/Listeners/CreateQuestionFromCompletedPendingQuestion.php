<?php

namespace App\Listeners;

use Spatie\ModelStates\Events\StateChanged;
use App\PendingQuestion;
use App\Question;
use App\States\PendingQuestion\Completed;

class CreateQuestionFromCompletedPendingQuestion
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(StateChanged $event)
    {
        if ($event->model instanceof PendingQuestion && $event->model->status->is(Completed::class)) {
            // TODO This could be moved to a standalone method defined on top of PendingQuestion model
            $question = new Question;
            $question->save();
            $question->pendingQuestion()->associate($event->model);
            $question->languages()->attach($event->model->languages->keyBy('id')->map(function($item) {
                return [
                    'description' => $item->pivot->description,
                ];
            }));
        }
    }
}
