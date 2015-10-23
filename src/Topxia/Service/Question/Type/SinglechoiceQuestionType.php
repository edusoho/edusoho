<?php
namespace Topxia\Service\Question\Type;

class SinglechoiceQuestionType extends ChoiceQuestionType
{
    public function hasMissScore()
    {
        return false;
    }
}