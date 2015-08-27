<?php
namespace Topxia\Service\Sms;

interface SmsService
{
    public function isOpen($smsType);
    public function smsSend($smsType, $to, $userIds, $parameters);
}