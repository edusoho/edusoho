<?php

namespace Biz\QuestionBank;

use AppBundle\Common\Exception\AbstractException;

class QuestionBankException extends AbstractException
{
    const EXCEPTION_MODUAL = 66;

    const FORBIDDEN_DELETE_CATEGORY = 4036601;

    const NOT_FOUND_BANK = 4046602;

    public $messages = array(
        4036601 => 'exception.question_bank.forbidden_delete_category',
        4046602 => 'exception.question_bank.not_found_bank',
    );
}
