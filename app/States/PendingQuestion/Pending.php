<?php

namespace App\States\PendingQuestion;

class Pending extends PendingQuestionState
{
    public static $name = 'pending';

    public function status(): string
    {
        return 'Pending';
    }
}