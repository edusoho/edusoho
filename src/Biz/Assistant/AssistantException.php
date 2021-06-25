<?php

namespace Biz\Assistant;

use AppBundle\Common\Exception\AbstractException;

class AssistantException extends AbstractException
{
    const EXCEPTION_MODULE = 92;

    const MUTLICLASS_ASSISTANT_REQUIRE = 5009201;

    public $message = [
        5009201 => 'exception.multi_class.assistant_require',
    ];
}
