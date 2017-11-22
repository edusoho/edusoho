<?php

namespace Tests\Unit\Sms;

use Mockery;
use Biz\BaseTestCase;
use Biz\CloudPlatform\CloudAPIFactory;

class SmsServiceTest extends BaseTestCase
{
    public function testIsOpen()
    {
        $smsSetting = $this->setSmsSetting();

        $isOpen = $this->getSmsService()->isOpen('sms_normal_lesson_publish');
        $this->assertEquals(false, $isOpen);

        $smsSetting['sms_normal_lesson_publish'] = 'on';
        $this->getSettingService()->set('cloud_sms', $smsSetting);
        $isOpen = $this->getSmsService()->isOpen('sms_normal_lesson_publish');
        $this->assertEquals(true, $isOpen);

        $smsSetting['sms_enabled'] = 0;
        $this->getSettingService()->set('cloud_sms', $smsSetting);
        $isOpen = $this->getSmsService()->isOpen('sms_normal_lesson_publish');
        $this->assertEquals(false, $isOpen);
    }

    public function testSmsSend()
    {
        $smsSetting = $this->setSmsSetting();
        $smsSetting['sms_testpaper_check'] = 'on';
        $this->getSettingService()->set('cloud_sms', $smsSetting);

        $user1 = $this->createUser('user1', '15869165217');
        $user2 = $this->createUser('user2', '15869165222');
        $userIds = array($user1['id'], $user2['id']);

        $description = '试卷批阅提醒';
        $parameters = array(
            'lesson_title' => '《综合测试》试卷',
            'course_title' => '《课程1》',
        );
        $result = $this->getSmsService()->smsSend('sms_testpaper_check', $userIds, $description, $parameters);

        $this->assertTrue($result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testSendVerifySms()
    {
        $smsSetting = $this->setSmsSetting();
        $smsSetting['sms_bind'] = 'on';
        $this->getSettingService()->set('cloud_sms', $smsSetting);

        $toMobile = '15869165217';
        $this->createUser('user1', $toMobile);

        $smsLastTime = 0;
        $result = $this->getSmsService()->sendVerifySms('sms_bind', $toMobile, $smsLastTime);
    }

    public function testCheckVerifySms()
    {
        $actualMobile = '15869165217';
        $expectedMobile = '15869165217';
        $actualSmsCode = 's2b5s0';
        $expectedSmsCode = 's2b5s0';

        $result = $this->getSmsService()->checkVerifySms($actualMobile, $expectedMobile, $actualSmsCode, $expectedSmsCode);

        $this->assertTrue($result['success']);
    }

    protected function createUser($user, $phone)
    {
        $userInfo = array();
        $userInfo['email'] = "{$user}@{$user}.com";
        $userInfo['verifiedMobile'] = $phone;
        $userInfo['nickname'] = "{$user}";
        $userInfo['password'] = "{$user}";
        $userInfo['loginIp'] = '127.0.0.1';

        return $this->getUserService()->register($userInfo);
    }

    protected function createApiMock()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('status' => 'ok'));
        $this->getSmsService()->setCloudeApi($mockObject);
    }

    protected function setSmsSetting()
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

        return $this->getSettingService()->get('cloud_sms', array());
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getSmsService()
    {
        return $this->createService('Sms:SmsService');
    }
}
