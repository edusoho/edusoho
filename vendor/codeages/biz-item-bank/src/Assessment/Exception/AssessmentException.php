<?php

namespace Codeages\Biz\ItemBank\Assessment\Exception;

use AppBundle\Common\Exception\AbstractException;

class AssessmentException extends AbstractException
{
    const ASSESSMENT_NOTEXIST = 40495101;

    const ASSESSMENT_NOTOPEN = 50095103;

    const ASSESSMENT_EMPTY = 50095104;

    public $messages = [
        40495101 => 'exception.item_bank.assessment.not.exist',
        50095103 => 'exception.item_bank.assessment.not.open',
        50095104 => 'exception.item_bank.assessment.is_empty',
    ];
}
