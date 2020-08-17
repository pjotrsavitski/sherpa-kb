<?php

namespace App\States\Question;

class Translated extends QuestionState
{
    public static $name = 'translated';

    public function status(): string
    {
        return 'Translated';
    }
}