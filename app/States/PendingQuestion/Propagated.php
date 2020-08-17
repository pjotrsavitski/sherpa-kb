<?php

namespace App\States\PendingQuestion;

class Propagated extends PendingQuestionState
{
    public static $name = 'propagated';

    public function status(): string
    {
        return 'Propagated';
    }
}