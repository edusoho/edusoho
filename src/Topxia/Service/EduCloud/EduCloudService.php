<?php
namespace Topxia\Service\EduCloud;

interface EduCloudService
{
    public function getAccount();

    public function applyForSms($name = 'smsHead');

    public function getSmsOpenStatus();

    public function sendSms($to, $captcha, $category = 'captcha');

    public function verifyKeys();

	public function getBills($type, $page, $limit);

    public function getCloudSmsKey($key);

    public function getLiveCourseStatus();
}