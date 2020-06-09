<?php

namespace Biz\Question;

use AppBundle\Common\Exception\AbstractException;

class QuestionException extends AbstractException
{
    const EXCEPTION_MODULE = 27;

    const NOTFOUND_QUESTION = 4042701;

    const FORBIDDEN_PREVIEW_QUESTION = 4032702;

    const UNEXPECTED_ANSWER = 5002703;

    public $messages = [
        4042701 => 'exception.question.not_found',
        4032702 => 'exception.question.forbidden_preview_question',
        5002703 => 'exception.question.unexpected_answer',
    ];
}
