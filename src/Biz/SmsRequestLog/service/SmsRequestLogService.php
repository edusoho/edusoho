<?php

namespace Biz\SmsRequestLog\service;

interface SmsRequestLogService
{
    public function createSmsRequestLog($smsRequestLog, $isIllegal);
    public function isIllegalSmsRequest($ip, $fingerprint);
}
