<?php

namespace Biz\Course;

use AppBundle\Common\Exception\AbstractException;

class MaterialException extends AbstractException
{
    const EXCEPTION_MODULE = 29;

    const NOTFOUND_MATERIAL = 4042901;

    const LINK_REQUIRED = 5002902;

    public $messages = [
        4042901 => 'exception.material.not_found',
        5002902 => 'exception.material.link_required',
    ];
}
