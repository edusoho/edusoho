<?php

namespace Codeages\Biz\ItemBank\Item\AnswerMode;

use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Exception\QuestionException;

class SingleChoiceAnswerMode extends BaseChoiceMode
{
    const NAME = 'single_choice';

    const INPUT_TYPE = 'radio';

    protected function validateQuestionCount($answer)
    {
        if (1 != count($answer)) {
            throw new QuestionException('Single choice question only has one answer.', ErrorCode::QUESTION_ARGUMENT_INVALID);
        }
    }
}
