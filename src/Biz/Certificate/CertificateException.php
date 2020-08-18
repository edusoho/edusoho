<?php

namespace Biz\Certificate;

use AppBundle\Common\Exception\AbstractException;

class CertificateException extends AbstractException
{
    const EXCEPTION_MODULE = 77;

    const NOTFOUND_TEMPLATE = 4047701;

    const NOTFOUND_CERTIFICATE = 4047702;

    const FORBIDDEN_DELETE_PUBLISHED = 4037703;

    const NOTFOUND_RECORD = 4047704;

    const FORBIDDEN_CANCEL_RECORD = 4037705;

    const FORBIDDEN_GRANT_RECORD = 4037706;

    const FORBIDDEN_PASS_RECORD = 4037707;

    const FORBIDDEN_REJECT_RECORD = 4037708;

    public $messages = [
        4047701 => 'exception.certificate_template.notfound_template',
        4047702 => 'exception.certificate_template.notfound_certificate',
        4037703 => 'exception.certificate_template.forbidden_delete_published',
        4047704 => 'exception.certificate.record.notfound_record',
        4037705 => 'exception.certificate.record.forbidden_cancel',
        4037706 => 'exception.certificate.record.forbidden_regrant',
        4037707 => 'exception.certificate.examine.forbidden_pass',
        4037708 => 'exception.certificate.examine.forbidden_reject',
    ];
}
