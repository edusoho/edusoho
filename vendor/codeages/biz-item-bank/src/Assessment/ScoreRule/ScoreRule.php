<?php

namespace Codeages\Biz\ItemBank\Assessment\ScoreRule;

abstract class ScoreRule
{
    protected $biz;

    const RULE = '';

    abstract public function review($questionResult, $rule);

    abstract public function processRule($question);

    public function setQuestionScore($question, $score)
    {
        return $question;
    }
}
