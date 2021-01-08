<?php

namespace Tests\Unit\Sms\Service;

use Biz\BaseTestCase;
use Biz\Sms\SmsType;
use Mockery;

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
        $mockedSms = $this->mockPureBiz('ESCloudSdk.sms', [
            [
                'functionName' => 'sendToMany',
                'returnValue' => [
                    'no' => 'test no',
                ],
            ],
        ]);
        $smsSetting = $this->setSmsSetting();
        $smsSetting['sms_testpaper_check'] = 'on';
        $this->getSettingService()->set('cloud_sms', $smsSetting);

        $user1 = $this->createUser('user1', '15869165217');
        $user2 = $this->createUser('user2', '15869165222');
        $userIds = [$user1['id'], $user2['id']];

        $parameters = [
            'lesson_title' => '《综合测试》试卷',
            'course_title' => '《课程1》',
        ];
        $result = $this->getSmsService()->smsSend('sms_testpaper_check', $userIds, SmsType::EXAM_REVIEW, $parameters);

        $this->assertTrue($result);
        $mockedSms->shouldReceive('sendToMany')->times(1);
    }

    /**
     * @expectedException \Biz\User\UserException
     */
    public function testSendVerifySms()
    {
        $mockedSms = $this->mockPureBiz('ESCloudSdk.sms', [
            [
                'functionName' => 'sendToMany',
                'returnValue' => [
                    'no' => 'test no',
                ],
            ],
        ]);

        $smsSetting = $this->setSmsSetting();
        $smsSetting['sms_bind'] = 'on';
        $this->getSettingService()->set('cloud_sms', $smsSetting);

        $toMobile = '15869165217';
        $this->createUser('user1', $toMobile);

        $smsLastTime = 0;
        $result = $this->getSmsService()->sendVerifySms('sms_bind', $toMobile, $smsLastTime);
        $this->assertEquals($toMobile, $result['to']);
        $mockedSms->shouldReceive('sendToMany')->times(1);
    }

    public function testUserPayVerifySms()
    {
        $mockedSms = $this->mockPureBiz('ESCloudSdk.sms', [
            [
                'functionName' => 'sendToOne',
                'returnValue' => [
                    'no' => 'test no',
                ],
            ],
        ]);
        $smsSetting = $this->setSmsSetting();
        $smsSetting['sms_user_pay'] = 'on';
        $this->getSettingService()->set('cloud_sms', $smsSetting);

        $toMobile = '15869165217';
        $currentUser = $this->getCurrentUser();
        $currentUser['verifiedMobile'] = $toMobile;
        $smsLastTime = 0;
        $this->createApiMock();
        $result = $this->getSmsService()->sendVerifySms('sms_user_pay', $toMobile, $smsLastTime);
        $this->assertEquals($toMobile, $result['to']);
        $mockedSms->shouldReceive('sendToOne')->times(1);
    }

    public function testForgetPasswordVerifySms()
    {
        $mockedSms = $this->mockPureBiz('ESCloudSdk.sms', [
            [
                'functionName' => 'sendToOne',
                'returnValue' => [
                    'no' => 'test no',
                ],
            ],
        ]);
        $smsSetting = $this->setSmsSetting();
        $smsSetting['sms_forget_password'] = 'on';
        $this->getSettingService()->set('cloud_sms', $smsSetting);

        $toMobile = '15869165217';
        $this->createUser('user1', $toMobile);

        $smsLastTime = 0;
        $this->createApiMock();
        $result = $this->getSmsService()->sendVerifySms('sms_forget_password', $toMobile, $smsLastTime);
        $this->assertEquals($toMobile, $result['to']);
        $mockedSms->shouldReceive('sendToOne')->times(1);
    }

    /**
     * @expectedException \Biz\Sms\SmsException
     */
    public function testSendException()
    {
        $this->createUser('user1', '');
        $this->getSmsService()->sendVerifySms('sms_forget_password', '18435180000', 0);
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

    public function testCheckVerifySmsWithError()
    {
        $actualMobile = '15869165217';
        $expectedMobile = '15869165217';
        $actualSmsCode = '';
        $expectedSmsCode = '';

        $result = $this->getSmsService()->checkVerifySms($actualMobile, $expectedMobile, $actualSmsCode, $expectedSmsCode);
        $this->assertEquals('验证码错误', $result['message']);

        $actualSmsCode = 's2b5s1';
        $expectedSmsCode = 's2b5s2';

        $result = $this->getSmsService()->checkVerifySms($actualMobile, $expectedMobile, $actualSmsCode, $expectedSmsCode);
        $this->assertEquals('验证码错误', $result['message']);

        $actualMobile = '15869165217';
        $expectedMobile = '15869165212';
        $result = $this->getSmsService()->checkVerifySms($actualMobile, $expectedMobile, $actualSmsCode, $expectedSmsCode);
        $this->assertEquals('验证码和手机号码不匹配', $result['message']);
    }

    protected function createUser($user, $phone)
    {
        $userInfo = [];
        $userInfo['email'] = "{$user}@{$user}.com";
        $userInfo['verifiedMobile'] = $phone;
        $userInfo['nickname'] = "{$user}";
        $userInfo['password'] = "{$user}123";
        $userInfo['loginIp'] = '127.0.0.1';

        return $this->getUserService()->register($userInfo);
    }

    protected function createApiMock($return = null)
    {
        $return = isset($return) ? $return : ['status' => 'ok'];
        $mockObject = Mockery::mock('MockedApi_ddescll2'.rand());
        $mockObject->shouldReceive('post')->times(1)->andReturn($return);
        $mockObject->shouldReceive('getAccessKey')->andReturn('access_key');
        $this->getSmsService()->setCloudeApi($mockObject);
    }

    protected function setSmsSetting()
    {
        $dataUserPosted = [
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
        ];
        $this->getSettingService()->set('cloud_sms', $dataUserPosted);

        return $this->getSettingService()->get('cloud_sms', []);
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
