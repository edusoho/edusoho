<?php

namespace Tests\Unit\Component\RateLimit;

use Biz\BaseTestCase;
use AppBundle\Component\RateLimit\SmsRateLimiter;
use Biz\Common\BizCaptcha;

class SmsRateLimiterTest extends BaseTestCase
{
    public function testHandleWithMiddleSecurity()
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
}
