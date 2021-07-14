<?php

namespace Biz\WrongBook;

use AppBundle\Common\Exception\AbstractException;

class WrongBookException extends AbstractException
{
    const EXCEPTION_MODULE = 82;

    const WRONG_QUESTION_DATA_FIELDS_MISSING = 5008201;

    const WRONG_QUESTION_NOT_EXIST = 5008202;

    const WRONG_QUESTION_BOOK_POOL_NOT_EXIST = 5008203;

    const WRONG_QUESTION_TARGET_TYPE_REQUIRE = 5008204;

    const WRONG_QUESTION_BOOK_POOL_TARGET_ID_REQUIRE = 5008205;

    public $message = [
        '5008201' => 'exception.wrong_book.wrong_question.data_fields_missing',
        '5008202' => 'exception.wrong_book.wrong_question.not_exist',
        '5008203' => 'exception.wrong_book.wrong_question_book_pool.not_exist',
        '5008204' => 'exception.wrong_book.wrong_question.target_type_require',
        '5008205' => 'exception.wrong_book.wrong_question.target_id_require',
    ];
}
