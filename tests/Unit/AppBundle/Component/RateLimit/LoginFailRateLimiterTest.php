<?php

namespace Tests\Unit\Component\RateLimit;

use Biz\BaseTestCase;
use AppBundle\Component\RateLimit\LoginFailRateLimiter;

class LoginFailRateLimiterTest extends BaseTestCase
{
    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException
     */
    public function testHandleWithTooManyRequestsHttpException()
    {
        $limiter = new LoginFailRateLimiter($this->biz);
        $request = $this->mockRequest(
            array(
                'request' => array(
                    'username' => 'kuozhi',
                    'password' => 'password',
                ),
                'getClientIp' => '123.9.2.1',
            )
        );

        $userService = $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUserByLoginField',
                    'returnValue' => array(),
                ),
            )
        );

        $rateLimit = $this->mockBiz(
            'Codeages\RateLimiter\RateLimiter',
            array(
                array(
                    'functionName' => 'check',
                    'withParams' => array('123.9.2.1'),
                    'returnValue' => 0,
                ),
            )
        );

        $limiter->setRateLimiter($rateLimit);
        $limiter->handle($request);

        $userService->shouldHaveReceived('getUserByLoginField')->times(1);
        $rateLimit->shouldHaveReceived('check')->times(1);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function testHandleWithNoUserName()
    {
        $limiter = new LoginFailRateLimiter($this->biz);
        $request = $this->mockRequest(
            array(
                'request' => array(
                    'username' => '',
                    'password' => 'password',
                ),
                'getClientIp' => '123.9.2.1',
            )
        );
        $limiter->handle($request);
    }

    public function testHandle()
    {
        $limiter = new LoginFailRateLimiter($this->biz);
        $request = $this->mockRequest(
            array(
                'request' => array(
                    'username' => 'kuozhi',
                    'password' => 'password',
                ),
                'getClientIp' => '123.9.2.1',
            )
        );

        $userService = $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUserByLoginField',
                    'returnValue' => array(
                        'id' => 1,
                    ),
                ),
                array(
                    'functionName' => 'verifyPassword',
                    'withParams' => array(1, 'password'),
                    'returnValue' => 0,
                ),
            )
        );

        $rateLimit = $this->mockBiz(
            'Codeages\RateLimiter\RateLimiter',
            array(
                array(
                    'functionName' => 'check',
                    'withParams' => array('kuozhi'),
                    'returnValue' => 1,
                ),
            )
        );

        $limiter->setRateLimiter($rateLimit);
        $result = $limiter->handle($request);

        $userService->shouldHaveReceived('getUserByLoginField')->times(1);
        $rateLimit->shouldHaveReceived('check')->times(1);

        $this->assertNull($result);
    }

    private function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
