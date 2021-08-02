<?php

namespace Tests\Unit\AppBundle\Component\RateLimit;

use AppBundle\Component\RateLimit\RegisterSmsRateLimiter;
use Biz\BaseTestCase;
use Biz\Common\BizCaptcha;

class RegisterSmsRateLimiterTest extends BaseTestCase
{
    public function testHandle()
    {
        $limiter = new RegisterSmsRateLimiter($this->biz);
        $request = $this->mockRequest(
            [
                'request' => [
                    'mobile' => '13967340627',
                    'dragCaptchaToken' => 'kuozhi',
                    'phrase' => 'password',
                    'unique' => 'true',
                ],
                'getClientIp' => '128.2.2.1',
            ]
        );

        $captcha = $this->mockBiz(
            'biz_drag_captcha',
            [
                [
                    'functionName' => 'check',
                    'withParams' => ['kuozhi'],
                    'returnValue' => BizCaptcha::STATUS_SUCCESS,
                ],
            ]
        );

        $userService = $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'getSmsRegisterCaptchaStatus',
                    'withParams' => ['128.2.2.1'],
                    'returnValue' => 'captchaRequired',
                ],
                [
                    'functionName' => 'isMobileUnique',
                    'withParams' => '',
                    'returnValue' => true,
                ],
            ]
        );

        $this->biz['biz_drag_captcha'] = $captcha;

        $result = $limiter->handle($request);

        $captcha->shouldHaveReceived('check')->times(1);
        $request->shouldHaveReceived('getClientIp')->times(2);
        $userService->shouldHaveReceived('getSmsRegisterCaptchaStatus')->times(1);
        $this->assertNull($result);
    }

    public function testHandleWithoutCaptchaCode()
    {
        $limiter = new RegisterSmsRateLimiter($this->biz);
        $request = $this->mockRequest(
            [
                'request' => [
                    'captchaToken' => 'kuozhi',
                    'phrase' => 'password',
                    'mobile' => '13967340627',
                    'unique' => 'true',
                ],
                'getClientIp' => '128.2.2.1',
            ]
        );

        $captcha = $this->mockBiz(
            'biz_captcha',
            [
                [
                    'functionName' => 'check',
                    'withParams' => ['kuozhi', 'password'],
                    'returnValue' => BizCaptcha::STATUS_SUCCESS,
                ],
            ]
        );

        $userService = $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'getSmsRegisterCaptchaStatus',
                    'withParams' => ['128.2.2.1'],
                    'returnValue' => 'captchaIgnore',
                ],
                [
                    'functionName' => 'isMobileUnique',
                    'withParams' => '',
                    'returnValue' => true,
                ],
            ]
        );

        $this->biz['biz_captcha'] = $captcha;

        $result = $limiter->handle($request);

        $captcha->shouldNotHaveReceived('check');
        $request->shouldHaveReceived('getClientIp')->times(2);
        $userService->shouldHaveReceived('getSmsRegisterCaptchaStatus')->times(1);
        $this->assertNull($result);
    }
}
