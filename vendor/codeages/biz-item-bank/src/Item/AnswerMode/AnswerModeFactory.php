<?php

namespace Codeages\Biz\ItemBank\Item\AnswerMode;

use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Exception\QuestionException;

class AnswerModeFactory
{
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function create($answerMode)
    {
        if (empty($this->biz['answer_mode.'.$answerMode])) {
            throw new QuestionException('answer mode is not support', ErrorCode::QUESTION_ARGUMENT_INVALID);
        }

        return $this->biz['answer_mode.'.$answerMode];
    }
}
