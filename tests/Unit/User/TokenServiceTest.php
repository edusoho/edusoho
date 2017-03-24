<?php

namespace Tests\Unit\User;

use Biz\BaseTestCase;
use Biz\User\Service\TokenService;

class TokenServiceTest extends BaseTestCase
{
    public function testMakeToken()
    {
        $token = $this->getTokenService()->makeToken('test_token');

        $this->assertGreaterThan(0, strlen($token['token']));
        $this->assertEmpty($token['data']);
        $this->assertEquals(0, $token['times']);
        $this->assertEquals(0, $token['remainedTimes']);
        $this->assertEquals(0, $token['expiredTime']);
        $this->assertGreaterThan(0, $token['createdTime']);
    }

    public function testMakeTimesToken()
    {
        $token = $this->getTokenService()->makeToken('test_token', array(
            'times' => 1,
        ));

        $this->assertEquals(1, $token['times']);
        $this->assertEquals(1, $token['remainedTimes']);
    }

    public function testMakeDurationToken()
    {
        $token = $this->getTokenService()->makeToken('test_token', array(
            'duration' => 3600,
        ));

        $this->assertEquals(0, $token['times']);
        $this->assertEquals(0, $token['remainedTimes']);
        $this->assertGreaterThan(time(), $token['expiredTime']);
    }

    public function testMakeTimesAndDurationToken()
    {
        $token = $this->getTokenService()->makeToken('test_token', array(
            'times' => 2,
            'duration' => 3600,
        ));

        $this->assertEquals(2, $token['times']);
        $this->assertEquals(2, $token['remainedTimes']);
        $this->assertGreaterThan(time(), $token['expiredTime']);
    }

    public function testMakeApiAuthToken()
    {
        $args = array(
            'userId' => 1,
            'device' => 'iOS'
        );

        $token = $this->getTokenService()->makeApiAuthToken($args);

        $this->assertEquals($args['userId'], $token['userId']);
        $this->assertEquals($args['device'], $token['device']);


        $this->getTokenService()->makeApiAuthToken($args);
        $args['device'] = 'Android';
        $this->getTokenService()->makeApiAuthToken($args);

        $tokens = $this->getTokenService()->findTokensByUserIdAndType($args['userId'], TokenService::TYPE_API_AUTH);
        $this->assertCount(2, $tokens);
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }
}
