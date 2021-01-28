<?php

namespace Biz\Content;

use AppBundle\Common\Exception\AbstractException;

class ContentException extends AbstractException
{
    const EXCEPTION_MODULE = 50;

    const NOTFOUND_CONTENT = 4045001;

    const TYPE_REQUIRED = 5005002;

    const TITLE_REQUIRED = 5005003;

    public $messages = [
        4045001 => 'exception.content.not_found',
        5005002 => 'exception.content.type_required',
        5005003 => 'exception.content.title_required',
    ];
}
