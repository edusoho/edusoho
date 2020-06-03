<?php

namespace Biz\Marker;

use AppBundle\Common\Exception\AbstractException;

class MarkerException extends AbstractException
{
    const EXCEPTION_MODULE = 31;

    const NOTFOUND_MARKER = 4043101;

    const FIELD_SECOND_REQUIRED = 5003102;

    public $messages = [
        4043101 => 'exception.marker.not_found',
        5003102 => 'exception.marker.field_second_required',
    ];
}
