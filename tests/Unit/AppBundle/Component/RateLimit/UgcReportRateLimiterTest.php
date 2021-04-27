<?php

namespace Tests\Unit\AppBundle\Component\RateLimit;

use Biz\BaseTestCase;
use AppBundle\Component\RateLimit\UgcReportRateLimiter;
use AppBundle\Common\ReflectionUtils;

class UgcReportRateLimiterTest extends BaseTestCase
{
    public function testHandle()
    {
        $limiter = new UgcReportRateLimiter($this->biz);

        $request = $this->mockRequest([]);
        $result = $limiter->handle($request);
        $this->assertNull($result);
    }

    public function testCreateUgcReportMaxRequestOccurException()
    {
        $limiter = new UgcReportRateLimiter($this->biz);
        $exception = ReflectionUtils::invokeMethod($limiter, 'createUgcReportMaxRequestOccurException');
        $this->assertEquals(
            'AppBundle\Component\RateLimit\RateLimitException',
            get_class($exception)
        );
    }
}
