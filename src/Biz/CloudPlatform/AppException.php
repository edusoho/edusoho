<?php

namespace Biz\CloudPlatform;

use AppBundle\Common\Exception\AbstractException;

class AppException extends AbstractException
{
    const EXCEPTION_MODULE = 48;

    const GET_PACKAGE_FAILED = 5004801;

    const NOTFOUND_APP = 4044802;

    const DELETE_CACHE_FAILED = 5004803;

    const EXTRACT_FAILED = 5004804;

    public $messages = [
        5004801 => 'exception.app.get_package_failed',
        4044802 => 'exception.app.not_found',
        5004803 => 'exception.app.delete_cache_failed',
        5004804 => 'exception.app.extract_failed',
    ];
}
