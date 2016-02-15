<?php
namespace Topxia\Service\Sms\SmsProcessor;

interface SmsProcessor 
{
    public function getUrls($targetId, $smsType);

	public function getSmsInfo($targetId, $index, $smsType);

}