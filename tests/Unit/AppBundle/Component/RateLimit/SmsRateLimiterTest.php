<?php

namespace Tests\Unit\AppBundle\Component\RateLimit;

use Biz\BaseTestCase;
use AppBundle\Component\RateLimit\SmsRateLimiter;
use Biz\Common\BizCaptcha;
use AppBundle\Common\ReflectionUtils;

class SmsRateLimiterTest extends BaseTestCase
{
    public function testHandle()
    {
        $limiter = new SmsRateLimiter($this->biz);
        $request = $this->mockRequest(
            array(
                'request' => array(
                    'captchaToken' => 'kuozhi',
                    'phrase' => 'password',
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

        $this->biz['biz_captcha'] = $captcha;

        $result = $limiter->handle($request);

        $captcha->shouldHaveReceived('check')->times(1);
        $request->shouldHaveReceived('getClientIp')->times(1);
        $this->assertNull($result);
    }

    public function testCreateMaxRequestOccurException()
    {
        $limiter = new SmsRateLimiter($this->biz);
        $exception = ReflectionUtils::invokeMethod($limiter, 'createMaxRequestOccurException');
        $this->assertEquals(
            'Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException',
            get_class($exception)
        );
    }

    public function testCreateCaptchaOccurException()
    {
        $limiter = new SmsRateLimiter($this->biz);
        $exception = ReflectionUtils::invokeMethod($limiter, 'createCaptchaOccurException');
        $this->assertEquals(
            'Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException',
            get_class($exception)
        );
    }

    public function testSetBiz()
    {
        $limiter = new SmsRateLimiter($this->biz);
        $limiter->setBiz($this->biz);
        $result = ReflectionUtils::getProperty($limiter, 'biz');
        $this->assertEquals('Codeages\Biz\Framework\Context\Biz', get_class($result));
    }
}
