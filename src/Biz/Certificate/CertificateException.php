<?php

namespace Biz\Certificate;

use AppBundle\Common\Exception\AbstractException;

class CertificateException extends AbstractException
{
    const EXCEPTION_MODULE = 77;

    const NOTFOUND_TEMPLATE = 4047701;

    const NOTFOUND_CERTIFICATE = 4047702;

    public $messages = [
        4047701 => 'exception.certificate_template.notfound_template',
        4047702 => 'exception.certificate_template.notfound_certificate',
    ];
}
