<?php

namespace Biz\User;

use AppBundle\Common\Exception\AbstractException;

class TokenException extends AbstractException
{
    const EXCEPTION_MODUAL = 61;

    const TOKEN_INVALID = 5006101;

    const NOT_MATCH_USER = 5006102;

    const NOT_MATCH_COURSE = 5006103;

    public $messages = array(
        5006101 => 'exception.token.invalid',
        5006102 => 'exception.token.not_match_user',
        5006103 => 'exception.token.not_match_course',
    );
}
