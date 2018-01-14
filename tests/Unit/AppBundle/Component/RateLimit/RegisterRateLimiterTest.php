<?php

namespace Tests\Unit\AppBundle\Component\RateLimit;

use Biz\BaseTestCase;
use AppBundle\Component\RateLimit\RegisterRateLimiter;
use Biz\Common\BizCaptcha;

class RegisterRateLimiterTest extends BaseTestCase
{
    public function testHandleWithNoSecurity()
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

    public function testHandleWithLowSecurity()
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
                        'register_protective' => 'low',
                    ),
                ),
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

        $this->biz['biz_captcha'] = $captcha;

        $result = $limiter->handle($request);

        $settingService->shouldHaveReceived('get')->times(1);
        $captcha->shouldHaveReceived('check')->times(1);
        $this->assertNull($result);
    }

    public function testHandleWithMiddleSecurity()
    {
        $limiter = new RegisterRateLimiter($this->biz);
        $request = $this->mockRequest(
            array(
                'request' => array(
                    'captchaToken' => 'kuozhi',
                    'phrase' => 'password',
                ),
                'getClientIp' => '128.2.2.1',
            )
        );

        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('auth'),
                    'returnValue' => array(
                        'register_protective' => 'middle',
                    ),
                ),
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

        $this->biz['biz_captcha'] = $captcha;

        $result = $limiter->handle($request);

        $settingService->shouldHaveReceived('get')->times(1);
        $captcha->shouldHaveReceived('check')->times(1);
        $request->shouldHaveReceived('getClientIp')->times(1);
        $this->assertNull($result);
    }

    public function testHandleWithHighSecurity()
    {
        $limiter = new RegisterRateLimiter($this->biz);
        $request = $this->mockRequest(
            array(
                'request' => array(
                    'captchaToken' => 'kuozhi',
                    'phrase' => 'password',
                ),
                'getClientIp' => '128.2.2.1',
            )
        );

        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('auth'),
                    'returnValue' => array(
                        'register_protective' => 'high',
                    ),
                ),
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

        $this->biz['biz_captcha'] = $captcha;

        $result = $limiter->handle($request);

        $settingService->shouldHaveReceived('get')->times(1);
        $captcha->shouldHaveReceived('check')->times(1);
        $request->shouldHaveReceived('getClientIp')->times(2);
        $this->assertNull($result);
    }
}
