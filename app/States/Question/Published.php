<?php

namespace App\States\Question;

class Published extends QuestionState
{
    public static $name = 'published';

    public function status(): string
    {
        return 'Published';
    }
}