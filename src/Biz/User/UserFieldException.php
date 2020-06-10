<?php

namespace Biz\User;

use AppBundle\Common\Exception\AbstractException;

class UserFieldException extends AbstractException
{
    const EXCEPTION_MODULE = 28;

    const NOTFOUND_USERFIELD = 4042801;

    const TITLE_REQUIRED = 5002802;

    const SEQ_REQUIRED = 5002803;

    const SEQ_INVALID = 5002804;

    const TYPE_INVALID = 5002805;

    const DUPLICATE_TITLE = 5002806;

    public $messages = [
        4042801 => 'exception.userfield.not_found',
        5002802 => 'exception.userfield.title_required',
        5002803 => 'exception.userfield.seq_required',
        5002804 => 'exception.userfield.seq_invalid',
        5002805 => 'exception.userfield.type_invalid',
        5002806 => 'exception.userfield.duplicate_title',
    ];
}
