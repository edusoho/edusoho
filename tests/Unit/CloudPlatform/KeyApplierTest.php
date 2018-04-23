<?php

namespace Tests\Unit\CloudPlatform;

use Biz\BaseTestCase;
use Biz\CloudPlatform\KeyApplier;
use AppBundle\Common\ReflectionUtils;

class KeyApplierTest extends BaseTestCase
{
    public function testApplyKeyWithExistedKey()
    {
        $mockedSettingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('storage', array()),
                    'returnValue' => array(
                        'cloud_access_key' => 'key',
                        'cloud_secret_key' => 'secret',
                        'cloud_key_applied' => true,
                    ),
                ),
            )
        );

        $applier = new KeyApplier();
        $result = $applier->applyKey(array());

        $this->assertArrayEquals(
            array(
                'accessKey' => 'key',
                'secretKey' => 'secret',
            ),
            $result
        );

        $mockedSettingService->shouldHaveReceived('get');
    }

    public function testApplyKey()
    {
        $mockedSettingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('storage', array()),
                    'returnValue' => array(),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array('site'),
                    'returnValue' => array(),
                ),
            )
        );

        $mockedUserService = $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUserProfile',
                    'withParams' => array(123),
                    'returnValue' => array(
                        'truename' => 'truename_test',
                        'qq' => 'qq_test',
                        'mobile' => 13676221112,
                    ),
                ),
            )
        );

        $mockedSender = $this->mockBiz(
            'Sender:MockedSenderService',
            array(
                array(
                    'functionName' => 'postRequest',
                    'withParams' => array(
                        'http://api.edusoho.net/v1/keys',
                        array(
                            'siteName' => 'EduSoho网络课程',
                            'siteUrl' => 'http://test.com',
                            'email' => 'test@howzhi.com',
                            'contact' => 'truename_test',
                            'qq' => 'qq_test',
                            'mobile' => 13676221112,
                            'edition' => 'opensource',
                            'source' => 'apply',
                        ),
                    ),
                    'returnValue' => json_encode(array('a' => 'b')),
                ),
            )
        );

        $applier = new KeyApplier();

        ReflectionUtils::setProperty($applier, 'mockedSender', $mockedSender);
        $result = $applier->applyKey(array('id' => 123, 'email' => 'test@howzhi.com'));

        $mockedSettingService->shouldHaveReceived('get')->times(2);
        $mockedUserService->shouldHaveReceived('getUserProfile');
        $mockedSender->shouldHaveReceived('postRequest');

        $this->assertArrayEquals(array('a' => 'b'), $result);
    }
}
