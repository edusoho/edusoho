<?php

namespace Biz\WrongBook;

use AppBundle\Common\Exception\AbstractException;

class WrongBookException extends AbstractException
{
    const EXCEPTION_MODULE = 82;

    const WRONG_QUESTION_DATA_FIELDS_MISSING = 5008201;

    const WRONG_QUESTION_NOT_EXIST = 5008202;

    public $message = [
        '5008201' => 'exception.wrong_book.wrong_question.data_fields_missing',
        '5008202' => 'exception.wrong_book.wrong_question.not_exist',
    ];
}
