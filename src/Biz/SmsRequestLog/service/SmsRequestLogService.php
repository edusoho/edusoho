<?php

namespace Biz\SmsRequestLog\service;

interface SmsRequestLogService
{
    public function isIllegalSmsRequest($conditions);
}
