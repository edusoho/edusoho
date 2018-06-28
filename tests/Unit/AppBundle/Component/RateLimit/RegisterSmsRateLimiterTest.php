<?php

namespace Tests\Unit\AppBundle\Component\RateLimit;

use Biz\BaseTestCase;
use AppBundle\Component\RateLimit\RegisterSmsRateLimiter;
use Biz\Common\BizCaptcha;

class RegisterSmsRateLimiterTest extends BaseTestCase
{
    public function testHandle()
    {
        $limiter = new RegisterSmsRateLimiter($this->biz);
        $request = $this->mockRequest(
            array(
                'request' => array(
                    'mobile' => '13967340627',
                    'dragCaptchaToken' => 'kuozhi',
                    'phrase' => 'password',
                ),
                'getClientIp' => '128.2.2.1',
            )
        );

        $captcha = $this->mockBiz(
            'biz_drag_captcha',
            array(
                array(
                    'functionName' => 'check',
                    'withParams' => array('kuozhi'),
                    'returnValue' => BizCaptcha::STATUS_SUCCESS,
                ),
            )
        );

        $userService = $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getSmsRegisterCaptchaStatus',
                    'withParams' => array('128.2.2.1'),
                    'returnValue' => 'captchaRequired',
                ),
                array(
                    'functionName' => 'isMobileUnique',
                    'withParams' => '',
                    'returnValue' => true,
                )
            )
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
            array(
                'request' => array(
                    'captchaToken' => 'kuozhi',
                    'phrase' => 'password',
                    'mobile' => '13967340627',
                ),
                'getClientIp' => '128.2.2.1',
            )
        );

        $captcha = $this->mockBiz(
            'biz_captcha',
            array(
                array(
                    'functionName' => 'check',
                    'withParams' => array('kuozhi', 'password'),
                    'returnValue' => BizCaptcha::STATUS_SUCCESS,
                ),
            )
        );

        $userService = $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getSmsRegisterCaptchaStatus',
                    'withParams' => array('128.2.2.1'),
                    'returnValue' => 'captchaIgnore',
                ),
                array(
                    'functionName' => 'isMobileUnique',
                    'withParams' => '',
                    'returnValue' => true,
                )
            )
        );

        $this->biz['biz_captcha'] = $captcha;

        $result = $limiter->handle($request);

        $captcha->shouldNotHaveReceived('check');
        $request->shouldHaveReceived('getClientIp')->times(2);
        $userService->shouldHaveReceived('getSmsRegisterCaptchaStatus')->times(1);
        $this->assertNull($result);
    }
}
