<?php

namespace Codeages\Biz\ItemBank\Item\AnswerMode;

use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Exception\QuestionException;

class UncertainChoiceAnswerMode extends BaseChoiceMode
{
    const NAME = 'uncertain_choice';

    const INPUT_TYPE = 'checkbox';

    protected function validateQuestionCount($answer)
    {
        if (0 == count($answer)) {
            throw new QuestionException('Uncertain choice question must have one answer at least.', ErrorCode::QUESTION_ARGUMENT_INVALID);
        }
    }
}
