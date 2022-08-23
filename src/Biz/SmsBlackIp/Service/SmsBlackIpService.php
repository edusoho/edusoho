<?php

namespace Biz\SmsBlackIp\Service;

interface SmsBlackIpService
{
    public function isInBlackIpList($ip);

    public function addBlackIpList($ip);
}
