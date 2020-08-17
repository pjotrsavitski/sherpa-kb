<?php

namespace App\States\PendingQuestion;

use Spatie\ModelStates\State;

abstract class PendingQuestionState extends State
{
    abstract public function status(): string;
}