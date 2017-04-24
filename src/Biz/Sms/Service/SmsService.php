<?php

namespace Biz\Sms\Service;

interface SmsService
{
    public function isOpen($smsType);

    public function smsSend($smsType, $userIds, $description, $parameters);

    public function sendVerifySms($smsType, $to, $smsLastTime);

    public function checkVerifySms($actualMobile, $expectedMobile, $actualSmsCode, $expectedSmsCode);
}
