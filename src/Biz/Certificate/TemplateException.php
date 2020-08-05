<?php

namespace Biz\Certificate;

use AppBundle\Common\Exception\AbstractException;

class TemplateException extends AbstractException
{
    const EXCEPTION_MODULE = 77;

    const NOTFOUND_TEMPLATE = 4047701;

    public $messages = [
        4047701 => 'exception.certificate_template.notfound_template',
    ];
}
