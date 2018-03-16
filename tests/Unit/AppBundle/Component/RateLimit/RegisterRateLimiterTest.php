<?php

namespace Tests\Unit\AppBundle\Component\RateLimit;

use Biz\BaseTestCase;
use AppBundle\Component\RateLimit\RegisterRateLimiter;
use Biz\Common\BizCaptcha;
use AppBundle\Controller\OAuth2\OAuthUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;

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

    public function testHandleWithLowAndFristRegistSecurity()
    {
        $limiter = new RegisterRateLimiter($this->biz);

        $request = new Request(
            array(),
            array(
                'captchaToken' => 'kuozhi',
                'phrase' => 'password',
            )
        );

        $this->setOauthUser($request, true);

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

        $result = $limiter->handle($request);

        $settingService->shouldHaveReceived('get')->times(1);
        $this->assertNull($result);
    }

    public function testHandleWithLowAndNotFirstRegistSecurity()
    {
        $limiter = new RegisterRateLimiter($this->biz);

        $request = new Request(
            array(),
            array(
                'captchaToken' => 'kuozhi',
                'phrase' => 'password',
            )
        );

        $this->setOauthUser($request, false);

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

    public function testHandleWithMiddleAndFristRegistSecurity()
    {
        $limiter = new RegisterRateLimiter($this->biz);
        $request = new Request(
            array(),
            array(
                'captchaToken' => 'kuozhi',
                'phrase' => 'password',
            ),
            array(),
            array(),
            array(),
            array('REMOTE_ADDR' => '128.2.2.1')
        );

        $this->setOauthUser($request, true);

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

        $result = $limiter->handle($request);

        $settingService->shouldHaveReceived('get')->times(1);
        $this->assertNull($result);
    }

    public function testHandleWithMiddleAndNotFristRegistSecurity()
    {
        $limiter = new RegisterRateLimiter($this->biz);
        $request = new Request(
            array(),
            array(
                'captchaToken' => 'kuozhi',
                'phrase' => 'password',
            ),
            array(),
            array(),
            array(),
            array('REMOTE_ADDR' => '128.2.2.1')
        );

        $this->setOauthUser($request, false);

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

    private function setOauthUser($request, $isFirst)
    {
        $oauthUser = new OAuthUser();
        $oauthUser->captchaEnabled = !(true == $isFirst);
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        $request->getSession()->set(OAuthUser::SESSION_KEY, $oauthUser);
    }
}
