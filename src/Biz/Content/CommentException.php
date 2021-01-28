<?php

namespace Biz\Content;

use AppBundle\Common\Exception\AbstractException;

class CommentException extends AbstractException
{
    const EXCEPTION_MODULE = 37;

    const NOTFOUND_COMMENT = 4043701;

    const FORBIDDEN_DELETE = 4033702;

    const OBJECTTYPE_INVALID = 5003703;

    public $messages = [
        4043701 => 'exception.comment.not_found',
        4033702 => 'exception.comment.forbidden_delete',
        5003703 => 'exception.comment.objecttype_invalid',
    ];
}
