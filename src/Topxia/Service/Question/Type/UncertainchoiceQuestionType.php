<?php
namespace Topxia\Service\Question\Type;

class UncertainchoiceQuestionType extends ChoiceQuestionType
{
    public function hasMissScore()
    {
        return true;
    }
}