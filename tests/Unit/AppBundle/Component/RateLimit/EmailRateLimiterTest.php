<?php

namespace Tests\Unit\AppBundle\Component\RateLimit;

use Biz\BaseTestCase;
use AppBundle\Component\RateLimit\SmsRateLimiter;
use AppBundle\Common\ReflectionUtils;

class EmailRateLimiterTest extends BaseTestCase
{
    public function testHandle()
    {
        $limiter = new SmsRateLimiter($this->biz);

        $request = $this->mockRequest(
            array(
                'request' => array(
                    'dragCaptchaToken' => 'kuozhi',
                ),
                'getClientIp' => '128.2.2.1',
            )
        );

        $result = $limiter->handle($request);
        $request->shouldHaveReceived('getClientIp')->times(1);
        $this->assertNull($result);
    }

    public function testCreateEmailMaxRequestOccurException()
    {
        $limiter = new SmsRateLimiter($this->biz);
        $exception = ReflectionUtils::invokeMethod($limiter, 'createEmailMaxRequestOccurException');
        $this->assertEquals(
            'AppBundle\Component\RateLimit\RateLimitException',
            get_class($exception)
        );
    }
}
