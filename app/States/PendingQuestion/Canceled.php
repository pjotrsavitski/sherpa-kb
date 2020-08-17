<?php

namespace App\States\PendingQuestion;

class Canceled extends PendingQuestionState
{
    public static $name = 'canceled';
    
    public function status(): string
    {
        return 'Canceled';
    }
}