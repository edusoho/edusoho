<?php

namespace Biz\Marker;

use AppBundle\Common\Exception\AbstractException;

class QuestionMarkerException extends AbstractException
{
    const EXCEPTION_MODUAL = 58;

    const NOTFOUND_QUESTION_MARKER = 4045801;

    public $messages = array(
        4045801 => 'exception.question_marker.not_found',
    );
}
