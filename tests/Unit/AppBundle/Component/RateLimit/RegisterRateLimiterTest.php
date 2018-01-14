<?php

namespace Tests\Unit\Component\RateLimit;

use Biz\BaseTestCase;
use AppBundle\Component\RateLimit\RegisterRateLimiter;

class RegisterRateLimiterTest extends BaseTestCase
{
    public function testHandleWithTooManyRequestsHttpException()
    {
        $limiter = new RegisterRateLimiter($this->biz);
        $request = $this->mockRequest(
            array(
                'request' => array(
                    'captchaToken' => 'kuozhi',
                    'phrase' => 'password',
                ),
            )
        );

        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('auth'),
                    'returnValue' => array(
                        'register_protective' => 'none',
                    ),
                ),
            )
        );

        $result = $limiter->handle($request);

        $settingService->shouldHaveReceived('get')->times(1);
        $this->assertNull($result);
    }

    private function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
