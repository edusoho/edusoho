<?php

namespace Biz\Org;

use AppBundle\Common\Exception\AbstractException;

class OrgException extends AbstractException
{
    const EXCEPTION_MODULE = 34;

    const NOTFOUND_ORG = 4043401;

    public $messages = [
        4043401 => 'exception.org.not_found',
    ];
}
