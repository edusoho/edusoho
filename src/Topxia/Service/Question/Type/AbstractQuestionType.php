<?php

namespace Topxia\Service\Question\Type;

abstract class AbstractQuestionType
{
    public function canHaveSubQuestion()
    {
        return false;
    }
}