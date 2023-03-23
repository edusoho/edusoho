<?php

namespace Biz\SmsRequestLog\Service;

interface SmsRequestLogService
{
    public function createSmsRequestLog($fields);

    public function isIllegalSmsRequest($ip, $fingerprint);
}
