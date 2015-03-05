<?php
namespace Topxia\Service\EduCloud;

interface EduCloudService
{
    public function getAccount();

    public function applyForSms($name = 'smsHead');

    public function getSmsOpenStatus();

    public function sendSms($to, $captcha, $category = 'captcha');

    public function verifyKeys();

    public function checkSms($request, $smsUserPosted, $scenario);

    public function paramForSmsCheck($request);

    public function clearSmsSession($request);

    public function getCloudSmsKey($key);
}