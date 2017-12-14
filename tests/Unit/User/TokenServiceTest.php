<?php

namespace Tests\Unit\User;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

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

    public function testMakeFakeTokenString()
    {
        $token = $this->getTokenService()->makeFakeTokenString(32);
    }

    public function testVerifyToken()
    {
        $this->mockBiz(
            'User:TokenDao',
            array(
                array(
                    'functionName' => 'getByToken',
                    'returnValue' => array(),
                    'withParams' => array('test'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getByToken',
                    'returnValue' => array('type' => 'type'),
                    'withParams' => array('test'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getByToken',
                    'returnValue' => array('type' => 'test_token', 'expiredTime' => 50000),
                    'withParams' => array('test'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getByToken',
                    'returnValue' => array('type' => 'test_token', 'expiredTime' => 0, 'times' => 0, 'remainedTimes' => 1),
                    'withParams' => array('test'),
                    'runTimes' => 1,
                ),
            )
        );
        $result = $this->getTokenService()->verifyToken('test_token', 'test');
        $this->assertFalse($result);

        $result = $this->getTokenService()->verifyToken('test_token', 'test');
        $this->assertFalse($result);

        $result = $this->getTokenService()->verifyToken('test_token', 'test');
        $this->assertFalse($result);

        $result = $this->getTokenService()->verifyToken('test_token', 'test');
        $this->assertEquals('test_token', $result['type']);
    }

    public function testDestoryToken()
    {
        $this->mockBiz(
            'User:TokenDao',
            array(
                array(
                    'functionName' => 'getByToken',
                    'returnValue' => array(),
                    'withParams' => array('test'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getByToken',
                    'returnValue' => array('id' => 11),
                    'withParams' => array('test'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'delete',
                    'withParams' => array(11),
                    'runTimes' => 1,
                ),
            )
        );
        $result = $this->getTokenService()->destoryToken('test');
        $this->assertNull($result);
    }

    public function testFindTokensByUserIdAndType()
    {
        $this->mockBiz(
            'User:TokenDao',
            array(
                array(
                    'functionName' => 'findByUserIdAndType',
                    'returnValue' => array('id' => 12, 'userId' => 22),
                    'withParams' => array(22, 'test_token'),
                ),
            )
        );
        $result = $this->getTokenService()->findTokensByUserIdAndType(22, 'test_token');
        $this->assertEquals(array('id' => 12, 'userId' => 22), $result);
    }

    public function testGetTokenByType()
    {
        $this->mockBiz(
            'User:TokenDao',
            array(
                array(
                    'functionName' => 'getByType',
                    'returnValue' => array('id' => 12, 'userId' => 22),
                    'withParams' => array('test_token'),
                ),
            )
        );
        $result = $this->getTokenService()->getTokenByType('test_token');
        $this->assertEquals(array('id' => 12, 'userId' => 22), $result);
    }

    public function testDeleteTokenByTypeAndUserId()
    {
        $this->mockBiz(
            'User:TokenDao',
            array(
                array(
                    'functionName' => 'deleteByTypeAndUserId',
                    'returnValue' => 1,
                    'withParams' => array('test_token', 22),
                ),
            )
        );
        $result = $this->getTokenService()->deleteTokenByTypeAndUserId('test_token', 22);
        $this->assertEquals(1, $result);
    }

    public function testDeleteExpiredTokens()
    {
        $this->mockBiz(
            'User:TokenDao',
            array(
                array(
                    'functionName' => 'deleteTopsByExpiredTime',
                    'returnValue' => 1,
                    'withParams' => array(time(), 2),
                ),
            )
        );
        $result = $this->getTokenService()->deleteExpiredTokens(2);
        $this->getTokenDao()->shouldHaveReceived('deleteTopsByExpiredTime');
        $this->assertNull($result);
    }

    public function testGcToken()
    {
        $this->mockBiz(
            'User:TokenDao',
            array(
                array(
                    'functionName' => 'delete',
                    'returnValue' => 1,
                    'withParams' => array(2),
                ),
            )
        );
        $service = $this->getTokenService();
        $token = array('id' => 2, 'times' => 1, 'remainedTimes' => 1);
        $result = ReflectionUtils::invokeMethod($service, '_gcToken', array($token));
        $this->getTokenDao()->shouldHaveReceived('delete');
        $this->assertNull($result);

        $token = array('id' => 2, 'times' => 0, 'remainedTimes' => 1, 'expiredTime' => 5000);
        $result = ReflectionUtils::invokeMethod($service, '_gcToken', array($token));
        $this->getTokenDao()->shouldHaveReceived('delete');
        $this->assertNull($result);
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

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    protected function getTokenDao()
    {
        return $this->createDao('User:TokenDao');
    }
}
