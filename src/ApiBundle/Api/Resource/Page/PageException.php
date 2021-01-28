<?php

namespace ApiBundle\Api\Resource\Page;

use AppBundle\Common\Exception\AbstractException;

class PageException extends AbstractException
{
    const EXCEPTION_MODULE = 20;

    const ERROR_MODE = 5002001;

    const ERROR_TYPE = 5002002;

    const ERROR_PORTAL = 5002003;

    public $messages = [
        5002001 => 'exception.page.error_mode',
        5002002 => 'exception.page.error_type',
        5002003 => 'exception.page.error_portal',
    ];
}
