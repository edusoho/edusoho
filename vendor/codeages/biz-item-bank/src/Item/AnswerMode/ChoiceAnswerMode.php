<?php

namespace Codeages\Biz\ItemBank\Item\AnswerMode;

use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Exception\QuestionException;

class ChoiceAnswerMode extends BaseChoiceMode
{
    const NAME = 'choice';

    const INPUT_TYPE = 'checkbox';

    protected function validateQuestionCount($answer)
    {
        if (count($answer) < 2) {
            throw new QuestionException('Choice question must have two answer at least.', ErrorCode::QUESTION_ARGUMENT_INVALID);
        }
    }
}
