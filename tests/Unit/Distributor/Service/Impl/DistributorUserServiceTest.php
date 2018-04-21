<?php

namespace Tests\Unit\Distributor\Service\Impl;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use AppBundle\Common\TimeMachine;

class DistributorUserServiceTest extends BaseTestCase
{
    public function testEncodeToken()
    {
        $time = 1516508150;
        TimeMachine::setMockedTime($time);

        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('storage', array()),
                    'returnValue' => array(
                        'cloud_access_key' => 'abc',
                        'cloud_secret_key' => 'efg',
                    ),
                ),
            )
        );
        $result = $this->getDistributorUserService()->encodeToken(
            array(
                'merchant_id' => '123',
                'agency_id' => '222',
                'coupon_price' => '1000',
                'coupon_expiry_day' => '1',
            ),
            $time
        );

        $this->assertEquals(
            '123:222:1000:1:1516421750:11f75a4d43ba172ef4cd7d378bc47bd8:zISakBT4B_sa8nQPovtjgeRfOIs=',
            $result
        );

        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testDecodeToken()
    {
        $time = 1516508150;
        TimeMachine::setMockedTime($time);

        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('storage', array()),
                    'returnValue' => array(
                        'cloud_access_key' => 'abc',
                        'cloud_secret_key' => 'efg',
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array('developer', array()),
                    'returnValue' => array(
                    ),
                ),
            )
        );

        $result = $this->getDistributorUserService()->decodeToken(
            '123:222:1000:1:1516421750:11f75a4d43ba172ef4cd7d378bc47bd8:zISakBT4B_sa8nQPovtjgeRfOIs='
        );

        $this->assertArrayEquals(
            array(
                'valid' => true,
                'rewardable' => true,
                'couponPrice' => '1000',
                'couponExpiryDay' => '1',
            ),
            $result
        );
    }

    public function testGetSendType()
    {
        $this->assertEquals('user', $this->getDistributorUserService()->getSendType());
    }

    public function testGetJobType()
    {
        $jobType = ReflectionUtils::invokeMethod($this->getDistributorUserService(), 'getJobType', array());
        $this->assertEquals('User', $jobType);
    }

    public function testConvertData()
    {
        TimeMachine::setMockedTime(1516508150);
        $user = array(
            'id' => 1,
            'nickname' => 'hello',
            'verifiedMobile' => '1232',
            'createdTime' => TimeMachine::time(),
            'token' => 'token',
            'updatedTime' => TimeMachine::time(),
        );

        $result = ReflectionUtils::invokeMethod($this->getDistributorUserService(), 'convertData', array($user));
        $this->assertArrayEquals(
            array(
                'user_source_id' => $user['id'],
                'nickname' => $user['nickname'],
                'mobile' => $user['verifiedMobile'],
                'registered_time' => $user['createdTime'],
                'token' => $user['token'],
            ),
            $result
        );
    }

    /**
     * @expectedException \AppBundle\Common\Exception\RuntimeException
     */
    public function testValidateExistedTokenWithException()
    {
        $userService = $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'searchUsers',
                    'withParams' => array(
                        array(
                            'distributorToken' => 'token123',
                        ),
                        array('id' => 'ASC'),
                        0,
                        1,
                    ),
                    'returnValue' => array('id' => 123),
                ),
            )
        );
        ReflectionUtils::invokeMethod(
            $this->getDistributorUserService(),
            'validateExistedToken',
            array('token123')
        );
    }

    private function getDistributorUserService()
    {
        return $this->createService('Distributor:DistributorUserService');
    }
}
