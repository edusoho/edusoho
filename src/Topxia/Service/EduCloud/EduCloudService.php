<?php
namespace Topxia\Service\EduCloud;

interface EduCloudService
{
    public function getAccounts();

    public function applyForSms($name = 'smsHead');

    public function lookForStatus();

    public function sendSms($to, $captcha, $category = 'captcha');

    public function verifyKeys();

    public function checkSms($request, $smsUserPosted, $scenario);

    public function paramForSmsCheck($request);

    public function clearSmsSession($request);

    public function getCloudSmsKey($key);
}