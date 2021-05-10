<?php

namespace Biz\AuditCenter;

use AppBundle\Common\Exception\AbstractException;

class AuditCenterException extends AbstractException
{
    const EXCEPTION_MODULE = 80;

    const REPORT_AUDIT_STATUS_INVALID = 4038001;

    const REPORT_AUDIT_NOT_EXIST = 4048002;

    public $messages = [
        4038001 => 'exception.audit_center.report_audit_status_not_valid',
        4048002 => 'exception.audit_center.report_audit_not_exist',
    ];
}
