<?php

namespace Tests\Unit\AppBundle\Component\RateLimit;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\RateLimit\UgcReportRateLimiter;
use Biz\BaseTestCase;

class UgcReportRateLimiterTest extends BaseTestCase
{
    public function testHandle()
    {
        $limiter = new UgcReportRateLimiter($this->biz);

        $request = $this->mockRequest([]);
        $result = $limiter->handle($request);
        $this->assertEquals(UgcReportRateLimiter::USER_MAX_ALLOW_ATTEMPT_ONE_DAY, $result);
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
