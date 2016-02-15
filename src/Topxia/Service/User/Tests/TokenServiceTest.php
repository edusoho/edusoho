<?php
namespace Topxia\Service\User\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;

class TokenServiceTest extends BaseTestCase
{   

    public function testMakeToken()
    {
        $token = $this->getTokenService()->makeToken('test_token');

        $this->assertGreaterThan(0, strlen($token['token']));
        $this->assertNull($token['data']);
        $this->assertEquals(0, $token['times']);
        $this->assertEquals(0, $token['remainedTimes']);
        $this->assertEquals(0, $token['expiredTime']);
        $this->assertGreaterThan(0, $token['createdTime']);
    }

    public function testMakeTimesToken()
    {
        $token = $this->getTokenService()->makeToken('test_token', array(
            'times' => 1
        ));

        $this->assertEquals(1, $token['times']);
        $this->assertEquals(1, $token['remainedTimes']);
    }

    public function testMakeDurationToken()
    {
        $token = $this->getTokenService()->makeToken('test_token', array(
            'duration' => 3600
        ));

        $this->assertEquals(0, $token['times']);
        $this->assertEquals(0, $token['remainedTimes']);
        $this->assertGreaterThan(time(), $token['expiredTime']);
    }

    public function testMakeTimesAndDurationToken()
    {
        $token = $this->getTokenService()->makeToken('test_token', array(
            'times' => 2,
            'duration' => 3600
        ));

        $this->assertEquals(2, $token['times']);
        $this->assertEquals(2, $token['remainedTimes']);
        $this->assertGreaterThan(time(), $token['expiredTime']);
    }

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

}