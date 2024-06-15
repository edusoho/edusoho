<?php

namespace ApiBundle\Api\Resource\Assessment;

use AppBundle\Common\Exception\AbstractException;

class AssessmentException extends AbstractException
{
    const CHECK_FAILED = 4004001;
    const STATUS_ERROR = 4004002;

    public $messages = [
        4004001 => 'exception.assessment_check_failed',
        4004002 => 'exception.assessment_status_error',
    ];
}
