<?php

namespace Biz\Sms\Service;

interface SmsService
{
    public function isOpen($smsType);

    public function smsSend($smsType, $userIds, $description, $parameters);

    public function sendVerifySms($smsType, $to, $smsLastTime, $unique = 1);

    public function checkVerifySms($actualMobile, $expectedMobile, $actualSmsCode, $expectedSmsCode);
}
