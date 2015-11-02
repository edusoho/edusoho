<?php

namespace Topxia\Service\Sms\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;

// TODO

class SmsServiceTest extends BaseTestCase
{

    public function testIsOpen()
    {
       $dataUserPosted = array(
	       	'sms_enabled' => '1',
	        'sms_registration' => 'off',
	        'sms_forget_password' => 'off',
	        'sms_user_pay' => 'off',
	        'sms_forget_pay_password' => 'off',
	        'sms_bind' => 'off',
	        'sms_classroom_publish' => 'off',
	        'sms_course_publish' => 'off',
	        'sms_normal_lesson_publish' => 'off',
	        'sms_live_lesson_publish' => 'off',
	        'sms_live_play_one_day' => 'off',
	        'sms_live_play_one_hour' => 'off',
	        'sms_homework_check' => 'off',
	        'sms_testpaper_check' => 'off',
	        'sms_order_pay_success' => 'off',
	        'sms_course_buy_notify' => 'off',
	        'sms_classroom_buy_notify' => 'off',
	        'sms_vip_buy_notify' => 'off',
	        'sms_coin_buy_notify' => 'off',
       	);
       $this->getSettingService()->set('cloud_sms', $dataUserPosted);
       $isOpen = $this->getSmsService()->isOpen('sms_normal_lesson_publish');
       $this->assertEquals(false, $isOpen);
       $dataUserPosted['sms_normal_lesson_publish'] = 'on';
       $this->getSettingService()->set('cloud_sms', $dataUserPosted);
       $isOpen = $this->getSmsService()->isOpen('sms_normal_lesson_publish');
       $this->assertEquals(true, $isOpen);
       $dataUserPosted['sms_enabled'] = 0;
       $this->getSettingService()->set('cloud_sms', $dataUserPosted);
       $isOpen = $this->getSmsService()->isOpen('sms_normal_lesson_publish');
       $this->assertEquals(false, $isOpen);

    }
/*
    public function testSmsSend()
    {
       $this->assertNull(null);
    }
*/

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getSmsService()
    {
        return $this->getServiceKernel()->createService('Sms.SmsService');
    }
}