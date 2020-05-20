<?php

namespace Codeages\Biz\ItemBank\Item\Wrapper;

class QuestionWrapper
{
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function wrap($question, $withAnswer)
    {
        if (!$withAnswer) {
            unset($question['answer']);
            unset($question['analysis']);
        }

        return $question;
    }
}
