<?php

namespace Biz\Question;

use AppBundle\Common\Exception\AbstractException;

class QuestionException extends AbstractException
{
    const EXCEPTION_MODUAL = 27;

    const NOTFOUND_QUESTION = 4042701;

    public $messages = array(
        4042701 => 'exception.question.not_found',
    );
}
